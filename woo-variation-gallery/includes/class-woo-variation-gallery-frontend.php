<?php

defined( 'ABSPATH' ) or die( 'Keep Silent' );

use Automattic\WooCommerce\Enums\ProductType;

if ( ! class_exists( 'Woo_Variation_Gallery_Frontend' ) ):

	class Woo_Variation_Gallery_Frontend {

		protected static $_instance = null;

		protected function __construct() {
			$this->includes();
			$this->hooks();
			$this->init();
			do_action( 'woo_variation_gallery_frontend_loaded', $this );
		}

		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		protected function includes() {
			require_once dirname( __FILE__ ) . '/class-woo-variation-gallery-compatibility.php';
		}

		protected function hooks(): void {
			add_filter( 'body_class', array( $this, 'body_class' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_filter( 'woocommerce_post_class', array( $this, 'post_class' ), 25, 2 );
			add_filter( 'woocommerce_available_variation', array( $this, 'get_available_variation_gallery' ), 90, 3 );

			add_action( 'wc_ajax_get_default_gallery', array( $this, 'handle_get_default_gallery' ) );
			add_action( 'wc_ajax_get_variation_gallery', array( $this, 'handle_get_variation_gallery' ) );

			add_filter( 'disable_woo_variation_gallery', array( $this, 'disable_for_specific_product_type' ), 9 );
			add_filter( 'woo_variation_product_gallery_inline_style', array( $this, 'gallery_inline_style' ) );

			add_action( 'after_setup_theme', array( $this, 'enable_theme_support' ), 200 );
			add_action( 'wp_footer', array( $this, 'slider_template_js' ) );

			add_filter( 'wc_get_template', array( $this, 'gallery_template' ), 30, 2 );
			add_filter( 'wc_get_template_part', array( $this, 'gallery_template_part' ), 30, 2 );
		}

		protected function init() {
			Woo_Variation_Gallery_Compatibility::instance();
		}

		// Start

		public function remove_default_template() {
			remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 10 );
			remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );

			// remove_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );
			// remove_action( 'woocommerce_before_single_product_summary_product_images', 'woocommerce_show_product_thumbnails', 20 );
		}

		public function get_product_default_attributes( $product_id ) {
			$product = wc_get_product( $product_id );

			if ( ! $product->is_type( $this->get_variable_product_type() ) ) {
				return array();
			}

			$variable_product = new WC_Product_Variable( absint( $product_id ) );

			// $selected = isset( $_REQUEST[ $selected_key ] ) ? wc_clean( wp_unslash( $_REQUEST[ $selected_key ] ) ) : $args['product']->get_variation_default_attribute( $args['attribute'] );

			$selected_attributes = array();
			$default_attributes  = $variable_product->get_default_attributes();
			$attributes          = $variable_product->get_attributes();
			foreach ( $attributes as $attribute_name => $attribute_data ) {
				$selected_key = wc_variation_attribute_name( $attribute_name );
				if ( isset( $_REQUEST[ $selected_key ] ) ) {
					$selected_attributes[ sanitize_title( $attribute_name ) ] = wc_clean( wp_unslash( $_REQUEST[ $selected_key ] ) );
				}
			}

			return empty( $selected_attributes ) ? $default_attributes : $selected_attributes;
		}

		public function get_product_default_variation_id( $product, $attributes ) {
			if ( is_numeric( $product ) ) {
				$product = wc_get_product( $product );
			}

			if ( ! $product->is_type( 'variable' ) ) {
				return 0;
			}

			$product_id = $product->get_id();

			foreach ( $attributes as $key => $value ) {
				if ( strpos( $key, 'attribute_' ) === 0 ) {
					continue;
				}

				unset( $attributes[ $key ] );
				$attributes[ sprintf( 'attribute_%s', $key ) ] = $value;
			}

			$data_store = WC_Data_Store::load( 'product' );

			return $data_store->find_matching_product_variation( $product, $attributes );
		}

		public function enable_theme_support() {
			// WooCommerce.
			add_theme_support( 'wc-product-gallery-zoom' );
			add_theme_support( 'wc-product-gallery-lightbox' );
			$this->gallery_thumbnail_image_width();
		}

		public function gallery_thumbnail_image_width() {
			// Set from gallery settings
			$thumbnail_width = absint( woo_variation_gallery()->get_option( 'thumbnail_width', 100 ) );
			if ( $thumbnail_width > 0 ) {
				add_theme_support( 'woocommerce', array( 'gallery_thumbnail_image_width' => absint( $thumbnail_width ) ) );
			}
		}

		public function post_class( $classes, $product ) {
			$classes[] = 'woo-variation-gallery-product';

			return $classes;
		}

		public function body_class( $classes ) {
			$classes[] = 'woo-variation-gallery';
			$classes[] = sprintf( 'woo-variation-gallery-theme-%s', strtolower( basename( get_template_directory() ) ) );

			if ( is_rtl() ) {
				$classes[] = 'woo-variation-gallery-rtl';
			}

			if ( woo_variation_gallery()->is_pro() ) {
				$classes[] = 'woo-variation-gallery-pro';
			}

			return array_unique( array_values( $classes ) );
		}

		public function enqueue_scripts() {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			// Disable gallery on scripts

			if ( apply_filters( 'disable_woo_variation_gallery', false ) ) {
				return false;
			}


			$single_image_width     = absint( wc_get_theme_support( 'single_image_width', get_option( 'woocommerce_single_image_width', 600 ) ) );
			$gallery_thumbnails_gap = absint( woo_variation_gallery()->get_option( 'thumbnails_gap', apply_filters( 'woo_variation_gallery_default_thumbnails_gap', 0 ), 'woo_variation_gallery_thumbnails_gap' ) );
			$gallery_width          = absint( woo_variation_gallery()->get_option( 'width', apply_filters( 'woo_variation_gallery_default_width', 30 ), 'woo_variation_gallery_width' ) );
			$gallery_margin         = absint( woo_variation_gallery()->get_option( 'margin', apply_filters( 'woo_variation_gallery_default_margin', 30 ), 'woo_variation_gallery_margin' ) );

			$gallery_medium_device_width      = absint( woo_variation_gallery()->get_option( 'medium_device_width', apply_filters( 'woo_variation_gallery_medium_device_width', 0 ), 'woo_variation_gallery_medium_device_width' ) );
			$gallery_small_device_width       = absint( woo_variation_gallery()->get_option( 'small_device_width', apply_filters( 'woo_variation_gallery_small_device_width', 720 ), 'woo_variation_gallery_small_device_width' ) );
			$gallery_extra_small_device_width = absint( woo_variation_gallery()->get_option( 'extra_small_device_width', apply_filters( 'woo_variation_gallery_extra_small_device_width', 320 ), 'woo_variation_gallery_extra_small_device_width' ) );
			$thumbnail_position               = sanitize_text_field( woo_variation_gallery()->get_option( 'position', 'bottom', 'woo_variation_gallery_thumbnail_position' ) );

			wp_enqueue_script( 'woo-variation-gallery-slider', esc_url( woo_variation_gallery()->assets_url( "/js/slick{$suffix}.js" ) ), array( 'jquery' ), '1.8.1', true );

			wp_enqueue_style( 'woo-variation-gallery-slider', esc_url( woo_variation_gallery()->assets_url( "/css/slick{$suffix}.css" ) ), array(), '1.8.1' );

			wp_enqueue_script( 'woo-variation-gallery', esc_url( woo_variation_gallery()->assets_url( "/js/frontend{$suffix}.js" ) ), array(
				'jquery',
				'wp-util',
				'woo-variation-gallery-slider',
				'imagesloaded',
				'wc-add-to-cart-variation',
			), woo_variation_gallery()->assets_version( "/js/frontend{$suffix}.js" ), true );

			wp_localize_script( 'woo-variation-gallery', 'woo_variation_gallery_options', apply_filters( 'woo_variation_gallery_js_options', array(
				'gallery_reset_on_variation_change' => wc_string_to_bool( woo_variation_gallery()->get_option( 'reset_on_variation_change', 'no', 'woo_variation_gallery_reset_on_variation_change' ) ),
				'enable_gallery_zoom'               => wc_string_to_bool( woo_variation_gallery()->get_option( 'zoom', 'yes', 'woo_variation_gallery_zoom' ) ),
				'enable_gallery_lightbox'           => wc_string_to_bool( woo_variation_gallery()->get_option( 'lightbox', 'yes', 'woo_variation_gallery_lightbox' ) ),
				'enable_gallery_preload'            => wc_string_to_bool( woo_variation_gallery()->get_option( 'image_preload', 'yes', 'woo_variation_gallery_image_preload' ) ),
				'preloader_disable'                 => wc_string_to_bool( woo_variation_gallery()->get_option( 'preloader_disable', 'no', 'woo_variation_gallery_preloader_disable' ) ),
				'enable_thumbnail_slide'            => wc_string_to_bool( woo_variation_gallery()->get_option( 'thumbnail_slide', 'yes', 'woo_variation_gallery_thumbnail_slide' ) ),
				'gallery_thumbnails_columns'        => absint( woo_variation_gallery()->get_option( 'thumbnails_columns', apply_filters( 'woo_variation_gallery_default_thumbnails_columns', 4 ), 'woo_variation_gallery_thumbnails_columns' ) ),
				'is_vertical'                       => in_array( $thumbnail_position, array( 'left', 'right' ) ),
				'thumbnail_position'                => trim( $thumbnail_position ),
				'thumbnail_position_class_prefix'   => 'woo-variation-gallery-thumbnail-position-',
				// 'wrapper'                           => sanitize_text_field( get_option( 'woo_variation_gallery_and_variation_wrapper', apply_filters( 'woo_variation_gallery_and_variation_default_wrapper', '.product' ) ) ),
				'is_mobile'                         => wp_is_mobile(),
				'gallery_default_device_width'      => $gallery_width,
				'gallery_medium_device_width'       => $gallery_medium_device_width,
				'gallery_small_device_width'        => $gallery_small_device_width,
				'gallery_extra_small_device_width'  => $gallery_extra_small_device_width,

			) ) );

			// Stylesheet
			wp_enqueue_style( 'dashicons' );

			wp_enqueue_style( 'woo-variation-gallery', esc_url( woo_variation_gallery()->assets_url( "/css/frontend{$suffix}.css" ) ), array(), woo_variation_gallery()->assets_version( "/css/frontend{$suffix}.css" ) );

			$this->add_inline_style();

			do_action( 'woo_variation_gallery_enqueue_scripts', $this );
		}

		public function add_inline_style() {
			if ( apply_filters( 'disable_woo_variation_gallery', false ) ) {
				return false;
			}

			$single_image_width = absint( wc_get_theme_support( 'single_image_width', get_option( 'woocommerce_single_image_width', 600 ) ) );

			$gallery_thumbnails_columns = absint( woo_variation_gallery()->get_option( 'thumbnails_columns', 4 ) );

			$gallery_thumbnails_gap = absint( woo_variation_gallery()->get_option( 'thumbnails_gap', apply_filters( 'woo_variation_gallery_default_thumbnails_gap', 0 ) ) );
			$gallery_width          = absint( woo_variation_gallery()->get_option( 'width', apply_filters( 'woo_variation_gallery_default_width', 30 ) ) );
			$gallery_margin         = absint( woo_variation_gallery()->get_option( 'margin', apply_filters( 'woo_variation_gallery_default_margin', 30 ) ) );

			$gallery_medium_device_width      = absint( woo_variation_gallery()->get_option( 'medium_device_width', apply_filters( 'woo_variation_gallery_medium_device_width', 0 ) ) );
			$gallery_small_device_width       = absint( woo_variation_gallery()->get_option( 'small_device_width', apply_filters( 'woo_variation_gallery_small_device_width', 720 ) ) );
			$gallery_small_device_clear_float = wc_string_to_bool( woo_variation_gallery()->get_option( 'small_device_clear_float', apply_filters( 'woo_variation_gallery_small_device_clear_float', 'no' ) ) );


			$gallery_extra_small_device_width       = absint( woo_variation_gallery()->get_option( 'extra_small_device_width', apply_filters( 'woo_variation_gallery_extra_small_device_width', 320 ) ) );
			$gallery_extra_small_device_clear_float = wc_string_to_bool( woo_variation_gallery()->get_option( 'extra_small_device_clear_float', apply_filters( 'woo_variation_gallery_extra_small_device_clear_float', 'no' ) ) );


			ob_start();
			include_once dirname( __FILE__ ) . '/stylesheet.php';
			$css = ob_get_clean();
			$css = $this->clean_css( $css );

			$css = apply_filters( 'woo_variation_gallery_inline_style', $css );

			wp_add_inline_style( 'woo-variation-gallery', $css );
		}

		public function clean_css( $inline_css ) {
			$inline_css = str_ireplace( array(
				'<style type="text/css">',
				'<style>',
				'</style>',
				"\r\n",
				"\r",
				"\n",
				"\t",
			), '', $inline_css );
			// Normalize whitespace
			$inline_css = preg_replace( "/\s+/", ' ', $inline_css );

			return trim( $inline_css );
		}

		public function is_wc_enabled(): bool {
			if ( version_compare( wc()->version, '11.1.0', '>=' ) ) {
				return true;
			}

			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				$is_enabled = \Automattic\WooCommerce\Utilities\FeaturesUtil::feature_is_enabled( 'variation_gallery' );

				if ( $is_enabled ) {
					return true;
				}
			}

			return false;
		}

		public function wpml_object_id( $object_id, $type = 'post', $language = null ) {
			$current_language = apply_filters( 'wpml_current_language', $language );

			return apply_filters( 'wpml_object_id', $object_id, $type, true, $current_language );
		}

		public function get_gallery_image_ids( $variation_id ): array {
			// Comes from WooCommerce.
			if ( $this->is_wc_enabled() ) {
				return wc_get_product( $variation_id )->get_gallery_image_ids();
			}

			$images_as_string = get_post_meta( $variation_id, 'woo_variation_gallery_images', true );
			$images           = wp_parse_id_list( $images_as_string );

			// Comes from Gallery Plugin.
			return array_map( array( $this, 'wpml_object_id' ), $images );
		}

		public function get_available_variation_gallery( $available_variation, $product, $variation ) {
			// Product Data.
			$product_id       = absint( $product->get_id() );
			$product_image_id = absint( $product->get_image_id( 'edit' ) );

			$product_feature_image_id = $product_image_id;
			$product_gallery_images   = array_map( 'absint', $product->get_gallery_image_ids( 'edit' ) );

			if ( $product_image_id > 0 ) {
				array_unshift( $product_gallery_images, $product_image_id );
			}

			$has_product_gallery_images = count( $product_gallery_images ) > 0;

			// Variation data.
			$variation_id             = absint( $variation->get_id() );
			$variation_image_id       = absint( $variation->get_image_id( 'edit' ) );
			$variation_gallery_images = $this->get_gallery_image_ids( $variation_id );

			if ( $variation_image_id > 0 ) {
				array_unshift( $variation_gallery_images, $variation_image_id );
			}

			$has_variation_gallery_images = count( $variation_gallery_images ) > 0;

			// Has variation gallery.
			if ( $has_variation_gallery_images ) {
				$gallery_images = $variation_gallery_images;
			} else {
				$gallery_images = $product_gallery_images;
			}

			$gallery_images = array_map( 'absint', $gallery_images );
			// Prevent duplicate image load.
			$gallery_images = array_unique( $gallery_images );
			// Remove Empty.
			$gallery_images = array_filter( $gallery_images, static function ( $image_id ) {
				return $image_id > 0;
			} );

			$available_variation['variation_gallery_images'] = array();

			// Add default gallery to variation gallery.
			$include_default_gallery = wc_string_to_bool( woo_variation_gallery()->get_option( 'include_default_gallery', 'no' ) );

			if ( $include_default_gallery ) {
				$gallery_images = array_unique( array_merge( $gallery_images, $product_gallery_images ) );
			}

			// Hide default image from gallery and ajax.
			$gallery_visibility_old                = wc_string_to_bool( woo_variation_gallery()->get_option( 'remove_featured_image', 'no' ) ) ? 'hide_from_all_gallery' : 'default';
			$default_gallery_visibility            = woo_variation_gallery()->get_option( 'product_image_visibility', $gallery_visibility_old );
			$default_gallery_visibility_conditions = array( 'hide_from_all_gallery', 'hide_from_variation_gallery' );

			if ( in_array( $default_gallery_visibility, $default_gallery_visibility_conditions, true ) ) {
				$gallery_images = array_filter( $gallery_images, static function ( $image_id ) use ( $product_feature_image_id ) {
					return $image_id !== $product_feature_image_id;
				} );
			}

			$show_placeholder_image = wc_string_to_bool( woo_variation_gallery()->get_option( 'show_placeholder_image', 'no' ) );
			$placeholder_image_id   = absint( get_option( 'woocommerce_placeholder_image', 0 ) );

			// No variation gallery available and show placeholder image enabled.
			if ( ! $has_variation_gallery_images && $show_placeholder_image ) {
				$gallery_images   = array();
				$gallery_images[] = $placeholder_image_id;
			}

			// No Gallery Image Available, not in default or variation.
			if ( count( $gallery_images ) < 1 ) {
				$gallery_images   = array();
				$gallery_images[] = $placeholder_image_id;
			}

			$gallery_images = apply_filters( 'woo_variation_gallery_available_variation_gallery_images', $gallery_images, $product, $variation );

			foreach ( $gallery_images as $gallery_image_index => $variation_gallery_image_id ) {
				$data = $this->get_data_with_markup( $variation_gallery_image_id, $product_id, $gallery_image_index );

				$available_variation['variation_gallery_images'][] = apply_filters( 'woo_variation_gallery_get_variation_gallery_image', $data );
			}

			return apply_filters( 'woo_variation_gallery_available_variation_gallery', $available_variation, $variation, $product_id );
		}

		public function get_variable_product_type(): string {
			return class_exists( ProductType::class ) ? ProductType::VARIABLE : 'variable';
		}

		public function get_product_gallery_data( $product_id, $variation_id = 0 ): array {
			$product = wc_get_product( $product_id );

			$options = array(
				'product_id'          => $product_id,
				'variation_id'        => 0,
				'product_type'        => $product->get_type(),
				'has_product_image'   => false,
				'has_product_gallery' => false, // If gallery is more then 1
				'images'              => array(),
				'has_image'           => false,
			);

			$product_image_id  = absint( $product->get_image_id( 'edit' ) );
			$gallery_image_ids = array_map( 'absint', $product->get_gallery_image_ids( 'edit' ) );

			if ( $product_image_id > 0 ) {
				array_unshift( $gallery_image_ids, $product_image_id );
			}

			// NON Variation Products.
			if ( ! $product->is_type( $this->get_variable_product_type() ) ) {
				$options['images']              = array_unique( $gallery_image_ids );
				$options['has_product_image']   = count( $options['images'] ) > 0;
				$options['has_product_gallery'] = count( $options['images'] ) > 1;

				return $options;
			}


			// Set default variation.
			$default_attributes = $this->get_product_default_attributes( $product_id );

			$default_variation_id = absint( $this->get_product_default_variation_id( $product, $default_attributes ) );

			$options['images'] = $gallery_image_ids;

			if ( $variation_id > 0 && $default_variation_id > 0 ) {
				$product_variation = $this->get_available_variation( $product_id, $default_variation_id );

				$variation_image_id    = absint( $product_variation['image_id'] );
				$variation_gallery_ids = $this->get_gallery_image_ids( $default_variation_id );

				if ( $variation_image_id > 0 ) {
					array_unshift( $variation_gallery_ids, $variation_image_id );
				}

				$options['images'] = $variation_gallery_ids;

				$options['variation_id'] = $default_variation_id;

				// Add default gallery to variation gallery.
				$include_default_gallery = wc_string_to_bool( woo_variation_gallery()->get_option( 'include_default_gallery', 'no' ) );

				if ( $include_default_gallery ) {
					$options['images'] = array_unique( array_merge( $options['images'], $gallery_image_ids ) );
				}
			}

			// Hide default image from gallery and ajax.
			$gallery_visibility_old                = wc_string_to_bool( woo_variation_gallery()->get_option( 'remove_featured_image', 'no' ) ) ? 'hide_from_all_gallery' : 'default';
			$default_gallery_visibility            = woo_variation_gallery()->get_option( 'product_image_visibility', $gallery_visibility_old );
			$default_gallery_visibility_conditions = array( 'hide_from_all_gallery', 'hide_from_product_gallery' );

			if ( in_array( $default_gallery_visibility, $default_gallery_visibility_conditions, true ) ) {
				$options['images'] = array_filter( $options['images'], static function ( $image_id ) use ( $product_image_id ) {
					return $image_id !== $product_image_id;
				} );
			}

			$options['images']              = array_unique( $options['images'] );
			$options['has_product_image']   = count( $options['images'] ) > 0;
			$options['has_product_gallery'] = count( $options['images'] ) > 1;
			$options['has_image']           = count( $options['images'] ) > 0;

			return $options;
		}

		//-------------------------------------------------------------------------------
		// Gallery Template
		// Copy of: wc_get_product_attachment_props( $attachment_id = null, $product = false )
		//-------------------------------------------------------------------------------

		public function get_video_info( $url ): array {
			$videos = array(
				'type'      => false,
				'id'        => 0,
				'url'       => $url,
				'embed_url' => $url,
			);

			$youtube_hosts = array( 'www.youtube.com', 'youtube.com', 'youtu.be', 'www.youtu.be' );
			$vimeo_hosts   = array( 'vimeo.com', 'www.vimeo.com', 'player.vimeo.com' );

			$_url  = wp_parse_url( esc_url( $url ) );
			$_args = wp_parse_args( $_url['query'] ?? array() );

			if ( ! $_url || ! isset( $_url['host'] ) ) {
				return $videos;
			}

			// Youtube
			if ( in_array( $_url['host'], $youtube_hosts ) ) {
				$id = str_ireplace( array( '/shorts/', '/embed/', '/' ), '', $_url['path'] );


				// Old Watch Path
				if ( stripos( $_url['path'], 'watch' ) === 1 ) {
					wp_parse_str( $_url['query'], $query );
					$id = $query ? $query['v'] : false;
				}

				$args = wp_parse_args( array(
					'feature'     => 'oembed',
					'enablejsapi' => '1',
					'controls'    => '0',
				), $_args );

				return wp_parse_args( array(
					'type'      => $id ? 'youtube' : false,
					'id'        => $id,
					'embed_url' => add_query_arg( $args, sprintf( 'https://www.youtube.com/embed/%s', $id ) ),
				), $videos );


				// Shorts or embed video
				//if ( stripos( $url['path'], 'shorts' ) === 1 || stripos( $url['path'], 'embed' ) === 1 ) {
				//}
			}

			// Vimeo
			if ( in_array( $_url['host'], $vimeo_hosts ) ) {
				$id = str_ireplace( array( '/video/', '/' ), '', $_url['path'] );

				$args = wp_parse_args( array(
					'api' => '1',
					// 'background' => '1'
				), $_args );

				return wp_parse_args( array(
					'type'      => $id ? 'vimeo' : false,
					'id'        => $id,
					'embed_url' => add_query_arg( $args, sprintf( 'https://player.vimeo.com/video/%s', $id ) ),
				), $videos );
			}

			return $videos;
		}

		public function get_embed_url( $main_link ) {
			$video_info = $this->get_video_info( $main_link );

			return apply_filters( 'woo_variation_gallery_get_embed_url', $video_info['embed_url'], $video_info );
		}

		// 6.2
		public function get_html_attribute( $html, $tag, $attributes ) {
			$processor = new WP_HTML_Tag_Processor( $html );

			if ( $processor->next_tag( $tag ) ) {
				$attribute_value = $processor->get_attribute( $attributes );

				if ( is_null( $attribute_value ) ) {
					return '';
				}

				return $attribute_value;
			}

			return '';
		}

		public function get_data_with_markup( $attachment_id, $product_id = 0, $image_index = 0 ) {
			$image_data       = $this->get_product_attachment_props( $attachment_id, $product_id, $image_index );
			$image_markup     = $this->get_product_gallery_images_html( $image_data );
			$thumbnail_markup = $this->get_product_gallery_thumbnail_html( $image_data );

			return apply_filters( 'woo_variation_gallery_get_data_with_markup', array(
				'data'             => $image_data,
				'gallery_markup'   => $image_markup,
				'thumbnail_markup' => $thumbnail_markup,
			), $image_data );
		}


		public function get_product( $product_id = 0 ) {
			if ( $product_id > 0 ) {
				$product     = wc_get_product( $product_id );
				$has_product = ( $product instanceof WC_Product );

				return $has_product ? $product : null;
			}

			$has_product = array_key_exists( 'product', $GLOBALS );
			$product     = $has_product ? $GLOBALS['product'] : null;

			return $has_product ? $product : null;
		}

		public function get_thumbnail_columns( $product_id = 0 ): int {
			$product = $this->get_product( $product_id );

			$wc_columns = apply_filters( 'woocommerce_product_thumbnails_columns', 4 );

			return absint( woo_variation_gallery()->get_option( 'thumbnails_columns', apply_filters( 'woo_variation_gallery_default_thumbnails_columns', absint( $wc_columns ), $product ) ) );
		}

		public function get_product_attachment_props( $attachment_id, $product_id = 0, $image_index = 0 ) {
			$attachment    = get_post( $attachment_id );
			$no_attachment = is_null( $attachment );

			$data = array(
				'image_id'       => $attachment_id,
				'product_id'     => $product_id,
				'has_video'      => false,
				'images'         => array(
					array(
						'src'       => '',
						'srcset'    => '',
						'full'      => '',
						'thumbnail' => '',
					),
				),
				'videos'         => array(),
				'image_html'     => '',
				'thumbnail_html' => '',
				'video_html'     => '',
			);

			if ( $no_attachment ) {
				return apply_filters( 'woo_variation_gallery_get_image_props', $data, $no_attachment );
			}

			$product = $this->get_product( $product_id );

			$product_id  = is_null( $product ) ? 0 : $product->get_id();
			$has_product = $product_id > 0;

			$attachment_alt_text = trim( wp_strip_all_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) );
			$image_alt_text      = '' === $attachment_alt_text && $has_product ? sprintf( esc_html__( '%1$s - Image %2$s', 'woocommerce' ), $product->get_title(), ( $image_index + 1 ) ) : $attachment_alt_text;
			$thumbnail_text      = '' === $attachment_alt_text && $has_product ? sprintf( esc_html__( '%1$s - Thumbnail Image %2$s', 'woocommerce' ), $product->get_title(), ( $image_index + 1 ) ) : $attachment_alt_text;

			$gallery_thumbnail = wc_get_image_size( 'gallery_thumbnail' );
			$thumbnail_size    = apply_filters( 'woocommerce_gallery_thumbnail_size', array( $gallery_thumbnail['width'], $gallery_thumbnail['height'] ) );
			$image_size        = apply_filters( 'woocommerce_gallery_image_size', 'woocommerce_single' );
			$full_size         = apply_filters( 'woocommerce_gallery_full_size', apply_filters( 'woocommerce_product_thumbnails_large_size', 'full' ) );
			$thumbnail_html    = wp_get_attachment_image( $attachment_id, $thumbnail_size, false, array(
				'alt' => $thumbnail_text,
			) );
			$full_src          = wp_get_attachment_image_src( $attachment_id, $full_size );

			$image_size_class = $image_size;
			if ( is_array( $image_size_class ) ) {
				$image_size_class = implode( 'x', $image_size_class );
			}
			$image_class = sprintf( 'wp-post-image wvg-post-image attachment-%1$s size-%1$s', $image_size_class );

			/**
			 * Filters the attributes for the image markup.
			 *
			 * @param array{
			 *     title: string,
			 *     data-caption: string,
			 *     data-src: string,
			 *     data-large_image: string,
			 *     data-large_image_width: numeric,
			 *     data-large_image_height: numeric,
			 *     class: string,
			 *     alt: string,
			 *     loading: string,
			 * } $image_attributes Attributes for the image markup.
			 *
			 * @since 3.3.2
			 *
			 */
			$image_params = apply_filters(
				'woocommerce_gallery_image_html_attachment_image_params',
				array(
					'title'                   => _wp_specialchars( get_post_field( 'post_title', $attachment_id ), ENT_QUOTES, 'UTF-8', true ),
					'data-caption'            => _wp_specialchars( get_post_field( 'post_excerpt', $attachment_id ), ENT_QUOTES, 'UTF-8', true ),
					'data-src'                => isset( $full_src[0] ) ? esc_url( $full_src[0] ) : '',
					'data-large_image'        => isset( $full_src[0] ) ? esc_url( $full_src[0] ) : '',
					'data-large_image_width'  => isset( $full_src[1] ) ? esc_attr( $full_src[1] ) : '',
					'data-large_image_height' => isset( $full_src[2] ) ? esc_attr( $full_src[2] ) : '',
					'class'                   => esc_attr( $image_class ),
					'alt'                     => esc_attr( $image_alt_text ),
					'loading'                 => 'lazy',
				),
				$attachment_id,
				$image_size,
				true
			);

			if ( isset( $image_params['title'] ) ) {
				unset( $image_params['title'] );
			}

			if ( $image_index === 0 ) {
				unset( $image_params['loading'] );
			}

			$image_html = wp_get_attachment_image(
				$attachment_id,
				$image_size,
				false,
				$image_params
			);

			$image_src     = $this->get_html_attribute( $image_html, 'img', 'src' );
			$image_srcset  = $this->get_html_attribute( $image_html, 'img', 'srcset' );
			$thumbnail_src = $this->get_html_attribute( $thumbnail_html, 'img', 'src' );

			// @TODO: Add Archive Image
			$images = array(
				'src'       => esc_url_raw( $image_src ),
				'srcset'    => esc_html( $image_srcset ),
				'full'      => isset( $full_src[0] ) ? esc_url_raw( $full_src[0] ) : '',
				'thumbnail' => esc_url_raw( $thumbnail_src ),
			);

			$data = array(
				'image_id'       => $attachment_id,
				'product_id'     => $product_id,
				'has_video'      => false,
				'images'         => $images,
				'videos'         => array(),
				'image_html'     => $image_html,
				'thumbnail_html' => $thumbnail_html,
				'video_html'     => '',
			);

			return apply_filters( 'woo_variation_gallery_get_image_props', $data, true );
		}

		public function get_product_gallery_images_html( $data ): string {
			$image = $data['image_html'];

			$has_video = $data['has_video'];

			$classes = array( 'wvg-gallery-image' );

			if ( $has_video ) {
				$classes[] = 'wvg-gallery-video-slider';
			}

			$classes = apply_filters( 'woo_variation_gallery_slider_image_html_class', $classes, $data );

			$template = '<div class="wvg-single-gallery-image-container">%s</div>';

			$inner_html = sprintf( $template, $image );

			if ( $has_video ) {
				$videos       = $data['videos'];
				$video_type   = $videos['type'];
				$video_markup = $data['video_html'];

				if ( 'iframe' === $video_type ) {
					$template   = '<div class="wvg-single-gallery-iframe-container" style="--_video_ratio: %s">%s</div>';
					$inner_html = sprintf( $template, $videos['video_ratio'], $video_markup );
				}

				if ( 'video' === $video_type ) {
					$template   = '<div class="wvg-single-gallery-video-container" style="--_video_ratio: %s">%s</div>';
					$inner_html = sprintf( $template, $videos['video_ratio'], $video_markup );
				}
			}

			$inner_html = apply_filters( 'woo_variation_gallery_image_inner_html', $inner_html, $data, $template );

			return '<div class="' . esc_attr( implode( ' ', array_map( 'sanitize_html_class', array_unique( $classes ) ) ) ) . '">' . $inner_html . '</div>';
		}

		public function get_product_gallery_thumbnail_html( $data ): string {
			$image = $data['thumbnail_html'];

			$has_video = $data['has_video'];

			$classes = array( 'wvg-gallery-thumbnail-image' );

			if ( $has_video ) {
				$classes[] = 'wvg-gallery-video-thumbnail';
			}

			$classes = apply_filters( 'woo_variation_gallery_thumbnail_image_html_class', $classes, $data );

			$template   = '<div class="wvg-gallery-thumbnail-image-container">%s</div>';
			$inner_html = sprintf( $template, $image );
			$inner_html = apply_filters( 'woo_variation_gallery_thumbnail_image_inner_html', $inner_html, $data, $template );


			return '<div class="' . esc_attr( implode( ' ', array_map( 'sanitize_html_class', array_unique( $classes ) ) ) ) . '">' . $inner_html . '</div>';
		}

		public function get_available_variation( $product_id, $variation_id ) {
			return ( new WC_Product_Variable( $product_id ) )->get_available_variation( $variation_id );
		}

		public function get_available_variations( $product ) {
			if ( is_numeric( $product ) ) {
				$product = wc_get_product( absint( $product ) );
			}

			return $product->get_available_variations();
		}

		// FOR AJAX RESPONSE
		public function get_default_gallery_images( $product_id ) {
			$data = $this->get_product_gallery_data( $product_id );

			$gallery_images = $data['images'];
			$has_images     = $data['has_image'];

			$images = array();
			foreach ( $gallery_images as $gallery_image_index => $gallery_image ) {
				$data     = $this->get_data_with_markup( $gallery_image, $product_id, $gallery_image_index );
				$images[] = apply_filters( 'woo_variation_gallery_get_default_gallery_image', $data );
			}

			// @TODO: Setting For Show default gallery if no variation image is available or show placeholder.
			// $show_placeholder_image = wc_string_to_bool( woo_variation_gallery()->get_option( 'show_placeholder_image', 'no' ) );

			if ( ! $has_images ) {
				$placeholder_image_id = absint( get_option( 'woocommerce_placeholder_image', 0 ) );
				$data                 = $this->get_data_with_markup( $placeholder_image_id, $product_id );
				$images[]             = apply_filters( 'woo_variation_gallery_get_default_gallery_placeholder_image', $data );
			}

			return apply_filters( 'woo_variation_gallery_get_default_gallery_images', $images, $product_id );
		}

		// FOR AJAX RESPONSE
		public function get_variation_gallery_images( $product_id ) {
			$added                = array();
			$images               = array();
			$available_variations = $this->get_available_variations( $product_id );

			$product                  = wc_get_product( $product_id );
			$product_feature_image_id = absint( $product->get_image_id( 'edit' ) );

			// Hide default image.
			$gallery_visibility_old                = wc_string_to_bool( woo_variation_gallery()->get_option( 'remove_featured_image', 'no' ) ) ? 'hide_from_all_gallery' : 'default';
			$default_gallery_visibility            = woo_variation_gallery()->get_option( 'product_image_visibility', $gallery_visibility_old );
			$default_gallery_visibility_conditions = array( 'hide_from_all_gallery', 'hide_from_variation_gallery' );

			foreach ( $available_variations as $i => $variation ) {
				foreach ( $variation['variation_gallery_images'] as $image_index => $image ) {
					$image_id = absint( $image['data']['image_id'] );

					if ( $product_feature_image_id === $image_id && in_array( $default_gallery_visibility, $default_gallery_visibility_conditions, true ) ) {
						continue;
					}

					if ( in_array( $image_id, $added, true ) ) {
						continue;
					}

					$images[] = $image;
					$added[]  = $image_id;
				}
			}

			return apply_filters( 'woo_variation_gallery_get_variation_gallery_images', $images, $product_id );
		}

		// Ajax Response
		public function handle_get_default_gallery(): void {
			ob_start();

			if ( empty( $_POST ) || empty( $_POST['product_id'] ) ) {
				wp_send_json( false );
			}

			$product_id = absint( $_POST['product_id'] );

			$images = $this->get_default_gallery_images( $product_id );

			wp_send_json( apply_filters( 'woo_variation_gallery_get_default_gallery', $images, $product_id ) );
		}

		// Ajax Response
		public function handle_get_variation_gallery(): void {
			ob_start();

			if ( empty( $_POST ) || empty( $_POST['product_id'] ) ) {
				wp_send_json( false );
			}

			$product_id = absint( $_POST['product_id'] );

			$images = $this->get_variation_gallery_images( $product_id );

			wp_send_json( apply_filters( 'woo_variation_gallery_get_variation_gallery', $images, $product_id ) );
		}

		public function disable_for_specific_product_type( $default ) {
			if ( function_exists( 'is_product' ) && is_product() ) {
				$product = wc_get_product();

				$product_types         = woo_variation_gallery()->get_option( 'disabled_product_type', array(
					'gift-card',
					'bundle',
				) );
				$disabled_product_type = map_deep( $product_types, 'sanitize_text_field' );

				return is_object( $product ) ? in_array( $product->get_type(), $disabled_product_type ) : $default;
			}

			return $default;
		}

		public function gallery_inline_style( $styles ) {
			$gallery_width = absint( woo_variation_gallery()->get_option( 'width', apply_filters( 'woo_variation_gallery_default_width', 30 ), 'woo_variation_gallery_width' ) );

			if ( $gallery_width > 99 ) {
				$styles['float']   = 'none';
				$styles['display'] = 'block';
			}

			return $styles;
		}

		public function slider_template_js() {
			ob_start();
			$template = woo_variation_gallery()->template_path( '/slider-template.php' );
			require_once $template;
			$data = ob_get_clean();
			echo apply_filters( 'woo_variation_gallery_slider_template_js', $data );
		}

		public function gallery_template( $template, $template_name ) {
			$old_template = $template;

			// Disable gallery on specific product

			if ( apply_filters( 'disable_woo_variation_gallery', false ) ) {
				return $old_template;
			}

			if ( $template_name === 'single-product/product-image.php' ) {
				$template = woo_variation_gallery()->template_path( '/product-images.php' );
			}

			if ( $template_name === 'single-product/product-thumbnails.php' ) {
				$template = woo_variation_gallery()->template_path( '/product-thumbnails.php' );
			}

			return apply_filters( 'woo_variation_gallery_gallery_template_override_location', $template, $template_name, $old_template );
		}

		public function gallery_template_part( $template, $slug ) {
			$old_template = $template;

			// Disable gallery on specific product

			if ( apply_filters( 'disable_woo_variation_gallery', false ) ) {
				return $old_template;
			}

			if ( $slug === 'single-product/product-image' ) {
				$template = woo_variation_gallery()->template_path( '/product-images.php' );
			}

			if ( $slug === 'single-product/product-thumbnails' ) {
				$template = woo_variation_gallery()->template_path( '/product-thumbnails.php' );
			}

			return apply_filters( 'woo_variation_gallery_gallery_template_part_override_location', $template, $slug, $old_template );
		}
	}
endif;