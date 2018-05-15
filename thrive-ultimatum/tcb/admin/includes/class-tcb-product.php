<?php

/**
 * Created by PhpStorm.
 * User: Danut
 * Date: 12/9/2015
 * Time: 12:21 PM
 */
class TCB_Product extends TVE_Dash_Product_Abstract {
	protected $tag = 'tcb';

	protected $title = 'Thrive Architect';

	protected $productIds = array();

	protected $type = 'plugin';

	public function __construct( $data = array() ) {
		parent::__construct( $data );

		$this->logoUrl      = tve_editor_css() . '/images/thrive-architect-logo.png';
		$this->logoUrlWhite = tve_editor_css() . '/images/thrive-architect-logo-white.png';

		$this->description = __( 'Create beautiful content & conversion optimized landing pages.', 'thrive-cb' );

		$this->button = array(
			'label'   => __( 'View Video Tutorial', 'thrive-cb' ),
			'url'     => '//fast.wistia.net/embed/iframe/4m07jw6fmj?popover=true',
			'active'  => true,
			'target'  => '_bank',
			'classes' => 'wistia-popover[height=450,playerColor=2bb914,width=800]',
		);

		$this->moreLinks = array(
			'tutorials'         => array(
				'class'      => 'tve-leads-tutorials',
				'icon_class' => 'tvd-icon-graduation-cap',
				'href'       => 'https://thrivethemes.com/thrive-architect-tutorials/',
				'target'     => '_blank',
				'text'       => __( 'Tutorials', 'thrive-cb' ),
			),
			'support'           => array(
				'class'      => 'tve-leads-tutorials',
				'icon_class' => 'tvd-icon-life-bouy',
				'href'       => 'https://thrivethemes.com/forums/forum/plugins/thrive-architect/',
				'target'     => '_blank',
				'text'       => __( 'Support', 'thrive-cb' ),
			),
			'content_templates' => array(
				'class'      => 'tve-content-templates',
				'icon_class' => 'tvd-icon-content_templates',
				'href'       => admin_url( 'admin.php?page=tcb_admin_dashboard' ),
				'target'     => '_blank',
				'text'       => __( 'Content Templates', 'thrive-cb' ),
			),
		);
	}
}
