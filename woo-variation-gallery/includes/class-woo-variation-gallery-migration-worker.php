<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Woo_Variation_Gallery_Miragtion_Worker', false ) ):

	if ( ! class_exists( 'WC_Background_Process', false ) ) {
		include_once WC()->plugin_path() . '/includes/abstracts/class-wc-background-process.php';
	}

	/**
	 * Class that extends WC_Background_Process to migrate variation images in the background.
	 */
	class Woo_Variation_Gallery_Miragtion_Worker extends WC_Background_Process {

		/**
		 * Stores the product ID being processed.
		 *
		 * @var integer
		 */
		protected int $product_id = 0;

		/**
		 * Initiate new background process.
		 */
		public function __construct() {
			// Uses unique prefix per blog so each blog has separate queue.
			$this->prefix = 'wp_' . get_current_blog_id();
			$this->action = 'woo_variation_gallery_wc_migrate';
			parent::__construct();
		}

		/**
		 * Is job running?
		 *
		 * @return boolean
		 */
		public function is_running(): bool {
			return $this->is_queue_empty();
		}

		/**
		 * Limit each task ran per batch to 1 for image regen.
		 *
		 * @return bool
		 */
		protected function batch_limit_exceeded(): bool {
			return true;
		}

		protected function is_migrateable( $product ): bool {
			return true;
		}

		public function add_status( $total = 0 ): void {
			$status = array(
				'total'       => $total,
				'processed'   => 0,
				'is_complete' => $total > 0 ? 0 : 1
			);

			if ( $total > 0 ) {
				update_option( 'woo_variation_gallery_migration_status', $status, false );
			}
		}

		public function increment_status(): void {
			$status = $this->get_status();
			$status = map_deep( $status, 'absint' );

			$processed = $status['processed'];
			$total     = $status['total'];

			if ( $status['total'] > 0 ) {
				$processed ++;

				if ( $processed > $total ) {
					$processed = $total;
				}

				$status['processed'] = $processed;

				update_option( 'woo_variation_gallery_migration_status', $status, false );
			}
		}

		public function complete_status(): void {
			$status = $this->get_status();
			$status = map_deep( $status, 'absint' );

			if ( $status['total'] > 0 ) {
				$status['is_complete'] = 1;

				update_option( 'woo_variation_gallery_migration_status', $status, false );
			}
		}

		public function get_status(): array {
			$status = get_option( 'woo_variation_gallery_migration_status', array(
				'total'       => 0,
				'processed'   => 0,
				'is_complete' => 1
			) );

			return map_deep( $status, 'absint' );
		}

		public function delete_status(): void {
			delete_option( 'woo_variation_gallery_migration_status' );
		}

		/**
		 * Code to execute for each item in the queue
		 *
		 * @param array{variation_id: int, migrate_from: string} $item Queue item to iterate over.
		 *
		 * @return bool
		 */
		protected function task( $item ): bool {
			$log         = wc_get_logger();
			$log_context = array( 'source' => 'woo-variation-gallery' );

			if ( ! is_array( $item ) || ! isset( $item['variation_id'] ) ) {
				$log->error( esc_html__( 'No variation product ID Found.', 'woo-variation-gallery' ), $log_context );

				return false;
			}

			if ( ! isset( $item['migrate_from'] ) ) {
				$log->error( esc_html__( 'No migrate_from Found.', 'woo-variation-gallery' ), $log_context );

				return false;
			}

			$this->product_id = absint( $item['variation_id'] );
			$product          = wc_get_product( $this->product_id );
			$migrate_from     = sanitize_key( $item['migrate_from'] );

			if ( ! $product ) {
				$log->error( esc_html__( 'No variation product Found.', 'woo-variation-gallery' ), $log_context );

				return false;
			}

			$wc_gallery_images_array = apply_filters( 'woo_variation_gallery_migrate_images', array(), $migrate_from, $this->product_id, $product );
			$wc_gallery_images_array = wp_parse_id_list( $wc_gallery_images_array );

			$this->increment_status();

			if ( 0 === count( $wc_gallery_images_array ) ) {
				$log->error( sprintf( esc_html__( 'No Images Available for Variation %d.', 'woo-variation-gallery' ), absint( $this->product_id ) ), $log_context );

				return false;
			}

			$log->info( sprintf( esc_html__( 'Start: migration for variation product ID: %d. From: %s', 'woo-variation-gallery' ), absint( $this->product_id ), sanitize_key( $migrate_from ) ), $log_context );

			if ( 'from_default' === $migrate_from ) {
				$gallery_image_ids = $product->get_gallery_image_ids();
				$gallery_image_ids = wp_parse_id_list( $gallery_image_ids );

				$image_ids = array_unique( array_merge( array(), $gallery_image_ids, $wc_gallery_images_array ) );

				$log->info( sprintf( esc_html__( 'Image IDs', 'woo-variation-gallery' ), $this->product_id, $migrate_from ), array_merge( $log_context, array(
					'image_ids' => $image_ids,
				) ) );

				$product->set_gallery_image_ids( $image_ids );
				$id = $product->save();

				if ( $id > 0 ) {
					$log->info( sprintf( esc_html__( 'Done for ID: %s. From: %s' . "\n\n", 'woo-variation-gallery' ), $this->product_id, $migrate_from ), $log_context );
				} else {
					$log->info( sprintf( esc_html__( 'Not Saved product ID: %s. From: %s' . "\n\n", 'woo-variation-gallery' ), $this->product_id, $migrate_from ), $log_context );
				}

				return false;
			}

			// Update the meta data
			update_post_meta( $this->product_id, 'woo_variation_gallery_images', array_values( array_filter( $wc_gallery_images_array ) ) );

			// We made it till the end, now lets remove the item from the queue.
			return false;
		}

		/**
		 * This runs once the job has completed all items on the queue.
		 *
		 * @return void
		 */
		protected function complete(): void {
			parent::complete();

			update_option( '_woo_variation_gallery_image_migrated', 'yes' );
			$this->complete_status();

			$log = wc_get_logger();
			$log->info( esc_html__( '--- Migration completed of all variation product image. ---', 'woo-variation-gallery' ), array( 'source' => 'woo-variation-gallery' ) );
		}

		/**
		 * Kill process.
		 *
		 * Stop processing queue items, clear cronjob and delete all batches.
		 */
		public function kill_process(): void {
			parent::kill_process();

			$this->delete_status();

			$log = wc_get_logger();
			$log->info( esc_html__( 'Migration process killed.', 'woo-variation-gallery' ), array( 'source' => 'woo-variation-gallery' ) );
		}
	}

endif;
	
