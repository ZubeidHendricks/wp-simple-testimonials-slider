<?php
/**
 * Uninstall cleanup.
 *
 * @package TestimonialsSlider
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'simple-testimonials-slider_options' );
