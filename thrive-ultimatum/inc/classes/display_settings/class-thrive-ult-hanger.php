<?php

class Thrive_Ult_Hanger {
	public $identifier;
	public $tabs = array();
	protected $campaign_id;

	public function __construct( $identifier, $campaign_id ) {
		$this->identifier  = $identifier;
		$this->campaign_id = $campaign_id;
	}

	public function initTabs( Array $identifiers, $saved_options = null ) {
		$is_instance = $saved_options instanceof Thrive_Ult_Campaign_Options;

		foreach ( $identifiers as $identifier => $label ) {
			/** @var $tab Thrive_Ult_Tab */
			$tab = Thrive_Ult_Tab_Factory::build( $identifier );
			if ( $is_instance ) {
				$tab->setSavedOptions( $saved_options );
			}
			$tab->setGroup( $this->campaign_id )
			    ->setIdentifier( $identifier )
			    ->setLabel( $label )
			    ->setHanger( $this->identifier )
			    ->initOptions()
			    ->initFilters();

			$this->tabs[] = $tab;
		}
	}

}
