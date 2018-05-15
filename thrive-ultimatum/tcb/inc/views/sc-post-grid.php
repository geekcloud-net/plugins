<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 6/7/2017
 * Time: 11:44 AM
 */

$index = $data['index'];
$count = $data['count'];

if ( $data['cls']->_config['display'] === 'grid' && ( $index === 1 || ( ( $index - 1 ) % $data['cls']->_config['columns'] === 0 && $index - 1 > 0 ) ) ) :
?>
<div class="tve_pg_row tve_clearfix">
	<?php
	endif;
	?>

	<div class="tve_post tve_post_width_<?php echo $data['cls']->_config['columns'] ?>">
		<div class="tve_pg_container">
			<?php echo $data['cls']->get_post_content( $data['post'] ); ?>
		</div>
	</div>

	<?php

	if ( $data['cls']->_config['display'] === 'grid' && ( $index % $data['cls']->_config['columns'] === 0 || $index === $count ) ) :
	?>
</div>
<?php
endif;
?>
