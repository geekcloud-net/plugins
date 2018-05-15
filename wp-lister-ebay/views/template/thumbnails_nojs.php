<div id="wpl_list_images">
<?php for ($i=0; $i < count($images); $i++) : ?>

	<?php $image_url = $images[$i];	?>
	
	<a href="<?php echo $image_url ?>" target="_blank">
		<img class="wpl_thumb thumb_<?php echo $i+1 ?>" src="<?php echo $image_url ?>" alt="<?php echo basename( $image_url ) ?>" />
	</a>

<?php endfor; ?>
<div class="clearfix"></div>
</div>
