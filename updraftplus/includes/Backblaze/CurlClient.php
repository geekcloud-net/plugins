<?php
// @codingStandardsIgnoreFile

if (!defined('UPDRAFTPLUS_DIR')) die('No direct access allowed');

class UpdraftPlus_Backblaze_CurlClient {

	protected $accountId;
	protected $applicationKey;
	protected $credentials;

	protected $authToken;
	protected $apiUrl;
	protected $downloadUrl;

	public function __construct($accountId, $applicationKey, array $options = array()) {
		$this->accountId = $accountId;
		$this->applicationKey = $applicationKey;
		$this->authorizeAccount();
	}

	protected function request($method = 'GET', $uri = '', array $options = array(), $asJson = true) {
		$session = curl_init($uri);

		$headers = array();

		if (isset($options['auth'])) {
			$headers[] = 'Authorization: Basic ' . base64_encode($this->accountId . ':' . $this->applicationKey);
		}

		if (isset($options['headers'])) {
			foreach ($options['headers'] as $key => $header) {
				$headers[] = $key . ': ' . $header;
			}
		}

		if ('GET' == $method) {

			curl_setopt($session, CURLOPT_HTTPGET, true);

		} else {

			$data = array();

			if (isset($options['json'])) {

				$headers[] = "Accept: application/json";
				$data	  = json_encode($options['json']);

			}

			if (isset($options['body'])) {

				$data = $options['body'];

			}

			curl_setopt($session, CURLOPT_POSTFIELDS, $data);
			curl_setopt($session, CURLOPT_POST, true);
		}

		curl_setopt($session, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

		if (isset($options['sink'])) {
			$sink = fopen($options['sink'], 'w+');
			curl_setopt($session, CURLOPT_FILE, $sink);
			curl_setopt($session, CURLOPT_FOLLOWLOCATION, true);
		}

		$response = curl_exec($session);

		$decode_response = json_decode($response, true);

		if (isset($decode_response['status']) && 200 !== $decode_response['status']) {

			throw new Exception($decode_response['message'], $decode_response['status']);

		}

		curl_close($session);

		if (!empty($sink)) @fclose($sink);

		if ($asJson) return $decode_response;

		return $response;
	}

	public function uploadLargeStart($options) {
		// Request body parameters
		if ('/' === substr($options['FileName'], 0, 1)) {
			$options['FileName'] = ltrim($options['FileName'], '/');
		}

		if (!isset($options['BucketId']) && isset($options['BucketName'])) {
			$options['BucketId'] = $this->getBucketIdFromName($options['BucketName']);
		}

		if (!isset($options['FileContentType'])) {
			$options['FileContentType'] = 'b2/x-auto';
		}

		// Request start large file upload
		$response = $this->request('POST', $this->apiUrl . '/b2_start_large_file', array(
			'headers' => array(
				'Authorization' => $this->authToken,
			),
			'json'	=> array(
				'bucketId'	=> $options['BucketId'],
				'fileName'	=> $options['FileName'],
				'contentType' => $options['FileContentType'],
			),
		));

		/*
		 * fileId
		 * fileName
		 * accountId
		 * bucketId
		 * contentType
		 * fileInfo
		 * uploadTimestamp
		 */
		return $response;
	}

	public function uploadLargeUrl($options) {
		if (!isset($options['FileId'])) {
			throw new Exception('FileId required');
		}

		$response = $this->request('POST', $this->apiUrl . '/b2_get_upload_part_url', array(
			'headers' => array(
				'Authorization' => $this->authToken,
			),
			'json'	=> array(
				'fileId' => $options['FileId'],
			),
		));

		/*
		 * authorizationToken
		 * fileId
		 * uploadUrl
		 */
		return $response;
	}

	public function uploadLargePart($options) {

		if (!isset($options['AuthorizationToken'])) {
			throw new Exception('AuthorizationToken required');
		}

		if (!isset($options['FilePartNo'])) {
			throw new Exception('FilePartNo required');
		}

		if (!isset($options['UploadUrl'])) {
			throw new Exception('UploadUrl required');
		}

		if (!isset($options['Body'])) {
			throw new Exception('Body required');
		}

		if (is_resource($options['Body'])) {
			// We need to calculate the file's hash incrementally from the stream.
			$context = hash_init('sha1');
			hash_update_stream($context, $options['Body']);
			$hash = hash_final($context);

			// Similarly, we have to use fstat to get the size of the stream.
			$fstat = fstat($options['Body']);
			$size  = $fstat['size'];

			// Rewind the stream before passing it to the HTTP client.
			rewind($options['Body']);
		} else {
			// We've been given a simple string body, it's super simple to calculate the hash and size.
			$hash = sha1($options['Body']);
			$size = mb_strlen($options['Body'], '8bit');
		}

		$response = $this->request('POST', $options['UploadUrl'], array(
			'headers' => array(
				'Authorization'	 => $options['AuthorizationToken'],
				'X-Bz-Part-Number'  => $options['FilePartNo'],
				'Content-Length'	=> $size,
				'X-Bz-Content-Sha1' => $hash,
			),
			'body'	=> $options['Body'],
		));

		/*
		 * fileId
		 * partNumber
		 * contentLength
		 * contentSha1
		 */
		return $response;
	}

	public function uploadLargeFinish($options) {

		if (!isset($options['FileId'])) {
			throw new Exception('FileId required');
		}

		if (!isset($options['FilePartSha1Array'])) {
			throw new Exception('FilePartSha1Array required');
		}

		if (!is_array($options['FilePartSha1Array'])) {
			throw new Exception("FilePartSha1Array must be an array");

		}

		$response = $this->request('POST', $this->apiUrl . '/b2_finish_large_file', array(
			'headers' => array(
				'Authorization' => $this->authToken,
			),
			'json'	=> array(
				'fileId'		=> $options['FileId'],
				'partSha1Array' => $options['FilePartSha1Array'],
			),
		));

		if (empty($response['contentLength'])) {
			throw new Exception('B2: uploadLargeFinish error: contentLength returned was empty ('.serialize($response).')');
		}
		
		return new UpdraftPlus_Backblaze_File(
			$response['fileId'],
			$response['fileName'],
			$response['contentSha1'],
			$response['contentLength'],
			$response['contentType'],
			$response['fileInfo']
		);
	}

	protected function authorizeAccount() {
		$response = $this->request("GET", 'https://api.backblazeb2.com/b2api/v1/b2_authorize_account', array(
			'auth' => array($this->accountId, $this->applicationKey),
		));

		$this->authToken   = $response['authorizationToken'];
		$this->apiUrl	  = $response['apiUrl'] . '/b2api/v1';
		$this->downloadUrl = $response['downloadUrl'];
	}

	public function listBuckets() {
		$buckets = array();

		$response = $this->request('POST', $this->apiUrl . '/b2_list_buckets', array(
			'headers' => array(
				'Authorization' => $this->authToken,
			),
			'json'	=> array(
				'accountId' => $this->accountId,
			),
		));

		if (!isset($response['buckets'])) throw new Exception('Failed to list buckets: '.serialize($response));
		
		foreach ($response['buckets'] as $bucket) {
			$buckets[] = new UpdraftPlus_Backblaze_Bucket($bucket['bucketId'], $bucket['bucketName'], $bucket['bucketType']);
		}

		
		return $buckets;
	}

	protected function getBucketIdFromName($name) {
		$buckets = $this->listBuckets();

		foreach ($buckets as $bucket) {
			if ($bucket->getName() === $name) {
				return $bucket->getId();
			}
		}

		return null;
	}

	protected function getBucketNameFromId($id) {
		$buckets = $this->listBuckets();

		foreach ($buckets as $bucket) {
			if ($bucket->getId() === $id) {
				return $bucket->getName();
			}
		}

		return null;
	}

	protected function getFileIdFromBucketAndFileName($bucketName, $fileName) {
		$files = $this->listFiles(array(
			'BucketName' => $bucketName,
			'FileName'   => $fileName,
		));

		foreach ($files as $file) {
			if ($file->getName() === $fileName) {
				return $file->getId();
			}
		}

		return null;
	}

	public function listFiles($options) {
		// if FileName is set, we only attempt to retrieve information about that single file.
		$fileName = !empty($options['FileName']) ? $options['FileName'] : null;

		$nextFileName = null;
		$maxFileCount = 1000;
		$files		= array();

		if (!isset($options['BucketId']) && isset($options['BucketName'])) {
			$options['BucketId'] = $this->getBucketIdFromName($options['BucketName']);
		}

		if ($fileName) {
			$nextFileName = $fileName;
			$maxFileCount = 1;
		}

		$json = array(
			'bucketId'	  => $options['BucketId'],
			'startFileName' => $nextFileName,
			'maxFileCount'  => $maxFileCount,
		);
		
		if (!empty($options['Prefix'])) $json['prefix'] = $options['Prefix'];
		
		// B2 returns, at most, 1000 files per "page". Loop through the pages and compile an array of File objects.
		while (true) {
			$response = $this->request('POST', $this->apiUrl . '/b2_list_file_names', array(
				'headers' => array(
					'Authorization' => $this->authToken,
				),
				'json'	=> $json
			));

			if (!isset($response['files'])) throw new Exception('Failed to list files. '.serialize($files));
			
			foreach ($response['files'] as $file) {
				// if we have a file name set, only retrieve information if the file name matches
				if (!$fileName || ($fileName === $file['fileName'])) {
					$files[] = new UpdraftPlus_Backblaze_File($file['fileId'], $file['fileName'], null, $file['size']);
				}
			}

			if ($fileName || $response['nextFileName'] === null) {
				// We've got all the files - break out of loop.
				break;
			}

			$nextFileName = $response['nextFileName'];
		}

		return $files;
	}

	public function upload($options) {
		// Clean the path if it starts with /.
		if (substr($options['FileName'], 0, 1) === '/') {
			$options['FileName'] = ltrim($options['FileName'], '/');
		}

		if (!isset($options['BucketId']) && isset($options['BucketName'])) {
			$options['BucketId'] = $this->getBucketIdFromName($options['BucketName']);
		}

		// Retrieve the URL that we should be uploading to.
		$response = $this->request('POST', $this->apiUrl . '/b2_get_upload_url', array(
			'headers' => array(
				'Authorization' => $this->authToken,
			),
			'json'	=> array(
				'bucketId' => $options['BucketId'],
			),
		));

		$uploadEndpoint  = $response['uploadUrl'];
		$uploadAuthToken = $response['authorizationToken'];

		if (is_resource($options['Body'])) {
			// We need to calculate the file's hash incrementally from the stream.
			$context = hash_init('sha1');
			hash_update_stream($context, $options['Body']);
			$hash = hash_final($context);

			// Similarly, we have to use fstat to get the size of the stream.
			$fstat = fstat($options['Body']);
			$size  = $fstat['size'];

			// Rewind the stream before passing it to the HTTP client.
			rewind($options['Body']);
		} else {
			// We've been given a simple string body, it's super simple to calculate the hash and size.
			$hash = sha1($options['Body']);
			$size = mb_strlen($options['Body'], '8bit');
		}

		if (!isset($options['FileLastModified'])) {
			$options['FileLastModified'] = round(microtime(true) * 1000);
		}

		if (!isset($options['FileContentType'])) {
			$options['FileContentType'] = 'b2/x-auto';
		}

		$response = $this->request('POST', $uploadEndpoint, array(
			'headers' => array(
				'Authorization'					  => $uploadAuthToken,
				'Content-Type'					   => $options['FileContentType'],
				'Content-Length'					 => $size,
				'X-Bz-File-Name'					 => $options['FileName'],
				'X-Bz-Content-Sha1'				  => $hash,
				'X-Bz-Info-src_last_modified_millis' => $options['FileLastModified'],
			),
			'body'	=> $options['Body'],
		));

		return new UpdraftPlus_Backblaze_File(
			$response['fileId'],
			$response['fileName'],
			$response['contentSha1'],
			$response['contentLength'],
			$response['contentType'],
			$response['fileInfo']
		);
	}

	public function download($options) {
		$requestUrl	 = null;
		$requestOptions = array(
			'headers' => array(
				'Authorization' => $this->authToken,
			),
			'sink'	=> isset($options['SaveAs']) ? $options['SaveAs'] : null,
		);

		if (isset($options['FileId'])) {
			$requestOptions['query'] = array('fileId' => $options['FileId']);
			$requestUrl			  = $this->downloadUrl . '/b2api/v1/b2_download_file_by_id';
		} else {
			if (!isset($options['BucketName']) && isset($options['BucketId'])) {
				$options['BucketName'] = $this->getBucketNameFromId($options['BucketId']);
			}

			$requestUrl = sprintf('%s/file/%s/%s', $this->downloadUrl, $options['BucketName'], $options['FileName']);
		}
		
		if (isset($options['headers'])) {
			$requestOptions['headers'] = array_merge($requestOptions['headers'], $options['headers']);
		}

		$response = $this->request('GET', $requestUrl, $requestOptions, false);

		return isset($options['SaveAs']) ? true : $response;
	}

	public function getFile($options) {
		if (!isset($options['FileId']) && isset($options['BucketName']) && isset($options['FileName'])) {
			$options['FileId'] = $this->getFileIdFromBucketAndFileName($options['BucketName'], $options['FileName']);

			if (!$options['FileId']) {
				throw new UpdraftPlus_Backblaze_NotFoundException();
			}
		}

		$response = $this->request('POST', $this->apiUrl . '/b2_get_file_info', array(
			'headers' => array(
				'Authorization' => $this->authToken,
			),
			'json'	=> array(
				'fileId' => $options['FileId'],
			),
		));

		return new UpdraftPlus_Backblaze_File(
			$response['fileId'],
			$response['fileName'],
			$response['contentSha1'],
			$response['contentLength'],
			$response['contentType'],
			$response['fileInfo'],
			$response['bucketId'],
			$response['action'],
			$response['uploadTimestamp']
		);
	}

	public function deleteFile($options) {
		if (!isset($options['FileName'])) {
			$file = $this->getFile($options);

			$options['FileName'] = $file->getName();
		}

		if (!isset($options['FileId']) && isset($options['BucketName']) && isset($options['FileName'])) {
			$file = $this->getFile($options);

			$options['FileId'] = $file->getId();
		}

		$delete_result = $this->request('POST', $this->apiUrl . '/b2_delete_file_version', array(
			'headers' => array(
				'Authorization' => $this->authToken,
			),
			'json'	=> array(
				'fileName' => $options['FileName'],
				'fileId'   => $options['FileId'],
			),
		));

		return (is_array($delete_result) && !empty($delete_result['fileId'])) ? true : false;
	}
	
	/**
	 * Create a private bucket with the given name.
	 *
	 * @param string $bucket_name - valid bucket name
	 * @throws Exception
	 *
	 * @return boolean - If bucket created successfully, it returns true otherwise false.
	 */
    public function createPrivateBucket($bucket_name) {
		try {
			$response = $this->request('POST', $this->apiUrl.'/b2_create_bucket',
				array(
					'headers' => array(
						'Authorization' => $this->authToken,
					),
					'json' => array(
						'accountId' => $this->accountId,
						'bucketName' => $bucket_name,
						'bucketType' => 'allPrivate'
					)
				)
			);
		} catch (Exception $e) {
			if (400 == $e->getCode()) {
				throw new Exception("Bucket can't be created because Bucket name is already in use.", $e->getCode());
			} else {
				throw $e;	
			}
			return false;
		}
		if (isset($response['bucketId']) && isset($response['bucketName']) && isset($response['bucketType'])) {
			return true;
		}
		return false;
    }	

}

final class UpdraftPlus_Backblaze_Bucket {
	const TYPE_PUBLIC  = 'allPublic';
	const TYPE_PRIVATE = 'allPrivate';

	protected $id;
	protected $name;
	protected $type;

	/**
	 * Bucket constructor.
	 *
	 * @param $id
	 * @param $name
	 * @param $type
	 */
	public function __construct($id, $name, $type) {
		$this->id   = $id;
		$this->name = $name;
		$this->type = $type;
	}

	public function getId() {
		return $this->id;
	}

	public function getName() {
		return $this->name;
	}

	public function getType() {
		return $this->type;
	}
}

final class UpdraftPlus_Backblaze_File {
	protected $id;
	protected $name;
	protected $hash;
	protected $size;
	protected $type;
	protected $info;
	protected $bucketId;
	protected $action;
	protected $uploadTimestamp;

	/**
	 * File constructor.
	 *
	 * @param $id
	 * @param $name
	 * @param $hash
	 * @param $size
	 * @param $type
	 * @param $info
	 * @param $bucketId
	 * @param $action
	 * @param $uploadTimestamp
	 */
	public function __construct($id, $name, $hash = null, $size = null, $type = null, $info = null, $bucketId = null, $action = null, $uploadTimestamp = null) {
		$this->id			  = $id;
		$this->name			= $name;
		$this->hash			= $hash;
		$this->size			= $size;
		$this->type			= $type;
		$this->info			= $info;
		$this->bucketId		= $bucketId;
		$this->action		  = $action;
		$this->uploadTimestamp = $uploadTimestamp;
	}

	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getHash() {
		return $this->hash;
	}

	/**
	 * @return int
	 */
	public function getSize() {
		return $this->size;
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @return array
	 */
	public function getInfo()
	{
		return $this->info;
	}

	/**
	 * @return string
	 */
	public function getBucketId() {
		return $this->bucketId;
	}

	/**
	 * @return string
	 */
	public function getAction() {
		return $this->action;
	}

	/**
	 * @return string
	 */
	public function getUploadTimestamp() {
		return $this->uploadTimestamp;
	}
}

class UpdraftPlus_Backblaze_NotFoundException extends Exception {
}
