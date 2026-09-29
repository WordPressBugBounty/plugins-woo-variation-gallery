<?php

defined('ABSPATH') || exit;

if (!class_exists('Woo_Variation_Gallery_Miragtion_Worker', false)):

    if (!class_exists('WC_Background_Process', false)) {
        include_once WC()->plugin_path() . '/includes/abstracts/class-wc-background-process.php';
    }

    /**
     * Class that extends WC_Background_Process to migrate variation images in the background.
     */
    class Woo_Variation_Gallery_Miragtion_Worker extends WC_Background_Process
    {

        /**
         * Initiate new background process.
         */
        public function __construct()
        {
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
        public function is_running(): bool
        {
            return ! $this->is_queue_empty();
        }

        /**
         * Limit each task ran per batch to 1 for image regen.
         *
         * @return bool
         */
        protected function batch_limit_exceeded(): bool
        {
            return true;
        }

        protected function is_migrateable($product): bool
        {
            return true;
        }

        public function add_status($total = 0): void
        {
            $status = array(
                'total' => absint($total),
                'processed' => 0,
                'is_complete' => $total > 0 ? 0 : 1
            );

            if ($total > 0) {
                update_option('woo_variation_gallery_migration_status', $status, false);
            }
        }

        public function increment_status(): void
        {
            $status = $this->get_status();

            $processed = $status['processed'];
            $total = $status['total'];

            if ($status['total'] > 0) {
                ++$processed;

                if ($processed > $total) {
                    $processed = $total;
                }

                $status['processed'] = $processed;

                update_option('woo_variation_gallery_migration_status', $status, false);
            }
        }

        public function complete_status(): void
        {
            $status = $this->get_status();

            if ($status['total'] > 0) {
                $status['is_complete'] = 1;

                update_option('woo_variation_gallery_migration_status', $status, false);
            }
        }

        public function get_status(): array
        {
            $status = get_option('woo_variation_gallery_migration_status', array(
                'total' => 0,
                'processed' => 0,
                'is_complete' => 1
            ));

            return map_deep($status, 'absint');
        }

        public function get_progress(): int
        {
            $status = $this->get_status();

            if( 0 === $status['total'] ){
                return 100;
            }

            return absint( ( $status['processed'] / $status['total'] ) * 100 );
        }

        public function delete_status(): void
        {
            delete_option('woo_variation_gallery_migration_status');
        }

        /**
         * Code to execute for each item in the queue
         *
         * @param array{variation_id: int, ids: list<array{ ID: int, post_parent: int }>, migrate_from: string} $item Queue item to iterate over.
         *
         * @return bool
         */
        protected function task($item): bool
        {
            $log = wc_get_logger();
            $log_context = array('source' => 'woo-variation-gallery');

            if (!is_array($item) || !isset($item['ids']) || !is_array($item['ids'])) {
                $log->error(esc_html__('No Variation Product IDs Found.', 'woo-variation-gallery'), $log_context);

                return false;
            }

            if (!isset($item['migrate_from'])) {
                $log->error(esc_html__('No migrate_from Found.', 'woo-variation-gallery'), $log_context);

                return false;
            }

            $variation_ids = map_deep( $item['ids'], 'absint' );
            $migrate_from = sanitize_key(trim($item['migrate_from']));

            foreach ( $variation_ids as $variation) {

                $variation_id = absint($variation['ID']);

                $product = wc_get_product($variation_id);
                if ( ! $product) {
                    $log->error(sprintf(esc_html__('Variation Product Not Found in ID: %d', 'woo-variation-gallery'), $variation_id), $log_context);
                    continue;
                }

                $wc_gallery_images_array = apply_filters('woo_variation_gallery_migrate_images', array(), $migrate_from, $variation_id, $product);
                $wc_gallery_images_array = wp_parse_id_list($wc_gallery_images_array);
                $this->increment_status();

                if (0 === count($wc_gallery_images_array)) {
                    $log->error(sprintf(esc_html__('No Migratable Images Available for Variation: %d.', 'woo-variation-gallery'), $variation_id), $log_context);
                    continue;
                }

                $log->info(sprintf(esc_html__('Start Migration For Variation ID: %d. From: %s', 'woo-variation-gallery'), $variation_id, sanitize_key($migrate_from)), $log_context);

                // Default Image ID.
                $gallery_image_ids = $product->get_gallery_image_ids();
                $gallery_image_ids = wp_parse_id_list($gallery_image_ids);
                $image_ids = array_unique(array_merge(array(), $gallery_image_ids, $wc_gallery_images_array));
                $log->info(esc_html__('Available Image IDs: ', 'woo-variation-gallery'), array_merge($log_context, array(
                    'image_ids' => $image_ids,
                )));

                $product->set_gallery_image_ids($image_ids);
                $id = $product->save();

                if ($id > 0) {
                    $log->info(sprintf(esc_html__('Done for ID: %s. From: %s' . "\n\n", 'woo-variation-gallery'), $variation_id, $migrate_from), $log_context);
                } else {
                    $log->info(sprintf(esc_html__('Not Saved product ID: %s. From: %s' . "\n\n", 'woo-variation-gallery'), $variation_id, $migrate_from), $log_context);
                }
            }

            // Rest for 1 seconds to prevent CPU spikes
            sleep(1);

            return false;
        }

        /**
         * This runs once the job has completed all items on the queue.
         *
         * @return void
         */
        protected function complete(): void
        {
            parent::complete();

            update_option('_woo_variation_gallery_image_migrated', 'yes');
            $this->complete_status();

            $log = wc_get_logger();
            $log->info(esc_html__('--- Migration Done. ---', 'woo-variation-gallery'), array('source' => 'woo-variation-gallery'));
        }

        /**
         * Kill process.
         *
         * Stop processing queue items, clear cronjob and delete all batches.
         */
        public function kill_process(): void
        {
            parent::kill_process();

            $this->delete_status();
            delete_option('_woo_variation_gallery_image_migrated');

            $log = wc_get_logger();
            $log->info(esc_html__('Previous Migration Process Killed.', 'woo-variation-gallery'), array('source' => 'woo-variation-gallery'));
        }
    }

endif;
	
