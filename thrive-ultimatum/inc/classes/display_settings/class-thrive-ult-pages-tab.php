<?php

/**
 * Class PagesTab
 */
class Thrive_Ult_Pages_Tab extends Thrive_Ult_Tab implements Thrive_Ult_Tab_Interface {
	protected function matchItems() {
		if ( ! $this->getItems() ) {
			return array();
		}

		$optionArr = $this->getSavedOptions()->getTabSavedOptions( 3, $this->hanger );

		foreach ( $this->getItems() as $page ) {
			$option = new Thrive_Ult_Option();
			$option->setLabel( $page->post_title );
			$option->setId( $page->ID );
			$option->setType( 'item_page' );
			$option->setIsChecked( in_array( $page->ID, $optionArr ) );
			$this->options[] = $option;
		}
	}

	protected function getSavedOption( $item ) {
		return $this->getSavedOptionForTab( 3, $item->ID );
	}

	/**
	 * @return $this
	 */
	protected function initItems() {
		$this->setItems( get_pages( array(
			'sort_column'  => 'post_title',
			'sort_order'   => 'ASC',
			'hierarchical' => 0
		) ) );

		return $this;
	}

	/**
	 * @param $post WP_Post
	 *
	 * @return bool
	 */
	public function displayWidget( WP_Post $post ) {
		$this->hanger = 'show_options';
		$showOption   = $this->getSavedOption( $post );
		$display      = $showOption->isChecked;

		if ( $display === true ) {
			$this->hanger = 'hide_options';
			$display      = ! $this->getSavedOption( $post )->isChecked;
		}

		return $display;

	}

	public function isPageDenied( $page ) {
		$this->hanger = 'hide_options';

		return $this->getSavedOption( $page )->isChecked;
	}

	public function isPageAllowed( $page ) {
		$this->hanger = 'show_options';

		return apply_filters( 'tu_is_page_allowed', $this->getSavedOption( $page )->isChecked, $page, $this );
	}

}
