<?php

$output = $el_class = '';

extract(shortcode_atts(array(
    'el_class' => '',
), $atts));

$el_class = porto_shortcode_extract_class( $el_class );

$output .= '<div class="floating-menu '.$el_class.'">';
	$output .= '<div class="floating-menu-body">';
		$output .= '<div class="floating-menu-container">';
			$output .= '<div class="floating-menu-row">';
				$output .= '<div class="floating-menu-column">';
					$output .= '<div class="floating-menu-row">';
						$output .= '<div class="floating-menu-nav pt-xs">';
							$output .= '<button class="btn floating-menu-btn-collapse-nav" data-toggle="collapse" data-target=".floating-menu-nav-main">';
								$output .= '<i class="fa fa-bars"></i>';
							$output .= '</button>';
							$output .= '<div class="floating-menu-nav-main collapse">';
								$output .= '<nav class="wrapper-spy">';
									$output .= '<ul class="nav">';
										$output .= do_shortcode( $content );
									$output .= '</ul>';
								$output .= '</nav>';
							$output .= '</div>';
						$output .= '</div>';
					$output .= '</div>';
				$output .= '</div>';
			$output .= '</div>';
		$output .= '</div>';
	$output .= '</div>';
$output .= '</div>';

echo $output;