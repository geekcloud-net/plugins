<?php

class WPLA_CountryHelper {


	// convert state name to two letter code 
	// the Amazon API is inconsistent and returns either a full name or a two letter code...
	static public function get_state_two_letter_code( $statename ) {

	    // remove accents #20080
        $statename = remove_accents( $statename );

		$states = self::get_states();
		if ( ! in_array( $statename, $states ) ) return $statename;

		foreach ( $states as $code => $name ) {
			if ( $name == $statename ) return $code;
		}

		return $statename;
	}

	static function get_states() {

		$states = array(
			'AL' => 'Alabama',
			'AK' => 'Alaska',
			'AZ' => 'Arizona',
			'AR' => 'Arkansas',
			'CA' => 'California',
			'CO' => 'Colorado',
			'CT' => 'Connecticut',
			'DE' => 'Delaware',
			'DC' => 'District Of Columbia',
			'FL' => 'Florida',
			'GA' => 'Georgia',
			'HI' => 'Hawaii',
			'ID' => 'Idaho',
			'IL' => 'Illinois',
			'IN' => 'Indiana',
			'IA' => 'Iowa',
			'KS' => 'Kansas',
			'KY' => 'Kentucky',
			'LA' => 'Louisiana',
			'ME' => 'Maine',
			'MD' => 'Maryland',
			'MA' => 'Massachusetts',
			'MI' => 'Michigan',
			'MN' => 'Minnesota',
			'MS' => 'Mississippi',
			'MO' => 'Missouri',
			'MT' => 'Montana',
			'NE' => 'Nebraska',
			'NV' => 'Nevada',
			'NH' => 'New Hampshire',
			'NJ' => 'New Jersey',
			'NM' => 'New Mexico',
			'NY' => 'New York',
			'NC' => 'North Carolina',
			'ND' => 'North Dakota',
			'OH' => 'Ohio',
			'OK' => 'Oklahoma',
			'OR' => 'Oregon',
			'PA' => 'Pennsylvania',
			'RI' => 'Rhode Island',
			'SC' => 'South Carolina',
			'SD' => 'South Dakota',
			'TN' => 'Tennessee',
			'TX' => 'Texas',
			'UT' => 'Utah',
			'VT' => 'Vermont',
			'VA' => 'Virginia',
			'WA' => 'Washington',
			'WV' => 'West Virginia',
			'WI' => 'Wisconsin',
			'WY' => 'Wyoming',
			'AA' => 'Armed Forces (AA)',
			'AE' => 'Armed Forces (AE)',
			'AP' => 'Armed Forces (AP)',
			'AS' => 'American Samoa',
			'GU' => 'Guam',
			'MP' => 'Northern Mariana Islands',
			'PR' => 'Puerto Rico',
			'UM' => 'US Minor Outlying Islands',
			'VI' => 'US Virgin Islands',
		);

		return $states;
	} // get_states()

	
} // class WPLA_CountryHelper
