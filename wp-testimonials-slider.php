<?php
/**
 * Plugin Name:       Testimonials Slider
 * Plugin URI:        https://zubeidhendricks.dev/wp-plugins/testimonials-slider
 * Description:        Show rotating customer testimonials with a simple shortcode — names, roles and star ratings, no jQuery.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.2
 * Author:            Zubeid Hendricks
 * Author URI:        https://zubeidhendricks.dev
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       testimonials-slider
 *
 * @package TestimonialsSlider
 */

defined( 'ABSPATH' ) || exit;

define( 'TESTIMONIALS_SLIDER_VERSION', '1.0.0' );

require_once __DIR__ . '/includes/factory-core.php';

/**
 * Testimonials Slider.
 */
final class TestimonialsSlider extends ZubFactory_Plugin {

	private $styled = false;

	protected function configure() {
		$this->slug    = 'testimonials-slider';
		$this->title   = 'Testimonials Slider';
		$this->version = TESTIMONIALS_SLIDER_VERSION;
	}

	protected function settings_fields() {
		return array(
			'accent'   => array(
				'label'   => __( 'Accent colour', 'testimonials-slider' ),
				'type'    => 'color',
				'default' => '#2271b1',
			),
			'interval' => array(
				'label'   => __( 'Auto-rotate every (seconds)', 'testimonials-slider' ),
				'type'    => 'number',
				'default' => 5,
			),
		);
	}

	protected function hooks() {
		add_shortcode( 'testimonials', array( $this, 'wrap' ) );
		add_shortcode( 'testimonial', array( $this, 'item' ) );
	}

	private $items = array();

	/** [testimonials] ... [/testimonials] */
	public function wrap( $atts, $content = '' ) {
		$this->items = array();
		do_shortcode( (string) $content ); // populates $this->items

		if ( empty( $this->items ) ) {
			return '';
		}

		$accent   = $this->option( 'accent', '#2271b1' ) ?: '#2271b1';
		$interval = max( 2, (int) $this->option( 'interval', 5 ) ) * 1000;
		$id       = 'zts-' . wp_rand( 1000, 9999 );

		ob_start();
		if ( ! $this->styled ) {
			$this->styled = true;
			$this->styles( $accent );
		}
		?>
		<div class="zts" id="<?php echo esc_attr( $id ); ?>" data-interval="<?php echo esc_attr( $interval ); ?>">
			<div class="zts-track">
				<?php foreach ( $this->items as $i => $t ) : ?>
					<figure class="zts-slide <?php echo 0 === $i ? 'is-active' : ''; ?>">
						<?php if ( $t['stars'] ) : ?>
							<div class="zts-stars"><?php echo esc_html( str_repeat( '★', $t['stars'] ) . str_repeat( '☆', 5 - $t['stars'] ) ); ?></div>
						<?php endif; ?>
						<blockquote><?php echo esc_html( $t['text'] ); ?></blockquote>
						<figcaption>
							<strong><?php echo esc_html( $t['name'] ); ?></strong>
							<?php if ( $t['role'] ) : ?><span><?php echo esc_html( $t['role'] ); ?></span><?php endif; ?>
						</figcaption>
					</figure>
				<?php endforeach; ?>
			</div>
			<?php if ( count( $this->items ) > 1 ) : ?>
				<div class="zts-dots">
					<?php foreach ( $this->items as $i => $t ) : ?>
						<button type="button" class="<?php echo 0 === $i ? 'is-active' : ''; ?>"
							data-i="<?php echo esc_attr( $i ); ?>"
							aria-label="<?php echo esc_attr( sprintf( __( 'Show testimonial %d', 'testimonials-slider' ), $i + 1 ) ); ?>"></button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<script>
		(function(){
			var root=document.getElementById('<?php echo esc_js( $id ); ?>');
			if(!root)return;
			var slides=root.querySelectorAll('.zts-slide'),
			    dots=root.querySelectorAll('.zts-dots button'),
			    n=slides.length,cur=0,
			    ms=parseInt(root.getAttribute('data-interval'),10)||5000,timer;
			if(n<2)return;
			function go(i){
				slides[cur].classList.remove('is-active');
				if(dots[cur])dots[cur].classList.remove('is-active');
				cur=(i+n)%n;
				slides[cur].classList.add('is-active');
				if(dots[cur])dots[cur].classList.add('is-active');
			}
			function play(){timer=setInterval(function(){go(cur+1);},ms);}
			function stop(){clearInterval(timer);}
			dots.forEach(function(d){d.addEventListener('click',function(){
				stop();go(parseInt(d.getAttribute('data-i'),10));play();
			});});
			root.addEventListener('mouseenter',stop);
			root.addEventListener('mouseleave',play);
			play();
		})();
		</script>
		<?php
		return ob_get_clean();
	}

	/**
	 * [testimonial name="Jane" role="CEO, Acme" stars="5"]Great product![/testimonial]
	 */
	public function item( $atts, $content = '' ) {
		$atts = shortcode_atts(
			array(
				'name'  => '',
				'role'  => '',
				'stars' => '',
			),
			$atts,
			'testimonial'
		);
		$text = trim( wp_strip_all_tags( (string) $content ) );
		if ( '' === $text ) {
			return '';
		}
		$this->items[] = array(
			'text'  => $text,
			'name'  => trim( $atts['name'] ),
			'role'  => trim( $atts['role'] ),
			'stars' => $atts['stars'] !== '' ? max( 0, min( 5, (int) $atts['stars'] ) ) : 0,
		);
		return '';
	}

	private function styles( $accent ) {
		?>
		<style>
			.zts{max-width:680px;margin:24px auto;text-align:center;font-family:inherit;position:relative}
			.zts-slide{display:none;margin:0}
			.zts-slide.is-active{display:block}
			.zts-stars{color:<?php echo esc_attr( $accent ); ?>;font-size:20px;letter-spacing:2px;margin-bottom:12px}
			.zts blockquote{font-size:20px;line-height:1.6;margin:0 0 18px;font-style:italic;color:#1e293b}
			.zts figcaption strong{display:block;font-size:16px}
			.zts figcaption span{font-size:14px;opacity:.65}
			.zts-dots{margin-top:20px;display:flex;gap:8px;justify-content:center}
			.zts-dots button{width:10px;height:10px;border-radius:50%;border:0;cursor:pointer;
				background:#cbd5e1;padding:0}
			.zts-dots button.is-active{background:<?php echo esc_attr( $accent ); ?>}
		</style>
		<?php
	}
}

add_action(
	'plugins_loaded',
	function () {
		( new TestimonialsSlider( __FILE__ ) )->boot();
	}
);
