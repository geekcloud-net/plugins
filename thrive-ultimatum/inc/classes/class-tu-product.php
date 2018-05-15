<?php

class TU_Product extends TVE_Dash_Product_Abstract {
	protected $tag = 'tu';

	protected $title = 'Thrive Ultimatum';

	protected $productIds = array();

	protected $type = 'plugin';

	public function __construct( $data = array() ) {
		parent::__construct( $data );

		$this->logoUrl = TVE_Ult_Const::plugin_url( 'admin/img/logo_90x90.png' );
		$this->logoUrlWhite = TVE_Ult_Const::plugin_url( 'admin/img/logo_90x90-white.png' );

		$this->description = __( 'Ultimate scarcity plugin for WordPress', TVE_Ult_Const::T );

		$this->button = array(
			'active' => true,
			'url'    => admin_url( 'admin.php?page=tve_ult_dashboard' ),
			'label'  => __( 'Ultimatum Dashboard', TVE_Ult_Const::T ),
		);

		$this->moreLinks = array(
			'support' => array(
				'class'      => '',
				'icon_class' => 'tvd-icon-life-bouy',
				'href'       => 'https://thrivethemes.com/forums/forum/plugins/thrive-ultimatum/',
				'target'     => '_blank',
				'text'       => __( 'Support', TVE_Ult_Const::T ),
			),
		);
	}
}
