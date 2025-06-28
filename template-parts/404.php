<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<main id="content" class="site-main" role="main">
	<?php if ( apply_filters( 'wts_elementor_page_title', true ) ) : ?>
		<header class="page-header">
			<h1 class="entry-title"><?php esc_html_e( 'The page can&rsquo;t be found.', 'wts-elementor' ); ?></h1>
		</header>
	<?php endif; ?>
	<div class="page-content">
		<p><?php esc_html_e( 'It looks like nothing was found at this location.', 'wts-elementor' ); ?></p>
	</div>

</main>
