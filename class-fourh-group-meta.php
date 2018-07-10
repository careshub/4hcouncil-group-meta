<?php
/**
 * FourH_Group_Meta
 *
 * @package   FourH_Group_Meta
 * @author    David Cavins
 * @license   GPL-2.0+
 * @copyright 2018 CARES
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * If you're interested in introducing administrative or dashboard
 * functionality, then refer to `class-plugin-name-admin.php`
 *
 * @TODO: Rename this class to a proper name for your plugin.
 *
 * @package FourH_Group_Meta
 * @author  David Cavins
 */
class FourH_Group_Meta {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.0';

	/**
	 *
	 * Unique identifier for your plugin.
	 *
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'fourh-group-meta';

	/**
	 * Instance of this class.
	 *
	 * @since    0.1.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     0.1.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Add a meta box to the group's "admin>settings" tab.
		// We're also using BP_Group_Extension's admin_screen method to add this meta box to the WP-admin group edit
		add_filter( 'groups_custom_group_fields_editable', array( $this, 'meta_form_markup' ) );
		// Catch the saving of the meta form, fired when create>settings pane is saved or admin>settings is saved
		add_action( 'groups_group_details_edited', array( $this, 'meta_form_save') );
		add_action( 'groups_created_group', array( $this, 'meta_form_save' ) );
	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    0.1.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     0.1.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    0.1.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}

	/**
	 *  Renders extra fields on form when creating a group and when editing group details
	 * 	Used by CC_Custom_Meta_Group_Extension::admin_screen()
	 *  @param  	int $group_id
	 *  @return 	string html markup
	 *  @since    	0.1.0
	 */
	public function meta_form_markup( $group_id = 0 ) {
		if ( ! current_user_can( 'delete_others_pages' ) ) {
			return;
		}

		$group_id = $group_id ? $group_id : bp_get_current_group_id();

		if ( ! is_admin() ) : ?>
			<div class="content-row">
				<hr />
				<h4>Group Meta</h4>
		<?php endif; ?>

		<label for="group-meta-geoid">GeoID</label>
		<input id="group-meta-geoid" type="text" name="geoid" value="<?php echo groups_get_groupmeta( $group_id, 'geoid', true ); ?>">

		<label for="group-meta-area_ids">Area IDs</label>
		<input id="group-meta-area_ids" type="text" name="area_ids" value="<?php echo groups_get_groupmeta( $group_id, 'area_ids', true ); ?>">

		<?php
		if ( ! is_admin() ) : ?>
			<hr />
			</div>
		<?php endif;
	}

	/**
	 *  Saves the input from our extra meta fields
	 * 	Used by CC_Custom_Meta_Group_Extension::admin_screen_save()
	 *  @param  	int $group_id
	 *  @return 	void
	 *  @since    	0.1.0
	 */
	public function meta_form_save( $group_id = 0 ) {
		// Don't update these settings unless the user can update them.
		if ( ! current_user_can( 'delete_others_pages' ) ) {
			return;
		}

		$group_id = $group_id ? $group_id : bp_get_current_group_id();

		$meta = array(
			// Text boxes
			'geoid' => isset( $_POST['geoid'] ) ? $_POST['geoid'] : '',
			'area_ids' => isset( $_POST['area_ids'] ) ? $_POST['area_ids'] : '',
		);

		foreach ( $meta as $meta_key => $new_meta_value ) {
			/* Get the meta value of the custom field key. */
			$meta_value = groups_get_groupmeta( $group_id, $meta_key, true );

			/* If there is no new meta value but an old value exists, delete it. */
			if ( '' == $new_meta_value && $meta_value ) {
				groups_delete_groupmeta( $group_id, $meta_key, $meta_value );

			/* If a new meta value was added and there was no previous value, add it. */
			} elseif ( $new_meta_value && '' == $meta_value ) {
				groups_add_groupmeta( $group_id, $meta_key, $new_meta_value, true );

			/* If the new meta value does not match the old value, update it. */
			} elseif ( $new_meta_value && $new_meta_value != $meta_value ) {
				groups_update_groupmeta( $group_id, $meta_key, $new_meta_value );
			}
		}
	}

}
