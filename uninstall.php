<?php
/**
 * Uninstall cleanup.
 *
 * @package TestimonialsSlider
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'testimonials-slider_options' );
