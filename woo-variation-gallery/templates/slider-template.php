<?php

defined( 'ABSPATH' ) or die( 'Keep Quit' );
?>

<script type="text/html" id="tmpl-woo-variation-gallery-slider-template">
	<?php
	ob_start() ?>
	{{{data.gallery_markup}}}
	<?php
	echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', ob_get_clean(), 0 ); ?>
</script>

<script type="text/html" id="tmpl-woo-variation-gallery-thumbnail-template">
	{{{data.thumbnail_markup}}}
</script>