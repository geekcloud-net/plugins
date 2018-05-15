<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

?>

<svg>
	<defs>
		<filter id="tcb-loading-dots">
			<feGaussianBlur in="SourceGraphic" stdDeviation="7" result="blur" />
			<feColorMatrix in="blur" mode="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 18 -7" result="tcb-loading-dots" />
		</filter>
	</defs>
</svg>

<div class="loader-container">
	<div></div>
	<div></div>
	<div></div>
	<div></div>
	<div></div>
</div>

