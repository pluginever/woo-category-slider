<?php

class WC_Category_Slider_Shortcode {
	/**
	 * Shortcode constructor.
	 */
	public function __construct() {
		add_shortcode( 'woo_category_slider', array( $this, 'render' ) );
		add_shortcode( 'wc_category_slider', array( $this, 'render_shortcode_demo' ) );
	}


	public function render_shortcode_demo( $attr ) {
		ob_start();
		$attr = wp_parse_args( $attr, array(
			'template' => 'default',
		) );
		?>
		<style>
			.wc-slider {
				width: 300px !important;
				overflow: hidden;
				float: left;
				margin: 0 10px 10px 0;
			}

			.wrap {
				width: 1200px !important;
			}
		</style>
		<?php

		$files = glob( WC_SLIDER_TEMPLATES . '/*.php' );
		foreach ( $files as $file ) {
			include $file;
		}

		$file = WC_SLIDER_TEMPLATES . '/' . $attr['template'] . '.php';
		if ( file_exists( $file ) ) {
			include $file;
		}

		$html = ob_get_contents();
		ob_get_clean();

		return $html;
	}

	public function render( $attr ) {

		$params = shortcode_atts( [ 'id' => null ], $attr );
		if ( empty( $params['id'] ) ) {
			return false;
		}

		$post_id = $params['id'];

		$selected_categories = 'all';

		$theme               = wc_slider_get_settings( $post_id, 'theme', 'default' );
		$selection_type      = wc_slider_get_settings( $post_id, 'selection_type', 'all' );
		$limit_number        = wc_slider_get_settings( $post_id, 'limit_number', '10' );
		$orderby             = wc_slider_get_settings( $post_id, 'orderby', 'name' );
		$order               = wc_slider_get_settings( $post_id, 'order', 'asc' );
		$include_child       = wc_slider_get_settings( $post_id, 'include_child', 'on' );
		$show_empty          = wc_slider_get_settings( $post_id, 'show_empty', 'on' );
		$empty_name          = wc_slider_get_settings( $post_id, 'empty_name', 'off' );
		$empty_image         = wc_slider_get_settings( $post_id, 'empty_image', 'off' );
		$empty_content       = wc_slider_get_settings( $post_id, 'empty_content', 'off' );
		$empty_product_count = wc_slider_get_settings( $post_id, 'empty_product_count', 'off' );
		$empty_border        = wc_slider_get_settings( $post_id, 'empty_border', 'off' );
		$empty_button        = wc_slider_get_settings( $post_id, 'empty_button', 'off' );

		if ( 'all' != $selection_type ) {
			$selected_category_ids = wc_slider_get_settings( $post_id, 'selected_categories', [] );

			if ( is_array( $selected_category_ids ) && ! empty( $selected_category_ids ) ) {
				$selected_categories = wp_parse_id_list( $selected_category_ids );
			}
		}


		$terms = get_terms( apply_filters( 'wc_category_slider_term_list_args', array(
			'taxonomy'   => 'product_cat',
			'orderby'    => $orderby,
			'order'      => $order,
			'hide_empty' => $show_empty == 'on' ? false : true,
			'include'    => $selected_categories,
			'number'     => $limit_number,
			//'child_of'   => $include_child == 'on' ? $selected_categories : 0,
			'child_of'   => 0,
			'childless'  => false,
		), $post_id ) );

		$theme_class  = 'wc-category-' . $theme;
		$slider_class = 'wc-category-slider-' . $post_id . ' ' . $theme_class;

		ob_start();

		?>

		<div class="wc-category-slider <?php echo $slider_class; ?>" id="<?php echo 'wc-category-slider-' . $post_id ?>" data-slider-config='<?php echo $this->get_slider_config( $post_id ); ?>'>
			<?php

			foreach ( $terms as $term ) {

				$settings = wc_category_slider_get_categories( array(
					'slider_id' => $post_id,
					'include'   => $term->term_id,
				) );
				$settings = reset( $settings );

				$image = $settings['image'] != WC_SLIDER_ASSETS_URL . '/images/no-image-placeholder.jpg' ? esc_url( $settings['image'] ) : '';

				//add "empty-image" class if image is empty or hidden
				$image_class = '';
				if ( $empty_image == 'on' ) {
					$image_class = 'empty-image';
				} elseif ( empty( $image ) ) {
					$image_class = 'empty-image';
				}

				$single_classes   = array();
				$single_classes[] = $image_class;
				$single_classes[] = $empty_border == 'on' ? 'empty-border' : '';
				$single_classes   = implode( ' ', $single_classes );

				?>

				<div class="wc-slide <?php echo $single_classes ?>">

					<!--Image-->
					<?php if ( empty( $image_class ) && ! empty( $image ) ) { ?>
						<div class="wc-slide-image-wrapper">
							<?php echo sprintf( '<a class="wc-slide-link" href="%s"><img src="%s" alt="%s"></a>', $settings['url'], $image, $term->name ) ?>
						</div>
					<?php } ?>

					<div class="wc-slide-content-wrapper">

						<!--Icon-->
						<?php if ( ! empty( $settings['icon'] ) ) {
							echo sprintf( '<i class="fa %s wc-slide-icon fa-2x" aria-hidden="true"></i>', esc_attr( $settings['icon'] ) );
						} ?>

						<!--Title-->
						<?php if ( $empty_name != 'on' ) { ?>
							<a href="#" class="wc-slide-link">
								<h3 class="wc-slide-title"><?php echo $term->name ?></h3></a>
						<?php } ?>

						<!--Product Count-->
						<?php if ( $empty_product_count != 'on' ) { ?>
							<span class="wc-slide-product-count"><?php echo $term->count ?> Products</span>
						<?php } ?>

						<!--Description-->
						<?php if ( $empty_content != 'on' && ! empty( $term->description ) ) {
							echo sprintf( '<p class="wc-slide-description">%s</p>', $term->description );
						} ?>

						<!--Button-->
						<?php if ( $empty_button != 'on' ) {
							echo sprintf( '<a href="%s" class="wc-slide-button">%s</a>', esc_url( $settings['url'] ), 'Shop Now' );
						} ?>

					</div>
				</div>
			<?php }

			?>
		</div>

		<?php

		do_action( 'wc_category_slider_after_html', $post_id );

		$html = ob_get_clean();

		return $html;
	}


	/**
	 * Get slider settings
	 *
	 * @param $settings
	 *
	 * @return object
	 */
	protected function get_slider_config( $post_id ) {

		$config = array(
			'dots'               => false,
			'autoHeight'         => true,
			'singleItem'         => true,
			'autoplay'           => 'on' == wc_slider_get_settings( $post_id, 'autoplay' ) ? true : false,
			'loop'               => 'on' == wc_slider_get_settings( $post_id, 'loop' ) ? true : false,
			'lazyLoad'           => 'on' == wc_slider_get_settings( $post_id, 'lazy_load' )  ? true : false,
			'margin'             => intval( wc_slider_get_settings( $post_id, 'column_gap', 10 ) ),
			'autoplayTimeout'    => intval( wc_slider_get_settings( $post_id, 'slider_speed', 2000 ) ),
			'autoplayHoverPause' => true,
			'nav'                => 'on' == wc_slider_get_settings( $post_id, 'hide_nav' ) ? true : false,
			'navText'            => [ '<i class="fa fa-chevron-left"></i>', '<i class="fa fa-chevron-right"></i>' ],
			'stagePadding'       => 4,
			'items'              => intval( wc_slider_get_settings( $post_id, 'cols', 3 ) ),
			'responsive'         => [
				'0'    => [
					'items' => intval( wc_slider_get_settings( $post_id, 'phone_cols', 3 ) ),
				],
				'600'  => [
					'items' => intval( wc_slider_get_settings( $post_id, 'tab_cols', 3 ) ),
				],
				'1000' => [
					'items' => intval( wc_slider_get_settings( $post_id, 'cols', 3 ) ),
				],
			],
		);

//		if ( ! empty( $settings['fluid_speed'] ) ) {
//			$config['fluidSpeed'] = intval( wc_slider_get_settings( $post_id, 'slider_speed' ) );
//			$config['smartSpeed'] = intval( wc_slider_get_settings( $post_id, 'slider_speed' ) );
//		}

		$config = apply_filters( 'wc_slider_config', $config );

		return json_encode( $config );
	}

}

new WC_Category_Slider_Shortcode();
