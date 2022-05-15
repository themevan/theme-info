<?php
/**
 * Theme info settings
 *
 * @package ExampleTheme
 */

use ThemeVan\ThemeInfo\Info;

$config = array(
	/* translators: 1: theme name. */
	'welcome_content' => sprintf( esc_html__( '%1$s is now installed and ready to use. We want to make sure you have the best experience using the theme and that is why we gathered here all the necessary information for you. Thanks for using our theme!', 'example-theme' ), '{{theme_name}}' ),

	// Badge image URL.
	'badge_image_url' => get_template_directory_uri() . '/assets/img/logo.png',

	// Quick links.
	'quick_links'     => array(
		'theme_url' => array(
			'text'        => esc_html__( 'Theme Details', 'example-theme' ),
			'url'         => 'https://themevan.com/item/{{theme_slug}}/',
			'button_type' => 'secondary',
		),
		'demo_url'  => array(
			'text'        => esc_html__( 'View Theme Demo', 'example-theme' ),
			'url'         => 'https://{{theme_slug}}.themevan.com/',
			'button_type' => 'secondary',
		),
	),

	// Tabs.
	'tabs'            => array(
		'getting-started' => array(
			'title'       => esc_html__( 'Getting Started', 'example-theme' ),
			'render_mode' => 'grid',
			'items'       => array(
				array(
					'title'       => esc_html__( 'Theme Options', 'example-theme' ),
					'icon'        => 'dashicons dashicons-admin-customizer',
					'description' => esc_html__( 'Theme uses Customizer API for theme options. Using the Customizer you can easily customize different aspects of the theme.', 'example-theme' ),
					'button_text' => esc_html__( 'Customize', 'example-theme' ),
					'button_url'  => wp_customize_url(),
					'button_type' => 'primary',
				),
				array(
					'title'       => esc_html__( 'Static Front Page', 'example-theme' ),
					'icon'        => 'dashicons dashicons-admin-generic',
					'description' => esc_html__( 'To achieve custom home page other than blog listing, you need to create and set static front page.', 'example-theme' ),
					'button_text' => esc_html__( 'Static Front Page', 'example-theme' ),
					'button_url'  => admin_url( 'customize.php?autofocus[section]=static_front_page' ),
					'button_type' => 'primary',
				),
				array(
					'title'       => esc_html__( 'Demo Content', 'example-theme' ),
					'icon'        => 'dashicons dashicons-layout',
					/* translators: 1: plugin name. */
					'description' => sprintf( esc_html__( 'To import sample demo content, %1$s plugin should be installed and activated. After plugin is activated, visit Import Demo menu under Appearance.', 'example-theme' ), esc_html__( 'One Click Demo Import', 'example-theme' ) ),
					'button_text' => esc_html__( 'Demo Content', 'example-theme' ),
					'button_url'  => admin_url( 'themes.php?page={{theme_slug}}-info&tab=demo-content' ),
					'button_type' => 'secondary',
				),
				array(
					'title'       => esc_html__( 'Theme Preview', 'example-theme' ),
					'icon'        => 'dashicons dashicons-welcome-view-site',
					'description' => esc_html__( 'You can check out the theme demos for reference to find out what you can achieve using the theme and how it can be customized.', 'example-theme' ),
					'button_text' => esc_html__( 'View Demo', 'example-theme' ),
					'button_url'  => 'https://{{theme_slug}}.themevan.com/',
					'button_type' => 'link',
					'is_new_tab'  => true,
				),
				array(
					'title'       => esc_html__( 'Contact Support', 'example-theme' ),
					'icon'        => 'dashicons dashicons-sos',
					/* translators: 1: theme name. */
					'description' => sprintf( esc_html__( 'Got theme support question or found bug or got some feedbacks? Please submit support request from Appearance -> %1$s Info -> Contact Us.', 'example-theme' ), '{{theme_name}}' ),
				),
			),
		),

		'useful-plugins'  => array(
			'title'       => esc_html__( 'Useful Plugins', 'example-theme' ),
			'render_mode' => 'tgmpa',
			'content'     => esc_html__( 'Theme supports some helpful WordPress plugins to enhance your site.', 'example-theme' ),
		),

		'demo-content'    => array(
			'title'       => esc_html__( 'Demo Content', 'example-theme' ),
			'render_mode' => 'content',
			'content'     => sprintf( esc_html__( 'To import demo content for this theme, %1$s plugin is needed. Please make sure plugin is installed and activated. After plugin is activated, you will see Import Demo menu under Appearance.', 'example-theme' ), '<a href="https://wordpress.org/plugins/one-click-demo-import/" target="_blank">' . esc_html__( 'One Click Demo Import', 'example-theme' ) . '</a>' ),
		),
	),
);

Info::init( $config );
