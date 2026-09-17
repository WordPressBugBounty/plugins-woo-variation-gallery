<?php

defined( 'ABSPATH' ) or die( 'Keep Quit' );
?>

<h2>
	<?php
	esc_html_e( 'Gallery Migration', 'woo-variation-gallery' ) ?>
</h2>

<div id="migration_settings">
	<p><?php
		esc_html_e( 'Migrate gallery from other plugins. Migration process will run on background.', 'woo-variation-gallery' ) ?></p>
</div>


<table class="wc_status_table wc_status_table--tools widefat">
	<tbody class="tools">

	<?php

	$migration_list = (array) apply_filters( 'woo_variation_gallery_migration_list', array() );

	foreach ( $migration_list as $action => $tool ): ?>

		<tr class="<?php
		echo esc_attr( $action ) ?>">
			<th>
				<strong class="name"><?php
					echo esc_html( $tool['name'] ) ?></strong>
				<p class="description"><?php
					echo wp_kses_post( $tool['desc'] ) ?></p>
			</th>
			<td class="run-tool">
				<?php
				if ( isset( $tool['is_running'] ) && $tool['is_running'] ): ?>
					<button disabled class="button button-large <?php
					echo esc_attr( $action ); ?>">
						<span id="progress-spinner" class="spinner is-active"></span>
						<span data-is-running="<?php echo absint( $tool['is_running']) ?>"
							  data-done-text="<?php esc_html_e( '% Done', 'woo-variation-gallery' ) ?>"
							  id="migration-progress-text"
							  class="text-content"><?php
							echo sprintf( '%d%s', $tool['progress'], esc_html__( '% Done', 'woo-variation-gallery' ) ) ?></span>
					</button>
				<?php
				else: ?>
					<button data-started="<?php
					esc_html_e( '0% Done', 'woo-variation-gallery' ) ?>" data-confirm-message="<?php
					printf( esc_html__( 'Are you sure you want to %s?', 'woo-variation-gallery' ), str_ireplace( '&quot;', '\"', esc_attr( $tool['name'] ) ) ) ?>" data-action="<?php
					echo esc_attr( $action ); ?>" class="woo-variation-gallery-migration-start button button-large <?php
					echo esc_attr( $action ); ?>">
						<span id="progress-spinner" style="display: none" class="spinner"></span>
						<span data-is-running="<?php echo absint( $tool['is_running']) ?>"
							  data-done-text="<?php esc_html_e( '% Done', 'woo-variation-gallery' ) ?>"
							  id="migration-progress-text"
							  class="text-content"><?php
							echo esc_html( $tool['button'] ); ?></span>
					</button>
				<?php
				endif; ?>
			</td>
		</tr>
	<?php
	endforeach; ?>
	</tbody>
</table>

