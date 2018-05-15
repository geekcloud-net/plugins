<?php

/**
 * Class PostTypesTab
 */
class Thrive_Ult_Post_Types_Tab extends Thrive_Ult_Tab implements Thrive_Ult_Tab_Interface {
	protected function matchItems() {
		if ( ! $this->getItems() ) {
			return array();
		}

		$optionArr = $this->getSavedOptions()->getTabSavedOptions( 5, $this->hanger );

		foreach ( $this->getItems() as $key => $item ) {
			$option = new Thrive_Ult_Option();
			$option->setLabel( $item );
			$option->setId( $item );
			$option->setIsChecked( in_array( $item, $optionArr ) );
			$this->options[] = $option;
		}
	}

	protected function getSavedOption( $item ) {
		return $this->getSavedOptionForTab( 5, $item );
	}

	/**
	 * @return $this
	 */
	protected function initItems() {
		$post_types = get_post_types( array(
			'public' => true
		) );

		$blacklist = apply_filters( 'tve_ult_settings_post_types_blacklist', array() );

		if ( is_array( $blacklist ) && ! empty( $blacklist ) ) {
			foreach ( $blacklist as $item ) {
				unset( $post_types[ $item ] );
			}
		}

		$this->setItems( $post_types );

		return $this;
	}

	/**
	 * @param $type string
	 *
	 * @return bool
	 */
	public function displayWidget( $type ) {
		$this->hanger = 'show_options';
		$showOption   = $this->getSavedOption( $type );
		$display      = $showOption->isChecked;

		if ( $display === true ) {
			$this->hanger = 'hide_options';
			$display      = ! $this->getSavedOption( $type )->isChecked;
		}

		return $display;

	}

	public function isDeniedType( $type ) {
		$this->hanger = 'hide_options';

		return $this->getSavedOption( $type )->isChecked;
	}

	public function isTypeAllowed( $type ) {
		$this->hanger = 'show_options';

		return $this->getSavedOption( $type )->isChecked;
	}

}
