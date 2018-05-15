<?php

/**
 * Class Tab
 * Basic implementation of a tab
 */
abstract class Thrive_Ult_Tab implements Thrive_Ult_Tab_Interface {
	/**
	 * @var array of items from the wordpress database
	 */
	protected $items = array();

	/**
	 * @var array of Options build based on the items
	 */
	public $options = array();

	/**
	 * @var array "Select All, Select None, etc"
	 */
	public $actions = array();

	/**
	 * @var array
	 */
	public $filters = array();

	/**
	 * @var int campaign post ID identifier
	 */
	protected $campaign_id;

	/**
	 * @var Thrive_Ult_Campaign_Options saved by user
	 */
	protected $savedOptions;

	/**
	 * @var string tab identifier
	 */
	public $identifier;

	/**
	 * @var string tab label
	 */
	public $label;

	/**
	 * @var string hanger identifier
	 */
	protected $hanger;

	public function __construct() {
		$this->actions = $this->getActions();
	}

	/**
	 * @return array of Option models
	 */
	final public function getOptions() {
		if ( empty( $this->options ) ) {
			$this->matchItems();
		}

		return $this->options;
	}

	/**
	 * Get the items from database once
	 * If the $items is not initialized initItems is called which is an abstract method
	 * and has to be implemented for each tab
	 * @return array
	 */
	protected function getItems() {
		if ( empty( $this->items ) ) {
			$this->initItems();
		}

		return $this->items;
	}

	/**
	 * Read the options from the database once
	 * Init GroupOptions with json strings
	 * @return Thrive_Ult_Campaign_Options
	 */
	protected function getSavedOptions() {
		if ( $this->savedOptions ) {
			return $this->savedOptions;
		}

		$campaign_idOptions = new Thrive_Ult_Campaign_Options( $this->getGroup() );
		$campaign_idOptions->initOptions();
		$this->savedOptions = $campaign_idOptions;

		return $campaign_idOptions;
	}

	public function setSavedOptions( Thrive_Ult_Campaign_Options $savedOptions ) {
		$this->savedOptions = $savedOptions;

		return $this;
	}

	/**
	 * Overwrite this method to set a specific list of actions
	 * @return array of Action
	 */
	public function getActions() {
		return array(
			new Thrive_Ult_Action( 'selectAll tvd-btn-flat-primary', '', 'Select All' ),
			new Thrive_Ult_Action( 'selectNone tvd-btn-flat-secondary', '', 'Select None' )
		);
	}

	/**
	 * Overwrite this method for specific list of filters
	 * @return array empty
	 */
	public function getFilters() {
		return $this->filters;
	}

	/**
	 * Set the items outside the box
	 *
	 * @param array $items
	 *
	 * @return $this
	 */
	public function setItems( Array $items ) {
		$this->items = $items;

		return $this;
	}

	/**
	 * Can be called outside the box
	 * @return $this
	 */
	public function initFilters() {
		$this->filters = $this->getFilters();

		return $this;
	}

	/**
	 * @param string $campaign_id
	 *
	 * @return $this
	 */
	public function setGroup( $campaign_id ) {
		$this->campaign_id = $campaign_id;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getGroup() {
		return $this->campaign_id;
	}

	/**
	 * @param mixed $identifier
	 *
	 * @return $this
	 */
	public function setIdentifier( $identifier ) {
		$this->identifier = $identifier;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getIdentifier() {
		return $this->identifier;
	}

	/**
	 * @param string $label
	 *
	 * @return $this;
	 */
	public function setLabel( $label ) {
		$this->label = $label;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getLabel() {
		return $this->label;
	}

	/**
	 * @param string $hanger
	 *
	 * @return $this
	 */
	public function setHanger( $hanger ) {
		$this->hanger = $hanger;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getHanger() {
		return $this->hanger;
	}

	/**
	 * Callable from outside the box
	 * Init the items and options in one call
	 * @return $this
	 */
	public function initOptions() {
		$this->initItems();
		$this->matchItems();

		return $this;
	}

	protected function getSavedOptionForTab( $tabIndex, $id ) {
		$savedOptions = $this->getSavedOptions();

		$optionArr = $savedOptions->getTabSavedOptions( $tabIndex, $this->hanger );
		$tlOption  = new Thrive_Ult_Option();

		if ( empty( $optionArr ) ) {
			return $tlOption;
		}

		$hanger  = $this->hanger;
		$options = json_decode( stripcslashes( $savedOptions->$hanger ) );
		if ( ! $options ) {
			return new Thrive_Ult_Option();
		}

		$tlOption->setId( $id );
		$tlOption->setLabel( isset( $this->items[ $id ] ) ? $this->items[ $id ] : '' );
		$tlOption->setIsChecked( in_array( $id, $optionArr ) );

		return $tlOption;
	}

	/**
	 * Specific tab has to implement this function which transforms
	 * items(pages, posts, post types) into Option models
	 * @return void
	 */
	abstract protected function matchItems();

	/**
	 * Has to get the Option from json string based on the $item
	 *
	 * @param $item
	 *
	 * @return Option
	 */
	abstract protected function getSavedOption( $item );

	/**
	 * Read items from the database and initiate them
	 * @return $this
	 */
	abstract protected function initItems();

}
