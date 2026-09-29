<?php

defined( 'ABSPATH' ) or die( 'Keep Quit' );

if ( ! class_exists( 'Woo_Variation_Gallery_Migrate', false ) ):
	class Woo_Variation_Gallery_Migrate {

		protected static $_instance = null;

		protected function __construct() {
			$this->includes();
			$this->hooks();
			$this->init();
			do_action( 'woo_variation_gallery_migrate_loaded', $this );
		}

		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		public function includes() {
			require_once dirname( __FILE__ ) . '/class-woo-variation-gallery-migration-queue.php';
		}

		public function hooks() {
			add_filter( 'woo_variation_gallery_migration_list', array( $this, 'add_migration_list' ) );
			add_filter( 'woocommerce_debug_tools', array( $this, 'add_migration_list' ) );
			add_filter( 'woo_variation_gallery_migrate_images', array( $this, 'migrate_images' ), 10, 3 );
			add_action( 'init', array( 'Woo_Variation_Gallery_Migration_Queue', 'init' ) );
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

		public function init(): void {
		}

		public function add_migration_list( $tools = array() ) {
			if ( $this->is_wc_enabled() ) {
				$tools['__to_wc_gallery'] = array(
					'name'       => esc_html__( 'Migrate to WooCommerce Variation gallery', 'woo-variation-gallery' ),
					'button'     => esc_html__( 'Start', 'woo-variation-gallery' ),
					'is_running' => $this->is_running(),
					'progress'   => $this->get_progress(),
					'desc'       => esc_html__( 'This will migrate from "Additional Variation Images Gallery for WooCommerce" to "WooCommerce Variation gallery".', 'woo-variation-gallery' ),
					'callback'   => array( $this, 'to_wc_gallery_migration_queue' ),
				);

				return $tools;
			}

			return apply_filters( 'woo_variation_gallery_add_to_migration_list', $tools, $this );
		}

		/**
		 * See if thumbnail regeneration is currently in progress.
		 *
		 * @return Woo_Variation_Gallery_Miragtion_Worker
		 * @since 11.0.0
		 */
		public function get_worker(): Woo_Variation_Gallery_Miragtion_Worker {
			return Woo_Variation_Gallery_Migration_Queue::get_worker();
		}

		public function is_running(): bool {
			return Woo_Variation_Gallery_Migration_Queue::is_in_progress();
		}

		public function get_progress(): int {
			return $this->get_worker()->get_progress();
		}

		public function wc_avi_migration_queue(): string {
			Woo_Variation_Gallery_Migration_Queue::queue_migration( 'woocommerce-additional-variation-images' );

			return esc_html__( 'Variation product migration has been scheduled to run in the background.', 'woo-variation-gallery' );
		}

		public function to_wc_gallery_migration_queue(): string {
			Woo_Variation_Gallery_Migration_Queue::queue_migration( 'from_default' );

			return esc_html__( 'Variation product migration has been scheduled to run in the background.', 'woo-variation-gallery' );
		}

		public function woothumbs_migration_queue(): string {
			Woo_Variation_Gallery_Migration_Queue::queue_migration( 'woothumbs' );

			return esc_html__( 'Variation product migration has been scheduled to run in the background.', 'woo-variation-gallery' );
		}

		public function smart_variations_images_migration_queue(): string {
			Woo_Variation_Gallery_Migration_Queue::queue_migration( 'smart-variations-images' );

			return esc_html__( 'Variation product migration has been scheduled to run in the background.', 'woo-variation-gallery' );
		}

		public function avmi_migration_queue(): string {
			Woo_Variation_Gallery_Migration_Queue::queue_migration( 'avmi' );

			return esc_html__( 'Variation product migration has been scheduled to run in the background.', 'woo-variation-gallery' );
		}

		public function rtwpvg_migration_queue(): string {
			Woo_Variation_Gallery_Migration_Queue::queue_migration( 'rtwpvg' );

			return esc_html__( 'Variation product migration has been scheduled to run in the background.', 'woo-variation-gallery' );
		}

		public function migrate_images( $images, $migrate_from, $product_id ) {
			if ( 'from_default' === $migrate_from ) {
				$gallery_images = get_post_meta( $product_id, 'woo_variation_gallery_images', true );
				$images         = wp_parse_id_list( $gallery_images );
			}

			if ( 'woocommerce-additional-variation-images' === $migrate_from ) {
				$wc_gallery_images = get_post_meta( $product_id, '_wc_additional_variation_images', true );
				$images            = array_values( array_filter( explode( ',', $wc_gallery_images ) ) );
			}

			if ( 'woothumbs' === $migrate_from ) {
				$wc_gallery_images = get_post_meta( $product_id, 'variation_image_gallery', true );
				$images            = array_values( array_filter( explode( ',', $wc_gallery_images ) ) );
			}

			if ( 'smart-variations-images' === $migrate_from ) {
				$wc_gallery_images = get_post_meta( $product_id, '_product_image_gallery', true );
				$images            = array_values( array_filter( explode( ',', $wc_gallery_images ) ) );
			}

			if ( 'avmi' === $migrate_from ) {
				$wc_gallery_images = get_post_meta( $product_id, 'avmi_image_id', true );
				$images            = array_values( array_filter( explode( ',', $wc_gallery_images ) ) );
			}

			if ( 'rtwpvg' === $migrate_from ) {
				$wc_gallery_images = (array) get_post_meta( $product_id, 'rtwpvg_images', true );
				$images            = array_values( array_filter( $wc_gallery_images ) );
			}

			return apply_filters( 'woo_variation_gallery_migrated_images', $images, $migrate_from, $product_id );
		}
	}
endif;


