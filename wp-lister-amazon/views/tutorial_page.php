<?php #include_once( dirname(__FILE__).'/common_header.php' ); ?>

<style type="text/css">
	p.desc {
		padding-left: 14px;
	}
</style>

<div class="wrap">
	<div class="icon32" style="background: url(<?php echo $wpl_plugin_url; ?>img/amazon-32x32.png) no-repeat;" id="wpl-icon"><br /></div>
	<h2><?php echo __('Tutorial','wpla') ?></h2>
	
	<div style="width:640px;" class="postbox-container">
		<div class="metabox-holder">
			<div class="meta-box-sortables ui-sortable">
				<form method="post" action="<?php echo $wpl_form_action; ?>">
				
					<div class="postbox" id="ImportHelpBox">
						<h3 class="hndle"><span><?php echo __('Importing products','wpla'); ?></span></h3>
						<div class="inside">

							<?php echo $wpl_content_help_import ?>

						</div>
					</div>

					<div class="postbox" id="ListingHelpBox">
						<h3 class="hndle"><span><?php echo __('Listing items','wpla'); ?></span></h3>
						<div class="inside">

							<?php echo $wpl_content_help_listing ?>

						</div>
					</div>

					<!--
					<div class="postbox" id="LinksBox">
						<h3 class="hndle"><span><?php echo __('Ressources','wpla'); ?></span></h3>
						<div class="inside">

						</div>
					</div>
					-->

				</form>
			</div>
		</div>
	</div>

	<script type="text/javascript">
	</script>

</div>