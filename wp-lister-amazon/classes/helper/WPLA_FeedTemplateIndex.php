<?php

class WPLA_FeedTemplateIndex {
	
	static public function get_file_index() {

		// index of feed template files - sites / categories / files
		$file_index = array(
			
			// amazon.com (US)
			'US' => array(
				'site'  => 'amazon.com',
				'code'  => 'US',
				'categories' => array(

					// Baby
					'Baby' => array(
						'title' => 'Baby',
						'templates' => array(
							'Flat.File.Baby-Template.csv',
							'Flat.File.Baby-DataDefinitions.csv',	
							'Flat.File.Baby-ValidValues.csv',								
						),
						'btguides' => array(
							'baby-products_browse_tree_guide.csv',
						),
					),

					// Beauty
					'Beauty' => array(
						'title' => 'Beauty',
						'templates' => array(
							'Flat.File.Beauty-Template.csv',
							'Flat.File.Beauty-DataDefinitions.csv',	
							'Flat.File.Beauty-ValidValues.csv',								
						),
						'btguides' => array(
							'beauty_browse_tree_guide.csv',
						),
					),

					// Camera & Photo
					'CameraAndPhoto' => array(
						'title' => 'Camera & Photo',
						'templates' => array(
							'Flat.File.CameraAndPhoto-Template.csv',
							'Flat.File.CameraAndPhoto-DataDefinitions.csv',	
							'Flat.File.CameraAndPhoto-ValidValues.csv',								
						),
						'btguides' => array(
							'electronics_browse_tree_guide.csv',
						),
					),

					// Cell Phones & Accessories 
					'Wireless' => array(
						'title' => 'Cell Phones & Accessories (Wireless)',
						'templates' => array(
							'Flat.File.Wireless-Template.csv',
							'Flat.File.Wireless-DataDefinitions.csv',	
							'Flat.File.Wireless-ValidValues.csv',								
						),
						'btguides' => array(
							'cellphone-accessories_browse_tree_guide.csv',
						),
					),

					// Collectible Coins 
					'Coins' => array(
						'title' => 'Collectible Coins',
						'templates' => array(
							'Flat.File.Coins-Template.csv',
							'Flat.File.Coins-DataDefinitions.csv',	
							'Flat.File.Coins-ValidValues.csv',								
						),
						'btguides' => array(
						),
					),

					// Computers
					'Computers' => array(
						'title' => 'Computers',
						'templates' => array(
							'Flat.File.Computers-Template.csv',
							'Flat.File.Computers-DataDefinitions.csv',	
							'Flat.File.Computers-ValidValues.csv',								
						),
						'btguides' => array(
							'electronics_browse_tree_guide.csv',
						),
					),

					// Consumer Electronics
					'ConsumerElectronics' => array(
						'title' => 'Consumer Electronics',
						'templates' => array(
							'Flat.File.ConsumerElectronics-Template.csv',
							'Flat.File.ConsumerElectronics-DataDefinitions.csv',	
							'Flat.File.ConsumerElectronics-ValidValues.csv',								
						),
						'btguides' => array(
							'electronics_browse_tree_guide.csv',
						),
					),

					// Grocery & Gourmet Food
					'FoodAndBeverages' => array(
						'title' => 'Grocery & Gourmet Food',
						'templates' => array(
							'Flat.File.FoodAndBeverages-Template.csv',
							'Flat.File.FoodAndBeverages-DataDefinitions.csv',	
							'Flat.File.FoodAndBeverages-ValidValues.csv',								
						),
						'btguides' => array(
							'grocery_browse_tree_guide.csv',
						),
					),

					// Health & Personal Care
					'Health' => array(
						'title' => 'Health & Personal Care',
						'templates' => array(
							'Flat.File.Health-Template.csv',
							'Flat.File.Health-DataDefinitions.csv',	
							'Flat.File.Health-ValidValues.csv',								
						),
						'btguides' => array(
							'health_browse_tree_guide.csv',
						),
					),

					// Home & Garden
					'Home' => array(
						'title' => 'Home & Garden',
						'templates' => array(
							'Flat.File.Home-Template.csv',
							'Flat.File.Home-DataDefinitions.csv',	
							'Flat.File.Home-ValidValues.csv',								
						),
						'btguides' => array(
							'home-kitchen_browse_tree_guide.csv',
							'garden_browse_tree_guide.csv',
							'arts-and-crafts_browse_tree_guide.csv',
						),
					),

					// Music
					'Music' => array(
						'title' => 'Music',
						'templates' => array(
							'Flat.File.Music-Template.csv',
							'Flat.File.Music-DataDefinitions.csv',	
							'Flat.File.Music-ValidValues.csv',								
						),
						'btguides' => array(
						),
					),

					// Musical Instruments
					'MusicalInstruments' => array(
						'title' => 'Musical Instruments',
						'templates' => array(
							'Flat.File.MusicalInstruments-Template.csv',
							'Flat.File.MusicalInstruments-DataDefinitions.csv',	
							'Flat.File.MusicalInstruments-ValidValues.csv',								
						),
						'btguides' => array(
							'musical-instruments_browse_tree_guide.csv',
						),
					),

					// Office Products
					'Office' => array(
						'title' => 'Office Products',
						'templates' => array(
							'Flat.File.Office-Template.csv',
							'Flat.File.Office-DataDefinitions.csv',	
							'Flat.File.Office-ValidValues.csv',								
						),
						'btguides' => array(
							'office-products_browse_tree_guide.csv',
						),
					),

					// Pet Supplies
					'PetSupplies' => array(
						'title' => 'Pet Supplies',
						'templates' => array(
							'Flat.File.PetSupplies-Template.csv',
							'Flat.File.PetSupplies-DataDefinitions.csv',	
							'Flat.File.PetSupplies-ValidValues.csv',								
						),
						'btguides' => array(
							'pet-supplies_browse_tree_guide.csv',
						),
					),

					// Software & Video Games
					'SWVG' => array(
						'title' => 'Software & Video Games',
						'templates' => array(
							'Flat.File.SWVG-Template.csv',
							'Flat.File.SWVG-DataDefinitions.csv',	
							'Flat.File.SWVG-ValidValues.csv',								
						),
						'btguides' => array(
							'software_browse_tree_guide.csv',
							'videogames_browse_tree_guide.csv',
						),
					),

					// Sports & Outdoors
					'Sports' => array(
						'title' => 'Sports & Outdoors',
						'templates' => array(
							'Flat.File.Sports-Template.csv',
							'Flat.File.Sports-DataDefinitions.csv',	
							'Flat.File.Sports-ValidValues.csv',								
						),
						'btguides' => array(
							'sporting-goods_browse_tree_guide.csv',
						),
					),

					// Tools & Home Improvement
					'HomeImprovement' => array(
						'title' => 'Tools & Home Improvement',
						'templates' => array(
							'Flat.File.HomeImprovement-Template.csv',
							'Flat.File.HomeImprovement-DataDefinitions.csv',	
							'Flat.File.HomeImprovement-ValidValues.csv',								
						),
						'btguides' => array(
							'home-improvement_browse_tree_guide.csv',
						),
					),

					// Toys & Games
					'Toys' => array(
						'title' => 'Toys & Games',
						'templates' => array(
							'Flat.File.Toys-Template.csv',
							'Flat.File.Toys-DataDefinitions.csv',	
							'Flat.File.Toys-ValidValues.csv',								
						),
						'btguides' => array(
							'toys-and-games_browse_tree_guide.csv',
						),
					),

					// Video & DVD
					'Video' => array(
						'title' => 'Video & DVD',
						'templates' => array(
							'Flat.File.Video-Template.csv',
							'Flat.File.Video-DataDefinitions.csv',	
							'Flat.File.Video-ValidValues.csv',								
						),
						'btguides' => array(
						),
					),

					// BookLoader
					'BookLoader' => array(
						'title' => 'Books',
						'templates' => array(
							'Flat.File.BookLoader-Template.csv',
							'Flat.File.BookLoader-DataDefinitions.csv',	
							'Flat.File.BookLoader-ValidValues.csv',								
						),
						'btguides' => array(
						),
					),


					// // Miscellaneous (apparently broken? last updated in 2012...)
					// 'Miscellaneous' => array(
					// 	'title' => 'Miscellaneous',
					// 	'templates' => array(
					// 		'Flat.File.Miscellaneous-Template.csv',
					// 		'Flat.File.Miscellaneous-DataDefinitions.csv',	
					// 		// 'Flat.File.Miscellaneous-ValidValues.csv',  // Misc doesn't have valid values
					// 	),
					// 	'btguides' => array(
					// 	),
					// ),




					// Automotive & Powersports
					'AutoAccessory' => array(
						'title' => 'Automotive & Powersports',
						'templates' => array(
							'Flat.File.AutoAccessory-Template.csv',
							'Flat.File.AutoAccessory-DataDefinitions.csv',	
							'Flat.File.AutoAccessory-ValidValues.csv',								
							// 'Flat.File.TiresAndWheels-Template.csv',
							// 'Flat.File.TiresAndWheels-DataDefinitions.csv',	
							// 'Flat.File.TiresAndWheels-ValidValues.csv',								
						),
						'btguides' => array(
							'automotive_browse_tree_guide.csv',
						),
					),

					// Clothing, Accessories & Luggage
					'Clothing' => array(
						'title' => 'Clothing, Accessories & Luggage',
						'templates' => array(
							'Flat.File.Clothing-Template.csv',
							'Flat.File.Clothing-DataDefinitions.csv',	
							'Flat.File.Clothing-ValidValues.csv',								
						),
						'btguides' => array(
							'apparel_browse_tree_guide.csv',
						),
					),

					// Entertainment Collectibles
					'EntertainmentCollectibles' => array(
						'title' => 'Entertainment Collectibles',
						'templates' => array(
							'Flat.File.EntertainmentCollectibles-Template.csv',
							'Flat.File.EntertainmentCollectibles-DataDefinitions.csv',	
							'Flat.File.EntertainmentCollectibles-ValidValues.csv',								
						),
						'btguides' => array(
							'entertainment-collectibles_browse_tree_guide.csv',
						),
					),

					// Gift Cards
					'GiftCards' => array(
						'title' => 'Gift Cards',
						'templates' => array(
							'Flat.File.GiftCards-Template.csv',
							'Flat.File.GiftCards-DataDefinitions.csv',	
							'Flat.File.GiftCards-ValidValues.csv',								
						),
						'btguides' => array(
						),
					),

					// Jewelry
					'Jewelry' => array(
						'title' => 'Jewelry',
						'templates' => array(
							'Flat.File.Jewelry-Template.csv',
							'Flat.File.Jewelry-DataDefinitions.csv',	
							'Flat.File.Jewelry-ValidValues.csv',								
						),
						'btguides' => array(
							'jewelry_browse_tree_guide.csv',
						),
					),

					// Shoes, Handbags & Sunglasses
					'Shoes' => array(
						'title' => 'Shoes, Handbags & Sunglasses',
						'templates' => array(
							'Flat.File.Shoes-Template.csv',
							'Flat.File.Shoes-DataDefinitions.csv',	
							'Flat.File.Shoes-ValidValues.csv',								
						),
						'btguides' => array(
							'shoes_browse_tree_guide.csv',
						),
					),

					// Sports Collectibles
					'SportsMemorabilia' => array(
						'title' => 'Sports Collectibles',
						'templates' => array(
							'Flat.File.SportsMemorabilia-Template.csv',
							'Flat.File.SportsMemorabilia-DataDefinitions.csv',	
							'Flat.File.SportsMemorabilia-ValidValues.csv',								
						),
						'btguides' => array(
						),
					),

					// Watches
					'Watches' => array(
						'title' => 'Watches',
						'templates' => array(
							'Flat.File.Watches-Template.csv',
							'Flat.File.Watches-DataDefinitions.csv',	
							'Flat.File.Watches-ValidValues.csv',								
						),
						'btguides' => array(
							'watches_browse_tree_guide.csv',
						),
					),




					// Industrial & Scientific

					// Fasteners
					'MechanicalFasteners' => array(
						'title' => 'Industrial & Scientific: Fasteners',
						'templates' => array(
							'Flat.File.MechanicalFasteners-Template.csv',
							'Flat.File.MechanicalFasteners-DataDefinitions.csv',	
							'Flat.File.MechanicalFasteners-ValidValues.csv',								
						),
						'btguides' => array(
							'industrial_browse_tree_guide.csv',
						),
					),

					// Food Service, Janitorial, Sanitation, Safety
					'FoodServiceAndJanSan' => array(
						'title' => 'Industrial & Scientific: Food Service, Janitorial, Sanitation, Safety',
						'templates' => array(
							'Flat.File.FoodServiceAndJanSan-Template.csv',
							'Flat.File.FoodServiceAndJanSan-DataDefinitions.csv',	
							'Flat.File.FoodServiceAndJanSan-ValidValues.csv',								
						),
						'btguides' => array(
							'industrial_browse_tree_guide.csv',
						),
					),

					// Lab & Scientific Supplies
					'LabSupplies' => array(
						'title' => 'Industrial & Scientific: Lab & Scientific Supplies',
						'templates' => array(
							'Flat.File.LabSupplies-Template.csv',
							'Flat.File.LabSupplies-DataDefinitions.csv',	
							'Flat.File.LabSupplies-ValidValues.csv',								
						),
						'btguides' => array(
							'industrial_browse_tree_guide.csv',
						),
					),

					// Power Transmission
					'PowerTransmission' => array(
						'title' => 'Industrial & Scientific: Power Transmission',
						'templates' => array(
							'Flat.File.PowerTransmission-Template.csv',
							'Flat.File.PowerTransmission-DataDefinitions.csv',	
							'Flat.File.PowerTransmission-ValidValues.csv',								
						),
						'btguides' => array(
							'industrial_browse_tree_guide.csv',
						),
					),

					// Raw Materials
					'RawMaterials' => array(
						'title' => 'Industrial & Scientific: Raw Materials',
						'templates' => array(
							'Flat.File.RawMaterials-Template.csv',
							'Flat.File.RawMaterials-DataDefinitions.csv',	
							'Flat.File.RawMaterials-ValidValues.csv',								
						),
						'btguides' => array(
							'industrial_browse_tree_guide.csv',
						),
					),

					// Other
					'Industrial' => array(
						'title' => 'Industrial & Scientific: Other',
						'templates' => array(
							'Flat.File.Industrial-Template.csv',
							'Flat.File.Industrial-DataDefinitions.csv',	
							'Flat.File.Industrial-ValidValues.csv',								
						),
						'btguides' => array(
							'industrial_browse_tree_guide.csv',
						),
					),


					// deprecated feed templates

					// Lighting
					'Lighting' => array(
						'title' => 'Lighting (deprecated - use Tools & HI instead)',
						'templates' => array(
							'Flat.File.Lighting-Template.csv',
							'Flat.File.Lighting-DataDefinitions.csv',	
							'Flat.File.Lighting-ValidValues.csv',								
						),
						'btguides' => array(
							'home-improvement_browse_tree_guide.csv',
						),
					),

					// Outdoors
					'Outdoors' => array(
						'title' => 'Outdoors (deprecated - use Sports & Outdoors instead)',
						'templates' => array(
							'Flat.File.Outdoors-Template.csv',
							'Flat.File.Outdoors-DataDefinitions.csv',	
							'Flat.File.Outdoors-ValidValues.csv',								
						),
						'btguides' => array(
							'sporting-goods_browse_tree_guide.csv',
						),
					),


					// ListingLoader
					'ListingLoader' => array(
						'title' => 'ListingLoader',
						'templates' => array(
							'ListingLoader-Template.csv',
							'ListingLoader-DataDefinitions.csv',	
							'ListingLoader-ValidValues.csv',	
						),
						'btguides' => array(
						),
					),

				),
			), // amazon.com
			

			// amazon.co.uk (UK)
			'UK' => array(
				'site'  => 'amazon.co.uk',
				'code'  => 'UK',
				'categories' => array(

					// Automotive & Motorcycle
					'AutoAccessory' => array(
						'title' => 'Automotive & Motorcycle',
						'templates' => array(
							'Flat.File.AutoAccessory.uk-Template.csv',
							'Flat.File.AutoAccessory.uk-DataDefinitions.csv',	
							'Flat.File.AutoAccessory.uk-ValidValues.csv',								
						),
						'btguides' => array(
							'uk_automotive_browse_tree_guide.csv',
						),
					),

					// Baby
					'Baby' => array(
						'title' => 'Baby',
						'templates' => array(
							'Flat.File.Baby.uk-Template.csv',
							'Flat.File.Baby.uk-DataDefinitions.csv',	
							'Flat.File.Baby.uk-ValidValues.csv',								
						),
						'btguides' => array(
							'uk_baby-products_browse_tree_guide.csv',
						),
					),

					// Beauty
					'Beauty' => array(
						'title' => 'Beauty',
						'templates' => array(
							'Flat.File.Beauty.uk-Template.csv',
							'Flat.File.Beauty.uk-DataDefinitions.csv',	
							'Flat.File.Beauty.uk-ValidValues.csv',								
						),
						'btguides' => array(
							'uk_beauty_browse_tree_guide.csv',
						),
					),

					// BookLoader
					'BookLoader' => array(
						'title' => 'Books',
						'templates' => array(
							'Flat.File.BookLoader.uk-Template.csv',
							'Flat.File.BookLoader.uk-DataDefinitions.csv',	
							'Flat.File.BookLoader.uk-ValidValues.csv',								
						),
						'btguides' => array(
						),
					),

					// Clothing
					'Clothing' => array(
						'title' => 'Clothing',
						'templates' => array(
							'Flat.File.Clothing.uk-Template.csv',
							'Flat.File.Clothing.uk-DataDefinitions.csv',	
							'Flat.File.Clothing.uk-ValidValues.csv',								
						),
						'btguides' => array(
							'uk_apparel_browse_tree_guide.csv',
						),
					),

					// Computers
					'Computers' => array(
						'title' => 'Computers & Accessories',
						'templates' => array(
							'Flat.File.Computers.uk-Template.csv',
							'Flat.File.Computers.uk-DataDefinitions.csv',	
							'Flat.File.Computers.uk-ValidValues.csv',								
						),
						'btguides' => array(
							'uk_electronics_browse_tree_guide.csv',
						),
					),

					// Consumer Electronics
					'ConsumerElectronics' => array(
						'title' => 'Consumer Electronics',
						'templates' => array(
							'Flat.File.ConsumerElectronics.uk-Template.csv',
							'Flat.File.ConsumerElectronics.uk-DataDefinitions.csv',	
							'Flat.File.ConsumerElectronics.uk-ValidValues.csv',								
						),
						'btguides' => array(
							'uk_electronics_browse_tree_guide.csv',
						),
					),

					// Grocery & Beverages
					'FoodAndBeverages' => array(
						'title' => 'Grocery & Beverages',
						'templates' => array(
							'Flat.File.FoodAndBeverages.uk-Template.csv',
							'Flat.File.FoodAndBeverages.uk-DataDefinitions.csv',	
							'Flat.File.FoodAndBeverages.uk-ValidValues.csv',								
						),
						'btguides' => array(
							'uk_food_browse_tree_guide.csv',
						),
					),

					// Health & Personal Care
					'Health' => array(
						'title' => 'Health & Personal Care',
						'templates' => array(
							'Flat.File.Health.uk-Template.csv',
							'Flat.File.Health.uk-DataDefinitions.csv',	
							'Flat.File.Health.uk-ValidValues.csv',								
						),
						'btguides' => array(
							'uk_drugstore_browse_tree_guide.csv',
						),
					),

					// Home & Garden
					'Home' => array(
						'title' => 'Home & Garden',
						'templates' => array(
							'Flat.File.Home.uk-Template.csv',
							'Flat.File.Home.uk-DataDefinitions.csv',	
							'Flat.File.Home.uk-ValidValues.csv',								
						),
						'btguides' => array(
							'uk_home-garden_browse_tree_guide.csv',
						),
					),

					// Home Improvement
					'HomeImprovement' => array(
						'title' => 'Home Improvement',
						'templates' => array(
							'Flat.File.HomeImprovement.uk-Template.csv',
							'Flat.File.HomeImprovement.uk-DataDefinitions.csv',	
							'Flat.File.HomeImprovement.uk-ValidValues.csv',								
						),
						'btguides' => array(
							'uk_tools_browse_tree_guide.csv',
						),
					),

					// Jewelry
					'Jewelry' => array(
						'title' => 'Jewelry',
						'templates' => array(
							'Flat.File.Jewelry.uk-Template.csv',
							'Flat.File.Jewelry.uk-DataDefinitions.csv',	
							'Flat.File.Jewelry.uk-ValidValues.csv',								
						),
						'btguides' => array(
							'uk_jewelry_browse_tree_guide.csv',
						),
					),

					// Lighting
					'Lighting' => array(
						'title' => 'Lighting',
						'templates' => array(
							'Flat.File.Lighting.uk-Template.csv',
							'Flat.File.Lighting.uk-DataDefinitions.csv',	
							'Flat.File.Lighting.uk-ValidValues.csv',								
						),
						'btguides' => array(
							'uk_lighting_browse_tree_guide.csv',
						),
					),

					// Luggage
					'Luggage' => array(
						'title' => 'Luggage',
						'templates' => array(
							'Flat.File.Luggage.uk-Template.csv',
							'Flat.File.Luggage.uk-DataDefinitions.csv',	
							'Flat.File.Luggage.uk-ValidValues.csv',								
						),
						'btguides' => array(
							'uk_luggage_browse_tree_guide.csv',
						),
					),

					// Music
					'Music' => array(
						'title' => 'Music',
						'templates' => array(
							'Flat.File.Music.uk-Template.csv',
							'Flat.File.Music.uk-DataDefinitions.csv',	
							'Flat.File.Music.uk-ValidValues.csv',								
						),
						'btguides' => array(
						),
					),

					// Musical Instruments
					'MusicalInstruments' => array(
						'title' => 'Musical Instruments',
						'templates' => array(
							'Flat.File.MusicalInstruments.uk-Template.csv',
							'Flat.File.MusicalInstruments.uk-DataDefinitions.csv',	
							'Flat.File.MusicalInstruments.uk-ValidValues.csv',								
						),
						'btguides' => array(
							'uk_musical-instruments_browse_tree_guide.csv',
						),
					),

					// Office
					'Office' => array(
						'title' => 'Office',
						'templates' => array(
							'Flat.File.Office.uk-Template.csv',
							'Flat.File.Office.uk-DataDefinitions.csv',	
							'Flat.File.Office.uk-ValidValues.csv',								
						),
						'btguides' => array(
							'uk_office-products_browse_tree_guide.csv',
						),
					),

					// Pet Supplies
					'PetSupplies' => array(
						'title' => 'Pet Supplies',
						'templates' => array(
							'Flat.File.PetSupplies.uk-Template.csv',
							'Flat.File.PetSupplies.uk-DataDefinitions.csv',	
							'Flat.File.PetSupplies.uk-ValidValues.csv',								
						),
						'btguides' => array(
							'uk_pet-supplies_browse_tree_guide.csv',
						),
					),

					// Shoes & Accessories
					'Shoes' => array(
						'title' => 'Shoes & Accessories',
						'templates' => array(
							'Flat.File.Shoes.uk-Template.csv',
							'Flat.File.Shoes.uk-DataDefinitions.csv',	
							'Flat.File.Shoes.uk-ValidValues.csv',								
						),
						'btguides' => array(
							'uk_shoes_browse_tree_guide.csv',
						),
					),

					// Sex & Sensuality
					'Custom' => array(
						'title' => 'Sex & Sensuality (Custom)',
						'templates' => array(
							'Flat.File.SexSensuality.uk-Template.csv',
							'Flat.File.SexSensuality.uk-DataDefinitions.csv',	
							'Flat.File.SexSensuality.uk-ValidValues.csv',								
						),
						'btguides' => array(
							'uk_drugstore_browse_tree_guide.csv',	// provides parent category: Health & Personal Care
							'uk_adult-toys_browse_tree_guide.csv',	// provides child categories Health & Personal Care/Sex & Sensuality/*
						),
					),

					// Software & Video Games
					'SWVG' => array(
						'title' => 'Software & Video Games',
						'templates' => array(
							'Flat.File.SWVG.uk-Template.csv',
							'Flat.File.SWVG.uk-DataDefinitions.csv',	
							'Flat.File.SWVG.uk-ValidValues.csv',								
						),
						'btguides' => array(
							'uk_software_browse_tree_guide.csv',
							'uk_games_browse_tree_guide.csv',
						),
					),

					// Sports
					'Sports' => array(
						'title' => 'Sports',
						'templates' => array(
							'Flat.File.Sports.uk-Template.csv',
							'Flat.File.Sports.uk-DataDefinitions.csv',	
							'Flat.File.Sports.uk-ValidValues.csv',								
						),
						'btguides' => array(
							'uk_sports_browse_tree_guide.csv',
						),
					),

					// Sunglasses and Fashion Eyewear
					'Eyewear' => array(
						'title' => 'Sunglasses and Fashion Eyewear',
						'templates' => array(
							'Flat.File.Eyewear.uk-Template.csv',
							'Flat.File.Eyewear.uk-DataDefinitions.csv',	
							'Flat.File.Eyewear.uk-ValidValues.csv',								
						),
						'btguides' => array(
							'uk_apparel_browse_tree_guide.csv',
						),
					),

					// Toys
					'Toys' => array(
						'title' => 'Toys',
						'templates' => array(
							'Flat.File.Toys.uk-Template.csv',
							'Flat.File.Toys.uk-DataDefinitions.csv',	
							'Flat.File.Toys.uk-ValidValues.csv',								
						),
						'btguides' => array(
							'uk_kids_browse_tree_guide.csv',
						),
					),

					// Watches
					'Watches' => array(
						'title' => 'Watches',
						'templates' => array(
							'Flat.File.Watches.uk-Template.csv',
							'Flat.File.Watches.uk-DataDefinitions.csv',	
							'Flat.File.Watches.uk-ValidValues.csv',								
						),
						'btguides' => array(
							'uk_watches_browse_tree_guide.csv',
						),
					),

					// ListingLoader
					'ListingLoader' => array(
						'title' => 'ListingLoader',
						'templates' => array(
							'Flat.File.Listingloader.uk-Template.csv',
							'Flat.File.Listingloader.uk-DataDefinitions.csv',	
							'Flat.File.Listingloader.uk-ValidValues.csv',	
						),
						'btguides' => array(
						),
					),

				),
			), // amazon.co.uk
			
			
			// amazon.ca (CA)
			'CA' => array(
				'site'  => 'amazon.ca',
				'code'  => 'CA',
				'categories' => array(

					// Automotive Parts & Accessories
					'AutoAccessory' => array(
						'title' => 'Automotive Parts & Accessories',
						'templates' => array(
							'Flat.File.AutoAccessory.ca-Template.csv',
							'Flat.File.AutoAccessory.ca-DataDefinitions.csv',	
							'Flat.File.AutoAccessory.ca-ValidValues.csv',								
							'Flat.File.TiresAndWheels.ca-Template.csv',
							'Flat.File.TiresAndWheels.ca-DataDefinitions.csv',	
							'Flat.File.TiresAndWheels.ca-ValidValues.csv',								
						),
						'btguides' => array(
							'ca_automotive_browse_tree_guide.csv',
						),
					),

					// Baby
					'Baby' => array(
						'title' => 'Baby',
						'templates' => array(
							'Flat.File.Baby.ca-Template.csv',
							'Flat.File.Baby.ca-DataDefinitions.csv',	
							'Flat.File.Baby.ca-ValidValues.csv',								
						),
						'btguides' => array(
							'ca_baby-products_browse_tree_guide.csv',
						),
					),

					// Beauty
					'Beauty' => array(
						'title' => 'Beauty',
						'templates' => array(
							'Flat.File.Beauty.ca-Template.csv',
							'Flat.File.Beauty.ca-DataDefinitions.csv',	
							'Flat.File.Beauty.ca-ValidValues.csv',								
						),
						'btguides' => array(
							'ca_beauty_browse_tree_guide.csv',
						),
					),

					// BookLoader
					'BookLoader' => array(
						'title' => 'Books',
						'templates' => array(
							'Flat.File.BookLoader.ca-Template.csv',
							'Flat.File.BookLoader.ca-DataDefinitions.csv',	
							'Flat.File.BookLoader.ca-ValidValues.csv',								
						),
						'btguides' => array(
						),
					),

					// Camera & Photo
					'CameraAndPhoto' => array(
						'title' => 'Camera & Photo',
						'templates' => array(
							'Flat.File.CameraAndPhoto.ca-Template.csv',
							'Flat.File.CameraAndPhoto.ca-DataDefinitions.csv',	
							'Flat.File.CameraAndPhoto.ca-ValidValues.csv',								
						),
						'btguides' => array(
							'ca_ce_browse_tree_guide.csv',
						),
					),

					// Cell Phones
					'Wireless' => array(
						'title' => 'Cell Phones',
						'templates' => array(
							'Flat.File.Wireless.ca-Template.csv',
							'Flat.File.Wireless.ca-DataDefinitions.csv',	
							'Flat.File.Wireless.ca-ValidValues.csv',								
						),
						'btguides' => array(
							'ca_ce_browse_tree_guide.csv',
						),
					),

					// Clothing & Accessories
					'Clothing' => array(
						'title' => 'Clothing & Accessories',
						'templates' => array(
							'Flat.File.Clothing.ca-Template.csv',
							'Flat.File.Clothing.ca-DataDefinitions.csv',	
							'Flat.File.Clothing.ca-ValidValues.csv',								
						),
						'btguides' => array(
							'ca_apparel_browse_tree_guide.csv',
						),
					),

					// Computers
					'Computers' => array(
						'title' => 'Computers & Accessories',
						'templates' => array(
							'Flat.File.Computers.ca-Template.csv',
							'Flat.File.Computers.ca-DataDefinitions.csv',	
							'Flat.File.Computers.ca-ValidValues.csv',								
						),
						'btguides' => array(
							'ca_ce_browse_tree_guide.csv',
						),
					),

					// Grocery & Beverages
					'FoodAndBeverages' => array(
						'title' => 'Grocery & Beverages',
						'templates' => array(
							'Flat.File.FoodAndBeverages.ca-Template.csv',
							'Flat.File.FoodAndBeverages.ca-DataDefinitions.csv',	
							'Flat.File.FoodAndBeverages.ca-ValidValues.csv',								
						),
						'btguides' => array(
							'ca_grocery_browse_tree_guide.csv',
						),
					),

					// Health & Personal Care
					'Health' => array(
						'title' => 'Health & Personal Care',
						'templates' => array(
							'Flat.File.Health.ca-Template.csv',
							'Flat.File.Health.ca-DataDefinitions.csv',	
							'Flat.File.Health.ca-ValidValues.csv',								
						),
						'btguides' => array(
							'ca_hpc_browse_tree_guide.csv',
						),
					),

					// Home & Garden
					'Home' => array(
						'title' => 'Home & Garden',
						'templates' => array(
							'Flat.File.Home.ca-Template.csv',
							'Flat.File.Home.ca-DataDefinitions.csv',	
							'Flat.File.Home.ca-ValidValues.csv',								
						),
						'btguides' => array(
							'ca_garden_browse_tree_guide.csv',
							'ca_kitchen_browse_tree_guide.csv',
						),
					),

					// Luggage & Bags
					'Luggage' => array(
						'title' => 'Luggage & Bags',
						'templates' => array(
							'Flat.File.Luggage.ca-Template.csv',
							'Flat.File.Luggage.ca-DataDefinitions.csv',	
							'Flat.File.Luggage.ca-ValidValues.csv',								
						),
						'btguides' => array(
							'ca_luggage_browse_tree_guide.csv',
						),
					),

					// Musical Instruments
					'MusicalInstruments' => array(
						'title' => 'Musical Instruments',
						'templates' => array(
							'Flat.File.MusicalInstruments.ca-Template.csv',
							'Flat.File.MusicalInstruments.ca-DataDefinitions.csv',	
							'Flat.File.MusicalInstruments.ca-ValidValues.csv',								
						),
						'btguides' => array(
							'ca_musical-instruments_browse_tree_guide.csv',
						),
					),

					// Pet Supplies
					'PetSupplies' => array(
						'title' => 'Pet Supplies',
						'templates' => array(
							'Flat.File.PetSupplies.ca-Template.csv',
							'Flat.File.PetSupplies.ca-DataDefinitions.csv',	
							'Flat.File.PetSupplies.ca-ValidValues.csv',								
						),
						'btguides' => array(
							'ca_pet-supplies_browse_tree_guide.csv',
						),
					),

					// Software & Video Games
					'SWVG' => array(
						'title' => 'Software & Video Games',
						'templates' => array(
							'Flat.File.SWVG.ca-Template.csv',
							'Flat.File.SWVG.ca-DataDefinitions.csv',	
							'Flat.File.SWVG.ca-ValidValues.csv',								
						),
						'btguides' => array(
							'ca_software_browse_tree_guide.csv',
							'ca_videogames_browse_tree_guide.csv',
						),
					),

					// Sports & Outdoors
					'Sports' => array(
						'title' => 'Sports & Outdoors',
						'templates' => array(
							'Flat.File.Sports.ca-Template.csv',
							'Flat.File.Sports.ca-DataDefinitions.csv',	
							'Flat.File.Sports.ca-ValidValues.csv',								
						),
						'btguides' => array(
							'ca_sports_browse_tree_guide.csv',
						),
					),

					// Tools & Building Supplies
					'HomeImprovement' => array(
						'title' => 'Tools & Building Supplies',
						'templates' => array(
							'Flat.File.HomeImprovement.ca-Template.csv',
							'Flat.File.HomeImprovement.ca-DataDefinitions.csv',	
							'Flat.File.HomeImprovement.ca-ValidValues.csv',								
						),
						'btguides' => array(
							'ca_tools_browse_tree_guide.csv',
						),
					),

					// Toys
					'Toys' => array(
						'title' => 'Toys',
						'templates' => array(
							'Flat.File.Toys.ca-Template.csv',
							'Flat.File.Toys.ca-DataDefinitions.csv',	
							'Flat.File.Toys.ca-ValidValues.csv',								
						),
						'btguides' => array(
							'ca_toys_browse_tree_guide.csv',
						),
					),

					// Jewelry
					'Jewelry' => array(
						'title' => 'Jewelry',
						'templates' => array(
							'Flat.File.Jewelry.ca-Template.csv',
							'Flat.File.Jewelry.ca-DataDefinitions.csv',	
							'Flat.File.Jewelry.ca-ValidValues.csv',								
						),
						'btguides' => array(
							'ca_jewelry_browse_tree_guide.csv',
						),
					),

					// Watches
					'Watches' => array(
						'title' => 'Watches',
						'templates' => array(
							'Flat.File.Watches.ca-Template.csv',
							'Flat.File.Watches.ca-DataDefinitions.csv',	
							'Flat.File.Watches.ca-ValidValues.csv',								
						),
						'btguides' => array(
							'ca_watches_browse_tree_guide.csv',
						),
					),


					// ListingLoader
					'ListingLoader' => array(
						'title' => 'ListingLoader',
						'templates' => array(
							'ListingLoader-Template.csv',
							'ListingLoader-DataDefinitions.csv',	
							'ListingLoader-ValidValues.csv',	
						),
						'btguides' => array(
						),
					),

				),
			), // amazon.ca
			

			// amazon.com.au (AU)
			'AU' => array(
				'site'  => 'amazon.com.au',
				'code'  => 'AU',
				'categories' => array(


					// Automotive & Powersports
					'AutoAccessory' => array(
						'title' => 'Automotive & Powersports',
						'templates' => array(
							'Flat.File.AutoAccessory.au-Template.csv',
							'Flat.File.AutoAccessory.au-DataDefinitions.csv',	
							'Flat.File.AutoAccessory.au-ValidValues.csv',								
						),
						'btguides' => array(
							'au_automotive_browse_tree_guide.csv',
						),
					),

					// Baby
					'Baby' => array(
						'title' => 'Baby',
						'templates' => array(
							'Flat.File.Baby.au-Template.csv',
							'Flat.File.Baby.au-DataDefinitions.csv',	
							'Flat.File.Baby.au-ValidValues.csv',								
						),
						'btguides' => array(
							'au_baby-products_browse_tree_guide.csv',
						),
					),

					// Beauty
					'Beauty' => array(
						'title' => 'Beauty',
						'templates' => array(
							'Flat.File.Beauty.au-Template.csv',
							'Flat.File.Beauty.au-DataDefinitions.csv',	
							'Flat.File.Beauty.au-ValidValues.csv',								
						),
						'btguides' => array(
							'au_beauty_browse_tree_guide.csv',
						),
					),

					// BookLoader
					'BookLoader' => array(
						'title' => 'Books',
						'templates' => array(
							'Flat.File.BookLoader.au-Template.csv',
							'Flat.File.BookLoader.au-DataDefinitions.csv',	
							'Flat.File.BookLoader.au-ValidValues.csv',								
						),
						'btguides' => array(
						),
					),

					// Clothing, Luggage & Accessories
					'Clothing' => array(
						'title' => 'Clothing, Luggage & Accessories',
						'templates' => array(
							'Flat.File.Clothing.au-Template.csv',
							'Flat.File.Clothing.au-DataDefinitions.csv',	
							'Flat.File.Clothing.au-ValidValues.csv',								
						),
						'btguides' => array(
							'au_fashion_browse_tree_guide.csv',
						),
					),

					// Computers
					'Computers' => array(
						'title' => 'Computers & Accessories',
						'templates' => array(
							'Flat.File.Computers.au-Template.csv',
							'Flat.File.Computers.au-DataDefinitions.csv',	
							'Flat.File.Computers.au-ValidValues.csv',								
						),
						'btguides' => array(
							'au_computers_browse_tree_guide.csv',
						),
					),

					// Consumer Electronics
					'ConsumerElectronics' => array(
						'title' => 'Consumer Electronics',
						'templates' => array(
							'Flat.File.ConsumerElectronics.au-Template.csv',
							'Flat.File.ConsumerElectronics.au-DataDefinitions.csv',	
							'Flat.File.ConsumerElectronics.au-ValidValues.csv',								
						),
						'btguides' => array(
							'au_electronics_browse_tree_guide.csv',
						),
					),

					// Health & Personal Care
					'Health' => array(
						'title' => 'Health & Personal Care',
						'templates' => array(
							'Flat.File.Health.au-Template.csv',
							'Flat.File.Health.au-DataDefinitions.csv',	
							'Flat.File.Health.au-ValidValues.csv',								
						),
						'btguides' => array(
							'au_health_browse_tree_guide.csv',
						),
					),

					// Home & Kitchen
					'Home' => array(
						'title' => 'Home & Kitchen',
						'templates' => array(
							'Flat.File.Home.au-Template.csv',
							'Flat.File.Home.au-DataDefinitions.csv',	
							'Flat.File.Home.au-ValidValues.csv',								
						),
						'btguides' => array(
							'au_home_browse_tree_guide.csv',
							'au_kitchen_browse_tree_guide.csv',
						),
					),

					// Home Improvement & Tools
					'HomeImprovement' => array(
						'title' => 'Home Improvement & Tools',
						'templates' => array(
							'Flat.File.HomeImprovement.au-Template.csv',
							'Flat.File.HomeImprovement.au-DataDefinitions.csv',	
							'Flat.File.HomeImprovement.au-ValidValues.csv',								
						),
						'btguides' => array(
							'au_home-improvement_browse_tree_guide.csv',
						),
					),

					// Lighting
					'Lighting' => array(
						'title' => 'Lighting',
						'templates' => array(
							'Flat.File.Lighting.au-Template.csv',
							'Flat.File.Lighting.au-DataDefinitions.csv',	
							'Flat.File.Lighting.au-ValidValues.csv',								
						),
						'btguides' => array(
							'au_lighting_browse_tree_guide.csv',
						),
					),

					// Music
					'Music' => array(
						'title' => 'Music',
						'templates' => array(
							'Flat.File.Music.au-Template.csv',
							'Flat.File.Music.au-DataDefinitions.csv',	
							'Flat.File.Music.au-ValidValues.csv',								
						),
						'btguides' => array(
							'au_music_browse_tree_guide.csv',
						),
					),

					// Office
					'Office' => array(
						'title' => 'Office',
						'templates' => array(
							'Flat.File.Office.au-Template.csv',
							'Flat.File.Office.au-DataDefinitions.csv',	
							'Flat.File.Office.au-ValidValues.csv',								
						),
						'btguides' => array(
							'au_office-products_browse_tree_guide.csv',
						),
					),

					// Shoes
					'Shoes' => array(
						'title' => 'Shoes',
						'templates' => array(
							'Flat.File.Shoes.au-Template.csv',
							'Flat.File.Shoes.au-DataDefinitions.csv',	
							'Flat.File.Shoes.au-ValidValues.csv',								
						),
						'btguides' => array(
							'au_fashion_browse_tree_guide.csv',
						),
					),

					// Software & Video Games
					'SoftwareVideoGames' => array(
						'title' => 'Software & Video Games',
						'templates' => array(
							'Flat.File.SoftwareVideoGames.au-Template.csv',
							'Flat.File.SoftwareVideoGames.au-DataDefinitions.csv',	
							'Flat.File.SoftwareVideoGames.au-ValidValues.csv',								
						),
						'btguides' => array(
							'au_software_browse_tree_guide.csv',
							'au_videogames_browse_tree_guide.csv',
						),
					),

					// Sports
					'Sports' => array(
						'title' => 'Sports',
						'templates' => array(
							'Flat.File.Sports.au-Template.csv',
							'Flat.File.Sports.au-DataDefinitions.csv',	
							'Flat.File.Sports.au-ValidValues.csv',								
						),
						'btguides' => array(
							'au_sporting-goods_browse_tree_guide.csv',
						),
					),

					// Toys
					'Toys' => array(
						'title' => 'Toys',
						'templates' => array(
							'Flat.File.Toys.au-Template.csv',
							'Flat.File.Toys.au-DataDefinitions.csv',	
							'Flat.File.Toys.au-ValidValues.csv',								
						),
						'btguides' => array(
							'au_toys_browse_tree_guide.csv',
						),
					),

					// Watches
					'Watches' => array(
						'title' => 'Watches',
						'templates' => array(
							'Flat.File.Watches.au-Template.csv',
							'Flat.File.Watches.au-DataDefinitions.csv',	
							'Flat.File.Watches.au-ValidValues.csv',								
						),
						'btguides' => array(
							'au_fashion_browse_tree_guide.csv',
						),
					),

					// ListingLoader
					'ListingLoader' => array(
						'title' => 'ListingLoader',
						'templates' => array(
							'ListingLoader-Template.csv',
							'ListingLoader-DataDefinitions.csv',	
							'ListingLoader-ValidValues.csv',	
						),
						'btguides' => array(
						),
					),

				),
			), // amazon.com.au
			
			
			// amazon.de (DE)
			'DE' => array(
				'site'  => 'amazon.de',
				'code'  => 'DE',
				'categories' => array(

					// Baumarkt
					'HomeImprovement' => array(
						'title' => 'Baumarkt',
						'templates' => array(
							'Flat.File.HomeImprovement.de-Template.csv',
							'Flat.File.HomeImprovement.de-DataDefinitions.csv',	
							'Flat.File.HomeImprovement.de-ValidValues.csv',								
						),
						'btguides' => array(
							'de_tools-sgp_browse_tree_guide.csv',
						),
					),

					// Beauty
					'Beauty' => array(
						'title' => 'Beauty',
						'templates' => array(
							'Flat.File.Beauty.de-Template.csv',
							'Flat.File.Beauty.de-DataDefinitions.csv',	
							'Flat.File.Beauty.de-ValidValues.csv',								
						),
						'btguides' => array(
							'de_beauty_browse_tree_guide.csv',
						),
					),

					// Bekleidung & Accessories
					'Clothing' => array(
						'title' => 'Bekleidung & Accessories',
						'templates' => array(
							'Flat.File.Clothing.de-Template.csv',
							'Flat.File.Clothing.de-DataDefinitions.csv',	
							'Flat.File.Clothing.de-ValidValues.csv',								
						),
						'btguides' => array(
							'de_apparel_browse_tree_guide.csv',
						),
					),

					// Beleuchtung
					'Lighting' => array(
						'title' => 'Beleuchtung',
						'templates' => array(
							'Flat.File.Lighting.de-Template.csv',
							'Flat.File.Lighting.de-DataDefinitions.csv',	
							'Flat.File.Lighting.de-ValidValues.csv',								
						),
						'btguides' => array(
							'de_lighting_browse_tree_guide.csv',
						),
					),

					// BookLoader
					'BookLoader' => array(
						'title' => 'Bücher',
						'templates' => array(
							'Flat.File.BookLoader.de-Template.csv',
							'Flat.File.BookLoader.de-DataDefinitions.csv',	
							'Flat.File.BookLoader.de-ValidValues.csv',								
						),
						'btguides' => array(
						),
					),

					// Bürobedarf & Schreibwaren
					'Office' => array(
						'title' => 'Bürobedarf & Schreibwaren',
						'templates' => array(
							'Flat.File.Office.de-Template.csv',
							'Flat.File.Office.de-DataDefinitions.csv',	
							'Flat.File.Office.de-ValidValues.csv',								
						),
						'btguides' => array(
							'de_office-products_browse_tree_guide.csv',
						),
					),

					// Computer & Zubehör
					'Computers' => array(
						'title' => 'Computer & Zubehör',
						'templates' => array(
							'Flat.File.Computers.de-Template.csv',
							'Flat.File.Computers.de-DataDefinitions.csv',	
							'Flat.File.Computers.de-ValidValues.csv',								
						),
						'btguides' => array(
							'de_computers_browse_tree_guide.csv',
						),
					),

					// Drogerie & Körperpflege
					'Health' => array(
						'title' => 'Drogerie & Körperpflege',
						'templates' => array(
							'Flat.File.Health.de-Template.csv',
							'Flat.File.Health.de-DataDefinitions.csv',	
							'Flat.File.Health.de-ValidValues.csv',								
						),
						'btguides' => array(
							'de_drugstore_browse_tree_guide.csv',
						),
					),

					// Elektronik & Foto
					'ConsumerElectronics' => array(
						'title' => 'Elektronik & Foto',
						'templates' => array(
							'Flat.File.ConsumerElectronics.de-Template.csv',
							'Flat.File.ConsumerElectronics.de-DataDefinitions.csv',	
							'Flat.File.ConsumerElectronics.de-ValidValues.csv',								
						),
						'btguides' => array(
							'de_ce_browse_tree_guide.csv',
						),
					),

					// Haus & Garten
					'Home' => array(
						'title' => 'Haus & Garten',
						'templates' => array(
							'Flat.File.Home.de-Template.csv',
							'Flat.File.Home.de-DataDefinitions.csv',	
							'Flat.File.Home.de-ValidValues.csv',								
						),
						'btguides' => array(
							'de_garden_browse_tree_guide.csv',
							'de_kitchen_browse_tree_guide.csv',
						),
					),

					// Haushalt & Küche
					'Kitchen' => array(
						'title' => 'Haushalt & Küche',
						'templates' => array(
							'Flat.File.Kitchen.de-Template.csv',
							'Flat.File.Kitchen.de-DataDefinitions.csv',	
							'Flat.File.Kitchen.de-ValidValues.csv',								
						),
						'btguides' => array(
							'de_kitchen_browse_tree_guide.csv',
						),
					),

					// Haustierbedarf
					'PetSupplies' => array(
						'title' => 'Haustierbedarf',
						'templates' => array(
							'Flat.File.PetSupplies.de-Template.csv',
							'Flat.File.PetSupplies.de-DataDefinitions.csv',	
							'Flat.File.PetSupplies.de-ValidValues.csv',								
						),
						'btguides' => array(
							'de_pet-supplies_browse_tree_guide.csv',
						),
					),

					// Lebensmittel & Getränke
					'FoodAndBeverages' => array(
						'title' => 'Lebensmittel & Getränke',
						'templates' => array(
							'Flat.File.FoodAndBeverages.de-Template.csv',
							'Flat.File.FoodAndBeverages.de-DataDefinitions.csv',	
							'Flat.File.FoodAndBeverages.de-ValidValues.csv',								
						),
						'btguides' => array(
							'de_food_browse_tree_guide.csv',
						),
					),

					// Koffer, Rucksäcke & Taschen
					'Luggage' => array(
						'title' => 'Koffer, Rucksäcke & Taschen',
						'templates' => array(
							'Flat.File.Luggage.de-Template.csv',
							'Flat.File.Luggage.de-DataDefinitions.csv',	
							'Flat.File.Luggage.de-ValidValues.csv',								
						),
						'btguides' => array(
							'de_luggage_browse_tree_guide.csv',
						),
					),

					// Musikinstrumente
					'MusicalInstruments' => array(
						'title' => 'Musikinstrumente',
						'templates' => array(
							'Flat.File.MusicalInstruments.de-Template.csv',
							'Flat.File.MusicalInstruments.de-DataDefinitions.csv',	
							'Flat.File.MusicalInstruments.de-ValidValues.csv',								
						),
						'btguides' => array(
							'de_musical-instruments_browse_tree_guide.csv',
						),
					),

					// Schmuck
					'Jewelry' => array(
						'title' => 'Schmuck',
						'templates' => array(
							'Flat.File.Jewelry.de-Template.csv',
							'Flat.File.Jewelry.de-DataDefinitions.csv',	
							'Flat.File.Jewelry.de-ValidValues.csv',								
						),
						'btguides' => array(
							'de_jewelry_browse_tree_guide.csv',
						),
					),

					// Schuhe & Handtaschen
					'Shoes' => array(
						'title' => 'Schuhe & Handtaschen',
						'templates' => array(
							'Flat.File.Shoes.de-Template.csv',
							'Flat.File.Shoes.de-DataDefinitions.csv',	
							'Flat.File.Shoes.de-ValidValues.csv',								
						),
						'btguides' => array(
							'de_shoes_browse_tree_guide.csv',
						),
					),

					// Sonnenbrillen & modische Brillenfassungen
					'Eyewear' => array(
						'title' => 'Sonnenbrillen & modische Brillenfassungen',
						'templates' => array(
							'Flat.File.Eyewear.de-Template.csv',
							'Flat.File.Eyewear.de-DataDefinitions.csv',	
							'Flat.File.Eyewear.de-ValidValues.csv',								
						),
						'btguides' => array(
							'de_apparel_browse_tree_guide.csv',
						),
					),

					// Spielzeug & Baby
					'ToysBaby' => array(
						'title' => 'Spielzeug & Baby',
						'templates' => array(
							'Flat.File.ToysBaby.de-Template.csv',
							'Flat.File.ToysBaby.de-DataDefinitions.csv',	
							'Flat.File.ToysBaby.de-ValidValues.csv',								
						),
						'btguides' => array(
							'de_baby_browse_tree_guide.csv',
							'de_toys_browse_tree_guide.csv',
						),
					),

					// Sport & Freizeit
					'Sports' => array(
						'title' => 'Sport & Freizeit',
						'templates' => array(
							'Flat.File.Sports.de-Template.csv',
							'Flat.File.Sports.de-DataDefinitions.csv',	
							'Flat.File.Sports.de-ValidValues.csv',								
						),
						'btguides' => array(
							'de_sports_browse_tree_guide.csv',
						),
					),

					// Uhren
					'Watches' => array(
						'title' => 'Uhren',
						'templates' => array(
							'Flat.File.Watches.de-Template.csv',
							'Flat.File.Watches.de-DataDefinitions.csv',	
							'Flat.File.Watches.de-ValidValues.csv',								
						),
						'btguides' => array(
							'de_watches_browse_tree_guide.csv',
						),
					),

					// ListingLoader
					'ListingLoader' => array(
						'title' => 'ListingLoader',
						'templates' => array(
							'Flat.File.Listingloader.de-Template.csv',
							'Flat.File.Listingloader.de-DataDefinitions.csv',	
							'Flat.File.Listingloader.de-ValidValues.csv',	
						),
						'btguides' => array(
						),
					),

				),
			), // amazon.de


			// amazon.fr (FR)
			'FR' => array(
				'site'  => 'amazon.fr',
				'code'  => 'FR',
				'categories' => array(

					// Aliments et boissons
					'FoodAndBeverages' => array(
						'title' => 'Aliments et boissons',
						'templates' => array(
							'Flat.File.FoodAndBeverages.fr-Template.csv',
							'Flat.File.FoodAndBeverages.fr-DataDefinitions.csv',	
							'Flat.File.FoodAndBeverages.fr-ValidValues.csv',								
						),
						'btguides' => array(
							'fr_grocery_browse_tree_guide.csv',
						),
					),

					// Animalerie
					'PetSupplies' => array(
						'title' => 'Animalerie',
						'templates' => array(
							'Flat.File.PetSupplies.fr-Template.csv',
							'Flat.File.PetSupplies.fr-DataDefinitions.csv',	
							'Flat.File.PetSupplies.fr-ValidValues.csv',								
						),
						'btguides' => array(
							'fr_pet-supplies_browse_tree_guide.csv',
						),
					),

					// Automobile
					'AutoAccessory' => array(
						'title' => 'Automobile',
						'templates' => array(
							'Flat.File.AutoAccessory.fr-Template.csv',
							'Flat.File.AutoAccessory.fr-DataDefinitions.csv',	
							'Flat.File.AutoAccessory.fr-ValidValues.csv',								
						),
						'btguides' => array(
							'fr_automotive_browse_tree_guide.csv',
						),
					),

					// Bijoux
					'Jewelry' => array(
						'title' => 'Bijoux',
						'templates' => array(
							'Flat.File.Jewelry.fr-Template.csv',
							'Flat.File.Jewelry.fr-DataDefinitions.csv',	
							'Flat.File.Jewelry.fr-ValidValues.csv',								
						),
						'btguides' => array(
							'fr_jewelry_browse_tree_guide.csv',
						),
					),

					// Bricolage
					'HomeImprovement' => array(
						'title' => 'Bricolage',
						'templates' => array(
							'Flat.File.HomeImprovement.fr-Template.csv',
							'Flat.File.HomeImprovement.fr-DataDefinitions.csv',	
							'Flat.File.HomeImprovement.fr-ValidValues.csv',								
						),
						'btguides' => array(
							'fr_tools_browse_tree_guide.csv',
						),
					),

					// Chaussures, Sacs à Main et Maroquinerie
					'Shoes' => array(
						'title' => 'Chaussures, Sacs à Main et Maroquinerie',
						'templates' => array(
							'Flat.File.Shoes.fr-Template.csv',
							'Flat.File.Shoes.fr-DataDefinitions.csv',	
							'Flat.File.Shoes.fr-ValidValues.csv',								
						),
						'btguides' => array(
							'fr_shoes_browse_tree_guide.csv',
						),
					),

					// Fournitures Scolaires et de Bureau
					'Office' => array(
						'title' => 'Fournitures Scolaires et de Bureau',
						'templates' => array(
							'Flat.File.Office.fr-Template.csv',
							'Flat.File.Office.fr-DataDefinitions.csv',	
							'Flat.File.Office.fr-ValidValues.csv',								
						),
						'btguides' => array(
							'fr_office-products_browse_tree_guide.csv',
						),
					),

					// High-Tech
					'ConsumerElectronics' => array(
						'title' => 'High-Tech',
						'templates' => array(
							'Flat.File.ConsumerElectronics.fr-Template.csv',
							'Flat.File.ConsumerElectronics.fr-DataDefinitions.csv',	
							'Flat.File.ConsumerElectronics.fr-ValidValues.csv',								
						),
						'btguides' => array(
							'fr_electronics_browse_tree_guide.csv',
						),
					),

					// Informatique
					'Computers' => array(
						'title' => 'Informatique',
						'templates' => array(
							'Flat.File.Computers.fr-Template.csv',
							'Flat.File.Computers.fr-DataDefinitions.csv',	
							'Flat.File.Computers.fr-ValidValues.csv',								
						),
						'btguides' => array(
							'fr_computers_browse_tree_guide.csv',
						),
					),

					// Instruments de musique et Sono
					'MusicalInstruments' => array(
						'title' => 'Instruments de musique et Sono',
						'templates' => array(
							'Flat.File.MusicalInstruments.fr-Template.csv',
							'Flat.File.MusicalInstruments.fr-DataDefinitions.csv',	
							'Flat.File.MusicalInstruments.fr-ValidValues.csv',								
						),
						'btguides' => array(
							'fr_musical-instruments_browse_tree_guide.csv',
						),
					),

					// Jardin
					'LawnAndGarden' => array(
						'title' => 'Jardin',
						'templates' => array(
							'Flat.File.LawnAndGarden.fr-Template.csv',
							'Flat.File.LawnAndGarden.fr-DataDefinitions.csv',	
							'Flat.File.LawnAndGarden.fr-ValidValues.csv',								
						),
						'btguides' => array(
							'fr_garden_browse_tree_guide.csv',
						),
					),

					// Jeux et Jouets
					'ToysBaby' => array(
						'title' => 'Jeux et Jouets',
						'templates' => array(
							'Flat.File.ToysBaby.fr-Template.csv',
							'Flat.File.ToysBaby.fr-DataDefinitions.csv',	
							'Flat.File.ToysBaby.fr-ValidValues.csv',								
						),
						'btguides' => array(
							'fr_baby_browse_tree_guide.csv',
							'fr_toys_browse_tree_guide.csv',
						),
					),

					// BookLoader
					'BookLoader' => array(
						'title' => 'Livres',
						'templates' => array(
							'Flat.File.BookLoader.fr-Template.csv',
							'Flat.File.BookLoader.fr-DataDefinitions.csv',	
							'Flat.File.BookLoader.fr-ValidValues.csv',								
						),
						'btguides' => array(
						),
					),

					// Logiciels & Jeux Vidéos
					'SWVG' => array(
						'title' => 'Logiciels & Jeux Vidéos',
						'templates' => array(
							'Flat.File.SWVG.fr-Template.csv',
							'Flat.File.SWVG.fr-DataDefinitions.csv',	
							'Flat.File.SWVG.fr-ValidValues.csv',								
						),
						'btguides' => array(
							'fr_software_browse_tree_guide.csv',
							'fr_videogames_browse_tree_guide.csv',
						),
					),

					// Luminaires et Eclairage
					'Lighting' => array(
						'title' => 'Luminaires et Eclairage',
						'templates' => array(
							'Flat.File.Lighting.fr-Template.csv',
							'Flat.File.Lighting.fr-DataDefinitions.csv',	
							'Flat.File.Lighting.fr-ValidValues.csv',								
						),
						'btguides' => array(
							'fr_luminaires-eclairage_browse_tree_guide.csv',
						),
					),

					// Maison
					'Home' => array(
						'title' => 'Maison',
						'templates' => array(
							'Flat.File.Home.fr-Template.csv',
							'Flat.File.Home.fr-DataDefinitions.csv',	
							'Flat.File.Home.fr-ValidValues.csv',								
						),
						'btguides' => array(
							'fr_kitchen_browse_tree_guide.csv',
						),
					),

					// Montres
					'Watches' => array(
						'title' => 'Montres',
						'templates' => array(
							'Flat.File.Watches.fr-Template.csv',
							'Flat.File.Watches.fr-DataDefinitions.csv',	
							'Flat.File.Watches.fr-ValidValues.csv',								
						),
						'btguides' => array(
							'fr_watches_browse_tree_guide.csv',
						),
					),

					// Parfum et Beauté
					'Beauty' => array(
						'title' => 'Parfum et Beauté',
						'templates' => array(
							'Flat.File.Beauty.fr-Template.csv',
							'Flat.File.Beauty.fr-DataDefinitions.csv',	
							'Flat.File.Beauty.fr-ValidValues.csv',								
						),
						'btguides' => array(
							'fr_beauty_browse_tree_guide.csv',
						),
					),

					// Santé et Soins du corps
					'Health' => array(
						'title' => 'Santé et Soins du corps',
						'templates' => array(
							'Flat.File.Health.fr-Template.csv',
							'Flat.File.Health.fr-DataDefinitions.csv',	
							'Flat.File.Health.fr-ValidValues.csv',								
						),
						'btguides' => array(
							'fr_hpc_browse_tree_guide.csv',
						),
					),

					// Sports et Loisirs
					'Sports' => array(
						'title' => 'Sports et Loisirs',
						'templates' => array(
							'Flat.File.Sports.fr-Template.csv',
							'Flat.File.Sports.fr-DataDefinitions.csv',	
							'Flat.File.Sports.fr-ValidValues.csv',								
						),
						'btguides' => array(
							'fr_sports_browse_tree_guide.csv',
						),
					),

					// Vêtements et Accessoires
					'Clothing' => array(
						'title' => 'Vêtements et Accessoires',
						'templates' => array(
							'Flat.File.Clothing.fr-Template.csv',
							'Flat.File.Clothing.fr-DataDefinitions.csv',	
							'Flat.File.Clothing.fr-ValidValues.csv',								
						),
						'btguides' => array(
							'fr_apparel_browse_tree_guide.csv',
						),
					),

					// ListingLoader
					'ListingLoader' => array(
						'title' => 'ListingLoader',
						'templates' => array(
							'ListingLoader-Template.csv',
							'ListingLoader-DataDefinitions.csv',	
							'ListingLoader-ValidValues.csv',	
						),
						'btguides' => array(
						),
					),

				),
			), // amazon.fr
			

			// amazon.it (IT)
			'IT' => array(
				'site'  => 'amazon.it',
				'code'  => 'IT',
				'categories' => array(

					// Abbigliamento
					'Clothing' => array(
						'title' => 'Abbigliamento',
						'templates' => array(
							'Flat.File.Clothing.it-Template.csv',
							'Flat.File.Clothing.it-DataDefinitions.csv',	
							'Flat.File.Clothing.it-ValidValues.csv',								
						),
						'btguides' => array(
							'it_apparel_browse_tree_guide.csv',
						),
					),

					// Alimentari e cura della casa
					'FoodAndBeverages' => array(
						'title' => 'Alimentari e cura della casa',
						'templates' => array(
							'Flat.File.FoodAndBeverages.it-Template.csv',
							'Flat.File.FoodAndBeverages.it-DataDefinitions.csv',	
							'Flat.File.FoodAndBeverages.it-ValidValues.csv',								
						),
						'btguides' => array(
							'it_grocery_browse_tree_guide.csv',
						),
					),

					// Auto e Moto
					'AutoAccessory' => array(
						'title' => 'Auto e Moto',
						'templates' => array(
							'Flat.File.AutoAccessory.it-Template.csv',
							'Flat.File.AutoAccessory.it-DataDefinitions.csv',	
							'Flat.File.AutoAccessory.it-ValidValues.csv',															
						),
						'btguides' => array(
							'it_automotive_browse_tree_guide.csv',
						),
					),

					// Bellezza
					'Beauty' => array(
						'title' => 'Bellezza',
						'templates' => array(
							'Flat.File.Beauty.it-Template.csv',
							'Flat.File.Beauty.it-DataDefinitions.csv',	
							'Flat.File.Beauty.it-ValidValues.csv',								
						),
						'btguides' => array(
							'it_beauty_browse_tree_guide.csv',
						),
					),

					// Cancelleria e prodotti per ufficio
					'Office' => array(
						'title' => 'Cancelleria e prodotti per ufficio',
						'templates' => array(
							'Flat.File.Office.it-Template.csv',
							'Flat.File.Office.it-DataDefinitions.csv',	
							'Flat.File.Office.it-ValidValues.csv',								
						),
						'btguides' => array(
							'it_office-products_browse_tree_guide.csv',
						),
					),

					// Casa
					'Home' => array(
						'title' => 'Casa',
						'templates' => array(
							'Flat.File.Home.it-Template.csv',
							'Flat.File.Home.it-DataDefinitions.csv',	
							'Flat.File.Home.it-ValidValues.csv',								
						),
						'btguides' => array(
							'it_kitchen_browse_tree_guide.csv',
						),
					),

					// Elettronica di consumo e accessori
					'CE' => array(
						'title' => 'Elettronica di consumo e accessori',
						'templates' => array(
							'Flat.File.CE.it-Template.csv',
							'Flat.File.CE.it-DataDefinitions.csv',	
							'Flat.File.CE.it-ValidValues.csv',								
						),
						'btguides' => array(
							'it_electronics_browse_tree_guide.csv',
						),
					),

					// Fai da te
					'Tools' => array(
						'title' => 'Fai da te',
						'templates' => array(
							'Flat.File.Tools.it-Template.csv',
							'Flat.File.Tools.it-DataDefinitions.csv',	
							'Flat.File.Tools.it-ValidValues.csv',								
						),
						'btguides' => array(
							'it_tools_browse_tree_guide.csv',
						),
					),

					// Giardino e giardinaggio
					'LawnAndGarden' => array(
						'title' => 'Giardino e giardinaggio',
						'templates' => array(
							'Flat.File.LawnAndGarden.it-Template.csv',
							'Flat.File.LawnAndGarden.it-DataDefinitions.csv',	
							'Flat.File.LawnAndGarden.it-ValidValues.csv',								
						),
						'btguides' => array(
							'it_garden_browse_tree_guide.csv',
						),
					),

					// Giochi e giocattoli
					'ToysBaby' => array(
						'title' => 'Giochi e giocattoli',
						'templates' => array(
							'Flat.File.ToysBaby.it-Template.csv',
							'Flat.File.ToysBaby.it-DataDefinitions.csv',	
							'Flat.File.ToysBaby.it-ValidValues.csv',								
						),
						'btguides' => array(
							'it_toys_browse_tree_guide.csv',
						),
					),

					// Gioielli
					'Jewelry' => array(
						'title' => 'Gioielli',
						'templates' => array(
							'Flat.File.Jewelry.it-Template.csv',
							'Flat.File.Jewelry.it-DataDefinitions.csv',	
							'Flat.File.Jewelry.it-ValidValues.csv',								
						),
						'btguides' => array(
							'it_jewelry_browse_tree_guide.csv',
						),
					),

					// Illuminazione
					'Lighting' => array(
						'title' => 'Illuminazione',
						'templates' => array(
							'Flat.File.Lighting.it-Template.csv',
							'Flat.File.Lighting.it-DataDefinitions.csv',	
							'Flat.File.Lighting.it-ValidValues.csv',								
						),
						'btguides' => array(
							'it_lighting_browse_tree_guide.csv',
						),
					),

					// Informatica
					'Computers' => array(
						'title' => 'Informatica',
						'templates' => array(
							'Flat.File.Computers.it-Template.csv',
							'Flat.File.Computers.it-DataDefinitions.csv',	
							'Flat.File.Computers.it-ValidValues.csv',								
						),
						'btguides' => array(
							'it_computers_browse_tree_guide.csv',
						),
					),

					// BookLoader
					'BookLoader' => array(
						'title' => 'Libri',
						'templates' => array(
							'Flat.File.BookLoader.it-Template.csv',
							'Flat.File.BookLoader.it-DataDefinitions.csv',	
							'Flat.File.BookLoader.it-ValidValues.csv',								
						),
						'btguides' => array(
						),
					),

					// Occhiali da sole e occhiali di moda
					'Eyewear' => array(
						'title' => 'Occhiali da sole e occhiali di moda',
						'templates' => array(
							'Flat.File.Eyewear.it-Template.csv',
							'Flat.File.Eyewear.it-DataDefinitions.csv',	
							'Flat.File.Eyewear.it-ValidValues.csv',								
						),
						'btguides' => array(
							'it_apparel_browse_tree_guide.csv',
						),
					),

					// Orologi
					'Watches' => array(
						'title' => 'Orologi',
						'templates' => array(
							'Flat.File.Watches.it-Template.csv',
							'Flat.File.Watches.it-DataDefinitions.csv',	
							'Flat.File.Watches.it-ValidValues.csv',								
						),
						'btguides' => array(
							'it_watches_browse_tree_guide.csv',
						),
					),

					// Prima infanzia
					'Baby' => array(
						'title' => 'Prima infanzia',
						'templates' => array(
							'Flat.File.Baby.it-Template.csv',
							'Flat.File.Baby.it-DataDefinitions.csv',	
							'Flat.File.Baby.it-ValidValues.csv',								
						),
						'btguides' => array(
							'it_baby-products_browse_tree_guide.csv',
						),
					),

					// Salute e cura della persona
					'Health' => array(
						'title' => 'Salute e cura della persona',
						'templates' => array(
							'Flat.File.Health.it-Template.csv',
							'Flat.File.Health.it-DataDefinitions.csv',	
							'Flat.File.Health.it-ValidValues.csv',								
						),
						'btguides' => array(
							'it_health_browse_tree_guide.csv',
						),
					),

					// Scarpe e borse
					'Shoes' => array(
						'title' => 'Scarpe e borse',
						'templates' => array(
							'Flat.File.Shoes.it-Template.csv',
							'Flat.File.Shoes.it-DataDefinitions.csv',	
							'Flat.File.Shoes.it-ValidValues.csv',								
						),
						'btguides' => array(
							'it_shoes_browse_tree_guide.csv',
						),
					),

					// Sport e tempo libero
					'Sports' => array(
						'title' => 'Sport e tempo libero',
						'templates' => array(
							'Flat.File.Sports.it-Template.csv',
							'Flat.File.Sports.it-DataDefinitions.csv',	
							'Flat.File.Sports.it-ValidValues.csv',								
						),
						'btguides' => array(
							'it_sports_browse_tree_guide.csv',
						),
					),

					// Strumenti musicali e DJ
					'MusicalInstruments' => array(
						'title' => 'Strumenti musicali e DJ',
						'templates' => array(
							'Flat.File.MusicalInstruments.it-Template.csv',
							'Flat.File.MusicalInstruments.it-DataDefinitions.csv',	
							'Flat.File.MusicalInstruments.it-ValidValues.csv',								
						),
						'btguides' => array(
							'it_musical-instruments_browse_tree_guide.csv',
						),
					),

					// Valigeria
					'Luggage' => array(
						'title' => 'Valigeria',
						'templates' => array(
							'Flat.File.Luggage.it-Template.csv',
							'Flat.File.Luggage.it-DataDefinitions.csv',	
							'Flat.File.Luggage.it-ValidValues.csv',								
						),
						'btguides' => array(
							'it_luggage_browse_tree_guide.csv',
						),
					),

					// ListingLoader
					'ListingLoader' => array(
						'title' => 'ListingLoader',
						'templates' => array(
							'ListingLoader-Template.csv',
							'ListingLoader-DataDefinitions.csv',	
							'ListingLoader-ValidValues.csv',	
						),
						'btguides' => array(
						),
					),

				),
			), // amazon.it


			// amazon.es (ES)
			'ES' => array(
				'site'  => 'amazon.es',
				'code'  => 'ES',
				'categories' => array(

					// Bricolaje y Herramientas
					'Tools' => array(
						'title' => 'Bricolaje y Herramientas',
						'templates' => array(
							'Flat.File.Tools.es-Template.csv',
							'Flat.File.Tools.es-DataDefinitions.csv',	
							'Flat.File.Tools.es-ValidValues.csv',								
						),
						'btguides' => array(
							'es_tools_browse_tree_guide.csv',
						),
					),

					// Bebé
					'Baby' => array(
						'title' => 'Bebé',
						'templates' => array(
							'Flat.File.Baby.es-Template.csv',
							'Flat.File.Baby.es-DataDefinitions.csv',	
							'Flat.File.Baby.es-ValidValues.csv',								
						),
						'btguides' => array(
							'es_baby-products_browse_tree_guide.csv',
						),
					),

					// Belleza
					'Beauty' => array(
						'title' => 'Belleza',
						'templates' => array(
							'Flat.File.Beauty.es-Template.csv',
							'Flat.File.Beauty.es-DataDefinitions.csv',	
							'Flat.File.Beauty.es-ValidValues.csv',								
						),
						'btguides' => array(
							'es_beauty_browse_tree_guide.csv',
						),
					),

					// Coche y Moto
					'AutoAccessory' => array(
						'title' => 'Coche y Moto',
						'templates' => array(
							'Flat.File.AutoAccessory.es-Template.csv',
							'Flat.File.AutoAccessory.es-DataDefinitions.csv',	
							'Flat.File.AutoAccessory.es-ValidValues.csv',															
						),
						'btguides' => array(
							'es_automotive_browse_tree_guide.csv',
						),
					),

					// Cocina (sólo Pequeño electrodoméstico)
					'Kitchen' => array(
						'title' => 'Cocina (sólo Pequeño electrodoméstico)',
						'templates' => array(
							'Flat.File.Kitchen.es-Template.csv',
							'Flat.File.Kitchen.es-DataDefinitions.csv',	
							'Flat.File.Kitchen.es-ValidValues.csv',								
						),
						'btguides' => array(
							'es_kitchen_browse_tree_guide.csv',
						),
					),

					// Deportes y Aire libre
					'Sports' => array(
						'title' => 'Deportes y Aire libre',
						'templates' => array(
							'Flat.File.Sports.es-Template.csv',
							'Flat.File.Sports.es-DataDefinitions.csv',	
							'Flat.File.Sports.es-ValidValues.csv',								
						),
						'btguides' => array(
							'es_sports_browse_tree_guide.csv',
						),
					),

					// Electrónica y accesorios de electrónica
					'ConsumerElectronics' => array(
						'title' => 'Electrónica y accesorios de electrónica',
						'templates' => array(
							'Flat.File.ConsumerElectronics.es-Template.csv',
							'Flat.File.ConsumerElectronics.es-DataDefinitions.csv',	
							'Flat.File.ConsumerElectronics.es-ValidValues.csv',								
						),
						'btguides' => array(
							'es_electronics_browse_tree_guide.csv',
						),
					),

					// Equipaje
					'Luggage' => array(
						'title' => 'Equipaje',
						'templates' => array(
							'Flat.File.Luggage.es-Template.csv',
							'Flat.File.Luggage.es-DataDefinitions.csv',	
							'Flat.File.Luggage.es-ValidValues.csv',								
						),
						'btguides' => array(
							'es_luggage_browse_tree_guide.csv',
						),
					),

					// Gafas de sol y Gafas de moda
					'Eyewear' => array(
						'title' => 'Gafas de sol y Gafas de moda',
						'templates' => array(
							'Flat.File.Eyewear.es-Template.csv',
							'Flat.File.Eyewear.es-DataDefinitions.csv',	
							'Flat.File.Eyewear.es-ValidValues.csv',								
						),
						'btguides' => array(
							'es_apparel_browse_tree_guide.csv',
						),
					),

					// Hogar
					'Home' => array(
						'title' => 'Hogar',
						'templates' => array(
							'Flat.File.Home.es-Template.csv',
							'Flat.File.Home.es-DataDefinitions.csv',	
							'Flat.File.Home.es-ValidValues.csv',								
						),
						'btguides' => array(
							'es_kitchen_browse_tree_guide.csv',
						),
					),

					// Iluminación
					'Lighting' => array(
						'title' => 'Iluminación',
						'templates' => array(
							'Flat.File.Lighting.es-Template.csv',
							'Flat.File.Lighting.es-DataDefinitions.csv',	
							'Flat.File.Lighting.es-ValidValues.csv',								
						),
						'btguides' => array(
							'es_lighting_browse_tree_guide.csv',
						),
					),

					// Informática
					'Computers' => array(
						'title' => 'Informática',
						'templates' => array(
							'Flat.File.Computers.es-Template.csv',
							'Flat.File.Computers.es-DataDefinitions.csv',	
							'Flat.File.Computers.es-ValidValues.csv',								
						),
						'btguides' => array(
							'es_computers_browse_tree_guide.csv',
						),
					),

					// Instrumentos musicales
					'MusicalInstruments' => array(
						'title' => 'Instrumentos musicales',
						'templates' => array(
							'Flat.File.MusicalInstruments.es-Template.csv',
							'Flat.File.MusicalInstruments.es-DataDefinitions.csv',	
							'Flat.File.MusicalInstruments.es-ValidValues.csv',								
						),
						'btguides' => array(
							'es_musical-instruments_browse_tree_guide.csv',
						),
					),

					// Jardín
					'LawnAndGarden' => array(
						'title' => 'Jardín',
						'templates' => array(
							'Flat.File.LawnAndGarden.es-Template.csv',
							'Flat.File.LawnAndGarden.es-DataDefinitions.csv',	
							'Flat.File.LawnAndGarden.es-ValidValues.csv',								
						),
						'btguides' => array(
							'es_garden_browse_tree_guide.csv',
						),
					),

					// Joyería
					'Jewelry' => array(
						'title' => 'Joyería',
						'templates' => array(
							'Flat.File.Jewelry.es-Template.csv',
							'Flat.File.Jewelry.es-DataDefinitions.csv',	
							'Flat.File.Jewelry.es-ValidValues.csv',								
						),
						'btguides' => array(
							'es_jewelry_browse_tree_guide.csv',
						),
					),

					// Juguetes y Juegos
					'Toys' => array(
						'title' => 'Juguetes y Juegos',
						'templates' => array(
							'Flat.File.Toys.es-Template.csv',
							'Flat.File.Toys.es-DataDefinitions.csv',	
							'Flat.File.Toys.es-ValidValues.csv',								
						),
						'btguides' => array(
							'es_toys_browse_tree_guide.csv',
						),
					),

					// BookLoader
					'BookLoader' => array(
						'title' => 'Libros',
						'templates' => array(
							'Flat.File.BookLoader.es-Template.csv',
							'Flat.File.BookLoader.es-DataDefinitions.csv',	
							'Flat.File.BookLoader.es-ValidValues.csv',								
						),
						'btguides' => array(
						),
					),

					// Oficina y papelería
					'Office' => array(
						'title' => 'Oficina y papelería',
						'templates' => array(
							'Flat.File.Office.es-Template.csv',
							'Flat.File.Office.es-DataDefinitions.csv',	
							'Flat.File.Office.es-ValidValues.csv',								
						),
						'btguides' => array(
							'es_office-products_browse_tree_guide.csv',
						),
					),

					// Relojes
					'Watches' => array(
						'title' => 'Relojes',
						'templates' => array(
							'Flat.File.Watches.es-Template.csv',
							'Flat.File.Watches.es-DataDefinitions.csv',	
							'Flat.File.Watches.es-ValidValues.csv',								
						),
						'btguides' => array(
							'es_watches_browse_tree_guide.csv',
						),
					),

					// Ropa y accesorios
					'Clothing' => array(
						'title' => 'Ropa y accesorios',
						'templates' => array(
							'Flat.File.Clothing.es-Template.csv',
							'Flat.File.Clothing.es-DataDefinitions.csv',	
							'Flat.File.Clothing.es-ValidValues.csv',								
						),
						'btguides' => array(
							'es_apparel_browse_tree_guide.csv',
						),
					),

					// Salud y Cuidado personal
					'Health' => array(
						'title' => 'Salud y Cuidado personal',
						'templates' => array(
							'Flat.File.Health.es-Template.csv',
							'Flat.File.Health.es-DataDefinitions.csv',	
							'Flat.File.Health.es-ValidValues.csv',								
						),
						'btguides' => array(
							'es_health_browse_tree_guide.csv',
						),
					),

					// Zapatos y Complementos
					'Shoes' => array(
						'title' => 'Zapatos y Complementos',
						'templates' => array(
							'Flat.File.Shoes.es-Template.csv',
							'Flat.File.Shoes.es-DataDefinitions.csv',	
							'Flat.File.Shoes.es-ValidValues.csv',								
						),
						'btguides' => array(
							'es_shoes_browse_tree_guide.csv',
						),
					),

					// CE (deprecated - use Electrónica instead)
					'CE' => array(
						'title' => 'CE (deprecated - use Electrónica instead)',
						'templates' => array(
							'Flat.File.CE.es-Template.csv',
							'Flat.File.CE.es-DataDefinitions.csv',	
							'Flat.File.CE.es-ValidValues.csv',								
						),
						'btguides' => array(
							'es_electronics_browse_tree_guide.csv',
						),
					),

					// ListingLoader
					'ListingLoader' => array(
						'title' => 'ListingLoader',
						'templates' => array(
							'ListingLoader-Template.csv',
							'ListingLoader-DataDefinitions.csv',	
							'ListingLoader-ValidValues.csv',	
						),
						'btguides' => array(
						),
					),

				),
			), // amazon.es


			// amazon.co.jp (JP)
			'JP' => array(
				'site'  => 'amazon.co.jp',
				'code'  => 'JP',
				'categories' => array(

					// Clothing & Accessories, Shoes & Bags
					'Clothing' => array(
						'title' => 'Clothing & Accessories, Shoes & Bags',
						'templates' => array(
							'Flat.File.Clothing.jp-Template.csv',
							'Flat.File.Clothing.jp-DataDefinitions.csv',	
							'Flat.File.Clothing.jp-ValidValues.csv',								
						),
						'btguides' => array(
							'EN_jp_apparel_browse_tree_guide.csv',
							'EN_jp_shoes_browse_tree_guide.csv',
						),
					),

					// Computers
					'Computers' => array(
						'title' => 'Computers',
						'templates' => array(
							'Flat.File.Computers.jp-Template.csv',
							'Flat.File.Computers.jp-DataDefinitions.csv',	
							'Flat.File.Computers.jp-ValidValues.csv',								
						),
						'btguides' => array(
							'EN_jp_computers_browse_tree_guide.csv',
						),
					),

					// Consumer Electronics
					'ConsumerElectronics' => array(
						'title' => 'Consumer Electronics',
						'templates' => array(
							'Flat.File.CE.jp-Template.csv',
							'Flat.File.CE.jp-DataDefinitions.csv',	
							'Flat.File.CE.jp-ValidValues.csv',								
						),
						'btguides' => array(
							'EN_jp_ce_browse_tree_guide.csv',
						),
					),

					// Home
					'Home' => array(
						'title' => 'Home',
						'templates' => array(
							'Flat.File.Home.jp-Template.csv',
							'Flat.File.Home.jp-DataDefinitions.csv',	
							'Flat.File.Home.jp-ValidValues.csv',								
						),
						'btguides' => array(
							'EN_jp_kitchen_browse_tree_guide.csv',
						),
					),

					// Pet Supplies
					'PetSupplies' => array(
						'title' => 'Pet Supplies',
						'templates' => array(
							'Flat.File.PetSupplies.jp-Template.csv',
							'Flat.File.PetSupplies.jp-DataDefinitions.csv',	
							'Flat.File.PetSupplies.jp-ValidValues.csv',								
						),
						'btguides' => array(
							'EN_jp_pet-supplies_browse_tree_guide.csv',
						),
					),

					// Toys, Baby & Maternity
					'Toys' => array(
						'title' => 'Toys, Baby & Maternity',
						'templates' => array(
							'Flat.File.Toys.jp-Template.csv',
							'Flat.File.Toys.jp-DataDefinitions.csv',	
							'Flat.File.Toys.jp-ValidValues.csv',								
						),
						'btguides' => array(
							'EN_jp_toys_browse_tree_guide.csv',
							'EN_jp_hobby_browse_tree_guide.csv',
							'EN_jp_baby_browse_tree_guide.csv',
						),
					),

					// ListingLoader
					'ListingLoader' => array(
						'title' => 'ListingLoader',
						'templates' => array(
							'ListingLoader-Template.csv',
							'ListingLoader-DataDefinitions.csv',	
							'ListingLoader-ValidValues.csv',	
						),
						'btguides' => array(
						),
					),

				),
			), // amazon.co.jp
                    
                    
            // amazon.in (IN)
			'IN' => array(
				'site'  => 'amazon.in',
				'code'  => 'IN',
				'categories' => array(

					// Automotive Parts & Accessories
					'AutoAccessory' => array(
						'title' => 'Car & Motorbike',
						'templates' => array(
							'Flat.File.AutoAccessory.in-Template.csv',
							'Flat.File.AutoAccessory.in-DataDefinitions.csv',	
							'Flat.File.AutoAccessory.in-ValidValues.csv',															
						),
						'btguides' => array(
							'in_automotive_browse_tree_guide.csv',
						),
					),

					// Baby
					'Baby' => array(
						'title' => 'Baby',
						'templates' => array(
							'Flat.File.Baby.in-Template.csv',
							'Flat.File.Baby.in-DataDefinitions.csv',	
							'Flat.File.Baby.in-ValidValues.csv',								
						),
						'btguides' => array(
							'in_baby-products_browse_tree_guide.csv',
						),
					),

					// Beauty
					'Beauty' => array(
						'title' => 'Beauty',
						'templates' => array(
							'Flat.File.Beauty.in-Template.csv',
							'Flat.File.Beauty.in-DataDefinitions.csv',	
							'Flat.File.Beauty.in-ValidValues.csv',								
						),
						'btguides' => array(
							'in_beauty_browse_tree_guide.csv',
						),
					),
                                                      
                    // BookLoader
					'BookLoader' => array(
						'title' => 'Books',
						'templates' => array(
							'Flat.File.BookLoader.in-Template.csv',
							'Flat.File.BookLoader.in-DataDefinitions.csv',	
							'Flat.File.BookLoader.in-ValidValues.csv',								
						),
						'btguides' => array(
						'in_books_browse_tree_guide.csv',
						),
					),
					
					// Clothing & Accessories
					'Clothing' => array(
						'title' => 'Clothing & Accessories',
						'templates' => array(
							'Flat.File.Clothing.in-Template.csv',
							'Flat.File.Clothing.in-DataDefinitions.csv',	
							'Flat.File.Clothing.in-ValidValues.csv',								
						),
						'btguides' => array(
							'in_apparel_browse_tree_guide.csv',
						),
					),

					// Computers
					'Computers' => array(
						'title' => 'Computers & Accessories',
						'templates' => array(
							'Flat.File.Computers.in-Template.csv',
							'Flat.File.Computers.in-DataDefinitions.csv',	
							'Flat.File.Computers.in-ValidValues.csv',								
						),
						'btguides' => array(
							'in_computers_browse_tree_guide.csv',
						),
					),
                                    
                    // Consumer Electronics
					'ConsumerElectronics' => array(
						'title' => 'Electronics',
						'templates' => array(
							'Flat.File.ConsumerElectronics.in-Template.csv',
							'Flat.File.ConsumerElectronics.in-DataDefinitions.csv',	
							'Flat.File.ConsumerElectronics.in-ValidValues.csv',								
						),
						'btguides' => array(
							'in_electronics_browse_tree_guide.csv',
						),
					),
						
					//Gift cards
					'Gifts' => array(
						'title' => 'Gift Cards',
						'templates' => array(
							'Gift_cards_flatfile-Template.csv',
							'Gift_cards_flatfile-DataDefinitions.csv',
							'Gift_cards_flatfile-ValidValues.csv',
						),
						'btguides' => array(
							'in_gift-cards_browse_tree_guide.csv',
						),
					),

					// Grocery & Beverages
					'FoodAndBeverages' => array(
						'title' => 'Grocery & Gourmet',
						'templates' => array(
							'Flat.File.FoodAndBeverages.in-Template.csv',
							'Flat.File.FoodAndBeverages.in-DataDefinitions.csv',	
							'Flat.File.FoodAndBeverages.in-ValidValues.csv',								
						),
						'btguides' => array(
							'in_grocery_browse_tree_guide.csv',
						),
					),
						
					// Kirana Now
					'FoodServiceAndJanSan' => array(
						'title' => 'Kirana Now',
						'templates' => array(
							'Flat.File.FoodServiceAndJanSan.in-Template.csv',
							'Flat.File.FoodServiceAndJanSan.in-DataDefinitions.csv',
							'Flat.File.FoodServiceAndJanSan.in-ValidValues.csv',
						),
						'btguides' => array(							
						),
					),
					
					// Furniture
					'Furniture' => array(
						'title' => 'Furniture',
						'templates' => array(
							'Flat.File.Furniture.in-Template.csv',
							'Flat.File.Furniture.in-DataDefinitions.csv',
							'Flat.File.Furniture.in-ValidValues.csv',
						),
						'btguides' => array(
						),
					),

					// Health & Personal Care
					'Health' => array(
						'title' => 'Health & Personal Care',
						'templates' => array(
							'Flat.File.Health.in-Template.csv',
							'Flat.File.Health.in-DataDefinitions.csv',	
							'Flat.File.Health.in-ValidValues.csv',								
						),
						'btguides' => array(
							 'in_health_browse_tree_guide.csv',
						),
					),

					// Home & Kitchen
					'Home' => array(
						'title' => 'Home & Kitchen',
						'templates' => array(
							'Flat.File.Home.in-Template.csv',
							'Flat.File.Home.in-DataDefinitions.csv',	
							'Flat.File.Home.in-ValidValues.csv',								
						),
						'btguides' => array(
							'in_kitchen_browse_tree_guide.csv',
						),
					),
						
					
					// Industrial
					'Industrial' => array(
						'title' => 'Industrial',
						'templates' => array(
							'Flat.File.Industrial.in-Template.csv',
							'Flat.File.Industrial.in-DataDefinitions.csv',
							'Flat.File.Industrial.in-ValidValues.csv',
						),
						'btguides' => array(
						),
					),
					
					
					// Jewelry
					'Jewelry' => array(
						'title' => 'Jewelry',
						'templates' => array(
							'Flat.File.Jewelry.in-Template.csv',
							'Flat.File.Jewelry.in-DataDefinitions.csv',
							'Flat.File.Jewelry.in-ValidValues.csv',
						),
						'btguides' => array(
							'in_jewelry_browse_tree_guide.csv',
						),
					),
					
					// Lab Supplies
					'LabSupplies' => array(
						'title' => 'Lab Supplies',
						'templates' => array(
							'Flat.File.LabSupplies.in-Template.csv',
							'Flat.File.LabSupplies.in-DataDefinitions.csv',
							'Flat.File.LabSupplies.in-ValidValues.csv',
						),
						'btguides' => array(
						),
					),
					
					// Large Appliances
					'LargeAppliances' => array(
						'title' => 'Large Appliances',
						'templates' => array(
							'Flat.File.LargeAppliances.in-Template.csv',
							'Flat.File.LargeAppliances.in-DataDefinitions.csv',
							'Flat.File.LargeAppliances.in-ValidValues.csv',
						),
						'btguides' => array(
						),
					),
					
					// Luxury Beauty
					'LuxuryBeauty' => array(
						'title' => 'Luxury Beauty',
						'templates' => array(
							'Flat.File.LuxuryBeauty.in-Template.csv',
							'Flat.File.LuxuryBeauty.in-DataDefinitions.csv',
							'Flat.File.LuxuryBeauty.in-ValidValues.csv',
						),
						'btguides' => array(
						),
					),
					
					// Mechanical Fasteners
					'MechanicalFasteners' => array(
						'title' => 'Mechanical Fasteners',
						'templates' => array(
							'Flat.File.MechanicalFasteners.in-Template.csv',
							'Flat.File.MechanicalFasteners.in-DataDefinitions.csv',
							'Flat.File.MechanicalFasteners.in-ValidValues.csv',
						),
						'btguides' => array(
						),
					),
					
					
					// Musical Instruments
					'MusicalInstruments' => array(
						'title' => 'Musical Instruments',
						'templates' => array(
							'Flat.File.MusicalInstruments.in-Template.csv',
							'Flat.File.MusicalInstruments.in-DataDefinitions.csv',
							'Flat.File.MusicalInstruments.in-ValidValues.csv',
						),
						'btguides' => array(
						),
					),
					
					
					// Office Products
					'Office' => array(
						'title' => 'Office Products',
						'templates' => array(
							'Flat.File.Office.in-Template.csv',
							'Flat.File.Office.in-DataDefinitions.csv',
							'Flat.File.Office.in-ValidValues.csv',
						),
						'btguides' => array(
							'in_office_browse_tree_guide.csv',
						),
					),
						

					// Luggage & Bags
					'Luggage' => array(
						'title' => 'Luggage & Bags',
						'templates' => array(
							'Flat.File.Luggage.in-Template.csv',
							'Flat.File.Luggage.in-DataDefinitions.csv',	
							'Flat.File.Luggage.in-ValidValues.csv',								
						),
						'btguides' => array(
							'in_luggage_browse_tree_guide.csv',
						),
					),

					// Shoes
					'Shoes' => array(
						'title' => 'Shoes & Handbags',
						'templates' => array(
							'Flat.File.Shoes.in-Template.csv',
							'Flat.File.Shoes.in-DataDefinitions.csv',	
							'Flat.File.Shoes.in-ValidValues.csv',								
						),
						'btguides' => array(
							 'in_shoes_browse_tree_guide.csv',
						),
					),
						
					// Software
					'SoftwareVideoGames' => array(
						'title' => 'Software',
						'templates' => array(
							'Flat.File.SWVG.in-Template.csv',
							'Flat.File.SWVG.in-DataDefinitions.csv',
							'Flat.File.SWVG.in-ValidValues.csv',
						),
						'btguides' => array(
							'in_software_browse_tree_guide.csv',
							'in_videogames_browse_tree_guide.csv',
						),
					),
					
					// Pet Supplies
					'PetSupplies' => array(
						'title' => 'Pet Supplies',
						'templates' => array(
							'Flat.File.PetSupplies.in-Template.csv',
							'Flat.File.PetSupplies.in-DataDefinitions.csv',
							'Flat.File.PetSupplies.in-ValidValues.csv',
						),
						'btguides' => array(
							'in_pet-supplies_browse_tree_guide.csv',
						),
					),
					
					// Pro Healthcare
					'ProfessionalHealthCare' => array(
						'title' => 'Professional Health Care',
						'templates' => array(
							'Flat.File.ProfessionalHealthCare.in-Template.csv',
							'Flat.File.ProfessionalHealthCare.in-DataDefinitions.csv',
							'Flat.File.ProfessionalHealthCare.in-ValidValues.csv',
						),
						'btguides' => array(
						),
					),

					// Sports & Outdoors
					'Sports' => array(
						'title' => 'Sports, Fitness & Outdoors',
						'templates' => array(
							'Flat.File.Sports.in-Template.csv',
							'Flat.File.Sports.in-DataDefinitions.csv',	
							'Flat.File.Sports.in-ValidValues.csv',								
						),
						'btguides' => array(
							'in_sports_browse_tree_guide.csv',
						),
					),

				

					// Toys
					'Toys' => array(
						'title' => 'Toys & Games',
						'templates' => array(
							'Flat.File.Toys.in-Template.csv',
							'Flat.File.Toys.in-DataDefinitions.csv',	
							'Flat.File.Toys.in-ValidValues.csv',								
						),
						'btguides' => array(
							'in_toys_browse_tree_guide.csv',
						),
					),

					
					// Watches
					'Watches' => array(
						'title' => 'Watches',
						'templates' => array(
							'Flat.File.Watches.in-Template.csv',
							'Flat.File.Watches.in-DataDefinitions.csv',	
							'Flat.File.Watches.in-ValidValues.csv',								
						),
						'btguides' => array(
							'in_watches_browse_tree_guide.csv',
						),
					),
						
					// Video
					'Video' => array(
						'title' => 'Video',
						'templates' => array(
							'Flat.File.Video.in-Template.csv',
							'Flat.File.Video.in-DataDefinitions.csv',
							'Flat.File.Video.in-ValidValues.csv',
						),
						'btguides' => array(
							'in_dvd_browse_tree_guide.csv',
						),
					),


					// ListingLoader
					'ListingLoader' => array(
						'title' => 'ListingLoader',
						'templates' => array(
							'ListingLoader.in-Template.csv',
							'ListingLoader.in-DataDefinitions.csv',	
							'ListingLoader.in-ValidValues.csv',	
						),
						'btguides' => array(
						),
					),

				),
			), // amazon.in

		);

		return $file_index;

	} // get_tpl_index()

} // class WPLA_FeedTemplateIndex
