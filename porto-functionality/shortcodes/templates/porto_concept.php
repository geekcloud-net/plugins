<?php
$output = $title1 = $link1 = $image1_url = $image1_id = $title1 = $link2 = $image2_url = $image2_id = $title3 = $link3 = $image3_url = $image3_id = $title4 = $slide_link1 = $slide_image1_url = $slide_image1_id = $slide_link2 = $slide_image2_url = $slide_image2_id = $slide_link3 = $slide_image3_url = $slide_image3_id = $animation_type = $animation_duration = $animation_delay = $el_class = '';
extract(shortcode_atts(array(
    'title1' => '',
    'link1' => '',
    'image1_url' => '',
    'image1_id' => '',
    'title2' => '',
    'link2' => '',
    'image2_url' => '',
    'image2_id' => '',
    'title3' => '',
    'link3' => '',
    'image3_url' => '',
    'image3_id' => '',
    'title4' => '',
    'slide_link1' => '',
    'slide_image1_url' => '',
    'slide_image1_id' => '',
    'slide_link2' => '',
    'slide_image2_url' => '',
    'slide_image2_id' => '',
    'slide_link3' => '',
    'slide_image3_url' => '',
    'slide_image3_id' => '',
    'animation_type' => '',
    'animation_duration' => 1000,
    'animation_delay' => 0,
    'el_class' => '',
), $atts));


$el_class = porto_shortcode_extract_class( $el_class );

if (!$image1_url && $image1_id)
    $image1_url = wp_get_attachment_url($image1_id);

if (!$image2_url && $image2_id)
    $image2_url = wp_get_attachment_url($image2_id);

if (!$image3_url && $image3_id)
    $image3_url = wp_get_attachment_url($image3_id);

if (!$slide_image1_url && $slide_image1_id)
    $slide_image1_url = wp_get_attachment_url($slide_image1_id);

if (!$slide_image2_url && $slide_image2_id)
    $slide_image2_url = wp_get_attachment_url($slide_image2_id);

if (!$slide_image3_url && $slide_image3_id)
    $slide_image3_url = wp_get_attachment_url($slide_image3_id);

$output .= '<div class="porto-concept wpb_content_element '. $el_class . '"';
if ($animation_type) {
    $output .= ' data-appear-animation="'.$animation_type.'"';
    if ($animation_delay)
        $output .= ' data-appear-animation-delay="'.$animation_delay.'"';
    if ($animation_duration && $animation_duration != 1000)
        $output .= ' data-appear-animation-duration="'.$animation_duration.'"';
}
$output .= '>';

ob_start();
?>
<div class="container">
    <div class="row center">
        <span class="sun"></span>
        <span class="cloud"></span>
        <div class="col-lg-2 offset-lg-1">
            <div class="process-image" data-appear-animation="bounceIn">
                <?php if ($link1) : ?><a href="<?php echo esc_url($link1) ?>"><?php endif; ?>
                    <?php if ($image1_url) ?><img src="<?php echo esc_url(str_replace(array('http:', 'https:'), '', $image1_url)) ?>" alt="" />
                <?php if ($link1) : ?></a><?php endif; ?>
                <?php if ($title1) : ?><strong><?php echo $title1 ?></strong><?php endif; ?>
            </div>
        </div>
        <div class="col-lg-2">
            <div class="process-image" data-appear-animation="bounceIn" data-appear-animation-delay="200">
                <?php if ($link2) : ?><a href="<?php echo esc_url($link2) ?>"><?php endif; ?>
                    <?php if ($image2_url) ?><img src="<?php echo esc_url(str_replace(array('http:', 'https:'), '', $image2_url)) ?>" alt="" />
                <?php if ($link2) : ?></a><?php endif; ?>
                <?php if ($title2) : ?><strong><?php echo $title2 ?></strong><?php endif; ?>
            </div>
        </div>
        <div class="col-lg-2">
            <div class="process-image" data-appear-animation="bounceIn" data-appear-animation-delay="400">
                <?php if ($link3) : ?><a href="<?php echo esc_url($link3) ?>"><?php endif; ?>
                    <?php if ($image3_url) ?><img src="<?php echo esc_url(str_replace(array('http:', 'https:'), '', $image3_url)) ?>" alt="" />
                <?php if ($link3) : ?></a><?php endif; ?>
                <?php if ($title3) : ?><strong><?php echo $title3 ?></strong><?php endif; ?>
            </div>
        </div>
        <div class="col-lg-4 offset-lg-1">
            <div class="project-image">
                <div class="concept-slideshow fc-slideshow">
                    <ul class="fc-slides">
                        <?php if ($slide_image1_url) : ?>
                            <li>
                                <?php if ($slide_link1) : ?><a href="<?php echo esc_url($slide_link1) ?>"><?php endif; ?>
                                    <img class="img-responsive" src="<?php echo esc_url(str_replace(array('http:', 'https:'), '', $slide_image1_url)) ?>" alt="" />
                                <?php if ($slide_link1) : ?></a><?php endif; ?>
                            </li>
                        <?php endif; ?>
                        <?php if ($slide_image2_url) : ?>
                            <li>
                                <?php if ($slide_link2) : ?><a href="<?php echo esc_url($slide_link2) ?>"><?php endif; ?>
                                    <img class="img-responsive" src="<?php echo esc_url(str_replace(array('http:', 'https:'), '', $slide_image2_url)) ?>" alt="" />
                                <?php if ($slide_link2) : ?></a><?php endif; ?>
                            </li>
                        <?php endif; ?>
                        <?php if ($slide_image3_url) : ?>
                            <li>
                                <?php if ($slide_link3) : ?><a href="<?php echo esc_url($slide_link3) ?>"><?php endif; ?>
                                    <img class="img-responsive" src="<?php echo esc_url(str_replace(array('http:', 'https:'), '', $slide_image3_url)) ?>" alt="" />
                                <?php if ($slide_link3) : ?></a><?php endif; ?>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php if ($title4) : ?><strong class="our-work"><?php echo $title4 ?></strong><?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    (function($) {

        'use strict';

        /*
         Circle Slider
         */
        if ($.isFunction($.fn.flipshow)) {
            var circleContainer = $('.concept-slideshow');

            if (circleContainer.get(0)) {
                circleContainer.flipshow();

                setTimeout(function circleFlip() {
                    circleContainer.data().flipshow._navigate(circleContainer.find('div.fc-right span:first'), 'right');
                    setTimeout(circleFlip, 3000);
                }, 3000);
            }
        }

        /*
         Move Cloud
         */
        if ($('.cloud').get(0)) {
            var moveCloud = function() {
                $('.cloud').animate({
                    'top': '+=20px'
                }, 3000, 'linear', function() {
                    $('.cloud').animate({
                        'top': '-=20px'
                    }, 3000, 'linear', function() {
                        moveCloud();
                    });
                });
            };

            moveCloud();
        }

    }).apply(this, [jQuery]);
</script>
<?php

$output .= ob_get_clean();

$output .= '</div>';

echo $output;