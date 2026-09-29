<?php

defined('ABSPATH') || exit;

if (!class_exists('Woo_Variation_Gallery_Migration_Queue', false)):

    class Woo_Variation_Gallery_Migration_Queue
    {
        /**
         * Background process to regenerate all images
         *
         * @var Woo_Variation_Gallery_Miragtion_Worker
         */
        protected static Woo_Variation_Gallery_Miragtion_Worker $worker;

        public static function init(): void
        {
            // Not required when Jetpack Photon is in use.
            // class_exists( 'Jetpack' ) & method_exists( 'Jetpack', 'get_active_modules' ) & in_array( 'photon', Jetpack::get_active_modules() )
            if (class_exists('Jetpack') && method_exists('Jetpack', 'is_module_active') && Jetpack::is_module_active('photon')) {
                return;
            }

            if (apply_filters('woo_variation_gallery_migrate', true)) {
                include_once dirname(__FILE__) . '/class-woo-variation-gallery-migration-worker.php';

                self::$worker = new Woo_Variation_Gallery_Miragtion_Worker();

                add_action('admin_init', array(__CLASS__, 'migrate_notice'));

                // From WC_Admin_Notices::add_custom_notice( 'woo_variation_gallery_migrate')...
                // do_action( 'woocommerce_hide_' . $hide_notice . '_notice' );
                add_action('woocommerce_hide_woo_variation_gallery_migrate_notice', array(
                        __CLASS__,
                        'dismiss_notice',
                ));

                add_action('rest_api_init', static function () {
                    register_rest_route('woo-variation-gallery/v1', '/migration-progress', [
                            'methods' => 'GET',
                            'permission_callback' => fn() => current_user_can('manage_woocommerce'),
                            'callback' => fn () =>  self::get_worker()->get_progress(),
                    ]);
                });
            }
        }

        /**
         * Dismiss notice and cancel jobs.
         */
        public static function dismiss_notice(): void
        {
            if (self::$worker) {
                self::$worker->kill_process();

                $log = wc_get_logger();
                $log->info(esc_html__('Cancelled migration job.', 'woo-variation-gallery'), array('source' => 'woo-variation-gallery'));
            }
            WC_Admin_Notices::remove_notice('woo_variation_gallery_migrate');
        }

        public static function notice_markup()
        {
            ob_start();
            ?>
            <div class="updated woocommerce-message">
            <a class="woocommerce-message-close notice-dismiss" href="<?php
            echo esc_url(wp_nonce_url(add_query_arg('wc-hide-notice', 'woo_variation_gallery_migrate'), 'woocommerce_hide_notices_nonce', '_wc_notice_nonce')); ?>"><?php
                esc_html_e('Cancel migration', 'woo-variation-gallery'); ?></a>
            <p><?php
                esc_html_e('Variation Gallery Migration is running in the background. Depending on the amount of variation product in your store this may take a while.', 'woo-variation-gallery'); ?></p>
            </div><?php
            return ob_get_clean();
        }

        /**
         * Show notice when job is running in background.
         */
        public static function migrate_notice(): void
        {
            if (self::$worker->is_running()) {
                WC_Admin_Notices::add_custom_notice('woo_variation_gallery_migrate', esc_html__('Variation Gallery Migration is running in the background. Depending on the amount of variation product in your store this may take a while.', 'woo-variation-gallery'));
            } else {
                WC_Admin_Notices::remove_notice('woo_variation_gallery_migrate');
            }
        }

        /**
         * Get list of variation product and queue them for migrate
         *
         * @param string $migrate_from
         *
         * @return void
         */
        public static function queue_migration(string $migrate_from = ''): void
        {
            global $wpdb;

            $log = wc_get_logger();

            // First lets cancel existing running queue to avoid running it more than once.
            if (self::$worker->is_running()) {
                self::$worker->kill_process();
            }

            // Now lets find all product image attachments IDs and pop them onto the queue.
            $variations = $wpdb->get_results($wpdb->prepare(
                    "SELECT ID, post_parent FROM {$wpdb->posts} WHERE post_type = %s ORDER BY ID ASC",
                    'product_variation'
            ), ARRAY_A);

            $variations = array_map( fn( $variation ) => array( 'ID' => absint($variation['ID']), 'post_parent' => absint($variation['post_parent']) ), $variations );

            self::$worker->add_status(count($variations));

            $log_message = sprintf(
                    esc_html__('Total %d Variation Products Found', 'woo-variation-gallery'),
                    count($variations)
            );

            $log->info($log_message, array('source' => 'woo-variation-gallery'));

            // Add 20 items to queue.
            foreach (array_chunk($variations, 20) as $variation_chunk) {

                $variation_ids = map_deep(array_values($variation_chunk), 'absint');

                $log_message = esc_html__('Adding Variation Products to Queue', 'woo-variation-gallery');

                $log->info($log_message, array('ids' => $variation_ids, 'source' => 'woo-variation-gallery'));

                self::$worker->push_to_queue(array(
                        'ids' => $variation_ids,
                        'migrate_from' => sanitize_key(trim($migrate_from)),
                ));
            }

            // Let's dispatch the queue to start processing.
            self::$worker->save()->dispatch();
        }

        public static function get_worker(): Woo_Variation_Gallery_Miragtion_Worker
        {
            return self::$worker;
        }

        /**
         * See if thumbnail regeneration is currently in progress.
         *
         * @return bool
         * @since 11.0.0
         */
        public static function is_in_progress(): bool
        {
            return self::$worker && self::$worker->is_running();
        }
    }

endif;
