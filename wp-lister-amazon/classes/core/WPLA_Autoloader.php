<?php

class WPLA_Autoloader {

	/**
	 * class table
	 */
	protected static $class_cache = array(

		// core		
		'WPLA_Core'               => '/classes/core/WPLA_Core.php',
		'WPLA_Logger'             => '/classes/core/WPLA_Logger.php',
		'WPLA_StocksLogger'       => '/classes/core/WPLA_StocksLogger.php',
		'WPLA_AmazonLogger'       => '/classes/core/WPLA_AmazonLogger.php',
		'WPLA_MemCache'       	  => '/classes/core/WPLA_MemCache.php',
		'WPLA_Setup'              => '/classes/core/WPLA_Setup.php',
		'WPLA_Toolbar'            => '/classes/core/WPLA_Toolbar.php',
		'WPLA_AmazonAPI'          => '/classes/core/WPLA_AmazonAPI.php',
		'WPLA_API_Hooks'          => '/classes/core/WPLA_API_Hooks.php',
		'WPLA_AjaxHandler'        => '/classes/core/WPLA_AjaxHandler.php',
		'WPLA_CronActions'        => '/classes/core/WPLA_CronActions.php',
		'WPLA_AdminMessages'      => '/classes/core/WPLA_AdminMessages.php',
		// 'WPLA_Functions'          => '/classes/core/WPLA_Functions.php',

		// abstract		
		'WPLA_Model'              => '/classes/abstract/WPLA_Model.php',
		'WPLA_Page'               => '/classes/abstract/WPLA_Page.php',
		'WPLA_BasePlugin'         => '/classes/abstract/WPLA_BasePlugin.php',

		// helper
		'WPLA_ListingQueryHelper' => '/classes/helper/WPLA_ListingQueryHelper.php',
		'WPLA_FeedTemplateIndex'  => '/classes/helper/WPLA_FeedTemplateIndex.php',
		'WPLA_FeedTemplateHelper' => '/classes/helper/WPLA_FeedTemplateHelper.php',
		'WPLA_FeedDataBuilder'    => '/classes/helper/WPLA_FeedDataBuilder.php',
		'WPLA_FeedValidator'      => '/classes/helper/WPLA_FeedValidator.php',
		'WPLA_ImportHelper'       => '/classes/helper/WPLA_ImportHelper.php',
		'WPLA_OrdersImporter'     => '/classes/helper/WPLA_OrdersImporter.php',
		'WPLA_ProductsImporter'   => '/classes/helper/WPLA_ProductsImporter.php',
		'WPLA_InventoryCheck'     => '/classes/helper/WPLA_InventoryCheck.php',
		'WPLA_RepricingHelper'    => '/classes/helper/WPLA_RepricingHelper.php',
		'WPLA_SkuGenerator'       => '/classes/helper/WPLA_SkuGenerator.php',
		'WPLA_UpgradeHelper'      => '/classes/helper/WPLA_UpgradeHelper.php',
		'WPLA_AmazonWebHelper'    => '/classes/helper/WPLA_AmazonWebHelper.php',
		'WPLA_MinMaxPriceWizard'  => '/classes/helper/WPLA_MinMaxPriceWizard.php',
		'WPLA_ReportProcessor'    => '/classes/helper/WPLA_ReportProcessor.php',
		'WPLA_FbaHelper'          => '/classes/helper/WPLA_FbaHelper.php',
		'WPLA_CountryHelper'      => '/classes/helper/WPLA_CountryHelper.php',
		'WPLA_DateTimeHelper'     => '/classes/helper/WPLA_DateTimeHelper.php',
		
		// models		
		'WPLA_AmazonMarket'       => '/classes/model/AmazonMarket.php',
		'WPLA_AmazonAccount'      => '/classes/model/AmazonAccount.php',
		'WPLA_AmazonReport'       => '/classes/model/AmazonReport.php',
		'WPLA_AmazonFeed'         => '/classes/model/AmazonFeed.php',
		'WPLA_AmazonFeedTemplate' => '/classes/model/AmazonFeedTemplate.php',
		'WPLA_AmazonProfile'      => '/classes/model/AmazonProfile.php',
		'WPLA_ListingsModel'      => '/classes/model/ListingsModel.php',
		'WPLA_OrdersModel'        => '/classes/model/OrdersModel.php',
		'WPLA_JobsModel'          => '/classes/model/JobsModel.php',
		
		// tables		
		'WPLA_ListingsTable'      => '/classes/table/ListingsTable.php',
		'WPLA_OrdersTable'        => '/classes/table/OrdersTable.php',
		'WPLA_ReportsTable'       => '/classes/table/ReportsTable.php',
		'WPLA_FeedsTable'         => '/classes/table/FeedsTable.php',
		'WPLA_LogTable'           => '/classes/table/LogTable.php',
		'WPLA_AccountsTable'      => '/classes/table/AccountsTable.php',
		'WPLA_ProfilesTable'      => '/classes/table/ProfilesTable.php',
		'WPLA_RepricingTable'     => '/classes/table/RepricingTable.php',
		'WPLA_SkuGenTable'        => '/classes/table/SkuGenTable.php',
		'WPLA_StockLogTable'      => '/classes/table/StockLogTable.php',
		
		// pages		
		'WPLA_AccountsPage'       => '/classes/page/AccountsPage.php',
		'WPLA_ListingsPage'       => '/classes/page/ListingsPage.php',
		'WPLA_OrdersPage'         => '/classes/page/OrdersPage.php',
		'WPLA_ReportsPage'        => '/classes/page/ReportsPage.php',
		'WPLA_ProfilesPage'       => '/classes/page/ProfilesPage.php',
		'WPLA_FeedsPage'          => '/classes/page/FeedsPage.php',
		'WPLA_ImportPage'         => '/classes/page/ImportPage.php',
		'WPLA_SettingsPage'       => '/classes/page/SettingsPage.php',
		'WPLA_LogPage'            => '/classes/page/LogPage.php',
		'WPLA_ToolsPage'          => '/classes/page/ToolsPage.php',
		'WPLA_RepricingPage'      => '/classes/page/RepricingPage.php',
		'WPLA_SkuGenPage'         => '/classes/page/SkuGenPage.php',
		'WPLA_StockLogPage'       => '/classes/page/StockLogPage.php',
		'WPLA_HelpPage'           => '/classes/page/HelpPage.php',
		
		// integration		
		'WPLA_WooBackendIntegration'  => '/classes/integration/Woo_Backend.php',
		## BEGIN PRO ##
		'WPLA_OrderBuilder'           => '/classes/integration/Woo_OrderBuilder.php',
		## END PRO ##
		'WPLA_ProductBuilder'         => '/classes/integration/Woo_ProductBuilder.php',
		'WPLA_ProductWrapper'         => '/classes/integration/Woo_ProductWrapper.php',
		'WPLA_Product_Attributes'     => '/classes/integration/Woo_Product_Attributes.php',
		'WPLA_Product_MetaBox'        => '/classes/integration/Woo_ProductMetaBox.php',
		'WPLA_Product_Images_MetaBox' => '/classes/integration/Woo_Product_Images_MetaBox.php',
		'WPLA_Product_Feed_MetaBox'   => '/classes/integration/Woo_Product_Feed_MetaBox.php',
		'WPLA_Order_MetaBox'          => '/classes/integration/Woo_OrderMetaBox.php',
		'WC_Product_Amazon'           => '/classes/integration/Woo_AmazonProduct.php',
		'WPLA_Shipping_Method'        => '/classes/integration/WPLA_Shipping_Method.php',
		'WPLA_Shipping_Options'       => '/classes/integration/WPLA_Shipping_Options.php',

	);

	/**
	 * @param string $class_name
	 * @return string|false
	 */
	public static function autoload( $class_name ) {

		if ( array_key_exists( $class_name, self::$class_cache ) ) {
   			return include WPLA_PATH . self::$class_cache[ $class_name ];
	    } else {
	        return false;
	    }

	}

} // class WPLA_Autoloader
