<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       wpmerchant.com/team
 * @since      1.0.0
 *
 * @package    Wpmerchant
 * @subpackage Wpmerchant/wpdb
 */

/**
 * wordpress database interactions
 *
 *
 * @package    Wpmerchant
 * @subpackage Wpmerchant/wpdb
 * @author     Ben Shadle <ben@wpmerchant.com>
 */
class Wpmerchant_Wpdb {
	
	public $plugin_name;
	public function __construct($plugin_name) {
		$this->plugin_name = $plugin_name;
	}
	/**
	 * Insert record into the WPMerchant Orders Table
	 *
	 * @since    1.0.0
	 */
	public function addWPMOrderItem($wpmOrder){
		global $wpdb;
		// Get the wpmerchant db version to make sure the functions below are= compatible with the db version
    	$wpmerchant_db_version = get_option( $this->plugin_name.'_db_version' );
		$table_name = $wpdb->prefix . 'wpmerchant_order_items';
		// Make sure the insert statement is compatible with the db version		
		$field_values['order_id'] = $wpmOrder['order_id'];
		if(isset($wpmOrder['plan_id'])){
			$field_values['plan_id'] = $wpmOrder['plan_id']; 
		}
		if(isset($wpmOrder['product_id'])){
			$field_values['product_id'] = $wpmOrder['product_id']; 
		}
		
		$sql = $wpdb->insert( 
			$table_name, 
			$field_values
		);
		return $sql;
	}
	public function getWPMOrderItems($order_id){
		global $wpdb;
		// Get the wpmerchant db version to make sure the functions below are= compatible with the db version
    	$wpmerchant_db_version = get_option( $this->plugin_name.'_db_version' );
		$table_name = $wpdb->prefix . 'wpmerchant_orders';
		// Make sure the select statement is compatible with the db version		
		$wpmOrder = $wpdb->get_results("SELECT product_id, plan_id  FROM $table_name WHERE order_id = '$order_id'");
		return $wpmOrder;
	}
	/**
	 * Insert record into the WPMerchant Customer Table
	 *
	 * @since    1.0.0
	 */
	public function addWPMCustomer($wpmCustomer){
		global $wpdb;
		// Get the wpmerchant db version to make sure the functions below are= compatible with the db version
    	$wpmerchant_db_version = get_option( $this->plugin_name.'_db_version' );
		$table_name = $wpdb->prefix . 'wpmerchant_customers';
		// Make sure the insert statement is compatible with the db version		
		$sql = $wpdb->insert( 
			$table_name, 
			array( 
				'name' => $wpmCustomer['name'], 
				'email' => $wpmCustomer['email'],  
				'stripe_id' => $wpmCustomer['stripe_id'], 
			) 
		);
		return $sql;
	}
	/**
	 * Get record from the WPMerchant Customer Table
	 *
	 * @since    1.0.0
	 */
	public function getWPMCustomer($wpmCustomer){
		global $wpdb;
		// Get the wpmerchant db version to make sure the functions below are= compatible with the db version
    	$wpmerchant_db_version = get_option( $this->plugin_name.'_db_version' );
		$table_name = $wpdb->prefix . 'wpmerchant_customers';
		// Make sure the select statement is compatible with the db version		
		$email = $wpmCustomer['email'];
		$wpmCustomer2 = $wpdb->get_row("SELECT * FROM $table_name WHERE email = '$email'");
		return $wpmCustomer2;
	}
	/**
	 * Get record from the WPMerchant Customer Table
	 *
	 * @since    1.0.0
	 */
	public function getWPMCustomers(){
		global $wpdb;
		// Get the wpmerchant db version to make sure the functions below are= compatible with the db version
    	$wpmerchant_db_version = get_option( $this->plugin_name.'_db_version' );
		$table_name = $wpdb->prefix . 'wpmerchant_customers';
		// Make sure the select statement is compatible with the db version		
		$wpmCustomers = $wpdb->get_results("SELECT ID, name, email FROM $table_name");
		return $wpmCustomers;
	}
	/**
	 * Get record from the WPMerchant Customer Table
	 *
	 * @since    1.0.0
	 */
	public function getPosts($post_type){
		// RUN this instead of get_posts so that we can limit the amount of information that is pulled - improve performance
		global $wpdb;
		// Get the wpmerchant db version to make sure the functions below are= compatible with the db version
    	$wpmerchant_db_version = get_option( $this->plugin_name.'_db_version' );
		$table_name = $wpdb->prefix . 'posts';
		$posts = $wpdb->get_results("SELECT ID,post_title FROM $table_name WHERE post_type = '$post_type' AND post_status IN ('draft','publish') ORDER BY post_title ASC");
		return $posts;
	}
	/**
	 * Get record from the WPMerchant Customer Table
	 *
	 * @since    1.0.0
	 */
	public function getPostCount($post_type){
		// RUN this instead of get_posts so that we can limit the amount of information that is pulled - improve performance
		global $wpdb;
		// Get the wpmerchant db version to make sure the functions below are= compatible with the db version
    	$wpmerchant_db_version = get_option( $this->plugin_name.'_db_version' );
		$table_name = $wpdb->prefix . 'posts';
		$post_count = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE post_type = '$post_type' AND post_status IN ('draft','publish')");
		return $post_count;
	}
	/**
	 * Get record from the WPMerchant Customer Table
	 *
	 * @since    1.0.0
	 */
	public function getLatestPostID(){
		// RUN this instead of get_posts so that we can limit the amount of information that is pulled - improve performance
		global $wpdb;
		// Get the wpmerchant db version to make sure the functions below are= compatible with the db version
    	$wpmerchant_db_version = get_option( $this->plugin_name.'_db_version' );
		$table_name = $wpdb->prefix . 'posts';
		$posts = $wpdb->get_var("SELECT ID FROM $table_name WHERE post_status ORDER BY ID DESC LIMIT 1");
		return $posts;
	}
}
