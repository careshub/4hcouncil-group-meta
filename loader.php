<?php
/**
 * Plugin Name: 4H Council Group Meta
 * Description: Adds geoid and area_ids meta for groups.
 * Version: 1.0.0
 * Author: David Cavins
 * License: GPLv3
*/

/**
 * Loads BP_Group_Extension class
 * Must load after our meta class, since we use use functions from FourH_Group_Meta in the group extension
 *
 * @package CC Group Meta
 * @since 0.1.0
 */
function fourh_group_meta_plugin_init() {

	require( dirname( __FILE__ ) . '/class-bp-group-extension.php' );
}
add_action( 'bp_include', 'fourh_group_meta_plugin_init', 23 );

/**
 * Creates instance of CC_Group_Meta
 * This is where most of the running gears are.
 *
 * @package CC Group Meta
 * @since 0.1.0
 */

function fourh_group_meta_class_init(){
	// Get the class fired up
	require( dirname( __FILE__ ) . '/class-fourh-group-meta.php' );
	add_action( 'bp_include', array( 'FourH_Group_Meta', 'get_instance' ), 21 );
}
add_action( 'bp_include', 'fourh_group_meta_class_init' );
