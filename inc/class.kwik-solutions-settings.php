<?php
/**
 * KwikSolutions Settings Page
 * @package KwikSolutions
 * @subpackage KwikSolutionsSettings
 *
 * @since KwikSolutions 1.0
 */

class KwikSolutionsSettings {

	public function __construct() {
		add_action( 'admin_init', array( $this, 'settings_init' ) );
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'update_option_' . K_SOLUTIONS_SETTINGS, array( $this, 'after_save' ), 10, 2 );
	}

	public function add_settings_page() {
		$settings = $this->get_options();

		$this->plugin = get_plugin_data( K_SOLUTIONS_PATH . '/' . K_SOLUTIONS_BASENAME . '.php', true, true );
		$settings_page = add_submenu_page(
			'edit.php?post_type=' . K_SOLUTIONS_CPT,
			__( "{$this->plugin['Name']} Settings", 'kwik' ),
			__( 'Settings', 'kwik' ),
			'manage_options',
			K_SOLUTIONS_BASENAME,
			array( $this, 'settings_page' )
		);

		if ( ! $settings_page ) {
			return;
		}

		add_action( "load-$settings_page", array( $this, 'help_screen' ) );
	}

	public function settings_init() {
		$utils = new KwikUtils();
		$kwik_settings = new KwikSettings();
		$default_settings = $this->default_options();
		$kwik_settings->settings_init( K_SOLUTIONS_BASENAME, K_SOLUTIONS_SETTINGS, $default_settings );
	}

	public function help_screen() {

		$general_help = '<p>' . __( 'General settings allow you to defined the following:', 'kwik' ) . '</p>' .
		'<ol>' .
		'<li>' . __( '<strong>Names</strong>: Set the singular and plural name for your Projects/Solutions.', 'kwik' ) . '</li>' .
		'<li>' . __( '<strong>URL Slug</strong>: By default Kwik Solutions sets \'solutions\' as the slug. Here you can change this to \'projects\' \'designs\' or whatever you like.', 'kwik' ) . '</li>' .
		'<li>' . __( '<strong>Icon</strong>: The icon is used in the admin menu, here you can set your own icon.', 'kwik' ) . '</li>' .
		'</ol>' .
		'<p>' . __( 'Remember to click "Save Changes" to save any changes you have made to the theme options. Rewrite rules will automatically be flushed when you change the url slug.', 'kwik' ) . '</p>';

		$sidebar = '<p><strong>' . __( 'For more information:', 'kwik' ) . '</strong></p>' .
		'<p>' . __( '<a href="https://github.com/kevinchappell/kwik-solutions" target="_blank">Github Repository</a>', 'kwik' ) . '</p>' .
		'<p>' . __( '<a href="https://github.com/kevinchappell/kwik-solutions/issues" target="_blank">Report Issues</a>', 'kwik' ) . '</p>' .
		'<p>' . __( '<a href="https://wordpress.org/plugins/kwik-framework/" target="_blank">Kwik Framework</a>', 'kwik' ) . '</p>';

		$screen = get_current_screen();

		if ( method_exists( $screen, 'add_help_tab' ) ) {
			// WordPress 3.3+
			$screen->add_help_tab(
				array(
					'title' => __( 'General', 'kwik' ),
					'id' => 'general-options-help',
					'content' => $general_help,
				)
			);

			$screen->set_help_sidebar( $sidebar );
		} else {
			// WordPress 3.2
			add_contextual_help( $screen, $help . $sidebar );
		}
	}

	public function settings_page() {
		$settings = $this->get_options();
		echo '<div class="wrap">';
		echo KwikInputs::markup( 'h2', __( "{$this->plugin['Name']} Settings", 'kwik' ) );
		echo '<form action="options.php" method="post">';
		settings_fields( K_SOLUTIONS_SETTINGS );
		echo KwikSettings::settings_sections( K_SOLUTIONS_SETTINGS, $settings );
		echo '</form>';
		echo '</div>';
	}

	public static function get_options() {
		return get_option( K_SOLUTIONS_SETTINGS, array( &$this, 'default_options' ) );
	}

	public function default_options() {
		$kwik_solutions_default_options = array(
			'general' => array(
				'section_title' => __( 'General', 'kwik' ),
				'section_desc' => __( 'General plugin settings', 'kwik' ),
				'settings' => array(
					'name' => array(
						'type' => 'text',
						'title' => __( 'Singular Name', 'kwik' ),
						'value' => 'Solution',
						'desc' => __( 'Solution, customer, Member, Colleague, Team etc', 'kwik' ),
					),
					'name_plural' => array(
						'type' => 'text',
						'title' => __( 'Plural Name', 'kwik' ),
						'value' => 'Solutions',
						'desc' => __( 'Solutions, customers, Members, Colleagues, Teams etc', 'kwik' ),
					),
					'url_slug' => array(
						'type' => 'text',
						'title' => __( 'URL Slug', 'kwik' ),
						'value' => 'solutions',
						'desc' => __( 'example: http://yourwebsite.com/solutions', 'kwik' ),
					),
					'dash_icon' => array(
						'type' => 'multi',
						'title' => __( 'Icon', 'kwik' ),
						'desc' => __( 'Set the icon for your solutions', 'kwik' ),
						'fields' => array(
							'div' => array(
								'type' => 'element',
								'title' => __( 'Preview Image', 'kwik' ),
								'attrs' => array( 'class' => 'icon_preview' ),
								),
							'icon_select' => array(
								'type' => 'select',
								'value' => 'dashicons-awards',
								'options' => K_SOLUTIONS_HELPERS::icons(),
								'desc' => __( 'Select the Primary icon for this user type', 'kwik' ),
								),
							),

					),
				),
			),
			// @todo - add editable taxonimies, maybe move to kwik framework
			// 'solutions_taxonomies' => array(
			// 	'section_title' => __('Taxonomies', 'kwik'),
			// 	'section_desc' => __('Define how content should be categorized', 'kwik'),
			// 	'settings' => array(
			// 		'taxonomies' => array(
			// 			'type' => 'multi',
			// 			'title' => __('Taxonomies', 'kwik'),
			//                      'fields' => array(

			//                          )
			// 		),
			// 		'name_plural' => array(
			// 			'type' => 'text',
			// 			'title' => __('Plural Name', 'kwik'),
			// 			'value' => 'Solutions',
			// 			'desc' => __('Solutions, customers, Members, Colleagues, Teams etc', 'kwik'),
			// 		),
			// 		'dash_icon' => array(
			// 			'type' => 'select',
			// 			'title' => __('Icon', 'kwik'),
			// 			'value' => 'dashicons-awards',
			// 			'options' => K_SOLUTIONS_HELPERS::icons(),
			// 			'desc' => __('Select the Primary icon for this user type', 'kwik'),
			// 		),
			// 	),
			// ),
		);

		return apply_filters( 'kwik_solutions_default_options', $kwik_solutions_default_options );
	}

	public function set_defaults( $fields = null ) {
		$set_defaults = array();
		if ( isset( $fields ) ) {
			$defaults = $fields;
		} else {
			$defaults = $this->default_options();
			$defaults = $defaults['general']['settings'];
		}

		foreach ( $defaults as $key => $value ) {
			if ( isset( $defaults[ $key ]['value'] ) ) {
				$set_defaults[ $key ] = $defaults[ $key ]['value'];
			} else if ( $defaults[ $key ]['type'] === 'multi' ) {
				$set_defaults[ $key ] = $this->set_defaults( $defaults[ $key ]['fields'] );
			}
		}
		return $set_defaults;
	}

	public function after_save( $old_value, $new_value ) {
		if ( $old_value['url_slug'] !== $new_value['url_slug'] ) {
			flush_rewrite_rules();
		}
	}
}// END KwikSolutionsSettings

if ( is_admin() ) {
	$kwik_theme_options = new KwikSolutionsSettings();
}
