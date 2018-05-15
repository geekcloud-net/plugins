<div class="pl-wrapper">
	<h1><?php _e('WooCommerce Importer Add-ons/Extensions','wpeae'); ?></h1>
	<div class="pl-main">
		<?php 
		foreach($this->addons['addons'] as $addon){
			?>
			<div class="pl-block">
				<a href="<?php echo $addon['url'];?>">
					<div class="pl-header" style="background: url(<?php echo $addon['image'];?>) no-repeat 0 0;background-size: 100% 100%;"></div>
					<div class="pl-text">
						<p><?php echo $addon['description'];?></p>
					</div>
				</a>
			</div>
		<?php
		}
		?>
	</div>
</div>