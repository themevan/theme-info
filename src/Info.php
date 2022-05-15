<?php
/**
 * Main class
 *
 * @package ThemeInfo
 */

namespace ThemeVan\ThemeInfo;

if ( ! class_exists( 'Info' ) ) {

	/**
	 * Main class.
	 *
	 * @since 1.0.0
	 */
	class Info {

		/**
		 * Config.
		 *
		 * @var array $config Configuration array.
		 *
		 * @since 1.0.0
		 */
		private $config;

		/**
		 * Tabs.
		 *
		 * @var array $tabs Tabs array.
		 *
		 * @since 1.0.0
		 */
		private $tabs;

		/**
		 * Theme name.
		 *
		 * @var string $theme_name Theme name.
		 *
		 * @since 1.0.0
		 */
		private $theme_name;

		/**
		 * Theme slug.
		 *
		 * @var string $theme_slug Theme slug.
		 *
		 * @since 1.0.0
		 */
		private $theme_slug;

		/**
		 * Current theme object.
		 *
		 * @var WP_Theme $theme Current theme.
		 */
		private $theme;

		/**
		 * Single instance.
		 *
		 * @var Info $instance Instance object.
		 */
		private static $instance;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
		}

		/**
		 * Init.
		 *
		 * @since 1.0.0
		 *
		 * @param array $config Configuration array.
		 */
		public static function init( $config ) {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Info ) ) {
				self::$instance = new Info();
				if ( ! empty( $config ) && is_array( $config ) ) {
					self::$instance->config = $config;
					self::$instance->configure();
					self::$instance->prepare_content();
					self::$instance->hooks();
				}
			}
		}

		/**
		 * Configure data.
		 *
		 * @since 1.0.0
		 */
		public function configure() {
			$theme = wp_get_theme();

			if ( is_child_theme() ) {
				$this->theme_name = $theme->parent()->get( 'Name' );
				$this->theme      = $theme->parent();
			} else {
				$this->theme_name = $theme->get( 'Name' );
				$this->theme      = $theme->parent();
			}

			$this->theme_version    = $theme->get( 'Version' );
			$this->theme_slug       = $theme->get_template();
			$this->theme_uri        = $theme->get( 'ThemeURI' );
			$this->theme_author_uri = $theme->get( 'AuthorURI' );

			/* translators: 1: theme name. */
			$this->menu_name = isset( $this->config['menu_name'] ) ? $this->config['menu_name'] : sprintf( esc_html__( '%s Info', 'theme-info' ), $this->theme_name );
			/* translators: 1: theme name. */
			$this->page_name    = isset( $this->config['page_name'] ) ? $this->config['page_name'] : sprintf( esc_html__( '%s Info', 'theme-info' ), $this->theme_name );
			$this->tabs         = isset( $this->config['tabs'] ) ? $this->config['tabs'] : array();
			$this->page_slug    = $this->theme_slug . '-info';
			$this->page_url     = admin_url( 'themes.php?page=' . $this->page_slug );
			$this->notice_pages = array( 'themes', 'dashboard' );

			$this->badge_url = ( $this->theme_author_uri ) ? $this->theme_author_uri : $this->theme_uri;

			/* translators: 1: theme name. */
			$this->notice = '<p>' . sprintf( esc_html__( 'Welcome! Thank you for choosing %1$s. To fully take advantage of the best our theme can offer please make sure you visit theme info page.', 'theme-info' ), esc_html( $this->theme_name ) ) . '</p>'
			.
			/* translators: 1: get started link. */
			'<p><a href="' . esc_url( $this->page_url ) . '" class="button button-primary">' . sprintf( esc_html__( 'Get started with %1$s', 'theme-info' ), $this->theme_name ) . '</a> <a href="' . esc_url( $this->get_dismiss_url() ) . '">' . esc_html__( 'Dismiss this notice', 'theme-info' ) . '</a></p>';
		}

		/**
		 * Return dismiss URL.
		 *
		 * @since 1.0.0
		 */
		protected function get_dismiss_url() {
			return wp_nonce_url( add_query_arg( 'theme-info-dismiss', 'dismiss_admin_notices' ), 'theme-info-dismiss-' . get_current_user_id() );
		}

		/**
		 * Prepare content.
		 *
		 * @since 1.0.0
		 */
		public function prepare_content() {
			$keys = array( 'welcome_content', 'quick_links' );

			foreach ( $keys as $key ) {
				$this->prepare_data( $key );
			}
		}

		/**
		 * Prepare data.
		 *
		 * @since 1.0.0
		 *
		 * @param string $key Array key.
		 */
		protected function prepare_data( $key ) {
			if ( isset( $this->config[ $key ] ) && ! empty( $this->config[ $key ] ) ) {
				if ( is_array( $this->config[ $key ] ) ) {
					$this->config[ $key ] = array_map(
						function( $item ) {
							if ( is_array( $item ) ) {
								$item = array_map(
									function( $i ) {
										return $this->filter_content( $i );
									},
									$item
								);
							} else {
								return $this->filter_content( $item );
							}
							return $item;
						},
						$this->config[ $key ]
					);
				} else {
					$this->config[ $key ] = $this->filter_content( $this->config[ $key ] );
				}
			}
		}

		/**
		 * Setup hooks.
		 *
		 * @since 1.0.0
		 */
		public function hooks() {
			// Register menu.
			add_action( 'admin_menu', array( $this, 'register_page' ) );

			// Dismiss.
			add_action( 'admin_head', array( $this, 'check_notice' ) );

			// Admin notice.
			add_action( 'admin_notices', array( $this, 'admin_notice' ) );

			// Load assets.
			add_action( 'admin_enqueue_scripts', array( $this, 'load_assets' ), 11 );
		}

		/**
		 * Update user notice dismiss status.
		 *
		 * @since 1.0.0
		 */
		public function check_notice() {
			if ( isset( $_GET['theme-info-dismiss'] ) && check_admin_referer( 'theme-info-dismiss-' . get_current_user_id() ) ) {
				update_user_meta( get_current_user_id(), "theme_info_dismissed_{$this->theme_slug}", 1 );
			}
		}

		/**
		 * Register info page.
		 *
		 * @since 1.0.0
		 */
		public function register_page() {
			add_theme_page( $this->menu_name, $this->page_name, 'activate_plugins', $this->page_slug, array( $this, 'render_page' ) );
		}

		/**
		 * Render page.
		 *
		 * @since 1.0.0
		 */
		public function render_page() {
			echo '<div class="wrap ti-info-wrap">';

			echo '<h1>' . esc_html( $this->theme_name ) . ' - ' . esc_html( $this->theme_version ) . '</h1>';

			if ( isset( $this->config['welcome_content'] ) && ! empty( $this->config['welcome_content'] ) ) {
				echo '<p class="welcome-message">' . esc_html( $this->config['welcome_content'] ) . '</p>';
			}

			if ( isset( $this->config['badge_image_url'] ) && ! empty( $this->config['badge_image_url'] ) ) {

				if ( $this->badge_url ) {
					echo '<a href="' . esc_url( $this->badge_url ) . '" target="_blank">';
				}

				echo '<div class="ti-badge">';
				echo '<img src="' . esc_url( $this->config['badge_image_url'] ) . '" />';
				echo '</div><!-- .ti-badge -->';

				if ( $this->badge_url ) {
					echo '</a>';
				}
			}

			// Render quick links.
			$this->render_quick_links();

			// Render tab navigation.
			$this->render_tabs();

			// Render tab content.
			$this->render_current_tab_content();

			echo '</div><!-- .wrap .ti-info-wrap -->';
		}

		/**
		 * Render tabs.
		 *
		 * @since 1.0.0
		 */
		public function render_tabs() {
			$tabs = ( isset( $this->config['tabs'] ) && ! empty( $this->config['tabs'] ) ) ? $this->config['tabs'] : array();

			if ( empty( $tabs ) ) {
				return;
			}

			if ( isset( $_GET['tab'] ) ) {
				$current_tab = wp_unslash( $_GET['tab'] );
			} else {
				$current_tab = key( $tabs );
			}

			echo '<h2 class="nav-tab-wrapper wp-clearfix">';

			foreach ( $tabs as $key => $tab ) {
				$current_class  = ' tab-' . $key;
				$current_class .= ( $current_tab === $key ) ? ' nav-tab-active' : '';
				echo '<a href="' . esc_url( admin_url( 'themes.php?page=' . $this->page_slug ) ) . '&tab=' . esc_attr( $key ) . '" class="nav-tab' . esc_attr( $current_class ) . '">' . esc_html( $tab['title'] ) . '</a>';
			}

			echo '</h2>';
		}

		/**
		 * Render current tab content.
		 *
		 * @since 1.0.0
		 */
		public function render_current_tab_content() {
			$tabs = ( isset( $this->config['tabs'] ) && ! empty( $this->config['tabs'] ) ) ? $this->config['tabs'] : array();

			if ( empty( $tabs ) ) {
				return;
			}

			if ( isset( $_GET['tab'] ) ) {
				$current_tab = wp_unslash( $_GET['tab'] );
			} else {
				$current_tab = key( $tabs );
			}

			$render_mode = $tabs[ $current_tab ]['render_mode'];

			$section_classes = 'ti-section ti-section-' . $current_tab . ' ti-render-mode-' . $render_mode;

			if ( 'grid' === $render_mode ) {
				$section_classes .= ' ti-grid';
			}

			echo '<div class="' . esc_attr( $section_classes ) . '">';

			switch ( $render_mode ) {
				case 'content':
					if ( isset( $tabs[ $current_tab ]['content'] ) ) {
						echo wp_kses_post( wpautop( $this->filter_content( $tabs[ $current_tab ]['content'] ) ) );
					}

					break;

				case 'tgmpa':
					if ( isset( $tabs[ $current_tab ]['content'] ) ) {
						echo wp_kses_post( wpautop( $this->filter_content( $tabs[ $current_tab ]['content'] ) ) );
					}

					global $tgmpa;

					if ( $tgmpa ) {
						$tgmpa_url      = $tgmpa->get_tgmpa_url();
						$tgmpa_complete = $tgmpa->is_tgmpa_complete();
						$plugins        = $tgmpa->plugins;

						if ( true !== $tgmpa_complete ) {
							echo '<a href="' . esc_url( $tgmpa_url ) . '" class="button button-primary">' . esc_html__( 'Manage Plugins', 'theme-info' ) . '</a>';
						}

						if ( ! empty( $plugins ) ) {
							echo '<ul class="plugin-list">';
							foreach ( $plugins as $plugin ) {
								echo '<li>' . esc_html( $plugin['name'] ) . '</li>';
							}
							echo '</ul>';
						}
					}

					break;

				case 'custom':
					if ( isset( $tabs[ $current_tab ]['render_callback'] ) && is_callable( $tabs[ $current_tab ]['render_callback'] ) ) {
						$tabs[ $current_tab ]['render_callback']();
					}

					break;

				case 'grid':
					if ( isset( $tabs[ $current_tab ]['items'] ) && ! empty( $tabs[ $current_tab ]['items'] ) ) {
						$this->render_grid_items( $tabs[ $current_tab ]['items'] );
					}

					break;

				default:
					break;
			}

			echo '</div><!-- .ti-section -->';
		}

		/**
		 * Render grid items.
		 *
		 * @since 1.0.0
		 *
		 * @param array $items Items array.
		 */
		protected function render_grid_items( $items ) {
			foreach ( $items as $key => $item ) {
				$this->render_grid_item( $item );
			}
		}

		/**
		 * Render grid item.
		 *
		 * @since 1.0.0
		 *
		 * @param array $item Item details.
		 */
		private function render_grid_item( $item ) {
			echo '<div class="item">';

			if ( isset( $item['title'] ) && ! empty( $item['title'] ) ) {
				echo '<h3>';

				if ( isset( $item['icon'] ) && ! empty( $item['icon'] ) ) {
					echo '<span class="' . esc_attr( $item['icon'] ) . '"></span>';
				}

				echo esc_html( $this->filter_content( $item['title'] ) );

				echo '</h3>';
			}

			if ( isset( $item['description'] ) && ! empty( $item['description'] ) ) {
				echo '<p>' . wp_kses_post( $this->filter_content( $item['description'] ) ) . '</p>';
			}

			if ( isset( $item['button_text'] ) && ! empty( $item['button_text'] ) && isset( $item['button_url'] ) && ! empty( $item['button_url'] ) ) {
				$button_target = ( isset( $item['is_new_tab'] ) && ( true === $item['is_new_tab'] || '1' === $item['is_new_tab'] ) ) ? '_blank' : '_self';
				$button_class  = '';
				if ( isset( $item['button_type'] ) && ! empty( $item['button_type'] ) ) {
					if ( 'primary' === $item['button_type'] ) {
						$button_class = 'button button-primary';
					} elseif ( 'secondary' === $item['button_type'] ) {
						$button_class = 'button button-secondary';
					}
				}

				echo '<a href="' . esc_url( $this->filter_content( $item['button_url'] ) ) . '" class="' . esc_attr( $button_class ) . '" target="' . esc_attr( $button_target ) . '">' . esc_html( $this->filter_content( $item['button_text'] ) ) . '</a>';
			}

			echo '</div><!-- .item -->';
		}

		/**
		 * Hook admin notice.
		 *
		 * @since 1.0.0
		 */
		public function admin_notice() {
			add_action( 'admin_notices', array( $this, 'display_admin_notice' ), 99 );
		}

		/**
		 * Load assets.
		 *
		 * @since 1.0.0
		 *
		 * @param string $hook Hook.
		 */
		public function load_assets( $hook ) {
			if ( in_array( $hook, array( 'appearance_page_' . $this->page_slug ), true ) ) {
				wp_add_inline_style( 'common', $this->get_page_style() );
			}
		}

		/**
		 * Get page style.
		 *
		 * @since 1.0.0
		 *
		 * @return string Style.
		 */
		protected function get_page_style() {
			ob_start();
      // @codingStandardsIgnoreStart
			?>
				.ti-grid {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(15rem, 1fr));
				grid-gap: 2rem;
				}

				.ti-grid + .ti-grid {
				margin-top: 2rem;
				}

				.ti-info-wrap {
				margin: 25px 40px 0 20px;
				max-width: 1050px;
				font-size: 15px;
				position: relative;
				}

				.ti-info-wrap h1 {
				font-size: 30px;
				}

				.ti-info-wrap .welcome-message {
				font-size: 19px;
				line-height: 1.6;
				margin-top: 1.4em;
				margin: 1em 200px 1em 0;
				}

				.ti-info-wrap .ti-badge {
				position: absolute;
				width: 140px;
				height: 140px;
				top: 0;
				right: 0;
				background-color: #FFF;
				display: flex;
				align-items: center;
				justify-content: center;
				}

				.ti-info-wrap .ti-badge img {
				max-width: 80%;
				height: auto;
				}

				.ti-info-wrap .quick-links {
				margin-bottom: 20px;
				}

				.ti-info-wrap .quick-links a {
				margin-right: 10px;
				}

				.ti-info-wrap .nav-tab {
				font-size: 16px;
				line-height: 1.3;
				padding-left: 15px;
				padding-right: 15px;
				padding-top: 8px;
				padding-bottom: 8px;
				}

				.ti-info-wrap .feature-section h3 {
				font-size: 18px;
				}

				.ti-info-wrap .feature-section p {
				font-size: 16px;
				}

				.ti-info-wrap .ti-section {
				margin: 15px 0;
				}

				.ti-info-wrap .ti-section p {
				font-size: 15px;
				}

				.ti-info-wrap .ti-render-mode-grid h3 span {
				margin-right: 5px;
				}

				.ti-info-wrap .ti-render-mode-tgmpa .plugin-list {
				list-style-type: disc;
				list-style-position: inside;
				}
			<?php
      // @codingStandardsIgnoreEnd
			return ob_get_clean();
		}

		/**
		 * Display admin notice.
		 *
		 * @since 1.0.0
		 */
		public function display_admin_notice() {
			$screen_id = null;

			$current_screen = get_current_screen();

			if ( $current_screen ) {
				$screen_id = $current_screen->id;
			}

			$dismiss_status = get_user_meta( get_current_user_id(), "theme_info_dismissed_{$this->theme_slug}", true );

			if ( current_user_can( 'edit_theme_options' ) && in_array( $screen_id, $this->notice_pages, true ) && 1 !== absint( $dismiss_status ) ) {
				echo '<div class="ti-info-notice notice notice-info">';
				$this->render_notice();
				echo '</div><!-- .ti-info-notice -->';
			};
		}

		/**
		 * Render notice.
		 *
		 * @since 1.0.0
		 */
		public function render_notice() {
			echo wp_kses_post( $this->notice );
		}

		/**
		 * Render quick links.
		 *
		 * @since 1.0.0
		 */
		public function render_quick_links() {
			$quick_links = ( isset( $this->config['quick_links'] ) ) ? $this->config['quick_links'] : array();

			if ( ! empty( $quick_links ) ) {
				echo '<p class="quick-links">';
				foreach ( $quick_links as $link ) {
					$button_classes = '';

					if ( isset( $link['button_type'] ) ) {
						if ( 'primary' === $link['button_type'] ) {
							$button_classes = 'button button-primary';
						} elseif ( 'secondary' === $link['button_type'] ) {
							$button_classes = 'button button-secondary';
						}
					}

					echo '<a href="' . esc_url( $link['url'] ) . '" class="' . esc_attr( $button_classes ) . '" target="_blank">' . esc_html( $link['text'] ) . '</a>';
				}
				echo '</p>';
			}
		}

		/**
		 * Return content with shortcode replaced.
		 *
		 * @since 1.0.0
		 *
		 * @param string $content Content.
		 * @return string Modified content.
		 */
		protected function filter_content( $content ) {
			$search = array( '{{theme_name}}', '{{theme_slug}}', '{{theme_version}}' );

			$replace = array(
				$this->theme_name,
				$this->theme_slug,
				$this->theme_version,
			);

			return str_replace( $search, $replace, $content );
		}
	}
}
