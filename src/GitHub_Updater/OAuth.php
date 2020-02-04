<?php
/**
 * GitHub Updater
 *
 * @author    Andy Fragen
 * @license   GPL-2.0+
 * @link      https://github.com/afragen/github-updater
 * @package   github-updater
 */

namespace Fragen\GitHub_Updater;

use Fragen\GitHub_Updater\Traits\GHU_Trait;

/**
 * Class Remote_Management
 */
class OAuth {
	use GHU_Trait;


	/**
	 * Remote_Management constructor.
	 */
	public function __construct() {
	}

	public function run() {
		$this->load_hooks();
	}

	/**
	 * Load needed action/filter hooks.
	 */
	public function load_hooks() {
		add_action( 'admin_init', [ $this, 'oauth_page_init' ] );
		// add_action(
		// 'github_updater_update_settings',
		// function ( $post_data ) {
		// $this->save_settings( $post_data );
		// }
		// );
		$this->add_settings_tabs();
	}

	/**
	 * Save Remote Management settings.
	 *
	 * @uses 'github_updater_update_settings' action hook
	 * @uses 'github_updater_save_redirect' filter hook
	 *
	 * @param array $post_data $_POST data.
	 */
	public function save_settings( $post_data ) {
		if ( isset( $post_data['option_page'] ) &&
		'github_updater_remote_management' === $post_data['option_page']
		) {
			$options = isset( $post_data['github_updater_remote_management'] )
			? $post_data['github_updater_remote_management']
			: [];

			update_site_option( 'github_updater_remote_management', (array) $this->sanitize( $options ) );

			add_filter(
				'github_updater_save_redirect',
				function ( $option_page ) {
					return array_merge( $option_page, [ 'github_updater_remote_management' ] );
				}
			);
		}
	}

	/**
	 * Adds Remote Management tab to Settings page.
	 */
	public function add_settings_tabs() {
		$install_tabs = [ 'github_updater_oauth' => esc_html__( 'OAuth', 'github-updater' ) ];
		add_filter(
			'github_updater_add_settings_tabs',
			function ( $tabs ) use ( $install_tabs ) {
				return array_merge( $tabs, $install_tabs );
			},
			5
		);
		add_filter(
			'github_updater_add_admin_page',
			function ( $tab, $action ) {
				$this->add_admin_page( $tab, $action );
			},
			10,
			2
		);
	}

	/**
	 * Add Settings page data via action hook.
	 *
	 * @uses 'github_updater_add_admin_page' action hook
	 *
	 * @param string $tab    Tab name.
	 * @param string $action Form action.
	 */
	public function add_admin_page( $tab, $action ) {
		if ( 'github_updater_oauth' === $tab ) {
			$action = add_query_arg( 'tab', $tab, $action );
			?>
			<form class="settings" method="post" action="<?php esc_attr_e( $action ); ?>">
			<?php
			// phpcs:ignore Squiz.Commenting.InlineComment.InvalidEndChar
			// settings_fields( 'github_updater_remote_management' );
			do_settings_sections( 'github_updater_oauth_settings' );
			// phpcs:ignore Squiz.Commenting.InlineComment.InvalidEndChar
			// submit_button();

			echo '</form>';
		}
	}

	/**
	 * Settings for Remote Management.
	 */
	public function oauth_page_init() {
		register_setting(
			'github_updater_oauth',
			'github_updater_oauth_settings',
			[ $this, 'sanitize' ]
		);

		add_settings_section(
			'oauth_settings',
			esc_html__( 'OAuth', 'github-updater' ),
			[ $this, 'print_section_oauth' ],
			'github_updater_oauth_settings'
		);
	}

	/**
	 * Print the OAuth text.
	 */
	public function print_section_oauth() {

		echo '<p>';
		esc_html_e( 'GitHub has recently deprecated the use of access tokens with their API. This is causing users to be inundated with emails describing this issue. I am aware and working on a solution.', 'github-updater' );
		echo '</p>';

		echo '<p>';
		printf(
			wp_kses_post(
				/* translators: %s: Link to Git Remote Updater repository */
				__( 'You can help over on <a href="%s">issue 848</a>.', 'github-updater' )
			),
			'https://github.com/afragen/github-updater/issues/848'
		);
		echo '</p>';

	}

	/**
	 * Get the settings option array and print one of its values.
	 * For remote management settings.
	 *
	 * @param array $args Checkbox args.
	 *
	 * @return bool|void
	 */
	public function token_callback_checkbox_remote( $args ) {
		$checked = isset( self::$options_remote[ $args['id'] ] ) ? self::$options_remote[ $args['id'] ] : null;
		?>
		<label for="<?php esc_attr_e( $args['id'] ); ?>">
			<input type="checkbox" id="<?php esc_attr_e( $args['id'] ); ?>" name="github_updater_remote_management[<?php esc_attr_e( $args['id'] ); ?>]" value="1" <?php checked( '1', $checked ); ?> >
			<?php echo $args['title']; ?>
		</label>
		<?php
	}

}
