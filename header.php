<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<title><?php wp_title(); ?></title>
	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0" name="viewport" />
	<link rel="profile" href="https://gmpg.org/xfn/11">
	
	<?php wp_head(); ?>
</head>

<!-- ---------------------------------------------------------------------
Start of body ID
------------------------------------------------------------------------ -->

<body <?php body_class(); ?>>

<!-- ---------------------------------------------------------------------
End of body ID
------------------------------------------------------------------------ -->

<?php
// Elementor `header` location
if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'header' ) ) {
	get_template_part( 'template-parts/header' );
}