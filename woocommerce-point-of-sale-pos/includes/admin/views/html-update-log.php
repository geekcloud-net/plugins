<head>
	<style>
		.ae-badge {
			position: relative;
			text-align: center;
			padding: 2em;
		}
		.ae-badge:before {
			content: "\00e6";
			font-weight: 200;
			color: #8f1e20;
			font-size: 72px;
		}
		#ae_changelog {
			background: #fff;
		    padding: 1em 2em;
		    box-shadow: 0 0 0px 1px #ddd;
		    border-radius: 12px;
		}
	</style>
</head>
<div class="warp about-wrap">
	<div class="ae-badge">
	</div>
	<div id="ae_changelog">
		<?php
		foreach ($changes as $version => $list) {
		    echo '<h4>' . $version . '</h4><ul>';
		    foreach ($list as $item) {
		        echo '<li>' . $item . '</li>';
		    }
		    echo '</ul>';
		}
		?>
	</div>
</div>