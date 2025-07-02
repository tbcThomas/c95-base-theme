<?php

// This is what checks the Github Repository for the latest version 
// and gives the update notice to the Theme installed in Wordpress.
require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/tbcThomas/c95-base-theme/',
	__FILE__,
	'c95-base-theme'
);

//Set the branch that contains the stable release.
$myUpdateChecker->setBranch('main');

/* --------------------------------------------------------------------- */
/* --------------------------------------------------------------------- */



/* --------------------------------------------------------------------- */
/* --------------------------------------------------------------------- */

// Custom Admin Styles
function my_admin_head() {
   // Check if we are in the WordPress admin and the user is logged in
   if ( is_admin() && is_user_logged_in() ) {
       echo '<link href="' . get_stylesheet_directory_uri() . '/wp-admin.css" rel="stylesheet" type="text/css">';
   }
}
add_action('admin_head', 'my_admin_head');

// Custom Admin Scripts
function wpdocs_enqueue_custom_admin_script() {
   // Check if we are in the WordPress admin and the user is logged in
   if ( is_admin() && is_user_logged_in() ) {
       wp_enqueue_script('adminScripts', get_template_directory_uri() . '/js/adminScripts.js', array('jquery'), '1.0', true);
   }
}
// Set a high priority to ensure this runs late
add_action('admin_enqueue_scripts', 'wpdocs_enqueue_custom_admin_script', 100);

// Adds the Excerpt meta box for pages.
add_post_type_support( 'page', 'excerpt' );

// Adds Title support for pages
function title_theme_slug_setup() {
   add_theme_support( 'title-tag' );
}
add_action( 'after_setup_theme', 'title_theme_slug_setup' );


/*  Removal of Plugin Version Update Notices
_____________________________________________________________________*/

// Function to add settings fields for hiding plugin updates
function wpb_add_update_plugins_option() {
   // Register a new setting for hiding Elementor plugin updates
   register_setting('general', 'hide_plugin_updates', 'absint');
   
   // Register another setting for hiding updates of a different set of plugins
   register_setting('general', 'hide_additional_plugin_updates', 'absint');

   // Add a section for hiding Elementor plugin updates
   add_settings_field(
       'hide_plugin_updates', // ID
       'Hides Elementor Plugin Updates', // Title
       'wpb_hide_plugin_updates_callback', // Callback function
       'general' // Page to display on
   );
   
   // Add another section for hiding additional plugins
   add_settings_field(
       'hide_additional_plugin_updates', // ID
       'Hides Dynamic Plugin Updates', // Title
       'wpb_hide_additional_plugin_updates_callback', // Callback function
       'general' // Page to display on
   );
}

// Callback function for the first checkbox (Elementor plugins)
function wpb_hide_plugin_updates_callback() {
   $value = get_option('hide_plugin_updates', 0); // Default to 0 (unchecked)
   echo '<input type="checkbox" id="hide_plugin_updates" name="hide_plugin_updates" ' . checked(1, $value, false) . ' value="1"> Hides Elementor and Elementor Pro updates that require testing before being updated';
}

// Callback function for the second checkbox (Dynamic and Ultimate Elementor plugins)
function wpb_hide_additional_plugin_updates_callback() {
   $value = get_option('hide_additional_plugin_updates', 0); // Default to 0 (unchecked)
   echo '<input type="checkbox" id="hide_additional_plugin_updates" name="hide_additional_plugin_updates" ' . checked(1, $value, false) . ' value="1"> Hides various dynamic updates that require testing before being updated';
}

add_action('admin_init', 'wpb_add_update_plugins_option');

// Function to filter plugin updates based on the selected options
function filter_plugin_updates( $value ) {
   // Check if the Elementor plugin updates should be hidden
   if (get_option('hide_plugin_updates', 0)) {
       if ( isset( $value ) && is_object( $value ) ) {
           unset( $value->response[ 'elementor/elementor.php' ] );
           unset( $value->response[ 'elementor-pro/elementor-pro.php' ] );
       }
   }

   // Check if the additional plugin updates should be hidden
   if (get_option('hide_additional_plugin_updates', 0)) {
       if ( isset( $value ) && is_object( $value ) ) {
           unset( $value->response[ 'dynamic-content-for-elementor/dynamic-content-for-elementor.php' ] );
           unset( $value->response[ 'ultimate-elementor/ultimate-elementor.php' ] );
       }
   }

   return $value;
}
add_filter( 'site_transient_update_plugins', 'filter_plugin_updates' );


/* Removes Specific Admin Notices
_____________________________________________________________________*/

function hide_specific_admin_notices() {
   echo '
   <style>
       .code-snippets-pro-notice,
       .go-pro-button,
       .code-snippets-upgrade-button,
       .cptui-new .wdspromos,
       .yoast-seo-premium-upgrade,
       .wp-mail-smtp-sidebar-upgrade-pro,
       .wp-mail-smtp-pro,
       #snippet-type-tabs .nav-tab-inactive,
       .e-admin-top-bar__secondary-area-buttons,
       .elementor-control-notice-type-warning,
       #yoast-seo-settings .xl\:yst-fixed.xl\:yst-right-8,
       a[title="Upgrade to Code Snippets Pro"],
       .pro a[aria-label="Upgrade to WP Mail SMTP Pro"] {
           display: none !important;
       }
   </style>
   ';
}
add_action('admin_head', 'hide_specific_admin_notices');


/*  Performance & Security Edits
_____________________________________________________________________*/

// CANCELS AUTO UPDATES FOR PLUGINS AND THEMES
add_filter( 'auto_update_plugin', '__return_false' );
add_filter( 'auto_update_theme', '__return_false' );

// STOPS WORDPRESS' DEFAULT LAZY LOAD
add_filter( 'wp_lazy_loading_enabled', '__return_false' );

// REMOVE AVATAR DONATION MESSAGE
if ( has_action('wpua_donation_message', 'wpua_do_donation_message') ) {
    remove_action('wpua_donation_message', 'wpua_do_donation_message');
}


/*  Elementor Edits
________________________________________________________________________*/

// REMOVE GOOGLE FONTS - ELEMENTOR
add_filter( 'elementor/frontend/print_google_fonts', '__return_false' );

// REMOVE ELEMENTOR GLOBAL STYLES
function dequeue_elementor_global__css() {
  wp_dequeue_style('elementor-global');
  wp_deregister_style('elementor-global');
}
add_action('wp_print_styles', 'dequeue_elementor_global__css', 9999);

add_action( 'init',function(){
   remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
   remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
   remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
} );

// REMOVE GUTENBERG BLOCK LIBRARY CSS
function smartwp_remove_wp_block_library_css(){
   wp_dequeue_style( 'wp-block-library' );
   wp_dequeue_style( 'wp-block-library-theme' );
}
add_action( 'wp_enqueue_scripts', 'smartwp_remove_wp_block_library_css' );

add_filter('use_block_editor_for_post', '__return_false');

function eos_dequeue_gutenberg() {
   wp_dequeue_style( 'wp-core-blocks' );
   wp_dequeue_style( 'wp-block-library' );
   wp_deregister_style( 'wp-core-blocks' );
   wp_deregister_style( 'wp-block-library' );
}
add_action( 'wp_print_styles', 'eos_dequeue_gutenberg' );

// ENSURES CUSTOM FONTS ARE LOADED
add_filter( 'elementor_pro/custom_fonts/font_display', function( $current_value, $font_family, $data ) {
	return 'swap';
}, 10, 3 );

// THEME SUPPORT FOR FEATURED IMAGES
add_theme_support( 'post-thumbnails' );

// OVERRIDE EDITOR STYLES - SINCE 3.12.0
function override_elementor_styles_css() {
   // Check if we are in the WordPress admin and the user is logged in
   if ( is_admin() && is_user_logged_in() ) {
       wp_register_style('override-editor-styles', get_template_directory_uri().'/styles/editor-overrides.css');
       wp_enqueue_style('override-editor-styles');
   }
}
add_action( 'elementor/editor/after_enqueue_scripts', 'override_elementor_styles_css', 9999999 );


/*  REMOVES ELEMENTOR PROMOTIONAL ITEMS
________________________________________________________________________*/

function custom_admin_css() {
   echo '<style>
       .notice.e-notice.e-notice--cta.e-notice--dismissible.e-notice--extended[data-notice_id="plugin_image_optimization"] {
           display: none !important;
       }
   </style>';
}
add_action('admin_head', 'custom_admin_css', 9999999);


/*  HIDES ELEMENTOR PROMOTIONAL NOTICES
________________________________________________________________________*/

function hide_elementor_notices() {
   echo '<style>
       .notice.e-notice.e-notice--dismissible.e-notice--extended {
           display: none !important;
       }
   </style>';
}
add_action('admin_head', 'hide_elementor_notices');


/*  HIDE, EDIT WITH ELEMENTOR BUTTON(S)
________________________________________________________________________*/

function add_elementor_checkbox() {
   // Add a new setting to the "General" WordPress settings page
   add_settings_field(
       'show_edit_with_elementor_button',
       'Hide "Edit with Elementor"',
       'render_elementor_checkbox',
       'general'
   );
   
   // Register the new setting
   register_setting('general', 'show_edit_with_elementor_button');
}

function render_elementor_checkbox() {
   // Retrieve the current value of the setting
   $show_button = get_option('show_edit_with_elementor_button');
   ?>
   <input type="checkbox" name="show_edit_with_elementor_button" value="1" <?php checked(1, $show_button); ?>> Hides the edit with Elementor Buttons and links
   <?php
}

function hide_elementor_button() {
   // Check if the "Show 'Edit with Elementor' button" setting is checked
   $show_button = get_option('show_edit_with_elementor_button');
   if ($show_button) {
       // Hide the "Edit with Elementor" button on the post/page edit screen
       ?>
       <style>
            #elementor-switch-mode-button, #elementor-editor, #wp-admin-bar-elementor_edit_page {
                display:none;
            } 
      </style>
      <?php
   }
}

add_action('admin_init', 'add_elementor_checkbox');
add_action('admin_head-post.php', 'hide_elementor_button');
add_action('admin_head-post-new.php', 'hide_elementor_button');


/*  DISABLES THE CHECKBOX TO ENABLE ELEMENTOR AI
________________________________________________________________________*/

function update_current_user_elementor_ai_setting() {
   $user_id = get_current_user_id();
   if ( ! $user_id ) {
       return;
   }
   // If the option is not already '0', update it.
   if ( '0' !== get_user_option( 'elementor_enable_ai', $user_id ) ) {
       update_user_option( $user_id, 'elementor_enable_ai', '0' );
   }
}
add_action( 'admin_init', 'update_current_user_elementor_ai_setting' );


/*  HIDE ELEMENTOR NOTICES AND LINKS IN PAGE VIEWS
________________________________________________________________________*/

function add_elementor_page_view_checkbox() {
   // Add a new setting to the "General" WordPress settings page
   add_settings_field(
       'hide_elementor_notices',
       'Hide "Edit with Elementor"',
       'render_elementor_page_view_checkbox',
       'general'
   );
   
   // Register the new setting
   register_setting('general', 'hide_elementor_notices');
}

function render_elementor_page_view_checkbox() {
   // Retrieve the current value of the setting
   $hide_notices = get_option('hide_elementor_notices');
   ?>
   <input type="checkbox" name="hide_elementor_notices" value="1" <?php checked(1, $hide_notices); ?>> Hides the "Edit with Elementor" name, links, and preceding pipe when viewing all Pages
   <?php
}

function hide_elementor_notices_links() {
   // Check if the "Hide Elementor Notices" setting is checked
   $hide_notices = get_option('hide_elementor_notices');
   if ($hide_notices) {
       // Add JavaScript to hide spans containing "Elementor" and the "Edit with Elementor" span
       ?>
       <script>
           document.addEventListener('DOMContentLoaded', function () {
               // Hide "Elementor" post state and its preceding em dash
               const postStateElements = document.querySelectorAll('.post-state');
               postStateElements.forEach(function (element) {
                   if (element.textContent.trim() === 'Elementor') {
                       element.style.display = 'none';
                       
                       // Hide the preceding em dash (sibling text node)
                       const previousSibling = element.previousSibling;
                       if (previousSibling && previousSibling.nodeType === Node.TEXT_NODE) {
                           const trimmedText = previousSibling.textContent.trim();
                           if (trimmedText === '—') {
                               previousSibling.textContent = ''; // Clear the em dash text
                           }
                       }
                   }
               });

               // Hide "Edit with Elementor" button and its preceding pipe
               const editWithElementorElements = document.querySelectorAll('.edit_with_elementor');
               editWithElementorElements.forEach(function (element) {
                   element.style.display = 'none';
                   
                   // Hide the preceding pipe in the "view" span
                   const viewSpan = element.previousElementSibling;
                   if (viewSpan && viewSpan.classList.contains('view')) {
                       const previousSibling = viewSpan.lastChild;
                       if (previousSibling && previousSibling.nodeType === Node.TEXT_NODE) {
                           const trimmedText = previousSibling.textContent.trim();
                           if (trimmedText === '|') {
                               previousSibling.textContent = ''; // Clear the pipe text
                           }
                       }
                   }
               });
           });
       </script>
       <?php
   }
}

add_action('admin_init', 'add_elementor_page_view_checkbox');
add_action('admin_head', 'hide_elementor_notices_links');


/*  HIDE COMMENTS MENU WITH CHECKBOX SHOW/HIDE
_____________________________________________________________________*/

// Add the checkbox setting to the General settings page for Comments
function cre_add_comments_visibility_checkbox() {
   add_settings_field(
       'cre_hide_comments', // Option ID
       'Comments Admin Menu', // Label for the checkbox
       'cre_render_comments_visibility_checkbox', // Callback to render the checkbox
       'general' // Settings page (general)
   );
   
   register_setting('general', 'cre_hide_comments'); // Register the setting
}

function cre_render_comments_visibility_checkbox() {
   // Retrieve the current value of the setting
   $hide_comments = get_option('cre_hide_comments');
   ?>
   <input type="checkbox" name="cre_hide_comments" value="1" <?php checked(1, $hide_comments); ?>> Hide Comments Admin Menu for all users
   <?php
}

// Conditionally hide or show Comments menu based on the checkbox setting
function cre_conditional_hide_comments_menu() {
   $hide_comments = get_option('cre_hide_comments');

   // If the checkbox is checked, hide Comments for all users
   if ($hide_comments) {
       remove_menu_page('edit-comments.php'); // Comments admin menu slug
   }
}

// Hook the new functions to appropriate WordPress actions
add_action('admin_init', 'cre_add_comments_visibility_checkbox'); // To add the checkbox
add_action('admin_menu', 'cre_conditional_hide_comments_menu', 9999); // To hide/show Comments menu


/*  ADMIN DASHBOARD LINKS
________________________________________________________________________*/
// Remove Admin features from Dashboard excluding preferred users

// Add the checkbox setting to the General settings page
function cre_add_admin_features_checkbox() {
   add_settings_field(
       'cre_disable_admin_features_removal',
       'Enable Admin Features',
       'cre_render_admin_features_checkbox',
       'general'
   );
   register_setting('general', 'cre_disable_admin_features_removal');
}

function cre_render_admin_features_checkbox() {
   // Retrieve the current value of the setting
   $disable_removal = get_option('cre_disable_admin_features_removal');
   ?>
   <input type="checkbox" name="cre_disable_admin_features_removal" value="1" <?php checked(1, $disable_removal); ?>> Shows Admin Features for Users
   <?php
}

// Conditionally remove menu items based on the checkbox setting
function cre_conditional_remove_menus() {
   $disable_removal = get_option('cre_disable_admin_features_removal');

   // Only execute the menu removal if the checkbox is not checked
   if (!$disable_removal) {
       cre_remove_menus();
   }
}

// Original function to remove admin features
function cre_remove_menus() { 
  $current_user = wp_get_current_user(); 
  if (strpos($current_user->user_email, '@trestleboardcreative.com') === false) { 
     // List of menu pages to remove
     remove_submenu_page('index.php', 'update-core.php');
     remove_menu_page('themes.php');                             
     remove_menu_page('plugins.php');                           
     remove_menu_page('tools.php');                             
     remove_menu_page('options-general.php');                   
     remove_menu_page('edit.php?post_type=acf-field-group');
     remove_menu_page('cptui_main_menu');                       
     remove_menu_page('snippets');                              
     remove_menu_page('elementor');                             
     remove_menu_page('edit.php?post_type=elementor_library');
     remove_submenu_page('edit.php?post_type=elementor_library', 'edit.php?post_type=elementor_library&tabs_group=popup&elementor_library_type=popup');
     remove_menu_page('dce-features');
     remove_menu_page('search-filter');
     remove_menu_page('wp-mail-smtp');
     remove_menu_page('itsec');
     remove_menu_page('wpseo_dashboard');
     remove_menu_page('theseoframework-settings');
     remove_menu_page('tablepress');
     remove_menu_page('uaepro');
     remove_menu_page('dynamic-shortcodes');
  }
}

// Hook the functions to appropriate WordPress actions
add_action('admin_init', 'cre_add_admin_features_checkbox');
add_action('admin_init', 'cre_conditional_remove_menus', 9999);


/*  SHOW GRAVITY FORMS FOR USERS WITH CHECKBOX
_____________________________________________________________________*/

// Add the checkbox setting to the General settings page for Gravity Forms
function cre_add_gravity_forms_visibility_checkbox() {
   add_settings_field(
       'cre_show_gravity_forms', // Option ID
       'Gravity Forms Admin Menu', // Label for the checkbox
       'cre_render_gravity_forms_visibility_checkbox', // Callback to render the checkbox
       'general' // Settings page (general)
   );
   
   register_setting('general', 'cre_show_gravity_forms'); // Register the setting
}

function cre_render_gravity_forms_visibility_checkbox() {
   // Retrieve the current value of the setting
   $show_gravity_forms = get_option('cre_show_gravity_forms');
   ?>
   <input type="checkbox" name="cre_show_gravity_forms" value="1" <?php checked(1, $show_gravity_forms); ?>> Show Gravity Forms Admin Menu for Users
   <?php
}

// Conditionally hide or show Gravity Forms menu based on the checkbox setting
function cre_conditional_hide_gravity_forms_menu() {
   $show_gravity_forms = get_option('cre_show_gravity_forms');
   $current_user = wp_get_current_user();

   // If the user is not from TBC and the checkbox is unchecked, hide Gravity Forms
   if (strpos($current_user->user_email, '@trestleboardcreative.com') === false && !$show_gravity_forms) {
       remove_menu_page('gf_edit_forms'); // Gravity Forms admin menu slug
   }
}

// Hook the new functions to appropriate WordPress actions
add_action('admin_init', 'cre_add_gravity_forms_visibility_checkbox'); // To add the checkbox
add_action('admin_menu', 'cre_conditional_hide_gravity_forms_menu', 9999); // To hide/show Gravity Forms menu


/*  REMOVE DASHBOARD META BOXES
_____________________________________________________________________*/

function remove_dashboard_widgets() {
   remove_action( 'welcome_panel', 'wp_welcome_panel' );
   remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
   remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
   remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
   remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
   remove_meta_box( 'dashboard_site_health', 'dashboard', 'normal' );
   remove_meta_box( 'e-dashboard-overview', 'dashboard', 'normal' );
}
add_action( 'wp_dashboard_setup', 'remove_dashboard_widgets' );


/*  DASHBOARD META BOXES - DEFAULT SCREEN OPTIONS
________________________________________________________________________*/

// Hides the other screen option meta boxes.
// Boxes can be activated at any time by the user via Screen Options dropdown.

add_filter( 'hidden_meta_boxes', 'custom_hidden_meta_boxes' );
function custom_hidden_meta_boxes( $hidden ) {
//  $hidden[] = 'dashboard_primary';
    $hidden[] = 'rg_forms_dashboard';
    return $hidden;
}


/*  LOAD THEME STYLES AND SCRIPTS
________________________________________________________________________*/

function add_theme_enqueues() {
	wp_enqueue_style( 'style', get_stylesheet_uri() );
	wp_deregister_script('jquery');
	wp_enqueue_script( 'jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js', array(), '3.6.3', false);
   wp_enqueue_script( 'viewportHeight', get_template_directory_uri() . '/js/viewportHeight.js', array ( 'jquery' ), 1, true);
   wp_enqueue_script( 'jquery.matchHeight', get_template_directory_uri() . '/js/jquery.matchHeight.js', array ( 'jquery' ), 1, false);
   wp_enqueue_script( 'scrolltoHide', get_template_directory_uri() . '/js/scrolltoHide.js', array ( 'jquery' ), 1, false);
}
add_action( 'wp_enqueue_scripts', 'add_theme_enqueues' );

// DEFER RECAPTCHA
add_filter( 'clean_url', function( $url )
{
    if ( FALSE === strpos( $url, 'www.google.com/recaptcha/api.js' ) )
    { // not our file
        return $url;
    }
    // Must be a ', not "!
    return "$url' defer='defer";
}, 11, 1 );


/*  SVG IMAGES
________________________________________________________________________*/
// NOTE: SVG width and height functions are not required since we're 
// using Elementor and its' SVG upload to media library functions.

/*  Allows the use of SVGs to be uploaded to the Media Library
________________________________________________________________________*/

if ( current_user_can('administrator') ) {
    define( 'ALLOW_UNFILTERED_UPLOADS', true );
}

function cc_mime_types($mimes) {
  $mimes['svg'] = 'image/svg+xml';
  return $mimes;
}
add_filter('upload_mimes', 'cc_mime_types');


/*  LOADS ELEMENTOR TO TEMPLATE PAGES
________________________________________________________________________*/

function theme_prefix_register_elementor_locations( $elementor_theme_manager ) {
	$elementor_theme_manager->register_all_core_location();

}
add_action( 'elementor/theme/register_locations', 'theme_prefix_register_elementor_locations' );


/*  SUPPORT CONATACT CARD
________________________________________________________________________*/

function custom_dashboard_help() {
   echo '
   <div style="text-align:center;">
       <a href="https://creativ95.com/help/" title="Contact C95" target="_blank">
           <img src="'.get_template_directory_uri().'/img/c95-logo_whiteback.png" alt="C95" style="max-width:100%;width:80%;height:auto;margin:20px auto;">
       </a>
   </div>
   <p>
      Contact <a href="https://creativ95.com/help/" title="Contact C95" target="_blank">Contact C95</a> with questions, troubleshooting, edit for requests or alterations, or misc support you have with your custom built website.
   </p>
   <p><strong><a href="https://creativ95.com/help/" title="Contact C95" target="_blank">Contact C95</a></strong></p>
   ';
}
function cre_custom_dashboard_widgets() {
   global $wp_meta_boxes;
   wp_add_dashboard_widget('custom_help_widget', 'Website Support', 'custom_dashboard_help');
}
add_action('wp_dashboard_setup', 'cre_custom_dashboard_widgets');


/*  NAVIGATION
________________________________________________________________________*/

function eg_register_menus() {
	register_nav_menus(
  		array(
            'header_nav_menu' => __( 'Header Menu' ),
            'header_addnav_menu' => __( 'Additional Header Menu' ),
            'footer_nav_menu' => __( 'Footer Menu' ),
            'footer_addnav_menu' => __( 'Additional Footer Menu' ),
            'footer_alt_menu' => __( 'Alternate Menu' ),
            'content_altTwo_menu' => __( 'Alternate Menu - 2' ),
            'content_altThr_menu' => __( 'Alternate Menu - 3' ),
            'content_altFou_menu' => __( 'Alternate Menu - 4' ),
            'content_altFiv_menu' => __( 'Alternate Menu - 5' ),
            'content_altSix_menu' => __( 'Alternate Menu - 6' ),
            'content_altSev_menu' => __( 'Alternate Menu - 7' ),
            'content_altEig_menu' => __( 'Alternate Menu - 8' ),
    	)
	);
}
add_action( 'init', 'eg_register_menus' );

function cleanname($v) {
$v = preg_replace('/[^a-zA-Z0-9s]/', '', $v);
$v = str_replace(' ', '-', $v);
$v = strtolower($v);
return $v;
}


/*  WIDGETS
________________________________________________________________________*/

function cretheme_sidebar() {
	register_sidebar(
		array (
			'name' => __( 'Default Sidebar', 'cre-elementor-default' ),
			'id' => 'custom_sidebar_01',
			'description' => __( 'Custom sidebar that can be used with Elementor templates.', 'cre-elementor-default' ),
			'before_widget' => '<div class="widget-content">',
			'after_widget' => "</div>",
		)
	);
}
add_action( 'widgets_init', 'cretheme_sidebar' );

// Does not show Widget titles on page
add_filter('widget_title','my_widget_title'); 
function my_widget_title($t)
{
   return null;
}

/*  TAXONOMIES
________________________________________________________________________*/

add_action( 'init', 'build_taxonomies' ); 
function build_taxonomies() {
	
	register_taxonomy(
		'taxo_layout-template', // taxonomy name
		'page', // custom post type name(s)
		array(
			'label' 			=> 'Layout Template',
			'hierarchical' 	    => true,
			'query_var' 		=> true,
			'hide_empty' 		=> true,
			'rewrite'			=> array( '' ),
		));

}

/*  PLUGIN EDITS
________________________________________________________________________*/

/*  Yoast
__________________________________________*/
// Disable Yoast SEO Primary Category Feature

add_filter( 'wpseo_primary_term_taxonomies', '__return_false' );

// Moves Yoast below Content Editor
function yoasttobottom() {
  return 'low';
}
add_filter( 'wpseo_metabox_prio', 'yoasttobottom');

/*  SEO Framework
__________________________________________*/

add_filter(
	'the_seo_framework_metabox_priority',
	function () {
		return 'low';
	},
);

/*  Tablepress
__________________________________________*/
// Removes the Tablepress Admin links on site

add_filter( 'tablepress_edit_link_below_table', '__return_false' );

/*  GRAVITY FORMS
__________________________________________*/
// keeps the viewer at the form to read the confirmation message
// instead of having to scroll to message

add_filter( 'gform_confirmation_anchor', '__return_true' );


/*  ELEMENTOR QUERIES - USING QUERY ID'S
________________________________________________________________________*/
// Child Page(s) - use: 'child_pages'

function child_pages_query_callback( $query ) {
   global $post;
   $query->set( 'post_parent', $post->ID );
}
add_action( 'elementor/query/child_pages', 'child_pages_query_callback' );


/* THIS IS THE END                                                       */
/* --------------------------------------------------------------------- */
/* --------------------------------------------------------------------- */
/* --------------------------------------------------------------------- */