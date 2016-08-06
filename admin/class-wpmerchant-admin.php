<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       wpmerchant.com/team
 * @since      1.0.0
 *
 * @package    Wpmerchant
 * @subpackage Wpmerchant/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wpmerchant
 * @subpackage Wpmerchant/admin
 * @author     Ben Shadle <ben@wpmerchant.com>
 */
class Wpmerchant_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.2
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		// Init - register custom post types
		add_action('init', array( $this, 'register_custom_post_types' ));
		add_action('add_meta_boxes_wpmerchant_product', array( $this, 'setup_product_metaboxes' ));
		/* UNCOMMENT FOR ORDER AND SALES
		add_action('add_meta_boxes_wpmerchant_order', array( $this, 'setup_order_metaboxes' ));*/
		add_action( 'save_post_wpmerchant_product',  array( $this, $this->plugin_name.'_product_save_meta_box_data') );
		/* UNCOMMENT FOR ORDER AND SALES
		add_action( 'save_post_wpmerchant_order',  array( $this, $this->plugin_name.'_order_save_meta_box_data') );*/
		// for saving the order_meta_box_data - make sure to replace hte title with the actual post id so that the title is always referencing the post id
		
		// Add Settings Page to Sidebar
			// setting the priority to 9 or less allows us to move the post types to the bottom of the submenu
		add_action('admin_menu', array( $this, 'add_plugin_admin_menu' ), 9);
	    // Register Settings
	    add_action('admin_init', array( $this, 'register_and_build_fields' ));
	    
		// Create Shortcode
		add_shortcode( 'wpmerchant_button', array( $this, 'stripeCheckoutShortcode' ));
		// Create Stripe Modal for before adn After purchase
		add_shortcode( 'wpmerchant_modal', array( $this, 'stripeModalShortcode' ));
		
		// UPdate the columns shown on hte products edit.php file - so we also have cost, inventory and product id
		add_filter('manage_wpmerchant_product_posts_columns' , array($this,'wpmerchant_product_columns'));
		// this fills in the columns that were created with each individual post's value
		add_action( 'manage_wpmerchant_product_posts_custom_column' , array($this,'fill_wpmerchant_product_columns'), 10, 2 );
		// make the first field on the edit plans and products pages show Enter name here instead of Enter title here
		add_filter( 'enter_title_here', array($this,'custom_post_title') );
		// update the messages shown to the user when a successful save has occurred on the custom post type pages - product or plan pages
		add_filter( 'post_updated_messages', array($this,'wpmerchant_updated_messages') );
		// add links underneath the plugin name and to the left on hte wp-admin/plugins.php page
		// this will put it to the left of deactivate 
		// WPMerchant_file is set in the wpmerchant.php file
		add_filter( 'plugin_action_links_'.plugin_basename(WPMERCHANT_FILE), array($this,'wpmerchant_add_action_links'));
		
		
		// add links to the right of hte version informaiton
		add_filter('plugin_row_meta',  array($this,'wpmerchant_plugin_row_links'), 10, 2);
		
		
		//add_action( 'admin_init', array($this, 'shortcode_button'));
		add_action( 'admin_head', array($this,'wpmerchant_add_tinymce' ));
		foreach ( array('post.php','post-new.php') as $hook ) {
		     add_action( "admin_head-$hook", array($this,'localize_tinymce_script') );
		}

	}
	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wpmerchant_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wpmerchant_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wpmerchant-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name.'-select2', '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.2
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wpmerchant_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wpmerchant_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
  	  wp_register_script( $this->plugin_name.'-select2',  '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.min.js', array( 'jquery' ), $this->version, false );
  	  wp_enqueue_script( $this->plugin_name.'-select2');
		// include the stripe functionality in this js file
    	  wp_register_script( $this->plugin_name,  plugin_dir_url( __FILE__ ) . 'js/wpmerchant-admin.js', array( 'jquery' ), $this->version, false );
    	  wp_enqueue_script( $this->plugin_name);
		  // get variables that we want to send to the admin.js file as the ajax_object
		  $localizedVariables = $this->getLocalizedVariables();
		  
    	  // pass ajax object to this javascript file
		  // Add nonce values to this object so that we can access them in hte public.js javascript file
		  wp_localize_script( $this->plugin_name, 'wpm_ajax_object', 
		  	array( 
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'get_email_data_nonce'=> $localizedVariables['getEmailDataNonce'],
				'get_payment_data_nonce'=> $localizedVariables['getPaymentDataNonce'],
				'update_gen_list_nonce'=> $localizedVariables['updateGenListNonce'],
				'create_post_nonce'=> $localizedVariables['createPostNonce'],
				'update_post_nonce'=> $localizedVariables['updatePostNonce'],
				'get_post_nonce'=> $localizedVariables['getPostNonce'],
				'user_id' => $localizedVariables['user_ID'],
				'currency' => $localizedVariables['currency'],
				'next_post_id'=>$localizedVariables['next_post_id']
			) 
		  );

	}
	public function getLocalizedVariables(){
	 	// Set Nonce Values so that the ajax calls are secure
	 	$localizedVars['getEmailDataNonce'] = wp_create_nonce( "wpmerchant_get_email_data" );
	 	$localizedVars['getPaymentDataNonce'] = wp_create_nonce( "wpmerchant_get_payment_data" );
	 	$localizedVars['updateGenListNonce'] = wp_create_nonce( "wpmerchant_update_gen_list" );
		$localizedVars['createPostNonce'] = wp_create_nonce( "wpmerchant_create_post" );
		$localizedVars['updatePostNonce'] = wp_create_nonce( "wpmerchant_update_post" );
		$localizedVars['getPostNonce'] = wp_create_nonce( "wpmerchant_get_post" );
		$localizedVars['user_ID'] = get_current_user_id();
		require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/class-wpmerchant-helper.php';
		$helper_class = new Wpmerchant_Helper($this->plugin_name);
		$currency_symbol = $helper_class->get_currency_symbol();
		$localizedVars['currency'] = $currency_symbol;
		require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/class-wpmerchant-wpdb.php';
		$wordpress_class = new Wpmerchant_Wpdb($this->plugin_name);
		$latest_post_id = $wordpress_class->getLatestPostID();
		$localizedVars['next_post_id'] = $latest_post_id;
		return $localizedVars;
	}
	/**
	 * Localize Script
	 */
	public function localize_tinymce_script() {
		$localizedVars['plugin_dir_url'] = plugin_dir_url( __FILE__ );
		$products_list = $this->return_products_list();
	    $localizedVars['products'] = json_encode($products_list);
		$localizedVars['get_products_list_nonce'] = wp_create_nonce( "wpmerchant_get_products_list" );
		$localizedVars['shortcode_form'] = $this->shortcode_form('wpmerchant_product');
		$localizedVars['create_post_form'] = $this->create_post_form('wpmerchant_product');
	    ?>
	<!-- TinyMCE Shortcode Plugin -->
	<script type='text/javascript'>
	var wpm_o = <?= $localizedVars['products'] ?>;
	var wpm_arr = Object.keys(wpm_o).map(function(k) { return wpm_o[k] });
	console.log(wpm_arr)
	var wpm_localized_tinymce = {
		'plugin_dir_url': '<?= $localizedVars['plugin_dir_url'] ?>',
		'get_products_list_nonce': '<?= $localizedVars['get_products_list_nonce'] ?>',
		'products': wpm_arr,
		'ajax_url': '<?= admin_url( 'admin-ajax.php' ) ?>',
		'shortcode_form': '<?= $localizedVars['shortcode_form'] ?>',
		'create_post_form':'<?= $localizedVars['create_post_form'] ?>',
	};
	</script>
	<!-- TinyMCE Shortcode Plugin -->
	    <?php
	}
	public function return_products_list(){
		require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/class-wpmerchant-wpdb.php';
		$wordpress_class = new Wpmerchant_Wpdb($this->plugin_name);
		$products = $wordpress_class->getPosts('wpmerchant_product');
		//$args['api_key']
		$product_list[] = array('value'=> '', 'name' => 'Select a Product');
		if($products){
			foreach($products AS $p){
				$product_list[] = array('value'=> $p->ID, 'name' => $p->post_title);
			}
		}
		return $product_list;
	}
	public function wpmerchant_add_tinymce() {
	    global $typenow;

	    // only on Post Type: post and page
	    if( in_array( $typenow, array( 'wpmerchant_product','wpmerchant_plan') ) )
	        return ;

	    add_filter( 'mce_external_plugins', array($this,'add_tinymce_product_plugin' ));
	    // Add to line 1 form WP TinyMCE
	    add_filter( 'mce_buttons', array($this,'add_tinymce_button' ));
	}

	// inlcude the js for tinymce
	public function add_tinymce_product_plugin( $plugin_array ) {

	    $plugin_array['wpmerchant_product_plugin'] = plugin_dir_url( __FILE__ ).'js/tinymce-plugin1.js';
	    // Print all plugin js path
	    //var_dump( $plugin_array );
	    return $plugin_array;
	}

	// Add the button key for address via JS
	public function add_tinymce_button( $buttons ) {

	    array_push( $buttons, 'wpmerchant_button' );
	    // Print all buttons
	    //var_dump( $buttons );
	    return $buttons;
	}
	/*public function shortcode_button() {
	     if ( current_user_can( 'edit_posts' ) && current_user_can( 'edit_pages' ) ) {
	          add_filter( 'mce_buttons', array($this,'my_register_wpm_tinymce_button' ));
	          add_filter( 'mce_external_plugins', array($this,'add_wpm_tinymce_button' ));
	     }
	}

	public function register_wpm_tinymce_button( $buttons ) {
	     array_push( $buttons, "button_eek", "button_green" );
	     return $buttons;
	}

	public function add_wpm_tinymce_button( $plugin_array ) {
	     $plugin_array['wpm_button_script'] = plugin_dir_url( __FILE__ ).'js/tinymce-plugin.js';
	     return $plugin_array;
	}*/
	public function wpmerchant_updated_messages($messages){
		$post             = get_post();
		$post_type        = get_post_type( $post );
		$post_type_object = get_post_type_object( $post_type );
		$post_types[] = array('id'=>'wpmerchant_product','singular'=>'Product');
		
		foreach($post_types AS $p){
			$messages[$p['id']] = array(
				0  => '', // Unused. Messages start at index 1.
				1  => __( $p['singular'].' updated.', $this->plugin_name),
				2  => __( 'Custom field updated.', $this->plugin_name),
				3  => __( 'Custom field deleted.', $this->plugin_name),
				4  => __( $p['singular'].' updated.', $this->plugin_name),
				/* translators: %s: date and time of the revision */
				5  => isset( $_GET['revision'] ) ? sprintf( __( $p['singular'].' restored to revision from %s', 'your-plugin-textdomain' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
				6  => __( $p['singular'].' published.', $this->plugin_name),
				7  => __( $p['singular'].' saved.', $this->plugin_name),
				8  => __( $p['singular'].' submitted.', $this->plugin_name),
				9  => sprintf(
					__( $p['singular'].' scheduled for: <strong>%1$s</strong>.', 'wpmerchant' ),
					// translators: Publish box date format, see http://php.net/date
					date_i18n('M j, Y @ G:i', strtotime( $post->post_date ) )
				),
				10 => __( $p['singular'].' draft updated.', $this->plugin_name )
			);
			// plans isn't here because it's not publicly searchable
			if ( $p['id'] == 'wpmerchant_product' ) {
				$permalink = get_permalink( $post->ID );

				$view_link = sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), __( 'View '.$p['singular'], $this->plugin_name) );
				$messages[ $p['id'] ][1] .= $view_link;
				$messages[ $p['id'] ][6] .= $view_link;
				$messages[ $p['id'] ][9] .= $view_link;

				$preview_permalink = add_query_arg( 'preview', 'true', $permalink );
				$preview_link = sprintf( ' <a target="_blank" href="%s">%s</a>', esc_url( $preview_permalink ), __( 'Preview '.$p['singular'], $this->plugin_name ) );
				$messages[ $p['id'] ][8]  .= $preview_link;
				$messages[ $p['id'] ][10] .= $preview_link;
			}
		}
		return $messages;
	}
	public function setup_product_metaboxes(){
		add_meta_box( $this->plugin_name.'_product_data_meta_box', __('Product Data', $this->plugin_name), array($this,$this->plugin_name.'_product_data_meta_box'), 'wpmerchant_product', 'normal','high' );
		add_meta_box( $this->plugin_name.'_product_description_meta_box', __('Product Description', $this->plugin_name), array($this,$this->plugin_name.'_product_description_meta_box'), 'wpmerchant_product', 'normal','core' );
		add_meta_box( $this->plugin_name.'_product_shortcode_meta_box', __('Buy Button Shortcode', $this->plugin_name), array($this,$this->plugin_name.'_product_shortcode_meta_box'), 'wpmerchant_product', 'normal','core' );
		
		/*add_meta_box( $this->plugin_name.'_inventory', 'Product Inventory', array($this,$this->plugin_name.'_product_inventory_meta_box'), 'wpmerchant_products', 'normal','high' );*/
	}
	public function setup_order_metaboxes(){
		add_meta_box( $this->plugin_name.'_order_data_meta_box', __('General Order Data', $this->plugin_name), array($this,$this->plugin_name.'_order_data_meta_box'), 'wpmerchant_order', 'normal','high' );
		add_meta_box( $this->plugin_name.'_order_items_meta_box', __('Order Details', $this->plugin_name), array($this,$this->plugin_name.'_order_items_meta_box'), 'wpmerchant_order', 'normal','high' );
		/*add_meta_box( $this->plugin_name.'_order_data_meta_box_other', __('Order Details', $this->plugin_name), array($this,$this->plugin_name.'_order_data_meta_box_other'), 'wpmerchant_order', 'normal','high' ); */
	}
	public function custom_post_title( $post ) {
		global $post_type;
		if ( 'wpmerchant_product' == $post_type){
			    return __( 'Enter name here', $this->plugin_name);
		} else {
			return __('Enter title here', $this->plugin_name);
		}
	}
	/**
	* Add Custom Columns to edit.php page for wpmerchant_products
	 * 
	 * @since    1.0.0
	*/
	function wpmerchant_product_columns($columns) {
		// Remove Author and Comments from Columns and Add Cost, Inventory and Product Id
	    return array(
	           'cb' => '<input type="checkbox" />',
	           'title' => __('Name', $this->plugin_name),
	           'price' => __('Price', $this->plugin_name),
	           'inventory' => __('Inventory', $this->plugin_name),
	           'product_id' =>__( 'Product ID', $this->plugin_name),
			   'date' =>__( 'Date', $this->plugin_name)
	       );
	    //return $columns;
	}
	/**
	*
	* Fill in Custom Columns
	* @since 1.0.0
	*/
	public function fill_wpmerchant_product_columns( $column, $post_id ) {
	    // Fill in the columns with meta box info associated with each post
		switch ( $column ) {
		case 'price' :
			require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/class-wpmerchant-helper.php';
			$helper_class = new Wpmerchant_Helper($this->plugin_name);
			$currency_symbol = $helper_class->get_currency_symbol();
			$cost = get_post_meta( $post_id , '_regular_price' , true );
			if($cost){
				echo $currency_symbol.$cost;
			} else {
				echo '';
			}
			break;
		case 'inventory' :
		    echo get_post_meta( $post_id , '_stock' , true ); 
		    break;
		case 'product_id' :
		    echo $post_id; 
		    break;
	    }
	}
    public function add_notice_query_var( $location ) {
     remove_filter( 'redirect_post_location', array( $this, 'add_notice_query_var' ), 99 );
     return add_query_arg( array( 'wpmerchant_status' => 'saved' ), $location );
    }
	/**
	 * Product Shortcode Meta Box Callback
	 * 
	 * @since    1.0.0
	 */
	function wpmerchant_product_shortcode_meta_box( $post ) {
		/*
		 * Use get_post_meta() to retrieve an existing value
		 * from the database and use the value for the form.
		 */
		echo '<p><code>[wpmerchant_button products="'.$post->ID.'"]'.__('Buy', $this->plugin_name).'[/wpmerchant_button]</code></p>';
		
	}
	/**
	 * Other Order Details Meta Box Callback
	 * 
	 * @since    1.0.0
	 */
	function wpmerchant_order_data_meta_box( $post ) {

		// Add a nonce field so we can check for it later.
		wp_nonce_field( $this->plugin_name.'_meta_box', $this->plugin_name.'_meta_box_nonce' );
	
		/*
		 * Use get_post_meta() to retrieve an existing value
		 * from the database and use the value for the form.
		 */
		//$description = get_post_meta( $post->ID, $this->plugin_name.'_description', true );
		
		echo '<div class="product_container"><div class="product_field_containers">';
		echo '<h1><span class="wpm_order_number"></span></h1>';
		echo '<ul class="wpmerchant_plan_data_metabox"><li><label for="_order_date">'.__( 'Order Date', $this->plugin_name).'</label>';
		unset($args);
	  	$args = array (
	              'type'      => 'input',
				  'subtype'	  => 'date',
				  'id'	  => '_order_date',
				  'name'	  => '_order_date',
				  'required' => 'required="required"',
				  'get_data_list' => '',
				  'value_type'=>'normal',
				  'wp_data' => 'post_meta',
				  'post_id'=> $post->ID,
	          );
		// this gets the post_meta value and echos back the input
		$this->wpmerchant_render_settings_field($args);
		echo '&nbsp;@&nbsp;';
		unset($args);
		/*<input type="text" class="hour" placeholder="h" name="order_date_hour" id="order_date_hour" maxlength="2" size="2" value="13" pattern="\-?\d+(\.\d{0,})?">:*/
		/*<input type="text" class="minute" placeholder="m" name="order_date_minute" id="order_date_minute" maxlength="2" size="2" value="07" pattern="\-?\d+(\.\d{0,})?">*/
	  	$args = array (
	              'type'      => 'input',
				  'subtype'	  => 'number',
				  'id'	  => '_order_date_hour',
				  'name'	  => '_order_date_hour',
				  'required' => 'required="required"',
				  'get_data_list' => '',
				  'value_type'=>'normal',
				  'wp_data' => 'post_meta',
				  'post_id'=> $post->ID,
				  'min'=> '0',
				  'max'=> '24',
				  'step'=> '1'
	          );
		// this gets the post_meta value and echos back the input
		$this->wpmerchant_render_settings_field($args);
		echo '&nbsp;:&nbsp;';
	  	$args = array (
	              'type'      => 'input',
				  'subtype'	  => 'number',
				  'id'	  => '_order_date_minute',
				  'name'	  => '_order_date_minute',
				  'required' => 'required="required"',
				  'get_data_list' => '',
				  'value_type'=>'normal',
				  'wp_data' => 'post_meta',
				  'post_id'=> $post->ID,
				  'min'=> '00',
				  'max'=>'59',
				  'step'=> '1'
	          );
		// this gets the post_meta value and echos back the input
		$this->wpmerchant_render_settings_field($args);
		echo '</li><li><label for="_order_status">'.__( 'Order Status', $this->plugin_name).'</label>';
				unset($args);
				$args = array (
			              'type'      => 'select',
						  'subtype'	  => '',
						  'id'	  => '_order_status',
						  'name'	  => '_order_status',
						  'required' => '',
						  'get_data_list' => 'get_order_status',
						  'value_type'=>'normal',
						  'wp_data' => 'post_meta',
						  'post_id'=> $post->ID
			          );
				// this gets the post_meta value and echos back the input
				$this->wpmerchant_render_settings_field($args);
		echo '</li><li><label for="_customer">'.__( 'Customer', $this->plugin_name).'</label>';
				unset($args);
				$args = array (
			              'type'      => 'select',
						  'subtype'	  => '',
						  'id'	  => '_customer',
						  'name'	  => '_customer',
						  'required' => '',
						  'get_data_list' => 'get_customers',
						  'value_type'=>'normal',
						  'wp_data' => 'post_meta',
						  'post_id'=> $post->ID
			          );
				// this gets the post_meta value and echos back the input
				$this->wpmerchant_render_settings_field($args);
		
		$billing_first_name = get_post_meta($post->ID,'_billing_first_name',true);
		$billing_last_name = get_post_meta($post->ID,'_billing_last_name',true);
		$billing_company = get_post_meta($post->ID,'_billing_company',true);
		$billing_address_1 = get_post_meta($post->ID,'_billing_address_1',true);
		$billing_address_2 = get_post_meta($post->ID,'_billing_address_2',true);
		$billing_city = get_post_meta($post->ID,'_billing_city',true);
		$billing_state = get_post_meta($post->ID,'_billing_state',true);
		$billing_postcode = get_post_meta($post->ID,'_billing_postcode',true);
		$billing_email = get_post_meta($post->ID,'_billing_email',true);
		$billing_phone = get_post_meta($post->ID,'_billing_phone',true);
		$billing_payment_method = get_post_meta($post->ID,'_billing_payment_method',true);
		if($billing_first_name && $billing_last_name && $billing_company && $billing_address_1 && $billing_address_2 && $billing_city && $billing_state && $billing_postcode){
			echo '</li></ul><div class="wpm-column-2"><h3 class="wpm_metabox_tab_heading">Billing Details&nbsp;<a class="wpm-edit-billing"><span class="dashicons dashicons-edit"></span></a></h3>';
		} else {
			echo '</li></ul><div class="wpm-column-2"><h3 class="wpm_metabox_tab_heading">Billing Details</h3>';
		}
			echo '<ul class="wpmerchant_plan_data_metabox wpm_billing_display">';
			if($billing_first_name && $billing_last_name && $billing_company && $billing_address_1 && $billing_address_2 && $billing_city && $billing_state && $billing_postcode){
				echo '<li><strong>Address:</strong></li>';
				echo '<li>'.$billing_first_name.' '.$billing_last_name.'</li>';
				echo '<li>'.$billing_company.'</li>';
				echo '<li>'.$billing_address_1.' '.$billing_address_2.'</li>';
				echo '<li>'.$billing_city.', '.$billing_state.', '.$billing_postcode.'</li>';
			}
			if($billing_email){
				echo '<li><strong>Email:</strong></li>';
				echo '<li>'.$billing_email.'</li>';
			}	
			if($billing_phone){
				echo '<li><strong>Phone:</strong></li>';
				echo '<li>'.$billing_phone.'</li>';
			}	
			if($billing_payment_method){
				echo '<li><strong>Payment Method:</strong></li>';
				echo '<li>'.$billing_payment_method.'</li>';
			}	
			echo '</ul>';
		if($billing_first_name && $billing_last_name && $billing_company && $billing_address_1 && $billing_address_2 && $billing_city && $billing_state && $billing_postcode){
			echo '<ul class="wpmerchant_plan_data_metabox wpm_billing_fields" style="display:none;"><li><label for="_billing_first_name">'.__( 'First Name', $this->plugin_name).'</label>';
		} else {
			echo '<ul class="wpmerchant_plan_data_metabox wpm_billing_fields"><li><label for="_billing_first_name">'.__( 'First Name', $this->plugin_name).'</label>';
		}
		unset($args);
	  	$args = array (
	              'type'      => 'input',
				  'subtype'	  => 'text',
				  'id'	  => '_billing_first_name',
				  'name'	  => '_billing_first_name',
				  'required' => 'required="required"',
				  'value_type'=>'normal',
				  'wp_data' => 'post_meta',
				  'post_id'=> $post->ID,
	          );
		// this gets the post_meta value and echos back the input
		$this->wpmerchant_render_settings_field($args);
		echo '</li><li><label for="_billing_last_name">'.__( 'Last Name', $this->plugin_name).'</label>';
				unset($args);
			  	$args = array (
			              'type'      => 'input',
						  'subtype'	  => 'text',
						  'id'	  => '_billing_last_name',
						  'name'	  => '_billing_last_name',
						  'required' => 'required="required"',
						  'value_type'=>'normal',
						  'wp_data' => 'post_meta',
						  'post_id'=> $post->ID,
			          );
				// this gets the post_meta value and echos back the input
				$this->wpmerchant_render_settings_field($args);
		echo '</li><li><label for="_billing_company">'.__( 'Company Name', $this->plugin_name).'</label>';
				unset($args);
			  	$args = array (
			              'type'      => 'input',
						  'subtype'	  => 'text',
						  'id'	  => '_billing_company',
						  'name'	  => '_billing_company',
						  'required' => '',
						  'value_type'=>'normal',
						  'wp_data' => 'post_meta',
						  'post_id'=> $post->ID,
			          );
				// this gets the post_meta value and echos back the input
				$this->wpmerchant_render_settings_field($args);
		echo '</li><li><label for="_billing_address_1">'.__( 'Address 1', $this->plugin_name).'</label>';
				unset($args);
			  	$args = array (
			              'type'      => 'input',
						  'subtype'	  => 'text',
						  'id'	  => '_billing_address_1',
						  'name'	  => '_billing_address_1',
						  'required' => '',
						  'value_type'=>'normal',
						  'wp_data' => 'post_meta',
						  'post_id'=> $post->ID,
			          );
				// this gets the post_meta value and echos back the input
				$this->wpmerchant_render_settings_field($args);
		echo '</li><li><label for="_billing_address_2">'.__( 'Address 2', $this->plugin_name).'</label>';
				unset($args);
			  	$args = array (
			              'type'      => 'input',
						  'subtype'	  => 'text',
						  'id'	  => '_billing_address_2',
						  'name'	  => '_billing_address_2',
						  'required' => '',
						  'value_type'=>'normal',
						  'wp_data' => 'post_meta',
						  'post_id'=> $post->ID,
			          );
				// this gets the post_meta value and echos back the input
				$this->wpmerchant_render_settings_field($args);
		echo '</li><li><label for="_billing_city">'.__( 'City', $this->plugin_name).'</label>';
				unset($args);
			  	$args = array (
			              'type'      => 'input',
						  'subtype'	  => 'text',
						  'id'	  => '_billing_city',
						  'name'	  => '_billing_city',
						  'required' => '',
						  'value_type'=>'normal',
						  'wp_data' => 'post_meta',
						  'post_id'=> $post->ID,
			          );
				// this gets the post_meta value and echos back the input
				$this->wpmerchant_render_settings_field($args);
		echo '</li><li><label for="_billing_state">'.__( 'State', $this->plugin_name).'</label>';
				unset($args);
				$args = array (
			              'type'      => 'select',
						  'subtype'	  => '',
						  'id'	  => '_billing_state',
						  'name'	  => '_billing_state',
						  'required' => '',
						  'get_data_list' => 'get_state_list',
						  'value_type'=>'normal',
						  'wp_data' => 'post_meta',
						  'post_id'=> $post->ID
			          );
				// this gets the post_meta value and echos back the input
				$this->wpmerchant_render_settings_field($args);
		echo '</li><li><label for="_billing_country">'.__( 'Country', $this->plugin_name).'</label>';
				unset($args);
				$args = array (
			              'type'      => 'select',
						  'subtype'	  => '',
						  'id'	  => '_billing_country',
						  'name'	  => '_billing_country',
						  'required' => '',
						  'get_data_list' => 'get_country_list',
						  'value_type'=>'normal',
						  'wp_data' => 'post_meta',
						  'post_id'=> $post->ID
			          );
				// this gets the post_meta value and echos back the input
				$this->wpmerchant_render_settings_field($args);
		echo '</li><li><label for="_billing_postcode">'.__( 'Postal Code', $this->plugin_name).'</label>';
				unset($args);
			  	$args = array (
			              'type'      => 'input',
						  'subtype'	  => 'text',
						  'id'	  => '_billing_postcode',
						  'name'	  => '_billing_postcode',
						  'required' => '',
						  'value_type'=>'normal',
						  'wp_data' => 'post_meta',
						  'post_id'=> $post->ID,
			          );
				// this gets the post_meta value and echos back the input
				$this->wpmerchant_render_settings_field($args);
			echo '</li><li><label for="_billing_email">'.__( 'Email', $this->plugin_name).'</label>';
					unset($args);
				  	$args = array (
				              'type'      => 'input',
							  'subtype'	  => 'text',
							  'id'	  => '_billing_email',
							  'name'	  => '_billing_email',
							  'required' => '',
							  'value_type'=>'normal',
							  'wp_data' => 'post_meta',
							  'post_id'=> $post->ID,
				          );
					// this gets the post_meta value and echos back the input
					$this->wpmerchant_render_settings_field($args);
			echo '</li><li><label for="_billing_phone">'.__( 'Phone', $this->plugin_name).'</label>';
					unset($args);
				  	$args = array (
				              'type'      => 'input',
							  'subtype'	  => 'text',
							  'id'	  => '_billing_phone',
							  'name'	  => '_billing_phone',
							  'required' => '',
							  'value_type'=>'normal',
							  'wp_data' => 'post_meta',
							  'post_id'=> $post->ID,
				          );
					// this gets the post_meta value and echos back the input
					$this->wpmerchant_render_settings_field($args);
			echo '</li><li><label for="_billing_payment_method">'.__( 'Payment Method', $this->plugin_name).'</label>';
					unset($args);
					$args = array (
				              'type'      => 'select',
							  'subtype'	  => '',
							  'id'	  => '_billing_payment_method',
							  'name'	  => '_billing_payment_method',
							  'required' => '',
							  'get_data_list' => 'get_payment_processor_list',
							  'value_type'=>'normal',
							  'wp_data' => 'post_meta',
							  'post_id'=> $post->ID
				          );
					// this gets the post_meta value and echos back the input
					$this->wpmerchant_render_settings_field($args);
					
					
					$shipping_first_name = get_post_meta($post->ID,'_shipping_first_name',true);
					$shipping_last_name = get_post_meta($post->ID,'_shipping_last_name',true);
					$shipping_company = get_post_meta($post->ID,'_shipping_company',true);
					$shipping_address_1 = get_post_meta($post->ID,'_shipping_address_1',true);
					$shipping_address_2 = get_post_meta($post->ID,'_shipping_address_2',true);
					$shipping_city = get_post_meta($post->ID,'_shipping_city',true);
					$shipping_state = get_post_meta($post->ID,'_shipping_state',true);
					$shipping_postcode = get_post_meta($post->ID,'_shipping_postcode',true);
					$shipping_customer_note = get_post_meta($post->ID,'_shipping_customer_note',true);
					if($shipping_first_name && $shipping_last_name && $shipping_address_1 && $shipping_address_2 && $shipping_city && $shipping_state && $shipping_postcode){
						echo '</div><div class="wpm-column-2"><h3 class="wpm_metabox_tab_heading">'.__('Shipping Details',$this->plugin_name).'&nbsp;<a class="wpm-edit-shipping"><span class="dashicons dashicons-edit"></span></a></h3><a class="button wpm-copy-billing-address" >'.__('Copy Billing Address',$this->plugin_name).'</a>';
					} else {
						echo '</div><div class="wpm-column-2"><h3 class="wpm_metabox_tab_heading">'.__('Shipping Details',$this->plugin_name).'</h3><a class="btn wpm-copy-billing-address" >'.__('Copy Billing Address',$this->plugin_name).'</a>';
					}
						echo '<ul class="wpmerchant_plan_data_metabox wpm_shipping_display">';
						if($shipping_first_name && $shipping_last_name && $shipping_address_1 && $shipping_address_2 && $shipping_city && $shipping_state && $shipping_postcode){
						echo '<li><strong>Address:</strong></li>';
						echo '<li>'.$shipping_first_name.' '.$shipping_last_name.'</li>';
						echo '<li>'.$shipping_company.'</li>';
						echo '<li>'.$shipping_address_1.' '.$shipping_address_2.'</li>';
						echo '<li>'.$shipping_city.', '.$shipping_state.', '.$shipping_postcode.'</li>';
						}
						if($shipping_customer_note){
							echo '<li><strong>Note:</strong></li>';
							echo '<li>'.$shipping_customer_note.'</li>';
						}
						echo '</ul>';
					if($shipping_first_name && $shipping_last_name && $shipping_address_1 && $shipping_address_2 && $shipping_city && $shipping_state && $shipping_postcode){
						echo '<ul class="wpmerchant_plan_data_metabox wpm_shipping_fields" style="display:none;"><li><label for="_shipping_first_name">'.__( 'First Name', $this->plugin_name).'</label>';
					} else {
						echo '<ul class="wpmerchant_plan_data_metabox wpm_shipping_fields"><li><label for="_shipping_first_name">'.__( 'First Name', $this->plugin_name).'</label>';
					}
							unset($args);
						  	$args = array (
						              'type'      => 'input',
									  'subtype'	  => 'text',
									  'id'	  => '_shipping_first_name',
									  'name'	  => '_shipping_first_name',
									  'required' => 'required="required"',
									  'value_type'=>'normal',
									  'wp_data' => 'post_meta',
									  'post_id'=> $post->ID,
						          );
							// this gets the post_meta value and echos back the input
							$this->wpmerchant_render_settings_field($args);
							echo '</li><li><label for="_shipping_last_name">'.__( 'Last Name', $this->plugin_name).'</label>';
									unset($args);
								  	$args = array (
								              'type'      => 'input',
											  'subtype'	  => 'text',
											  'id'	  => '_shipping_last_name',
											  'name'	  => '_shipping_last_name',
											  'required' => 'required="required"',
											  'value_type'=>'normal',
											  'wp_data' => 'post_meta',
											  'post_id'=> $post->ID,
								          );
									// this gets the post_meta value and echos back the input
									$this->wpmerchant_render_settings_field($args);
							echo '</li><li><label for="_shipping_company">'.__( 'Company Name', $this->plugin_name).'</label>';
									unset($args);
								  	$args = array (
								              'type'      => 'input',
											  'subtype'	  => 'text',
											  'id'	  => '_shipping_company',
											  'name'	  => '_shipping_company',
											  'required' => '',
											  'value_type'=>'normal',
											  'wp_data' => 'post_meta',
											  'post_id'=> $post->ID,
								          );
									// this gets the post_meta value and echos back the input
									$this->wpmerchant_render_settings_field($args);
							echo '</li><li><label for="_shipping_address_1">'.__( 'Address 1', $this->plugin_name).'</label>';
									unset($args);
								  	$args = array (
								              'type'      => 'input',
											  'subtype'	  => 'text',
											  'id'	  => '_shipping_address_1',
											  'name'	  => '_shipping_address_1',
											  'required' => '',
											  'value_type'=>'normal',
											  'wp_data' => 'post_meta',
											  'post_id'=> $post->ID,
								          );
									// this gets the post_meta value and echos back the input
									$this->wpmerchant_render_settings_field($args);
							echo '</li><li><label for="_shipping_address_2">'.__( 'Address 2', $this->plugin_name).'</label>';
									unset($args);
								  	$args = array (
								              'type'      => 'input',
											  'subtype'	  => 'text',
											  'id'	  => '_shipping_address_2',
											  'name'	  => '_shipping_address_2',
											  'required' => '',
											  'value_type'=>'normal',
											  'wp_data' => 'post_meta',
											  'post_id'=> $post->ID,
								          );
									// this gets the post_meta value and echos back the input
									$this->wpmerchant_render_settings_field($args);
							echo '</li><li><label for="_shipping_city">'.__( 'City', $this->plugin_name).'</label>';
									unset($args);
								  	$args = array (
								              'type'      => 'input',
											  'subtype'	  => 'text',
											  'id'	  => '_shipping_city',
											  'name'	  => '_shipping_city',
											  'required' => '',
											  'value_type'=>'normal',
											  'wp_data' => 'post_meta',
											  'post_id'=> $post->ID,
								          );
									// this gets the post_meta value and echos back the input
									$this->wpmerchant_render_settings_field($args);
							echo '</li><li><label for="_shipping_state">'.__( 'State', $this->plugin_name).'</label>';
									unset($args);
									$args = array (
								              'type'      => 'select',
											  'subtype'	  => '',
											  'id'	  => '_shipping_state',
											  'name'	  => '_shipping_state',
											  'required' => '',
											  'get_data_list' => 'get_state_list',
											  'value_type'=>'normal',
											  'wp_data' => 'post_meta',
											  'post_id'=> $post->ID
								          );
									// this gets the post_meta value and echos back the input
									$this->wpmerchant_render_settings_field($args);
							echo '</li><li><label for="_shipping_country">'.__( 'Country', $this->plugin_name).'</label>';
									unset($args);
									$args = array (
								              'type'      => 'select',
											  'subtype'	  => '',
											  'id'	  => '_shipping_country',
											  'name'	  => '_shipping_country',
											  'required' => '',
											  'get_data_list' => 'get_country_list',
											  'value_type'=>'normal',
											  'wp_data' => 'post_meta',
											  'post_id'=> $post->ID
								          );
									// this gets the post_meta value and echos back the input
									$this->wpmerchant_render_settings_field($args);
							echo '</li><li><label for="_shipping_postcode">'.__( 'Postal Code', $this->plugin_name).'</label>';
									unset($args);
								  	$args = array (
								              'type'      => 'input',
											  'subtype'	  => 'text',
											  'id'	  => '_shipping_postcode',
											  'name'	  => '_shipping_postcode',
											  'required' => '',
											  'value_type'=>'normal',
											  'wp_data' => 'post_meta',
											  'post_id'=> $post->ID,
								          );
									// this gets the post_meta value and echos back the input
									$this->wpmerchant_render_settings_field($args);
							echo '</li><li><label for="_shipping_customer_note">'.__( 'Customer Note', $this->plugin_name).'</label>';
									unset($args);
								  	$args = array (
								              'type'      => 'input',
											  'subtype'	  => 'text',
											  'id'	  => '_shipping_customer_note',
											  'name'	  => '_shipping_customer_note',
											  'required' => '',
											  'value_type'=>'normal',
											  'wp_data' => 'post_meta',
											  'post_id'=> $post->ID,
								          );
									// this gets the post_meta value and echos back the input
									$this->wpmerchant_render_settings_field($args);
		echo '</li></ul></div></div></div><div class="clear"></div></div>';
	}
	/**
	 * Order Data Meta Box Callback
	 * 
	 * @since    1.0.0
	 */
	function wpmerchant_order_items_meta_box( $post ) {
		// Add a nonce field so we can check for it later.
		wp_nonce_field( $this->plugin_name.'_meta_box', $this->plugin_name.'_meta_box_nonce' );
	
		echo '<div class="product_container">';
		echo '<div class="wpm_item_header wpm_item"><div class="wpm-check-column"><ul><li><input type="checkbox"></li></ul></div><div class="wpm-item-column"><ul><li>Item</li></ul></div><div class="wpm-cost-column"><ul><li>Cost</li></ul></div><div class="wpm-quantity-column"><ul><li>Quantity</li></ul></div><div class="wpm-total-column"><ul><li>Total</li></ul></div></div>';
		echo '<div class="wpm_item">';

echo '<div class="wpm-check-column"><ul>';
$products[] = array('ID'=>1, 'quantity'=>1, 'total'=>20,'name'=>'Moped');
foreach($products AS $p){
	echo '<li><input type="checkbox"></li>';
}
	echo '</ul></div>';
echo '<div class="wpm-item-column"><ul>';
foreach($products AS $p){
	echo '<li><a href="/wp-admin/post.php?post='.$p['ID'].'&action=edit">'.$p['name'].'</a></li>';
}
	echo '</ul></div>';
echo '<div class="wpm-cost-column"><ul>';
require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/class-wpmerchant-helper.php';
$helper_class = new Wpmerchant_Helper($this->plugin_name);
$currency_symbol = $helper_class->get_currency_symbol();

foreach($products AS $p){
	$price = get_post_meta($p['ID'],'_regular_price',true);
	echo '<li>'.$currency_symbol.$price.'</li>';
}
	echo '</ul></div>';
echo '<div class="wpm-quantity-column"><ul>';
foreach($products AS $p){
	echo '<li>'.$p['quantity'].'</li>';
}
	echo '</ul></div>';
echo '<div class="wpm-total-column"><ul>';
foreach($products AS $p){
	echo '<li>'.$currency_symbol.$p['total'].'</li>';
}
	echo '</ul></div></div><div class="wpm_item wpm_item_totals">';
echo '<div class="wpm-column-2">&nbsp;</div>';
echo '<div class="wpm-total-header-column"><ul>';
	echo '<li>Discount</li>';
	echo '<li>Shipping</li>';
	echo '<li>Order Total</li>';
	echo '<li>Refunded</li>';
echo '</ul></div><div class="wpm-total-value-column"><ul>';
echo '<li><span class="wpm-order-detail-value"></span></li>';
echo '<li><span class="wpm-order-detail-value"></span></li>';
echo '<li><span class="wpm-order-detail-value"></span></li>';
echo '<li><span class="wpm-order-detail-value"></span></li>';
echo '</ul></div><div class="clear"></div></div>';
	echo '<div class="wpm_item_footer wpm_item">';
echo '<div class="wpm-column-2">';
unset($args);
$order_status = get_post_meta( $post->ID, '_order_status', true );
if($order_status == 'completed' || $order_status == 'processing'){
	$order_actions_list = 'get_order_actions_completed_list';
	$refund_btn = '<a href="" class="button button-primary wpm-refund-button">Refund</a>';
} else {
	$order_actions_list = 'get_order_actions_list';
	$refund_btn = '';
}
$args = array (
          'type'      => 'select',
		  'subtype'	  => '',
		  'id'	  => '_order_item_actions',
		  'name'	  => '_order_item_actions',
		  'required' => '',
		  'get_data_list' => $order_actions_list,
		  'value_type'=>'normal',
		  'wp_data' => 'custom',
		  'custom_value' => '',
      );
// this gets the post_meta value and echos back the input
$this->wpmerchant_render_settings_field($args);
echo '&nbsp;<a class="button wpm-apply-order-action">Apply</a></div>';
echo '<div class="wpm-column-2 wpm_btn_container">';
	echo '<span class="wpm-item-footer-message"></span>'.$refund_btn;
echo '</div><div class="clear"></div></div>';
	echo '</div>';
	}
	/**
	 * Product Data Meta Box Callback
	 * 
	 * @since    1.0.0
	 */
	function wpmerchant_product_data_meta_box( $post ) {

		// Add a nonce field so we can check for it later.
		wp_nonce_field( $this->plugin_name.'_meta_box', $this->plugin_name.'_meta_box_nonce' );
	
		/*
		 * Use get_post_meta() to retrieve an existing value
		 * from the database and use the value for the form.
		 */
		//$description = get_post_meta( $post->ID, $this->plugin_name.'_description', true );
		unset($args);
		$args = array('dashicon'=>'dashicons-admin-home','class'=>'general_tab active', 'tab_text'=>'General','link_href'=>'#general_product_fields','link_title'=>__('General Product Data', $this->plugin_name));
		$general_tab_id = 'general_product_fields';
		$general_tab = $this->getPostMetaTab($args);
		unset($args);
		$args = array('dashicon'=>'dashicons-chart-line','class'=>'inventory_tab', 'tab_text'=>'Inventory','link_href'=>'#inventory_product_fields','link_title'=>__('Inventory Product Data', $this->plugin_name));
		$inventory_tab = $this->getPostMetaTab($args);
		$inventory_tab_id = 'inventory_product_fields';
		unset($args);
		$args = array('dashicon'=>'dashicons-megaphone','class'=>'pre_post_tab', 'tab_text'=>'Pre/Post','link_href'=>'#pre_post_product_fields','link_title'=>__('Pre/Post Product Info', $this->plugin_name));
		$pre_post_tab = $this->getPostMetaTab($args);
		$pre_post_tab_id = 'pre_post_product_fields';
		echo '<div class="product_container">
				<ul class="product_container_tabs wpm_tabs">'.$general_tab.$inventory_tab.$pre_post_tab.'</ul>
				<!--Display block if general product fields is clicked otherwise hide-->
				<div id="'.$general_tab_id.'" class="wpm_show tab_content"><div class="product_field_containers">';
		echo '<ul class="wpmerchant_product_data_metabox">';
		
		echo '<li><label for="_regular_price">'.__( 'Price', $this->plugin_name).'</label>';
		
		require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/class-wpmerchant-helper.php';
		$helper_class = new Wpmerchant_Helper($this->plugin_name);
		$currency_symbol = $helper_class->get_currency_symbol();
		unset($args);
	  	$args = array (
	              'type'      => 'input',
				  'subtype'	  => 'number',
				  'id'	  => '_regular_price',
				  'name'	  => '_regular_price',
				  'required' => 'required="required"',
				  'get_data_list' => '',
				  'value_type'=>'normal',
				  'wp_data' => 'post_meta',
				  'post_id'=> $post->ID,
				  'min'=> '0',
				  'step'=> 'any',
				  'prepend_value'=>$currency_symbol
	          );
		// this gets the post_meta value and echos back the input
		$this->wpmerchant_render_settings_field($args);
		echo '</li></ul></div></div><div id="'.$inventory_tab_id.'" class="wpm_hide tab_content"><div class="product_field_containers"><ul class="wpmerchant_plan_data_metabox"><li><label for="_stock_status">'.__( 'Stock Status', $this->plugin_name).'</label>';
		unset($args);
	  	$args = array (
	              'type'      => 'select',
				  'subtype'	  => '',
				  'id'	  => '_stock_status',
				  'name'	  => '_stock_status',
				  'required' => '',
				  'get_data_list' => 'get_stock_status_list',
				  'value_type'=>'normal',
				  'wp_data' => 'post_meta',
				  'post_id'=> $post->ID
	          );
		// this gets the post_meta value and echos back the input
		$this->wpmerchant_render_settings_field($args);
		
		
		echo '</li><li><label for="_stock">'.__( 'Inventory', $this->plugin_name).'</label>';
		unset($args);
	  	$args = array (
	              'type'      => 'input',
				  'subtype'	  => 'text',
				  'id'	  => '_stock',
				  'name'	  => '_stock',
				  'required' => '',
				  'get_data_list' => '',
				  'value_type'=>'normal',
				  'wp_data' => 'post_meta',
				  'post_id'=> $post->ID
	          );
		// this gets the post_meta value and echos back the input
		$this->wpmerchant_render_settings_field($args);
		
		
		echo '</li><li><label for="_allow_backorders">'.__( 'Allow Backorders', $this->plugin_name).'</label>';
		unset($args);
	  	$args = array (
	              'type'      => 'input',
				  'subtype'	  => 'checkbox',
				  'id'	  => '_allow_backorders',
				  'name'	  => '_allow_backorders',
				  'required' => '',
				  'get_data_list' => '',
				  'value_type'=>'normal',
				  'wp_data' => 'post_meta',
				  'post_id'=> $post->ID
	          );
		// this gets the post_meta value and echos back the input
		$this->wpmerchant_render_settings_field($args);
		
		echo '</li>';
		// provide textarea name for $_POST variable
		$sold_out_message = get_post_meta( $post->ID, '_sold_out_message', true );
		//THis isn't necessary because we want the shortcodes to remain and only the description data to be shown
		//$sold_out_message = apply_filters('the_content', $sold_out_message); 
		$args = array(
		'textarea_name' => '_sold_out_message',
		); 
		echo '<li><label for="_sold_out_message">'.__( 'Sold Out Message', $this->plugin_name).'</label>';
		wp_editor( $sold_out_message, 'wpmerchant_sold_out_message_editor',$args); 
		echo '</li></ul></div></div><div id="'.$pre_post_tab_id.'" class="wpm_hide tab_content"><div class="product_field_containers"><p>'.__('Info to show before and after purchase', $this->plugin_name).'</p><ul class="wpmerchant_plan_data_metabox"><li><label for="_pre_checkout_title">'.__( 'Pre Checkout Title', $this->plugin_name).'</label>';
		unset($args);
		$args = array (
	              'type'      => 'input',
				  'subtype'	  => 'text',
				  'id'	  => '_pre_checkout_title',
				  'name'	  => '_pre_checkout_title',
				  'get_data_list' => '',
				  'value_type'=>'normal',
				  'wp_data' => 'post_meta',
				  'post_id'=> $post->ID
	          );
		$this->wpmerchant_render_settings_field($args);
		echo '</li><li><label for="_pre_checkout_description">'.__( 'Pre Checkout Desc', $this->plugin_name).'</label>';
		unset($args);
		$args = array (
	              'type'      => 'input',
				  'subtype'	  => 'text',
				  'id'	  => '_pre_checkout_description',
				  'name'	  => '_pre_checkout_description',
				  'get_data_list' => '',
				  'value_type'=>'normal',
				  'wp_data' => 'post_meta',
				  'post_id'=> $post->ID
	          );
		$this->wpmerchant_render_settings_field($args);
		echo'</li>';
		unset($args);
		// provide textarea name for $_POST variable
		$pre_checkout_content = get_post_meta( $post->ID, '_pre_checkout_content', true );
		//THis isn't necessary because we want the shortcodes to remain and only the pre checkout content data to be shown
		//$pre_checkout_content = apply_filters('the_content', $pre_checkout_content); 
		$args = array(
		'textarea_name' => '_pre_checkout_content',
		); 
		echo '<li><label for="_pre_checkout_content">'.__( 'Pre Checkout Content', $this->plugin_name).'</label>';
		wp_editor( $pre_checkout_content, 'wpmerchant_pre_checkout_editor',$args); 
		echo '</li><li><label for="_post_checkout_title">'.__( 'Post Checkout Title', $this->plugin_name).'</label>';
		unset($args);
		$args = array (
	              'type'      => 'input',
				  'subtype'	  => 'text',
				  'id'	  => '_post_checkout_title',
				  'name'	  => '_post_checkout_title',
				  'get_data_list' => '',
				  'value_type'=>'normal',
				  'wp_data' => 'post_meta',
				  'post_id'=> $post->ID
	          );
		$this->wpmerchant_render_settings_field($args);
		echo '</li><li><label for="_post_checkout_description">'.__( 'Post Checkout Desc', $this->plugin_name).'</label>';
		unset($args);
		$args = array (
	              'type'      => 'input',
				  'subtype'	  => 'text',
				  'id'	  => '_post_checkout_description',
				  'name'	  => '_post_checkout_description',
				  'get_data_list' => '',
				  'value_type'=>'normal',
				  'wp_data' => 'post_meta',
				  'post_id'=> $post->ID
	          );
		$this->wpmerchant_render_settings_field($args);
		echo'</li>';
		unset($args);
		// provide textarea name for $_POST variable
		$post_checkout_content = get_post_meta( $post->ID, '_post_checkout_content', true );
		//THis isn't necessary because we want the shortcodes to remain and only the post checkout data to be shown
		//$post_checkout_content = apply_filters('the_content', $post_checkout_content); 
		$args = array(
		'textarea_name' => '_post_checkout_content',
		); 
		echo '<li><label for="_post_checkout_content">'.__( 'Post Checkout Content', $this->plugin_name).'</label>';
		wp_editor( $post_checkout_content, 'wpmerchant_post_checkout_editor',$args); 
		echo '</li></ul></div></div><div class="clear"></div></div>';
	}
	/**
	 * Product Cost Meta Box Callback
	 * 
	 * @since    1.0.0
	 */
	function wpmerchant_product_description_meta_box( $post ) {
		/*
		 * Use get_post_meta() to retrieve an existing value
		 * from the database and use the value for the form.
		 */
		$description = get_post_meta( $post->ID, '_description', true );
		//THis isn't necessary because we want the shortcodes to remain and only the description data to be shown
		//$description = apply_filters('the_content', $description); 
		// provide textarea name for $_POST variable
		$args = array(
		'textarea_name' => '_description',
		); 
		wp_editor( $description, 'wpmerchant_product_description_editor',$args); 
	}
	/**
	 * When the post is saved, saves our custom data.
	 *
	 * @since    1.0.0
	 */
	function wpmerchant_product_save_meta_box_data( $post_id ) {
		/*
		 * We need to verify this came from our screen and with proper authorization,
		 * because the save_post action can be triggered at other times.
		 */
		
		// Check if our nonce is set.
		if ( ! isset( $_POST[$this->plugin_name.'_meta_box_nonce'] ) ) {
			return;
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST[$this->plugin_name.'_meta_box_nonce'], $this->plugin_name.'_meta_box' ) ) {
			return;
		}

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check the user's permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		/* OK, it's safe for us to save the data now. */
	
		// Make sure that it is set.
		if ( ! isset( $_POST['_regular_price'] ) && ! isset( $_POST['_description'] ) && ! isset( $_POST['_stock'] ) && ! isset( $_POST['_allow_backorders'] ) && ! isset( $_POST['_sold_out_message'] ) && ! isset( $_POST['_stock_status'] )  && !isset($_POST['_post_checkout_content']) && !isset($_POST['_pre_checkout_description']) && !isset($_POST['_post_checkout_description']) && !isset($_POST['_post_checkout_title']) && !isset($_POST['_pre_checkout_title']) && !isset($_POST['_pre_checkout_content'])) {
			return;
		}

		// Sanitize user input.
		$product_allow_backorders = sanitize_text_field( $_POST['_allow_backorders'] );
		$product_price = sanitize_text_field( $_POST['_regular_price'] );
		$product_inventory = sanitize_text_field( $_POST['_stock'] );
		$product_description = wp_kses_post( $_POST['_description'] );
		$sold_out_message = wp_kses_post( $_POST['_sold_out_message'] );
		$stock_status = sanitize_text_field( $_POST['_stock_status'] );
		$pre_checkout_content = wp_kses_post( $_POST['_pre_checkout_content'] ); 
		$post_checkout_content = wp_kses_post( $_POST['_post_checkout_content'] );
		$pre_checkout_title = sanitize_text_field( $_POST['_pre_checkout_title'] ); 
		$pre_checkout_description = sanitize_text_field( $_POST['_pre_checkout_description'] ); 
		$post_checkout_title = sanitize_text_field( $_POST['_post_checkout_title'] );
		$post_checkout_description = sanitize_text_field( $_POST['_post_checkout_description'] );
		
		
		
		// Update the meta field in the database.
		// ALL of these meta fields are PROTECTED - so that a user can use custom fields without having all of these display in addition to the ones that they create
		update_post_meta( $post_id, '_description', $product_description );
		update_post_meta( $post_id, '_regular_price', $product_price );
		update_post_meta( $post_id, '_stock', $product_inventory );
		update_post_meta( $post_id, '_allow_backorders', $product_allow_backorders );
		update_post_meta( $post_id, '_sold_out_message', $sold_out_message );
		update_post_meta( $post_id, '_stock_status', $stock_status );
		update_post_meta( $post_id, '_pre_checkout_content', $pre_checkout_content );
		update_post_meta( $post_id, '_post_checkout_content', $post_checkout_content );
		update_post_meta( $post_id, '_pre_checkout_title', $pre_checkout_title );
		update_post_meta( $post_id, '_pre_checkout_description', $pre_checkout_description );
		update_post_meta( $post_id, '_post_checkout_title', $post_checkout_title );
		update_post_meta( $post_id, '_post_checkout_description', $post_checkout_description );
		
	}
	/**
	 * When the post is saved, saves our custom data.
	 *
	 * @since    1.0.0
	 */
	function wpmerchant_order_save_meta_box_data( $post_id ) {
		/*
		 * We need to verify this came from our screen and with proper authorization,
		 * because the save_post action can be triggered at other times.
		 */
		
		// Check if our nonce is set.
		if ( ! isset( $_POST[$this->plugin_name.'_meta_box_nonce'] ) ) {
			return;
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST[$this->plugin_name.'_meta_box_nonce'], $this->plugin_name.'_meta_box' ) ) {
			return;
		}

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check the user's permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		/* OK, it's safe for us to save the data now. */
	
		// Make sure that it is set.
if(!isset($_POST['_order_date']) && !isset($_POST['_order_date_hour']) && !isset($_POST['_order_date_minute']) && !isset($_POST['_order_status']) && !isset($_POST['_customer']) && !isset($_POST['_billing_first_name']) && !isset($_POST['_billing_last_name']) && !isset($_POST['_billing_company']) && !isset($_POST['_billing_address_1']) && !isset($_POST['_billing_address_2']) && !isset($_POST['_billing_city']) && !isset($_POST['_billing_state']) && !isset($_POST['_billing_postcode']) && !isset($_POST['_billing_email']) && !isset($_POST['_billing_phone']) && !isset($_POST['_shipping_first_name']) && !isset($_POST['_shipping_last_name']) && !isset($_POST['_shipping_company']) && !isset($_POST['_shipping_address_1']) && !isset($_POST['_shipping_address_2']) && !isset($_POST['_shipping_city']) && !isset($_POST['_shipping_state']) && !isset($_POST['_shipping_postcode']) && !isset($_POST['_shipping_customer_note'])){
	return;
}	
		// Sanitize user input.
		$order_date 			= sanitize_text_field($_POST['_order_date']);
		$order_date_hour 		= sanitize_text_field($_POST['_order_date_hour']);
		$order_date_minute 		= sanitize_text_field($_POST['_order_date_minute']);
		$order_status 			= sanitize_text_field($_POST['_order_status']);
		$customer 				= sanitize_text_field($_POST['_customer']);
		$billing_first_name 	= sanitize_text_field($_POST['_billing_first_name']);
		$billing_last_name 		= sanitize_text_field($_POST['_billing_last_name']);
		$billing_company 		= sanitize_text_field($_POST['_billing_company']);
		$billing_address_1 		= sanitize_text_field($_POST['_billing_address_1']);
		$billing_address_2 		= sanitize_text_field($_POST['_billing_address_2']);
		$billing_city 			= sanitize_text_field($_POST['_billing_city']);
		$billing_state 			= sanitize_text_field($_POST['_billing_state']);
		$billing_postcode		= sanitize_text_field($_POST['_billing_postcode']);
		$billing_email			= sanitize_text_field($_POST['_billing_email']);
		$billing_phone 			= sanitize_text_field($_POST['_billing_phone']);
		$shipping_first_name	= sanitize_text_field($_POST['_shipping_first_name']);
		$shipping_last_name 	= sanitize_text_field($_POST['_shipping_last_name']);
		$shipping_company 		= sanitize_text_field($_POST['_shipping_company']);
		$shipping_address_1 	= sanitize_text_field($_POST['_shipping_address_1']);
		$shipping_address_2 	= sanitize_text_field($_POST['_shipping_address_2']);
		$shipping_city 			= sanitize_text_field($_POST['_shipping_city']);
		$shipping_state 		= sanitize_text_field($_POST['_shipping_state']);
		$shipping_postcode 		= sanitize_text_field($_POST['_shipping_postcode']);
		$shipping_customer_note = sanitize_text_field($_POST['_shipping_customer_note']);
		
		
		// Update the meta field in the database.
		// ALL of these meta fields are PROTECTED - so that a user can use custom fields without having all of these display in addition to the ones that they create
		update_post_meta($post_id,'_order_date',$order_date);
		update_post_meta($post_id,'_order_date_hour',$order_date_hour);
		update_post_meta($post_id,'_order_date_minute',$order_date_minute);
		update_post_meta($post_id,'_order_status',$order_status);
		update_post_meta($post_id,'_customer',$customer);
		update_post_meta($post_id,'_billing_first_name',$billing_first_name);
		update_post_meta($post_id,'_billing_last_name',$billing_last_name);
		update_post_meta($post_id,'_billing_company',$billing_company);
		update_post_meta($post_id,'_billing_address_1',$billing_address_1);
		update_post_meta($post_id,'_billing_address_2',$billing_address_2);
		update_post_meta($post_id,'_billing_city',$billing_city);
		update_post_meta($post_id,'_billing_state',$billing_state);
		update_post_meta($post_id,'_billing_postcode',$billing_postcode);
		update_post_meta($post_id,'_billing_email',$billing_email);
		update_post_meta($post_id,'_billing_phone',$billing_phone);
		update_post_meta($post_id,'_shipping_first_name',$shipping_first_name);
		update_post_meta($post_id,'_shipping_last_name',$shipping_last_name);
		update_post_meta($post_id,'_shipping_company',$shipping_company);
		update_post_meta($post_id,'_shipping_address_1',$shipping_address_1);
		update_post_meta($post_id,'_shipping_address_2',$shipping_address_2);
		update_post_meta($post_id,'_shipping_city',$shipping_city);
		update_post_meta($post_id,'_shipping_state',$shipping_state);
		update_post_meta($post_id,'_shipping_postcode',$shipping_postcode);
		update_post_meta($post_id,'_shipping_customer_note',$shipping_customer_note); 
		
		// unhook this function so it doesn't loop infinitely - do this because we're calling wp_update_post earlier which will call this function again if it isn't unhooked
		remove_action( 'save_post_wpmerchant_order',  array( $this, $this->plugin_name.'_order_save_meta_box_data') );
		// update the post, which calls save_post again		
		$saved_post = array( 'ID' => $post_id, 'post_title' => 'Order #'.$post_id, 'post_name' => 'order-'.$post_id,);
		wp_update_post( $saved_post );
		
		// re-hook this function
		add_action( 'save_post_wpmerchant_order',  array( $this, $this->plugin_name.'_order_save_meta_box_data') );
		
	}
	/**
	 * Register Custom Post Types
	 *
	 * @since    1.0.0
	 */
	public function register_custom_post_types(){
		
		$productArgs = array(
			'label'=>__('WPMerchant Product', $this->plugin_name),
			'labels'=>
				array(
					'name'=>__('Products', $this->plugin_name),
					'singular_name'=>__('Product', $this->plugin_name),
					'add_new'=>__('Add Product', $this->plugin_name),
					'add_new_item'=>__('Add New Product', $this->plugin_name),
					'edit_item'=>__('Edit Product', $this->plugin_name),
					'new_item'=>__('New Product', $this->plugin_name),
					'view_item'=>__('View Product', $this->plugin_name),
					'search_items'=>__('Search Product', $this->plugin_name),
					'not_found'=>__('No Products Found', $this->plugin_name),
					'not_found_in_trash'=>__('No Products Found in Trash', $this->plugin_name)
				),
			'public'=>true,
			'description'=>__('WPMerchant Product', $this->plugin_name), 
			'exclude_from_search'=>false,
			'show_ui'=>true,
			'show_in_menu'=>$this->plugin_name,
			'supports'=>array('title','thumbnail', 'custom_fields'),
			'taxonomies'=>array('category','post_tag'));
		// post type should be singular NOT plural - wpmerchant_product instaed of products
		register_post_type( 'wpmerchant_product', $productArgs );
		/*$orderArgs = array(
			'label'=>__('WPMerchant Order', $this->plugin_name),
			'labels'=>
				array(
					'name'=>__('Orders', $this->plugin_name),
					'singular_name'=>__('Order', $this->plugin_name),
					'add_new'=>__('Add Order', $this->plugin_name),
					'add_new_item'=>__('Add New Order', $this->plugin_name),
					'edit_item'=>__('Edit Order', $this->plugin_name),
					'new_item'=>__('New Order', $this->plugin_name),
					'view_item'=>__('View Order', $this->plugin_name),
					'search_items'=>__('Search Order', $this->plugin_name),
					'not_found'=>__('No Orders Found', $this->plugin_name),
					'not_found_in_trash'=>__('No Orders Found in Trash', $this->plugin_name)
				),
			'public'=>false,
			'description'=>__('WPMerchant Order', $this->plugin_name), 
			'exclude_from_search'=>true,
			'show_ui'=>true,
			'show_in_menu'=>$this->plugin_name,
			'supports'=>array('title'));
		// post type should be singular NOT plural - wpmerchant_product instaed of products
		register_post_type( 'wpmerchant_order', $orderArgs );*/
	}
	/**
	 * StripeCheckout Shortcode Functionality
	 *
	 * @since    1.0.0
	 */
	public function stripeCheckoutShortcode( $atts, $content = "" ) {
		//[wpmerchant_button plans="" products="" element="" classes="" style="" other=""][/wpmerchant_button]
		// [wpmerchant_button plans="" products="1196" element="span" classes="nectar-button handdrawn-arrow large accent-color regular-button" style="visibility: visible; color: rgb(255, 255, 255) !important;" other="data-color-override:false;data-hover-color-override:false;data-hover-text-color-override:#fff;"]Buy Now[/wpmerchant_button]
		$a = shortcode_atts( array(
			          'plans' => '',
					  'products'=>'',
					  'classes'=>'',
					  'element'=>'',
					  'style'=>'',
					  'other'=>''
			      ), $atts );
		if($a['products']) {
			// ALLOW MULTIPLE PRODUCTS TO BE PURCHASED
			if(strstr($a['products'],',')){
				$product_ids2 = $a['products'];
				$product_ids1 = explode(",",$product_ids2);
				// get Quantity of products purchased by getting frequency of different product ids in hte array
				$product_id_frequency = array_count_values($product_ids1);
				foreach($product_id_frequency AS $key=>$value){
					$product_ids[] = array('quantity'=>$value, 'id'=>$key);
				}
				
				foreach($product_ids AS $key=>$value){
					$p = trim($value['id']);
					if(!intval($p)){
						continue;
					}
					//$product = get_post($product_id)
					$cost = get_post_meta( $p, '_regular_price', true );
					$title = get_the_title( $p );
					// add this product's amount onto the sum of all previous products
					if($key == 0){
						$amount = $cost*100*$value['quantity'];
						$description = $title;
					} else {
						$amount += $cost*100*$value['quantity'];
						$description .= ', '.$title;
						//$title = 'Multiple Products';
					}
				}
			} else {
				$amount = get_post_meta( $a['products'], '_regular_price', true )*100;
				//$description = get_post_meta( $a['product_id'], '_description', true );
				$description = get_the_title( $a['products'] );
				$product_ids[] = array('quantity'=>1, 'id'=>$a['products']);
			}
			$products = json_encode($product_ids);
		} else {
			$products = '';
		}
		if($a['plans']) {
			// ALLOW MULTIPLE PRODUCTS TO BE PURCHASED
			if(strstr($a['plans'],',')){
				$plan_ids2 = $a['plans'];
				$plan_ids1 = explode(",",$plan_ids2);
				// get Quantity of products purchased by getting frequency of different product ids in hte array
				$plan_id_frequency = array_count_values($plan_ids1);
				foreach($plan_id_frequency AS $key=>$value){
					$plan_ids[] = array('quantity'=>$value, 'id'=>$key);
				}
				
				foreach($plan_ids AS $key=>$value){
					$p = trim($value['id']);
					if(!intval($p)){
						continue;
					}
					//$product = get_post($product_id)
					$cost = get_post_meta( $p, '_regular_price', true );
					$title = get_the_title( $p );
					// add this product's amount onto the sum of all previous products
					if($key == 0){
						$amount = $cost*100*$value['quantity'];
						$description = $title;
					} else {
						$amount += $cost*100*$value['quantity'];
						$description .= ', '.$title;
						//$title = 'Multiple Products';
					}
				}
			} else {
				$amount = get_post_meta( $a['plans'], '_regular_price', true )*100;
				//$description = get_post_meta( $a['plan_id'], '_description', true );
				$description = get_the_title( $a['plans'] );
				$plan_ids[] = array('quantity'=>1, 'id'=>$a['plans']);
			}
			$plans = json_encode($plan_ids);
			
		} else {
			$plans = '';
		}
		
		/**
		A bunch of vars are being localized to the public.js script (so they aren't included in the data vars) because they're in the class-wpmerchant-public.php enqueue_scripts function.  This is a lot more efficient than running all of the other get_option functions every time we're populating shortcode html.
		**/
		$element = $a['element'] ? $a['element'] : 'button';
		$classes1 = 'wpMerchantPurchase';
		$classes = $a['classes'] ? $classes1.' '.$a['classes'] : $classes1;
		$style = $a['style'] ? $a['style'] : '';
		
		if($a['other']){
			// split the strings based on colon and semicolon
			$pairs = explode(";",$a['other']);
			$other = '';
			foreach($pairs AS $p){
				if(!$p){
					continue;
				}
				$key_value = explode(":",$p);
				$other .= ' '.$key_value[0] .'="'. esc_attr($key_value[1]).'"';
			}
		} else {
			$other = '';
		}
		return '<'.$element.' class="'.esc_attr($classes).'" style="'.esc_attr($style).'" data-description="'.esc_attr($description).'" data-amount="'.esc_attr($amount).'" data-plans="'.esc_attr($plans).'" data-products="'.esc_attr($products).'" '.$other.'>'.$content.'</'.$element.'>';
		// if plan id panelLabel 'Subscribe - {{amount}}/month',
		// how ami passing public key to js file
	}
	/**
	 * Stripe Modal Shortcode Functionality
	 *
	 * @since    1.0.0
	 */
	public function stripeModalShortcode( $atts, $content = "" ) {
		//[wpmerchant_modal plans="" products="" element="" classes="" style="" other=""][/wpmerchant_modal]
		// [wpmerchant_modal plans="" products="1196" element="span" classes="nectar-button handdrawn-arrow large accent-color regular-button" style="visibility: visible; color: rgb(255, 255, 255) !important;" other="data-color-override:false;data-hover-color-override:false;data-hover-text-color-override:#fff;"]Buy Now[/wpmerchant_modal]
		// YOu can only get the content associated with 1 plan or 1 product
		$a = shortcode_atts( array(
			          'plan' => '',
					  'product'=>'',
					  'classes'=>'',
					  'element'=>'button',
					  'style'=>'',
					  'other'=>''
			      ), $atts );
		if($a['product']) {
			// Get modal content
			$modal_content = get_post_meta( $a['product'], '_pre_checkout_content', true );
			// allows us to place shortcodes in the pre_checkout_content as well
			$modal_content = do_shortcode($modal_content); 
			$title = get_post_meta( $a['product'], '_pre_checkout_title', true );
			$description = get_post_meta( $a['product'], '_pre_checkout_description', true );
			/*$title = get_the_title( $a['plan'] );
			$description = get_post_meta( $a['product'], $this->plugin_name.'_description', true );*/
			
		} else {
			$modal_content = '';
			$title = '';
			$description = '';
		}
		if($a['plan']) {
			// Get modal content
			$modal_content = get_post_meta( $a['plan'], '_pre_checkout_content', true );
			// RUN do_shortcode instead of apply_filters('the_content', $pre_checkout_content); 
				 // so that the html that results from the shortcode is shown
				 // otherwise the shortcode will be shown as well as additional content
			$modal_content = do_shortcode($modal_content); 
			$title = get_post_meta( $a['plan'], '_pre_checkout_title', true );
			$description = get_post_meta( $a['plan'], '_pre_checkout_description', true );
			/*$title = get_the_title( $a['plan'] );
			$description = get_post_meta( $a['plan'], $this->plugin_name.'_description', true );*/
		} else {
			$modal_content = '';
			$title = '';
			$description = '';
		}
		
		/**
		append the modal element in the public.js script (so they aren't included in the data vars) because they're in the class-wpmerchant-public.php enqueue_scripts function.  Then, we insert the content associated with this button into that foundation
		**/
		$element = $a['element'] ? $a['element'] : 'button';
		$classes1 = 'wpMerchantModal';
		$classes = $a['classes'] ? $classes1.' '.$a['classes'] : $classes1;
		$style = $a['style'] ? $a['style'] : '';
		
		if($a['other']){
			// split the strings based on colon and semicolon
			$pairs = explode(";",$a['other']);
			$other = '';
			foreach($pairs AS $p){
				if(!$p){
					continue;
				}
				$key_value = explode(":",$p);
				$other .= ' '.$key_value[0] .'="'. esc_attr($key_value[1]).'"';
			}
		} else {
			$other = '';
		}
  		$image = get_option( $this->plugin_name.'_stripe_checkout_logo' );
  		if(!$image){
  			$image = plugin_dir_url( __FILE__ ).'public/img/marketplace.png';
  		}
		if($a['product']){
			$data_item = 'data-plan="" data-product="'.esc_attr($a['product']).'"';
		} elseif($a['plan']){
			$data_item = 'data-plan="'.esc_attr($a['plan']).'" data-product=""';
		}
		
		return '<'.$element.' '.$data_item.' class="'.esc_attr($classes).'" style="'.esc_attr($style).'" data-description="'.esc_attr($description).'" data-title="'.esc_attr($title).'" '.$other.'>'.$content.'</'.$element.'>'.$modal;
		
	}
	/**
	OPTION PAGE FUNCTIONALITY
	**/
	public function add_and_register_settings_field($args){
		add_settings_field(
		  $args['id'],
		  $args['field_title'],
		  $args['callback'],
		  $args['section_page'],
		  $args['section_id'],
		  $args
		);
		if($args['register_callback']){
			register_setting(
				$args['section_page'],
				$args['name'],
				$args['register_callback']
			);
		} else {
			register_setting(
				$args['section_page'],
				$args['name']
			);
		}
	}
	/**
     * Register the Stripe account section Fields, Stripe API Secret and Public Key fields etc
     * 
     *
     * @since     1.0.0
     */
    public function register_and_build_fields() {
		/**
	   * First, we add_settings_section. This is necessary since all future settings must belong to one.
	   * Second, add_settings_field - this and the third step are accomplished via the add_and_register_settings_field function
	   * Third, register_setting
	   */ 
		
		$section_page = $this->plugin_name.'_general_settings';
		$section_id = $this->plugin_name.'_general_section';
		add_settings_section(
		  // ID used to identify this section and with which to register options
		  $section_id, 
		  // Title to be displayed on the administration page
		  '',  
		  // Callback used to render the description of the section
		   array( $this, 'wpmerchant_display_general_account' ),    
		  // Page on which to add this section of options
		  $section_page                   
		);
		unset($args);
	  	$args = array (
	              'type'      => 'input',
				  'subtype'	  => 'text',
				  'id'	  => $this->plugin_name.'_company_name',
				  'name'	  => $this->plugin_name.'_company_name',
				  'required' => '',
				  'get_data_list' => '',
				  'value_type'=>'normal',
				  'wp_data' => 'option',
				  'section_id' => $section_id,
				  'section_page' => $section_page,
				  'field_title'=>__('Company Name', $this->plugin_name),
				  'callback'=> array( $this, 'wpmerchant_render_settings_field' )
	          );
		$this->add_and_register_settings_field($args);
		unset($args);
	  	$args = array (
	              'type'      => 'input',
				  'subtype'	  => 'text',
				  'id'	  => $this->plugin_name.'_logo',
				  'name'	  => $this->plugin_name.'_logo',
				  'required' => '',
				  'get_data_list' => '',
				  'value_type'=>'normal',
				  'wp_data' => 'option',
				  'section_id' => $section_id,
				  'section_page' => $section_page,
				  'field_title'=>__('Logo', $this->plugin_name),
				  'callback'=> array( $this, 'wpmerchant_render_settings_field' )
	          );
		$this->add_and_register_settings_field($args);
		unset($args);
	  	$args = array (
	              'type'      => 'select',
				  'subtype'	  => '',
				  'id'	  => $this->plugin_name.'_currency',
				  'name'	  => $this->plugin_name.'_currency',
				  'required' => 'required="required"',
				  'get_data_list' => 'get_currency_list',
				  'value_type'=>'normal',
				  'wp_data' => 'option',
				  'section_id' => $section_id,
				  'section_page' => $section_page,
				  'field_title'=>__('Currency', $this->plugin_name),
				  'callback'=> array( $this, 'wpmerchant_render_settings_field' )
	          );
		$this->add_and_register_settings_field($args);
		unset($args);
	  	$args = array (
	              'type'      => 'select',
				  'subtype'	  => '',
				  'id'	  => $this->plugin_name.'_payment_processor',
				  'name'	  => $this->plugin_name.'_payment_processor',
				  'required' => 'required="required"',
				  'get_data_list' => 'get_payment_processor_list',
				  'value_type'=>'normal',
				  'wp_data' => 'option',
				  'section_id' => $section_id,
				  'section_page' => $section_page,
				  'field_title'=>__('Payment Processor', $this->plugin_name),
				  'callback'=> array( $this, 'wpmerchant_render_settings_field' )
	          );
		$this->add_and_register_settings_field($args);
			
		unset($args);
	  	$args = array (
	              'type'      => 'select',
				  'subtype'	  => '',
				  'id'	  => $this->plugin_name.'_email_list_processor',
				  'name'	  => $this->plugin_name.'_email_list_processor',
				  'required' => 'required="required"',
				  'get_data_list' => 'get_email_list_processor_list',
				  'value_type'=>'normal',
				  'wp_data' => 'option',
				  'section_id' => $section_id,
				  'section_page' => $section_page,
				  'field_title'=>__('Email List Processor', $this->plugin_name),
				  'callback'=> array( $this, 'wpmerchant_render_settings_field' )
	          );
		$this->add_and_register_settings_field($args);
			
			// THIS SECTION IS USED AS A BACKUP IN CASE A USER DOESN'T ENTER A PRODUCT SPECIFIC VERSION
			
			$section_id = $this->plugin_name.'_post_checkout_section';
			$section_page = $this->plugin_name.'_post_checkout_settings';
			add_settings_section(
				// ID used to identify this section and with which to register options
				$section_id, 
				// Title to be displayed on the administration page
				__('After Checkout', $this->plugin_name),  
				// Callback used to render the description of the section
				array( $this, 'wpmerchant_display_post_checkout' ),    
				// Page on which to add this section of options
				$section_page                   
			);
			unset($args);
		  	$args = array (
		              'type'      => 'input',
					  'subtype'	  => 'text',
					  'id'	  => $this->plugin_name.'_post_checkout_redirect',
					  'name'	  => $this->plugin_name.'_post_checkout_redirect',
					  'required' => '',
					  'get_data_list' => '',
					  'value_type'=>'normal',
					  'wp_data' => 'option',
					  'section_id' => $section_id,
			  		  'section_page' => $section_page,
			  		  'field_title'=>__('Thank You Page Redirect', $this->plugin_name),
			  		  'callback'=> array( $this, 'wpmerchant_render_settings_field' )
			        );
			$this->add_and_register_settings_field($args);
			/**
			START HERE
			**/
			unset($args);
		  	$args = array (
		              'type'      => 'input',
					  'subtype'	  => 'text',
					  'id'	  => $this->plugin_name.'_post_checkout_title',
					  'name'	  => $this->plugin_name.'_post_checkout_title',
					  'required' => '',
					  'get_data_list' => '',
					  'value_type'=>'normal',
					  'wp_data' => 'option',
					  'section_id' => $section_id,
			  		  'section_page' => $section_page,
			  		  'field_title'=>__('Thank You Title', $this->plugin_name),
			  		  'callback'=> array( $this, 'wpmerchant_render_settings_field' )
			        );
			$this->add_and_register_settings_field($args);
			unset($args);
		  	$args = array (
		              'type'      => 'input',
					  'subtype'	  => 'text',
					  'id'	  => $this->plugin_name.'_post_checkout_description',
					  'name'	  => $this->plugin_name.'_post_checkout_description',
					  'required' => '',
					  'get_data_list' => '',
					  'value_type'=>'normal',
					  'wp_data' => 'option',
					  'section_id' => $section_id,
			  		  'section_page' => $section_page,
			  		  'field_title'=>__('Thank You Description', $this->plugin_name),
			  		  'callback'=> array( $this, 'wpmerchant_render_settings_field' )
			        );
			$this->add_and_register_settings_field($args);
			unset($args);
		  	$args = array (
		              'type'      => 'wysiwyg',
					  'subtype'	  => '',
					  'id'	  => $this->plugin_name.'_post_checkout_content',
					  'name'	  => $this->plugin_name.'_post_checkout_content',
					  'required' => '',
					  'value_type'=>'normal',
					  'wp_data' => 'option',
					  'section_id' => $section_id,
			  		  'section_page' => $section_page,
			  		  'field_title'=>__('Thank You Content', $this->plugin_name),
			  		  'callback'=> array( $this, 'wpmerchant_render_settings_field' )
			        );
			$this->add_and_register_settings_field($args);
		
		$section_id = $this->plugin_name.'_stripe_account_section';
		$section_page = $this->plugin_name.'_stripe_settings';
		add_settings_section(
			// ID used to identify this section and with which to register options
			$section_id, 
			// Title to be displayed on the administration page
			__('Stripe Account', $this->plugin_name),  
			// Callback used to render the description of the section
			array( $this, 'wpmerchant_display_stripe_account' ),    
			// Page on which to add this section of options
			$section_page                   
		);
		unset($args);
	  	$args = array (
	              'type'      => 'select',
				  'subtype'	  => '',
				  'id'	  => $this->plugin_name.'_stripe_status',
				  'name'	  => $this->plugin_name.'_stripe_status',
				  'required' => 'required="required"',
				  'get_data_list' => 'get_stripe_status_list',
				  'value_type'=>'normal',
				  'wp_data' => 'option',
				  'section_id' => $section_id,
		  		  'section_page' => $section_page,
		  		  'field_title'=>__('Stripe Mode', $this->plugin_name),
		  		  'callback'=> array( $this, 'wpmerchant_render_settings_field' ),
				  'register_callback'=>array( $this, 'wpmerchant_validate_status' )
		        );
		$this->add_and_register_settings_field($args);
		unset($args);
	  	$args = array (
	              'type'      => 'input',
				  'subtype'	  => 'text',
				  'id'	  => $this->plugin_name.'_stripe_checkout_logo',
				  'name'	  => $this->plugin_name.'_stripe_checkout_logo',
				  'required' => '',
				  'get_data_list' => '',
				  'value_type'=>'normal',
				  'wp_data' => 'option',
				  'section_id' => $section_id,
		  		  'section_page' => $section_page,
		  		  'field_title'=>__('Checkout Logo (128x128px minimum)', $this->plugin_name),
		  		  'callback'=> array( $this, 'wpmerchant_render_settings_field' )
		        );
		$this->add_and_register_settings_field($args);
		unset($args);
	  	$args = array (
	              'type'      => 'select',
				  'subtype'	  => '',
				  'id'	  => $this->plugin_name.'_stripe_shippingAddress',
				  'name'	  => $this->plugin_name.'_stripe_shippingAddress',
				  'required' => '',
				  'get_data_list' => 'get_stripe_config_list',
				  'value_type'=>'normal',
				  'wp_data' => 'option',
				  'section_id' => $section_id,
		  		  'section_page' => $section_page,
		  		  'field_title'=>__('Get Shipping Address', $this->plugin_name),
		  		  'callback'=> array( $this, 'wpmerchant_render_settings_field' )
		        );
		$this->add_and_register_settings_field($args);
		unset($args);
	  	$args = array (
	              'type'      => 'select',
				  'subtype'	  => '',
				  'id'	  => $this->plugin_name.'_stripe_billingAddress',
				  'name'	  => $this->plugin_name.'_stripe_billingAddress',
				  'required' => '',
				  'get_data_list' => 'get_stripe_config_list',
				  'value_type'=>'normal',
				  'wp_data' => 'option',
				  'section_id' => $section_id,
		  		  'section_page' => $section_page,
		  		  'field_title'=>__('Get Billing Address', $this->plugin_name),
		  		  'callback'=> array( $this, 'wpmerchant_render_settings_field' )
		        );
		$this->add_and_register_settings_field($args);
		unset($args);
	  	$args = array (
	              'type'      => 'select',
				  'subtype'	  => '',
				  'id'	  => $this->plugin_name.'_stripe_zipCode',
				  'name'	  => $this->plugin_name.'_stripe_zipCode',
				  'required' => '',
				  'get_data_list' => 'get_stripe_config_list',
				  'value_type'=>'normal',
				  'wp_data' => 'option',
				  'section_id' => $section_id,
		  		  'section_page' => $section_page,
		  		  'field_title'=>__('Validate Billing Zip Code', $this->plugin_name),
		  		  'callback'=> array( $this, 'wpmerchant_render_settings_field' )
		        );
		$this->add_and_register_settings_field($args);
		
		
		$siteURL = urlencode(get_site_url().'/wp-admin/admin.php?page=wpmerchant-settings');
		$stripeLivePublicKey = get_option('wpmerchant_stripe_live_public_key');
		$stripeLiveSecretKey = get_option('wpmerchant_stripe_live_secret_key');
		$stripeTestPublicKey = get_option('wpmerchant_stripe_test_public_key');
		$stripeTestSecretKey = get_option('wpmerchant_stripe_test_secret_key');
		if($stripeLivePublicKey && $stripeLiveSecretKey && $stripeTestPublicKey && $stripeTestSecretKey){
			unset($args);
		  	$args = array (
					  'id'	  => $this->plugin_name.'_stripe_api_2',
					  'name'	  => $this->plugin_name.'_stripe_api_2',
					  'section_id' => $section_id,
			  		  'section_page' => $section_page,
			  		  'field_title'=>__('Connected to Stripe', $this->plugin_name),
			  		  'callback'=> array( $this, 'wpmerchant_render_stripe_connected' )
			        );
			$this->add_and_register_settings_field($args);
		} else {
			unset($args);
		  	$args = array (
					  'id'	  => $this->plugin_name.'_stripe_api_2',
					  'name'	  => $this->plugin_name.'_stripe_api_2',
					  'section_id' => $section_id,
			  		  'section_page' => $section_page,
			  		  'field_title'=>__('Connect to Stripe', $this->plugin_name),
			  		  'callback'=> array( $this, 'wpmerchant_render_stripe_connect' )
			        );
			$this->add_and_register_settings_field($args);
		}
		unset($args);
	  	$args = array (
	              'type'      => 'input',
				  'subtype'	  => 'hidden',
				  'id'	  => $this->plugin_name.'_stripe_live_secret_key',
				  'name'	  => $this->plugin_name.'_stripe_live_secret_key',
				  'get_data_list' => '',
				  'value_type'=>'normal',
				  'wp_data' => 'option',
				  'section_id' => $section_id,
		  		  'section_page' => $section_page,
		  		  'field_title'=>'',
		  		  'callback'=> array( $this, 'wpmerchant_render_settings_field' ),
				  'register_callback'=> array( $this, 'wpmerchant_validate_secret_api_key' )
		        );
		$this->add_and_register_settings_field($args);
		unset($args);
	  	$args = array (
	              'type'      => 'input',
				  'subtype'	  => 'hidden',
				  'id'	  => $this->plugin_name.'_stripe_live_public_key',
				  'name'	  => $this->plugin_name.'_stripe_live_public_key',
				  'get_data_list' => '',
				  'value_type'=>'normal',
				  'wp_data' => 'option',
				  'section_id' => $section_id,
		  		  'section_page' => $section_page,
		  		  'field_title'=>'',
		  		  'callback'=> array( $this, 'wpmerchant_render_settings_field' )
		        );
		$this->add_and_register_settings_field($args);
		unset($args);
	  	$args = array (
	              'type'      => 'input',
				  'subtype'	  => 'hidden',
				  'id'	  => $this->plugin_name.'_stripe_test_secret_key',
				  'name'	  => $this->plugin_name.'_stripe_test_secret_key',
				  'get_data_list' => '',
				  'value_type'=>'normal',
				  'wp_data' => 'option',
				  'section_id' => $section_id,
		  		  'section_page' => $section_page,
		  		  'field_title'=>'',
		  		  'callback'=> array( $this, 'wpmerchant_render_settings_field' )
		        );
		$this->add_and_register_settings_field($args);
		unset($args);
	  	$args = array (
	              'type'      => 'input',
				  'subtype'	  => 'hidden',
				  'id'	  => $this->plugin_name.'_stripe_test_public_key',
				  'name'	  => $this->plugin_name.'_stripe_test_public_key',
				  'get_data_list' => '',
				  'value_type'=>'normal',
				  'wp_data' => 'option',
				  'section_id' => $section_id,
		  		  'section_page' => $section_page,
		  		  'field_title'=>'',
		  		  'callback'=> array( $this, 'wpmerchant_render_settings_field' )
		        );
		$this->add_and_register_settings_field($args);
		
		$section_id = $this->plugin_name.'_mailchimp_account_section';
		$section_page = $this->plugin_name.'_mailchimp_settings';	
			/*MAILCHIMP*/
		add_settings_section(
		  // ID used to identify this section and with which to register options
		  $section_id, 
		  // Title to be displayed on the administration page
		  'MailChimp Account',  
		  // Callback used to render the description of the section
		   array( $this, 'wpmerchant_display_mailchimp_account' ),    
		  // Page on which to add this section of options
		  $section_page                  
		);
		// GET MAILCHIMP API FROM OAUTH2
		$siteURL = urlencode(get_site_url().'/wp-admin/admin.php?page=wpmerchant-settings');
		$mailchimpAPIKey = get_option('wpmerchant_mailchimp_api');
		if($mailchimpAPIKey){
			unset($args);
		  	$args = array (
					  'id'	  => $this->plugin_name.'_mailchimp_api_2',
					  'name'	  => $this->plugin_name.'_mailchimp_api_2',
					  'section_id' => $section_id,
			  		  'section_page' => $section_page,
			  		  'field_title'=>__('Connected to MailChimp', $this->plugin_name),
			  		  'callback'=> array( $this, 'wpmerchant_render_mailchimp_connected' )
			        );
			$this->add_and_register_settings_field($args);
		} else {
			unset($args);
		  	$args = array (
					  'id'	  => $this->plugin_name.'_mailchimp_api_2',
					  'name'	  => $this->plugin_name.'_mailchimp_api_2',
					  'section_id' => $section_id,
			  		  'section_page' => $section_page,
			  		  'field_title'=>__('Connect to MailChimp', $this->plugin_name),
			  		  'callback'=> array( $this, 'wpmerchant_render_mailchimp_connect' )
			        );
			$this->add_and_register_settings_field($args);
		}
		unset($args);
	  	$args = array (
	              'type'      => 'input',
				  'subtype'	  => 'hidden',
				  'id'	  => $this->plugin_name.'_mailchimp_api',
				  'name'	  => $this->plugin_name.'_mailchimp_api',
				  'required' => '',
				  'get_data_list' => '',
				  'value_type'=>'normal',
				  'wp_data' => 'option',
				  'section_id' => $section_id,
		  		  'section_page' => $section_page,
		  		  'field_title'=>'',
		  		  'callback'=> array( $this, 'wpmerchant_render_settings_field' )
		        );
		$this->add_and_register_settings_field($args);
		if($mailchimpAPIKey){
			unset($args);
		  	$args = array (
		              'type'      => 'select',
					  'subtype'	  => '',
					  'id'	  => $this->plugin_name.'_mailchimp_gen_list_id',
					  'name'	  => $this->plugin_name.'_mailchimp_gen_list_id',
					  'required' => '',
					  'get_data_list' => 'get_mailchimp_list',
					  'value_type'=>'normal',
					  'wp_data' => 'option',
					  'attr_value' =>true,
				  'section_id' => $section_id,
		  		  'section_page' => $section_page,
		  		  'field_title'=>__('General Interest List', $this->plugin_name),
		  		  'callback'=> array( $this, 'wpmerchant_render_settings_field' )
		        );
		$this->add_and_register_settings_field($args);
		} else {
			unset($args);
		  	$args = array (
		              'type'      => 'select',
					  'subtype'	  => '',
					  'id'	  => $this->plugin_name.'_mailchimp_gen_list_id',
					  'name'	  => $this->plugin_name.'_mailchimp_gen_list_id',
					  'required' => '',
					  'get_data_list' => 'get_mailchimp_list',
					  'value_type'=>'normal',
					  'wp_data' => 'option',
					  'display'=>'none',
					  'attr_value' =>true,
				  'section_id' => $section_id,
		  		  'section_page' => $section_page,
		  		  'field_title'=>'',
		  		  'callback'=> array( $this, 'wpmerchant_render_settings_field' )
		        );
		$this->add_and_register_settings_field($args);
		}
    }
	public function wpmerchant_render_mailchimp_connected(){
		/*echo '<span class="dashicons dashicons-yes" style="color:#7ad03a;"></span>&nbsp;<a id="mailchimp-log-out" style="cursor:pointer;">Log Out?</a>';*/
		echo '<a class="btn mailchimp-connected"><span>| '.__('Connected', $this->plugin_name).'</span></a>&nbsp;&nbsp;<a id="mailchimp-log-out" style="cursor:pointer;">'.__('Disconnect?', $this->plugin_name).'</a>';
	}
	public function wpmerchant_return_mailchimp_connected(){
		/*echo '<span class="dashicons dashicons-yes" style="color:#7ad03a;"></span>&nbsp;<a id="mailchimp-log-out" style="cursor:pointer;">Log Out?</a>';*/
		return '<a class="btn mailchimp-connected"><span>| '.__('Connected', $this->plugin_name).'</span></a>';
	}
	public function wpmerchant_return_gen_list_select(){
		unset($args);
	  	$args = array (
	              'type'      => 'select',
				  'subtype'	  => '',
				  'id'	  => $this->plugin_name.'_mailchimp_gen_list_id',
				  'name'	  => $this->plugin_name.'_mailchimp_gen_list_id',
				  'required' => '',
				  'get_data_list' => 'get_mailchimp_list',
				  'value_type'=>'normal',
				  'wp_data' => 'option',
				  'attr_value' =>true,
				  'return'=>true
	          );
		$mailchimpListSelect = $this->wpmerchant_render_settings_field($args);
		return '<div class="wpm_newsletter_list">'.$mailchimpListSelect.'</div>';
	}
	public function wpmerchant_render_stripe_connected(){
		/*echo '<span class="dashicons dashicons-yes" style="color:#7ad03a;"></span>&nbsp;<a id="stripe-log-out" style="cursor:pointer;">Log Out?</a>';*/
		echo '<a class="btn stripe-connected"><span>| '.__('Connected', $this->plugin_name).'</span></a>&nbsp;&nbsp;<a id="stripe-log-out" style="cursor:pointer;">'.__('Disconnect?', $this->plugin_name).'</a>';
	}
	public function wpmerchant_return_stripe_connected(){
		/*echo '<span class="dashicons dashicons-yes" style="color:#7ad03a;"></span>&nbsp;<a id="stripe-log-out" style="cursor:pointer;">Log Out?</a>';*/
		return '<a class="btn stripe-connected"><span>| '.__('Connected', $this->plugin_name).'</span></a>';
	}
	/**
	Settings Sections Displays
	**/
	/**
	 * This function provides a simple description for the Stripe Payments Options page.
	 * This function is being passed as a parameter in the add_settings_section function.
	 *
	 * @since 1.0.0
	 */
	public function wpmerchant_display_stripe_account() {
	  echo '<p>'.__('Please select whether you would like the Stripe functionality to be set to Live or Test Mode and enter your live/test secret and public api keys. This information connects the WPMerchant plugin with your Stripe account.', $this->plugin_name).'</p> <p><strong>'.__('*IMPORTANT NOTE*', $this->plugin_name).'</strong>'.__('The shortcode won\'t work without correct api keys.', $this->plugin_name).'</p>';
	} 
	/**
	 * This function provides a simple description for the Stripe Payments Options page.
	 * This function is being passed as a parameter in the add_settings_section function.
	 *
	 * @since 1.0.0
	 */
	public function wpmerchant_display_post_checkout() {
	  echo '<p>'.__('After a user successfully purchases a product, you can redirect them to a page that you enter in below OR you can add a short message to display to the user.', $this->plugin_name).'</p>';
	}
	/**
	 * This function provides a simple description for the Stripe Payments Options page.
	 * This function is being passed as a parameter in the add_settings_section function.
	 *
	 * @since 1.0.0
	 */
	public function wpmerchant_display_mailchimp_account() {
	  echo '<p>'.__('If you have a MailChimp account, click the Connect button below.  Otherwise, ', $this->plugin_name).'<a class="mailchimp-sign-up" target="_blank" href="http://login.mailchimp.com/signup">'.__('Sign Up', $this->plugin_name).'</a>.</p><p>'.__('This information will allow us to subscribe anyone who purchases a one off product from your site to your General Interest MailChimp list.', $this->plugin_name).'</p>';
	}
	/**
	 * This function provides a simple description for the Stripe Payments Options page.
	 * This function is being passed as a parameter in the add_settings_section function.
	 *
	 * @since 1.0.0
	 */
	public function wpmerchant_display_general_account() {
	  echo '<p>'.__('These settings apply to all WPMerchant functionality.', $this->plugin_name).'</p>';
	} 
	/**
	ADD ADMIN MENU
	**/
    /**
     * Register the administration menu for this plugin into the WordPress Dashboard menu.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {
      /*
       * Add a settings page for this plugin to the Settings menu.
       */
      /*add_options_page(
        __( 'WPLauncher Stripe Payments Settings', $this->plugin_name ),
        __( 'WPLauncher Stripe Payments', $this->plugin_name ),
        'manage_options',
        $this->plugin_name,
        array( $this, 'display_plugin_admin_page' )
      );*/
	  /**
add_menu_page('My Custom Page', 'My Custom Page', 'manage_options', 'my-top-level-slug');

add_submenu_page( 'my-top-level-slug', 'My Custom Page', 'My Custom Page', 'manage_options', 'my-top-level-slug');

add_submenu_page( 'my-top-level-slug', 'My Custom Submenu Page', 'My Custom Submenu Page', 'manage_options', 'my-secondary-slug');
	  **/
	  //add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
	  add_menu_page( 'WPMerchant', 'WPMerchant', 'administrator', $this->plugin_name, array( $this, 'display_plugin_admin_dashboard' ), plugin_dir_url( __FILE__ ) . 'img/logo2.png', '26.6894' );
      // this call removes the duplicate link at the top of the submenu 
	  	// bc you're giving the parent slug and menu slug the same values
	  add_submenu_page( $this->plugin_name, __('WPMerchant Dashboard', $this->plugin_name), __('Dashboard', $this->plugin_name), 'administrator', $this->plugin_name, array( $this, 'display_plugin_admin_dashboard' ));
	  /* UNCOMMENT FOR ORDER AND SALES
	  add_submenu_page( $this->plugin_name, __('WPMerchant Sales', $this->plugin_name), __('Sales', $this->plugin_name), 'administrator', $this->plugin_name.'-sales', array( $this, 'display_plugin_admin_sales' ));*/
	  add_submenu_page( $this->plugin_name, __('WPMerchant Settings', $this->plugin_name), __('Settings', $this->plugin_name), 'administrator', $this->plugin_name.'-settings', array( $this, 'display_plugin_admin_settings' ));
	  //add_submenu_page( '$parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
  	  
	  /*add_submenu_page( $this->plugin_name, 'Products', 'Products', 'administrator', $this->plugin_name.'-products', array( $this, 'display_plugin_products_page' ));
	  add_submenu_page( $this->plugin_name, 'Plans', 'Plans', 'administrator', $this->plugin_name.'-plans', array( $this, 'display_plugin_plans_page' ));*/
	  
    }
  	/**
  	 * View for AdminSettings Page
  	 *
  	 * @since    1.0.0
  	 */
    public function display_plugin_admin_settings() {
		// set this var to be used in the settings-display view
		$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general';
	    if(isset($_GET['error_message'])){
			// to display an error - add the admin_notices action
			// run do_action to pass an argument to the admin notices callback function
			// in the callback array run add_settings_error
			add_action('admin_notices', array($this,'wpmerchant_settings_messages'));
			do_action( 'admin_notices', $_GET['error_message'] );
	    }
		require_once 'partials/wpmerchant-admin-settings-display.php';
    }
  	/**
  	 * View for Sales Page
  	 *
  	 * @since    1.0.0
  	 */
    public function display_plugin_admin_sales() {
		$chart_js_src = plugin_dir_url( __FILE__ ).'js/plugins/Chart.js';
		require_once 'partials/wpmerchant-admin-sales-display.php';
    }
	/**
  	 * View for Dashboard Page
  	 *
  	 * @since    1.0.0
  	 */
    public function display_plugin_admin_dashboard() {
		// set this var to be used in the settings-display view
		$active_slide = isset( $_GET[ 'slide' ] ) ? $_GET[ 'slide' ] : 'payments';
	    if(isset($_GET['error_message'])){
			// to display an error - add the admin_notices action
			// run do_action to pass an argument to the admin notices callback function
			// in the callback array run add_settings_error
			add_action('admin_notices', array($this,'wpmerchant_settings_messages'));
			do_action( 'admin_notices', $_GET['error_message'] );
	    }
		require_once 'partials/wpmerchant-admin-dashboard-display.php';
    }
	public function create_public_purchase_script(){
		require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/class-wpmerchant-wpdb.php';
		$wordpress_class = new Wpmerchant_Wpdb($this->plugin_name);
		$localizedVars = $this->getPublicLocalizedVariables();
		//plugin_dir_url( dirname(__FILE__) ) . 'public/js/wpmerchant-external-public.js'
		/*$myfile = fopen('wpmerchant-external-public.js', "w") or die("Unable to open file!");*/
		$post_type = 'wpmerchant_product';
		$products = $wordpress_class->getPosts($post_type);
		
		foreach($products AS $p){
			$amt = get_post_meta($p->ID, '_regular_price', true);
			$amt2 = 100*$amt;
			$js_products[] = array('id'=>$p->ID, 'description'=>$p->post_title, 'amount'=>$amt2);
		}
		
		$json_products = json_encode($js_products);
		
		$txt = "(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note that this assume you're going to use jQuery, so it prepares
	 * the $ function reference to be used within the scope of this
	 * function.
	 *
	 * From here, you're able to define handlers for when the DOM is
	 * ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * Or when the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and so on.
	 *
	 * Remember that ideally, we should not attach any more than a single DOM-ready or window-load handler
	 * for any particular page. Though other scripts in WordPress core, other plugins, and other themes may
	 * be doing this, we should try to minimize doing that in our own work.
	 */
	var WPMerchant = {
		construct:function(){
			$(function() {
				if($('.wpMerchantPurchase').length > 0){
					$('body').append('<div class=\"wpm-overlay\"><div id=\"wpm_loading_indicator\" class=\"wpm-loading-indicator\"><img src=\"".$localizedVars['loading_gif']."\" width=\"50\" height=\"50\"></div><div id=\"wpm_message\"><a class=\"wpm-close-link\"><img class=\"wpm-close\" src=\"".$localizedVars['close_btn_img']."\"></a><h1>".$localizedVars['thank_you_msg']."</h1><p><img src=\"".$localizedVars['image']."\" height=\"128px\" width=\"128px\"></p></div></div>');
					$('.wpMerchantPurchase').bind('click', WPMerchant.purchase); 
			    } else {
					var js_products = ".$json_products.";
					console.log(js_products)
					var wpm_namespace = location.href+'#wpmerchant-';
					if($(\"a[href^='\"+wpm_namespace+\"']\").length > 0){
						$(\"a[href^='\"+wpm_namespace+\"']\").each(function(){
							var wpm_href = $(this).attr('href');
							var wpm_ns_length = wpm_namespace.length;
							console.log(wpm_href);
							var wpm_product_id = wpm_href.substr(wpm_ns_length);
							console.log(wpm_product_id);
							for(var i = 0; i < js_products.length; i++) { 
								if(wpm_product_id = js_products[i].id){
									var wpm_product_description = js_products[i].description;
									var wpm_product_amount = js_products[i].amount;
									break;
								}
							}
							
							$(this).addClass('wpMerchantPurchase').attr('data-products',\"[{\\\"quantity\\\":1,\\\"id\\\":\\\"\"+wpm_product_id+\"\\\"}]\").attr('data-description',wpm_product_description).attr('data-amount',wpm_product_amount).attr('data-plans','');
						});
						$('.wpMerchantPurchase').bind('click', WPMerchant.purchase); 
					}
				}
				if($('.wpMerchantModal').length > 0 ){
					$('.wpMerchantModal').bind('click', WPMerchant.appendModal);
				}
		 	 });
		},
		overlayOn:function(type){
			console.log('on')
			switch (type) {
			case 'loading':
			  $('#wpm_loading_indicator').css(\"display\",\"block\").css(\"opacity\",\"1\"); 
			  $('.wpm-overlay').css(\"display\",\"block\");
				break;
			case 'message':
				$('#wpm_message').css(\"display\",\"block\");
				$('.wpm-overlay').css(\"display\",\"block\");
				break;
			case 'stripe':
				$('.wpm_stripe_modal').css(\"display\",\"block\").css(\"opacity\",\"1\");
				$('.wpm-stripe-overlay').css(\"display\",\"block\");
				break;
			default:
				
			}
		},
		overlayOff:function(type){
			console.log('off')
			switch (type) {
				case 'loading':
				  $('#wpm_loading_indicator').css(\"display\",\"none\").css(\"opacity\",\"0\"); 
				  $('.wpm-overlay').css(\"display\",\"none\");
					break;
				case 'message':
					$('#wpm_message').css(\"display\",\"none\");
					$('.wpm-overlay').css(\"display\",\"none\");
					break;
				case 'stripe':
					$('.wpm_stripe_modal').css(\"display\",\"none\").css(\"opacity\",\"0\");
					$('.wpm-stripe-overlay').css(\"display\",\"none\");
					break;
				default:
    				  $('#wpm_loading_indicator').css(\"display\",\"none\").css(\"opacity\",\"0\"); 
    				  $('.wpm-overlay').css(\"display\",\"none\");
					  $('#thank-you-modal').css(\"display\",\"none\");
					$('#wpm_message').css(\"display\",\"none\");
					$('.wpm-overlay').css(\"display\",\"none\");
					$('.wpm_stripe_modal').css(\"display\",\"none\").css(\"opacity\",\"0\");
					$('.wpm-stripe-overlay').css(\"display\",\"none\");
				}
		},
		appendModal: function (event){
			event.preventDefault();
			  var productDescription = $(this).data(\"description\");
			  var title = $(this).data(\"title\");
			  /*this is the content holder*/
		  if($(this).data(\"product\")){
		  	var item_id = $(this).data(\"product\");
			var modal_selector = $('.wpm-stripe-overlay[data-product=\"'+item_id+'\"]');
		  }
		  
		  modal_selector.find('.wpm_stripe_modal').find(\".image\").attr(\"style\",\"background:url('".$localizedVars['icon_border_img']."') no-repeat;\");
		  modal_selector.find('.wpm_stripe_modal').find(\".close\").attr(\"style\",\"background:url('".$localizedVars['close_btn_img']."') no-repeat;\");
			
		  modal_selector.find('.wpm_stripe_modal').find(\".close\").unbind();
		  modal_selector.find('.wpm_stripe_modal').find(\".close\").bind(\"click\",WPMerchant.overlayOff);
		   modal_selector.find('.wpm_stripe_modal').css(\"display\",\"block\").css(\"opacity\",\"1\");
		   modal_selector.css(\"display\",\"block\");
			/*add this so any purchase buttons inside the modal will wokr*/
			if($('.wpMerchantPurchase').length > 0){
				$('.wpMerchantPurchase').unbind();
				$('.wpMerchantPurchase').bind('click', WPMerchant.purchase);
			}
		},
		customModal: function (productName, title, description, content){
			event.preventDefault();
			var socialLinksFB = 'https://www.facebook.com/sharer/sharer.php?u='+encodeURIComponent(location.href);
			var socialLinksGP = 'https://plus.google.com/share?url='+encodeURIComponent(location.href);
			var socialLinksTW = 'https://twitter.com/intent/tweet?text='+encodeURIComponent('I just bought '+productName+' on MettaGroup.org!')+'&url='+encodeURIComponent(location.href);
		    var stripeSocial = \"<a target='_blank' class='share-on-link share-on-twitter' href='\"+socialLinksTW+\"'>Twitter</a>&nbsp;<a target='_blank' class='share-on-link share-on-facebook' href='\"+socialLinksFB+\"'>Facebook</a>&nbsp;<a target='_blank' class='share-on-link share-on-googleplus' href='\"+socialLinksGP+\"'>Google+</a>\";
			if(!title){
				var title = 'Thank you!';
			}
			if(!description){
				var description = 'We\'d love your support.';
			}
			if(!content){
				var content = '<div class=\"stripeReceiptMsg3 wpm_clear\"><p>You have been emailed a receipt for your purchase. Share your purchase of <span class=\"stripeProductDescription\">'+productName+'</span> on your social networks.</p></div><div class=\"wpm_clear stripeSocial\">'+stripeSocial+'</div>';
			}
		  var modal = '<div id=\"thank-you-modal\"><div class=\"wpm_stripe_modal\"><div class=\"sc_header\"><a class=\"close\" style=\"display: block;\"></a><div class=\"image\"><img src=\"".$localizedVars['image']."\"></div><h1 class=\"stripeResponseMessage\">'+title+'</h1><h2 class=\"stripeProductDescription\">'+description+'</h2></div><div class=\"sc_body\">'+content+'</div></div></div>';
		  var modal_selector = $('#thank-you-modal');
		  if(modal_selector.length > 0 ){
			  modal_selector.remove();
		  }
		  $('body').append(modal);
		  $('#thank-you-modal').find('.wpm_stripe_modal').find(\".image\").attr(\"style\",\"background:url('".$localizedVars['icon_border_img']."') no-repeat;\");
		  $('#thank-you-modal').find('.wpm_stripe_modal').find(\".close\").attr(\"style\",\"background:url('".$localizedVars['close_btn_img']."') no-repeat;\");
			
		  $('#thank-you-modal').find('.wpm_stripe_modal').find(\".close\").unbind();
		  $('#thank-you-modal').find('.wpm_stripe_modal').find(\".close\").bind(\"click\",WPMerchant.overlayOff);
		   $('#thank-you-modal').find('.wpm_stripe_modal').css(\"display\",\"block\").css(\"opacity\",\"1\");
		   $('#thank-you-modal').css(\"display\",\"block\");
		   
			/* add this so any purchase buttons inside the modal will wokr*/
			if($('.wpMerchantPurchase').length > 0){
				$('.wpMerchantPurchase').unbind();
				$('.wpMerchantPurchase').bind('click', WPMerchant.purchase);
			}
		},
		purchase: function(event) {
			event.preventDefault();
		  console.log('clickspp');
		  WPMerchant.overlayOn('loading');
		  /*$(\".overlayView2\").css(\"display\",\"none\");*/
		  var receiptMsg1 = '';
		  var receiptMsg2 = '';
		  var companyName = '".$localizedVars['companyName']."';
		  var stripePublicKey = '".$localizedVars['stripe_public_key']."';
		  if($(this).data('products')){
			  var products = JSON.stringify($(this).data('products'));
		  } else {
			  var products = '';
		  }
		  
		  var amount = $(this).data('amount');
		  var description =  $(this).data('description');
		  var currency =  '".$localizedVars['currency']."';
		  
		  var panelLabel = 'Purchase - {{amount}}';
		  
		  var spImage = '".$localizedVars['image']."';
		  var shippingAddress = '".$localizedVars['stripe_shippingAddress']."';
		  var billingAddress = '".$localizedVars['stripe_billingAddress']."';
		  var zipCode = '".$localizedVars['stripe_zipCode']."';
		  console.log(companyName+', '+description+', '+amount+', '+panelLabel+', '+receiptMsg1+', '+receiptMsg2+', '+stripePublicKey+', '+spImage+', '+products+', '+currency);
		  /*display the loader gif*/
		  
		  WPMerchant.stripeHandler(companyName, description, amount, panelLabel, receiptMsg1, receiptMsg2, stripePublicKey, spImage, products,currency,shippingAddress,billingAddress,zipCode);
		},
		stripeHandler: function(companyName, productDescription, amount, panelLabel, receiptMsg1, receiptMsg2, stripePublicKey, spImage,products,currency,shippingAddress,billingAddress,zipCode){ 
			var handler2 = StripeCheckout.configure({
				key: stripePublicKey,
			    image: spImage,
				panelLabel: panelLabel,
				name: companyName,
				currency:currency,
			    description: productDescription,
				shippingAddress: shippingAddress,
				billingAddress: billingAddress,
				zipCode: zipCode,
			    amount: amount,
				opened:function(){  
					/* this runs when the modal is closed*/
					console.log('opened');
					WPMerchant.overlayOff();
				},
				token: function(token, args) {
				  WPMerchant.overlayOn('loading');
			      /* Use the token to create the charge with a server-side script.
			      // You can access the token ID with `token.id`*/
			      console.log(token);
				  console.log(products);
				  /*WPMerchant.loadingModal();*/
				  if(typeof token.card !== 'undefined'){
					  var customer_name = token.card.name;
					  var customer_address_1 = token.card.address_line1;
					  var customer_address_country = token.card.address_country;
					  var customer_address_state = token.card.address_state;
					  var customer_address_city = token.card.address_city;
					  var customer_address_zip = token.card.address_zip;
				  }else {
					  var customer_name = '';
					  var customer_address_1 = '';
					  var customer_address_country = '';
					  var customer_address_state = '';
					  var customer_address_city = '';
					  var customer_address_zip = '';
				  }
				  var dataString = \"token=\" + encodeURIComponent(token.id) + \"&email=\" + encodeURIComponent(token.email) + \"&products=\" + encodeURIComponent(products)+\"&action=wpmerchant_purchase&amount=\"+encodeURIComponent(amount)+\"&security=".$localizedVars['purchaseNonce']."\"+\"&name=\" + encodeURIComponent(customer_name) + \"&address_1=\" + encodeURIComponent(customer_address_1)+\"&country=\" + encodeURIComponent(customer_address_country) + \"&state=\" + encodeURIComponent(customer_address_state) + \"&zip=\" + encodeURIComponent(customer_address_zip);
					$.ajax({
						url: '".$localizedVars['ajax_url']."',  
						type: \"POST\",
						data: dataString,
						dataType:'json',
						success: function(data){
						    if(data.response == 'success'){
		  					  WPMerchant.overlayOff('loading');
						      console.log('success')
								if(data.redirect){
									console.log('redirect exists')
									window.open(data.redirect,'_self');
								} else {
									console.log('no redirect exists')
									/*WPMerchant.overlayOn('message');
									$(\".wpm-close-link\").bind(\"click\",WPMerchant.overlayOff);*/
									WPMerchant.customModal(productDescription,data.thanks_title,data.thanks_desc,data.thanks_content);
								}
								var responseMessage = 'Purchase Complete';
							   var receiptMsg1 = 'We have emailed you a receipt.';
							   var receiptMsg2 = 'Support us by sharing this purchase on your social networks.';
					   		   /*WPMerchant.updateModal(productDescription, responseMessage, receiptMsg1, receiptMsg2);*/
						   } else if (data.response == 'sold_out'){
							   WPMerchant.overlayOff;
   						      console.log('sold_out')
   								/*if(data.redirect){
   									console.log('redirect exists')
   									window.open(data.redirect,'_self');
   								} else {
   									console.log('no redirect exists')
							   		WPMerchant.overlayOn('message');
							   		$(\".wpm-close-link\").bind(\"click\",WPMerchant.overlayOff);
								}*/
						   		$(\"#wpm_message\").find('h1').empty().text('Sold Out!')
								WPMerchant.overlayOn('message');
						   		$(\".wpm-close-link\").bind(\"click\",WPMerchant.overlayOff);
							   
					   	   } else {
							   WPMerchant.overlayOff;
   						      console.log('error')
   								/*if(data.redirect){
   									console.log('redirect exists')
   									window.open(data.redirect,'_self');
   								} else {
   									console.log('no redirect exists')
							   		WPMerchant.overlayOn('message');
							   		$(\".wpm-close-link\").bind(\"click\",WPMerchant.overlayOff);
								}*/
						   		$(\"#wpm_message\").find('h1').empty().text('Purchase Error')
								WPMerchant.overlayOn('message');
						   		$(\".wpm-close-link\").bind(\"click\",WPMerchant.overlayOff);
							   var responseMessage = 'Purchase Error'
							   var receiptMsg1 = 'We\'re sorry! There was an error purchasing this product.  Please contact <a href=\"mailto:jill@example.com\">jill@example.com</a>.';
							   var receiptMsg2 = '';
					   		   /*WPMerchant.updateModal(productDescription, responseMessage, receiptMsg1, receiptMsg2);*/
						   }
						  console.log( data );
						  },
						error: function(jqXHR, textStatus, errorThrown) { 
					      WPMerchant.overlayOff;
						  console.log('error')
							/*if(data.redirect){
								console.log('redirect exists')
								window.open(data.redirect,'_self');
							} else {
								console.log('no redirect exists')
						   		WPMerchant.overlayOn('message');
						   		$(\".wpm-close-link\").bind(\"click\",WPMerchant.overlayOff);
							}*/
					   		$(\"#wpm_message\").find('h1').empty().text('Purchase Error')
							WPMerchant.overlayOn('message');
					   		$(\".wpm-close-link\").bind(\"click\",WPMerchant.overlayOff);
							console.log(jqXHR, textStatus, errorThrown); 
						   var responseMessage = 'Purchase Error'
						   var receiptMsg1 = 'We\'re sorry! There was an error purchasing this product.  Please contact <a href=\"mailto:jill@example.com\">jill@example.com</a>.';
						   var receiptMsg2 = '';
				   		   /*WPMerchant.updateModal(productDescription, responseMessage, receiptMsg1, receiptMsg2);*/
						}
					});
		 	  	 }
		 	 }); 
		 	 handler2.open();
	  	}
	
	}
	WPMerchant.construct();

})( jQuery );";
return '<pre class="wpmerchant"><code>'.htmlentities('<script type="text/javascript" src="https://checkout.stripe.com/checkout.js"></script><script type="text/javascript">'.$txt.'</script>').'</code></pre>';
		/*fwrite($myfile, $txt);
		fclose($myfile);*/
	}
	public function getPublicLocalizedVariables(){
		// Set Nonce Values so that the ajax calls are secure
		$localizedVars['purchaseNonce'] = wp_create_nonce( "wpmerchant_purchase" );
		update_option( 'wpmerchant_purchase_nonce', $localizedVars['purchaseNonce'] ); 
	  	$stripe_status = get_option( $this->plugin_name.'_stripe_status' );
	  	if($stripe_status == 'live'){
	  		$localizedVars['stripe_public_key'] = get_option( $this->plugin_name.'_stripe_live_public_key' );
	  	} else {
	  		$localizedVars['stripe_public_key'] = get_option( $this->plugin_name.'_stripe_test_public_key' );
	  	}
	  	$localizedVars['stripe_shippingAddress'] = get_option( $this->plugin_name.'_stripe_shippingAddress' );
		$localizedVars['stripe_billingAddress'] = get_option( $this->plugin_name.'_stripe_billingAddress' );
		$localizedVars['stripe_zipCode'] = get_option( $this->plugin_name.'_stripe_zipCode' );
		
		if(!$localizedVars['stripe_shippingAddress']){
			$localizedVars['stripe_shippingAddress'] = 'false';
		}
		if(!$localizedVars['stripe_billingAddress']){
			$localizedVars['stripe_billingAddress'] = 'false';
		}
		if(!$localizedVars['stripe_zipCode']){
			$localizedVars['stripe_zipCode'] = 'false';
		}
		// get option for company name
		$localizedVars['companyName'] = get_option( $this->plugin_name.'_company_name' );
		//http://www.jeansuschrist.com/wp-content/uploads/2015/05/stripe-logo.png
		$localizedVars['image'] = get_option( $this->plugin_name.'_stripe_checkout_logo' );
		if(!$localizedVars['image']){
			$localizedVars['image'] = plugin_dir_url( __FILE__ ).'img/marketplace.png';
		}
		$localizedVars['loading_gif'] = plugin_dir_url( __FILE__ ).'img/loader.gif';
		$localizedVars['close_btn_img'] = plugin_dir_url( __FILE__ ).'img/close.png';
		$localizedVars['icon_border_img'] = plugin_dir_url( __FILE__ ).'img/icon-border.png';
	
		$localizedVars['thank_you_msg'] = get_option($this->plugin_name.'_post_checkout_msg');
		if(!$localizedVars['thank_you_msg']){
			$localizedVars['thank_you_msg'] = __('Thank you!', $this->plugin_name);
		}
		$currency2 = get_option( $this->plugin_name.'_currency' );
		$currency1 = Wpmerchant_Admin::get_currency_details($currency2);
		$localizedVars['currency'] = $currency1['value'];
		$localizedVars['ajax_url'] = admin_url( 'admin-ajax.php' );
		return $localizedVars;
	}
	public function dashboard_slide_contents($image_class, $header, $description, $btn){
		if(!$btn && $image_class == 'payments'){
			$stripeLivePublicKey = get_option('wpmerchant_stripe_live_public_key');
			$stripeLiveSecretKey = get_option('wpmerchant_stripe_live_secret_key');
			$stripeTestPublicKey = get_option('wpmerchant_stripe_test_public_key');
			$stripeTestSecretKey = get_option('wpmerchant_stripe_test_secret_key');
			if($stripeLivePublicKey && $stripeLiveSecretKey && $stripeTestPublicKey && $stripeTestSecretKey){
				$description = __('Now that you\'ve linked to a payment processor, complete the', $this->plugin_name).' <a href="/wp-admin/admin.php?page=wpmerchant&slide=newsletters" class="arrow">'.__('next step', $this->plugin_name).'.</a>';
				$btn = $this->wpmerchant_return_stripe_connected();
			} else {
				$btn = $this->wpmerchant_return_stripe_connect();
			}
			
		} elseif(!$btn && $image_class == 'newsletters'){ 
			$mailchimpAPIKey = get_option('wpmerchant_mailchimp_api');
			if($mailchimpAPIKey){
				$description = __('Now that you\'ve linked to a newsletter provider, check out the', $this->plugin_name).' <a href="/wp-admin/admin.php?page=wpmerchant&slide=newsletter-list" class="arrow">'.__('next step', $this->plugin_name).'.</a>';
				$btn = $this->wpmerchant_return_mailchimp_connected();
			} else {
				$btn = $this->wpmerchant_return_mailchimp_connect();
			}
		} elseif(!$btn && $image_class == 'newsletter-list'){ 
			$mailchimpAPIKey = get_option('wpmerchant_mailchimp_api');
			if($mailchimpAPIKey){
				$list_id = get_option('wpmerchant_mailchimp_gen_list_id');
				if($list_id){
					$description = __('Now that you\'ve selected a newsletter list, check out the', $this->plugin_name).' <a href="/wp-admin/admin.php?page=wpmerchant&slide=products" class="arrow">'.__('next step', $this->plugin_name).'.</a>';
				}
			}
			$btn = $this->wpmerchant_return_gen_list_select();
		} elseif(!$btn && $image_class == 'products'){ 
			global $wpdb;
			$products_exist = $wpdb->get_row( 
				"
				SELECT ID 
				FROM $wpdb->posts
				WHERE post_type = 'wpmerchant_product'
				LIMIT 1
				"
			);
			
			if($products_exist ){
				$description = __('Now that you\'ve created a product, check out the', $this->plugin_name).' <a href="/wp-admin/admin.php?page=wpmerchant&slide=product-page" class="arrow">'.__('next step', $this->plugin_name).'.</a>';
				$btn = '<a class="btn wpm_no_decoration wpm-admin-modal-btn">'.__('Create More Products', $this->plugin_name).'</a>';
			} else {
				$image = plugin_dir_url( __FILE__ ).'public/img/plus-sign.png';
				$btn = '<a class="btn wpm_no_decoration  wpm-admin-modal-btn"><img src="'.$image.'" width="8" height="9">'.__('Create your first product', $this->plugin_name).'</a>';
			}
		} elseif(!$btn && $image_class == 'product-page'){
			global $wpdb;
			$wpm_button_exist = $wpdb->get_row( 
				"
				SELECT ID 
				FROM $wpdb->posts
				WHERE post_content LIKE '%[/wpmerchant_button]%'
				LIMIT 1
				"
			);
			
			if($wpm_button_exist ){
				$description = __('Now that you\'ve added a buy button to a page, check out the', $this->plugin_name).' <a href="/wp-admin/admin.php?page=wpmerchant&slide=subscriptions" class="arrow">'.__('next step', $this->plugin_name).'.</a>';
				//$btn = '<a class="btn wpm_no_decoration" href="/wp-admin/edit.php?post_type=page">'.__('Add More Buy Buttons', $this->plugin_name).'</a>';
				$btn = '<a class="btn wpm_no_decoration wpm-admin-modal-btn">'.__('Add More Buy Buttons', $this->plugin_name).'</a>';
			} else {
				$image = plugin_dir_url( __FILE__ ).'public/img/plus-sign.png';
				/*$btn = '<a class="btn wpm_no_decoration" href="https://www.wpmerchant.com">'.__('Add Buy Button', $this->plugin_name).'</a>';*/
				$btn = '<a class="btn wpm_no_decoration wpm-admin-modal-btn">'.__('Add Buy Button', $this->plugin_name).'</a>';
			}
			
		}
	    echo '<div class="no-data-img '.$image_class.'"></div><h2>'.$header.'</h2><p class="slider_description">'.$description.'</p><div class="controls"><p>'.$btn.'</p></div>';
	}
	/**
	* Admin Error Messages
	* @since 1.0.0
	*/
	public function wpmerchant_settings_messages($error_message){
		switch ($error_message) {
			case '1':
				$message = __( 'There was an error connecting to MailChimp. Please try again.  If this persists, shoot us an', $this->plugin_name).' <a href="mailto:ben@wpmerchant.com">'.__('email', $this->plugin_name).'</a>.';
				$err_code = esc_attr( 'mailchimp_settings' );
				$setting_field = 'wpmerchant_stripe_status';
				break;
			case '2':
				$message = __( 'There was an error connecting to Stripe. Please try again.  If this persists, shoot us an', $this->plugin_name).' <a href="mailto:ben@wpmerchant.com">'.__('email', $this->plugin_name).'</a>.';
				$err_code = esc_attr( 'stripe_settings' );
				$setting_field = 'wpmerchant_mailchimp_api';
				break;
		}
		$type = 'error';
		add_settings_error(
	           $setting_field,
	           $err_code,
	           $message,
	           $type
	       );
	}
	/**
	 * Render Mailchimp Connect Button
	 *
	 * @since    1.0.0
	 */
	public function wpmerchant_render_mailchimp_connect(){
  	  $args = $this->wpmerchant_mailchimp_connect_details();
	  $args['ref_url'] = admin_url( 'admin.php?page=wpmerchant-settings&tab=emails' );
	  $href = 'https://www.wpmerchant.com/wp-admin/admin-ajax.php?action=mailchimp_connect_auth&ajax_url='.urlencode($args['ajax_url']).'&ref_url='.urlencode($args['ref_url']).'&action2=wpmerchant_save_email_api&security='.urlencode($args['saveEmailAPINonce']).'&user_id='.urlencode($args['user_ID']);
   	  	echo '<a class="btn mailchimp-login" href="'.$href.'"><span>| '.__('Connect', $this->plugin_name).'</span></a>';
	}
	public function wpmerchant_return_mailchimp_connect(){
    	  $args = $this->wpmerchant_mailchimp_connect_details();
  	  $args['ref_url'] = admin_url( 'admin.php?page=wpmerchant&slide=newsletters' );
  	  $href = 'https://www.wpmerchant.com/wp-admin/admin-ajax.php?action=mailchimp_connect_auth&ajax_url='.urlencode($args['ajax_url']).'&ref_url='.urlencode($args['ref_url']).'&action2=wpmerchant_save_email_api&security='.urlencode($args['saveEmailAPINonce']).'&user_id='.urlencode($args['user_ID']);
     	  	return '<a class="btn mailchimp-login" href="'.$href.'"><span>| '.__('Connect', $this->plugin_name).'</span></a>';
	}
	public function wpmerchant_mailchimp_connect_details(){
  	  $args['saveEmailAPINonce'] = wp_create_nonce( "wpmerchant_save_email_api" );
  	  update_option( 'wpmerchant_save_email_api_nonce', $args['saveEmailAPINonce'] );
  	  $args['user_ID'] = get_current_user_id();
  	  $args['ajax_url'] = admin_url( 'admin-ajax.php' );
	  return $args;
	}
	/**
	 * Render Stripe Connect Button
	 *
	 * @since    1.0.0
	 */
	public function wpmerchant_render_stripe_connect(){
	  $args = $this->wpmerchant_stripe_connect_details();
	  $args['ref_url'] = admin_url( 'admin.php?page=wpmerchant-settings&tab=payment' );
	  $href = 'https://www.wpmerchant.com/wp-admin/admin-ajax.php?action=stripe_connect_auth&ajax_url='.urlencode($args['ajax_url']).'&ref_url='.urlencode($args['ref_url']).'&action2=wpmerchant_save_payment_api&security='.urlencode($args['savePaymentAPINonce']).'&user_id='.urlencode($args['user_ID']);
	  echo '<a class="btn stripe-login" href="'.$href.'"><span>| '.__('Connect', $this->plugin_name).'</span></a>';	 
	}
	public function wpmerchant_return_stripe_connect(){
	  $args = $this->wpmerchant_stripe_connect_details();
	  $args['ref_url'] = admin_url( 'admin.php?page=wpmerchant&slide=payments' );
	  $href = 'https://www.wpmerchant.com/wp-admin/admin-ajax.php?action=stripe_connect_auth&ajax_url='.urlencode($args['ajax_url']).'&ref_url='.urlencode($args['ref_url']).'&action2=wpmerchant_save_payment_api&security='.urlencode($args['savePaymentAPINonce']).'&user_id='.urlencode($args['user_ID']);
	  return '<a class="btn stripe-login" href="'.$href.'"><span>| '.__('Connect', $this->plugin_name).'</span></a>';	 
	}
	public function wpmerchant_stripe_connect_details(){
  	  $args['savePaymentAPINonce'] = wp_create_nonce( "wpmerchant_save_payment_api" );
  	  update_option( 'wpmerchant_save_payment_api_nonce', $args['savePaymentAPINonce'] );
  	  $args['user_ID'] = get_current_user_id();
  	  $args['ajax_url'] = admin_url( 'admin-ajax.php' );
	  return $args;
	}
	/**
	 * Render Settings Fields Inputs/Select Boxes - This streamlines the creation of a setting input or select box field. Pass arguments to this function to create the setting field you would like to create
	 *
	 * @since    1.0.0
	 */
	public function wpmerchant_render_settings_field($args) {
		/* EXAMPLE INPUT
	              'type'      => 'select',
				  'subtype'	  => '',
				  'id'	  => $this->plugin_name.'_currency',
				  'name'	  => $this->plugin_name.'_currency',
				  'required' => 'required="required"',
				  'get_option_list' => {function_name},
					'value_type' = serialized OR normal,
		'wp_data'=>(option or post_meta),
		'post_id' =>
		*/
		if($args['wp_data'] == 'option'){
			$wp_data_value = get_option($args['name']);
		} elseif($args['wp_data'] == 'post_meta'){
			$wp_data_value = (isset($args['post_id'])) ? get_post_meta($args['post_id'], $args['name'], true ) : '';
		} elseif($args['wp_data'] == 'post_field'){
			$wp_data_value = (isset($args['post_id'])) ? get_post_field($args['post_id'], $args['name'], true ) : '';
		} elseif($args['wp_data'] == 'custom'){
			$wp_data_value = $args['custom_value'];
		}
		
		switch ($args['type']) {
			case 'select':
				// get the options list array from the get_data_list array value
				$wp_data_list = $this->$args['get_data_list']($args);
				foreach($wp_data_list AS $o){
					$value = ($args['value_type'] == 'serialized') ? serialize($o) : $o['value'];
					$select_options .= ($value == $wp_data_value) ? '<option selected="selected" value="'.esc_attr($value).'">'.__($o['name'], $this->plugin_name).'</option>' : '<option value="'.esc_attr($value).'">'.__($o['name'], $this->plugin_name).'</option>';
				}
				if(isset($args['disabled'])){
					// hide the actual input bc if it was just a disabled input the informaiton saved in the database would be wrong - bc it would pass empty values and wipe the actual information
					if(!isset($args['return'])){
						echo '<select id="'.$args['id'].'_disabled" disabled name="'.$args['name'].'_disabled">'.$select_options.'</select><input type="hidden" id="'.$args['id'].'" name="'.$args['name'].'" value="' . esc_attr($wp_data_value) . '" />';
					} else {
						return '<select id="'.$args['id'].'_disabled" disabled name="'.$args['name'].'_disabled">'.$select_options.'</select><input type="hidden" id="'.$args['id'].'" name="'.$args['name'].'" value="' . esc_attr($wp_data_value) . '" />';
					}
					
				} else {
					$display = (isset($args['display'])) ? 'style="display:'.$args['display'].';"' : '';
					$attr_value = (isset($args['attr_value'])) ? 'data-value="'.esc_attr($wp_data_value).'"' : '';
					if(!isset($args['return'])){
						echo '<select '.$attr_value.' '.$display.' id="'.$args['id'].'" "'.$args['required'].'" name="'.$args['name'].'">'.$select_options.'</select>';
					} else {
						return '<select '.$attr_value.' '.$display.' id="'.$args['id'].'" "'.$args['required'].'" name="'.$args['name'].'">'.$select_options.'</select>';
					}
					
				}
				
				break;
			case 'input':
				$value = ($args['value_type'] == 'serialized') ? serialize($wp_data_value) : $wp_data_value;
				if($args['subtype'] != 'checkbox'){
					$prependStart = (isset($args['prepend_value'])) ? '<div class="input-prepend"> <span class="add-on">'.$args['prepend_value'].'</span>' : '';
					$prependEnd = (isset($args['prepend_value'])) ? '</div>' : '';
					$step = (isset($args['step'])) ? 'step="'.$args['step'].'"' : '';
					$min = (isset($args['min'])) ? 'min="'.$args['min'].'"' : '';
					$max = (isset($args['max'])) ? 'max="'.$args['max'].'"' : '';
					if(isset($args['disabled'])){
						// hide the actual input bc if it was just a disabled input the informaiton saved in the database would be wrong - bc it would pass empty values and wipe the actual information
						if(!isset($args['return'])){
							echo $prependStart.'<input type="'.$args['subtype'].'" id="'.$args['id'].'_disabled" '.$step.' '.$max.' '.$min.' name="'.$args['name'].'_disabled" size="40" disabled value="' . esc_attr($value) . '" /><input type="hidden" id="'.$args['id'].'" '.$step.' '.$max.' '.$min.' name="'.$args['name'].'" size="40" value="' . esc_attr($value) . '" />'.$prependEnd;
						} else {
							return $prependStart.'<input type="'.$args['subtype'].'" id="'.$args['id'].'_disabled" '.$step.' '.$max.' '.$min.' name="'.$args['name'].'_disabled" size="40" disabled value="' . esc_attr($value) . '" /><input type="hidden" id="'.$args['id'].'" '.$step.' '.$max.' '.$min.' name="'.$args['name'].'" size="40" value="' . esc_attr($value) . '" />'.$prependEnd;
						}
					} else {
						if(!isset($args['return'])){
							echo $prependStart.'<input type="'.$args['subtype'].'" id="'.$args['id'].'" "'.$args['required'].'" '.$step.' '.$max.' '.$min.' name="'.$args['name'].'" size="40" value="' . esc_attr($value) . '" />'.$prependEnd;
						} else {
							return $prependStart.'<input type="'.$args['subtype'].'" id="'.$args['id'].'" "'.$args['required'].'" '.$step.' '.$max.' '.$min.' name="'.$args['name'].'" size="40" value="' . esc_attr($value) . '" />'.$prependEnd;
						}
					}
				} else {
					$checked = ($value) ? 'checked' : '';
					if(!isset($args['return'])){
						echo '<input type="'.$args['subtype'].'" id="'.$args['id'].'" "'.$args['required'].'" name="'.$args['name'].'" size="40" value="1" '.$checked.' />';
					} else {
						return '<input type="'.$args['subtype'].'" id="'.$args['id'].'" "'.$args['required'].'" name="'.$args['name'].'" size="40" value="1" '.$checked.' />';
					}
				}
				break;
			case 'wysiwyg':
				$value = $wp_data_value;
				$media_buttons = (isset($args['media_buttons']) && $args['media_buttons'] == 'false') ? false : true;
				$wpautop = (isset($args['wpautop']) && $args['wpautop'] == 'false') ? false : true;
				$arguments = array(
				'textarea_name' => $args['name'],
				'media_buttons'=>$media_buttons,
				'wpautop'=>$wpautop
				); 
				//make sure hte id is only lowercase letters and underscores - NO hyphens
				wp_editor( $value, $args['id'],$arguments); 
				break;
			default:
				# code...
				break;
		}
	}
	/**
	 * Render a Shortcode Form - used in the tinymce plugin
	 *
	 * @since    1.0.0
	 */
	public function shortcode_form($post_type){
		$label_start = '<ul><li><label for="wpmerchant_product">'.__( 'Select Product', $this->plugin_name).'</label>';
		$label_end = '</li>';
		unset($args);
  	  	$args = array (
  	              'type'      => 'select',
  				  'subtype'	  => '',
  				  'id'	  => 'wpmerchant_product',
  				  'name'	  => 'wpmerchant_product',
  				  'required' => 'required="required"',
  				  'get_data_list' => 'return_products_list',
  				  'value_type'=>'normal',
  				  'wp_data' => 'custom',
				  'return'=>true
  	          );
		$product_list = $label_start.$this->wpmerchant_render_settings_field($args).$label_end;
		$label_start = '<li><label for="wpmerchant_button_text">'.__( 'Button Text', $this->plugin_name).'</label>';
		$label_end = '</li>';
		unset($args);
	  	$args = array (
	              'type'      => 'input',
				  'subtype'	  => 'text',
				  'id'	  => 'wpmerchant_button_text',
				  'name'	  => 'wpmerchant_button_text',
				  'required' => '',
				  'value_type'=>'normal',
				  'wp_data' => 'custom',
				  'return'=>true
	          );
		$wpmerchant_button = $label_start.$this->wpmerchant_render_settings_field($args).$label_end;
		$label_start = '<li><label for="wpmerchant_add_product">'.__( 'Add New Product?', $this->plugin_name).'</label>';
		$label_end = '</li>';
		$add_product = $label_start.'<br><a class="button wpm-admin-button wpm-add-new-product">Yes</a>'.$label_end;
		$ul_end = '</ul>';
		// Create Post form
			/*$label_start = '<div class="wpm_well wpmerchant_create_post_form" style="display:none;"><h1>Add Product</h1>';
			$create_post_form = $this->create_post_form('wpmerchant_product');
			$create_post_end = '</div>';*/
		//$create_post_fields = $label_start.$create_post_form.$label_end.$create_post_end;
		$form_fields = $product_list.$wpmerchant_button.$add_product;
		return $form_fields;
	}
	/**
	 * Render a Create Post Form - used in the dashboard among ohter places
	 *
	 * @since    1.0.0
	 */
	public function create_post_form($post_type){
		/**
		  'post_content'   => '', // The full text of the post.
		  'post_name'      => sanitize_text_field($_POST['post_name']), // The name (slug) for your post
		  'post_title'     => sanitize_text_field($_POST['post_title']), // The title of your post.
		  'post_status'    => 'publish',//[ 'draft' | 'publish' | 'pending'| 'future' | 'private' | custom registered status ] // Default 'draft'.
		  'post_type'      => 'wpmerchant_product',//[ 'post' | 'page' | 'link' | 'nav_menu_item' | custom post type ] // Default 'post'.
		  'post_author'    => $user_id, // The user ID number of the author. Default is the current user ID.
		  'ping_status'    => 'closed', // Pingbacks or trackbacks allowed. Default is the option 'default_ping_status'.
		  'post_excerpt'   => '', // For all your post excerpt needs.
		  'post_date'      => date('Y-m-d H:i:s'), // The time post was made.
		  'post_date_gmt'  => date('Y-m-d H:i:s') // The time post was made, in GMT.
		Make sure hte submit button used references the create_post_form name
		**/
		$label_start = '<ul><li><label for="post_title">'.__( 'Name', $this->plugin_name).'</label>';
		$label_end = '</li>';
		unset($args);
	  	$args = array (
	              'type'      => 'input',
				  'subtype'	  => 'text',
				  'id'	  => 'post_title',
				  'name'	  => 'post_title',
				  'required' => 'required="required"',
				  'value_type'=>'normal',
				  'wp_data' => 'post_field',
				  'return'=>true
	          );
		$post_title_field = $label_start.$this->wpmerchant_render_settings_field($args).$label_end;
		$label_start = '<li><label for="_regular_price">'.__( 'Price', $this->plugin_name).'</label>';
		$label_end = '</li>';
		require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/class-wpmerchant-helper.php';
		$helper_class = new Wpmerchant_Helper($this->plugin_name);
		$currency_symbol = $helper_class->get_currency_symbol();
		unset($args);	  	
  	  	$args = array (
  	              'type'      => 'input',
  				  'subtype'	  => 'number',
  				  'id'	  => '_regular_price',
  				  'name'	  => '_regular_price',
  				  'required' => 'required="required"',
  				  'get_data_list' => '',
  				  'value_type'=>'normal',
  				  'wp_data' => 'post_meta',
  				  'min'=> '0',
  				  'step'=> 'any',
  				  'prepend_value'=>$currency_symbol,
				  'return'=>true
  	          );
		$regular_price_field = $label_start.$this->wpmerchant_render_settings_field($args).$label_end;
		
		$label_start = '<li><label for="_stock_status">'.__( 'Stock Status', $this->plugin_name).'</label>';
		$label_end = '</li>';
		unset($args);
  	  	$args = array (
  	              'type'      => 'select',
  				  'subtype'	  => '',
  				  'id'	  => '_stock_status',
  				  'name'	  => '_stock_status',
  				  'required' => '',
  				  'get_data_list' => 'get_stock_status_list',
  				  'value_type'=>'normal',
  				  'wp_data' => 'post_meta',
				  'return'=>true
  	          );
		$stock_status = $label_start.$this->wpmerchant_render_settings_field($args).$label_end;
		unset($args);
	  	$args = array (
	              'type'      => 'input',
				  'subtype'	  => 'hidden',
				  'id'	  => 'post_type',
				  'name'	  => 'post_type',
				  'required' => 'required="required"',
				  'value_type'=>'normal',
				  'wp_data' => 'custom',
				  'custom_value' => 'wpmerchant_product',
				  'return'=>true
	          );
		$post_type = $this->wpmerchant_render_settings_field($args);
		$ul_end = '</ul>';
		$form_fields = $post_title_field.$regular_price_field.$stock_status.$post_type.$ul_end;
		return $form_fields;
	}
	/**
	 * Edit Page Form - used in the dashboard among ohter places
	 *
	 * @since    1.0.0
	 */
	public function update_post_form($post_type){
		// use output buffering so that we can use wp_editor by default (it echos the value by default)
		ob_start();
		$label_start = '<ol><li><label for="wpmerchant_post_id">'.__( 'Select a Page', $this->plugin_name).'</label>';
		$label_end = '</li>';
		unset($args);
  	  	$args = array (
  	              'type'      => 'select',
  				  'subtype'	  => '',
  				  'id'	  => $this->plugin_name.'_post_id',
  				  'name'	  => $this->plugin_name.'_post_id',
  				  'required' => '',
  				  'get_data_list' => 'get_page_list',
  				  'value_type'=>'normal',
  				  'wp_data' => 'custom',
				  'custom_value' => '',
				  'return'=>true
  	          );
		echo $label_start.$this->wpmerchant_render_settings_field($args).$label_end;
		/*$label_start = '<li id="wpmerchant_instructions" style="display:none;"><label for="wpmerchant_instructions">'.__( 'Click WPMerchant in the Editor', $this->plugin_name).'</label>';
		$label_end = '</li>';
		echo $label_start.'<br>In order to add a WPMerchant Buy Button, click WPMerchant in the editor toolbar below. Then, Select a Product, Add Button Text, and Click the Add button.'.$label_end;*/
		$label_start = '<li id="wpmerchant_post_content_li" style="display:none;"><label for="wpmerchant_post_content">'.__( 'Click WPMerchant in the Editor', $this->plugin_name).'</label>';
		$label_end = '</li>';
		$ol_end = '</ol>';
		unset($args);
	  	$args = array (
	              'type'      => 'wysiwyg',
				  'subtype'	  => '',
				  'id'	  => $this->plugin_name.'_post_content',
				  'name'	  => $this->plugin_name.'_post_content',
				  'required' => '',
				  'value_type'=>'normal',
				  'wp_data' => 'custom',
				  'custom_value'=>'',
				  'media_buttons'=>'false',
				  'wpautop'=>'false',
		  		  'callback'=> array( $this, 'wpmerchant_render_settings_field' )
		        );
		echo $label_start.'<p>In order to add a WPMerchant Buy Button, place the cursor where you want the Buy button and click WPMerchant in the editor toolbar below. A modal should pop up where you need to Select a Product, Add Button Text, and Click the Add button.</p><p>Important Note* You can also add products right from this modal by clicking Yes under "Add New Product?".</p>';
		$this->wpmerchant_render_settings_field($args);
		echo $label_end;
		echo $ol_end;
		ob_end_flush();
		/*$form_fields = $page_id_field.$page_content_field;
		return $form_fields;*/
	}
	/**
	 * Prdouct Settings Form - used in the dashboard among ohter places
	 *
	 * @since    1.0.0
	 */
	public function product_settings_form($post_type){
		// use output buffering so that we can use wp_editor by default (it echos the value by default)
		ob_start();
		$label_start = '<ol><li><label for="wpmerchant_post_id">'.__( 'Select a Product', $this->plugin_name).'</label>';
		$label_end = '</li>';
		unset($args);
  	  	$args = array (
  	              'type'      => 'select',
  				  'subtype'	  => '',
  				  'id'	  => $this->plugin_name.'_post_id',
  				  'name'	  => $this->plugin_name.'_post_id',
  				  'required' => '',
  				  'get_data_list' => 'get_product_list',
  				  'value_type'=>'normal',
  				  'wp_data' => 'custom',
				  'custom_value' => '',
				  'return'=>true
  	          );
		echo $label_start.$this->wpmerchant_render_settings_field($args).$label_end;
		$label_start = '<li class="wpm-next-steps"><label for="wpmerchant_edit_button">'.__( 'Click the Button below to Edit Page/Post', $this->plugin_name).'</label>';
		$label_end = '</li>';
		echo $label_start.'<p><a target="_blank" class="btn wpm-edit-post-link">Edit Page/Post</a></p>'.$label_end;
		$ol_end = '</ol>';
		echo $ol_end;
		ob_end_flush();
		/*$form_fields = $page_id_field.$page_content_field;
		return $form_fields;*/
	}
	
	/**
	 * Render Post Meta Tab
	 *
	 * @since    1.0.0
	 */
	public function getPostMetaTab($args){
		return '<li class="'.$args['class'].'">
			<a title="'.$args['link_title'].'" data-href="'.$args['link_href'].'">
				<div>
					<span class="dashicons '.$args['dashicon'].'"></span><span class="wpmTabText">'.$args['tab_text'].'</span>
				</div>
			</a>
		</li>';
	}
	
	
	/**
	Settings Field Validation Functions
	**/
	/**
	 * Sanitization callback for the email option.
	 * Use is_email for Sanitization
	 *
	 * @param  $input  The email user inputed
	 *
	 * @return         The sanitized email.
	 *
	 * @since 1.0.0
	 */
	public function wpmerchant_validate_input_email ( $input ) {
	  // Get old value from DB
	  $sp_stripe_email = get_option( $this->plugin_name.'_stripe_email' );

	  // Don't trust users
	  $input = sanitize_email( $input );

	  if ( is_email( $input ) || !empty( $input ) ) {
	      $output = $input;
	  }
	  else
	    add_settings_error( $this->plugin_name.'_stripe_account_section', 'invalid-email', __( 'You have entered an invalid email.', $this->plugin_name ) );

	  return $output;

	  } 
  	/**
  	 * Sanitization callback for the status option.
  	 *
  	 * @param  $input  The status user inputed
  	 *
  	 * @return         The sanitized status.
  	 *
  	 * @since 1.0.0
  	 */
  	public function wpmerchant_validate_status ( $input ) {
  	  // Get old value from DB
  	  $sp_stripe_status = get_option( $this->plugin_name.'_stripe_status' );
	  
  	  if (!empty( $input ) && ($input == 'test' || $input == 'live')) {
  	      $output = $input;
  	  }
  	  else
  	    add_settings_error( $this->plugin_name.'_stripe_account_section', 'invalid-status', __( 'You have entered an invalid status.', $this->plugin_name) );

  	  return $output;

  	  } 

	/**
	 * Sanitization callback for the api key option.
	 *
	 * @param  $input  The api key user inputed
	 *
	 * @return         The sanitized api key.
	 *
	 * @since 1.0.0
	 */
	public function wpmerchant_validate_public_api_key( $input ) {
	  // Get old value
	  	//$output = get_option( 'wpl_stripe_payments_stripe_api_key');

	  // Don't trust users
	  // Strip all HTML and PHP tags and properly handle quoted strings
	  // Leave a-z, A-Z, 0-9 only
	  /*$input = preg_replace('/[^a-zA-Z0-9]/', '' , strip_tags( stripslashes( $input ) ) );*/
	  if( !empty( $input ) ) {
	    $output = $input;
	  } else {
	    add_settings_error( $this->plugin_name.'_stripe_live_public_key', 'invalid-api-key', __( 'Make sure your Stripe Live Public Key is not empty.', $this->plugin_name ) );
	  }
	  return $output;
	} 
	/**
	 * Sanitization callback for the api key option.
	 *
	 * @param  $input  The api key user inputed
	 *
	 * @return         The sanitized api key.
	 *
	 * @since 1.0.0
	 */
	public function wpmerchant_validate_secret_api_key( $input ) {
	  // Get old value
	  	//$output = get_option( 'wpl_stripe_payments_stripe_api_key');

	  // Don't trust users
	  // Strip all HTML and PHP tags and properly handle quoted strings
	  // Leave a-z, A-Z, 0-9 only
	  /*$input = preg_replace('/[^a-zA-Z0-9]/', '' , strip_tags( stripslashes( $input ) ) );*/
	  if( !empty( $input )) {
	    $output = $input;
	  } else {
	    add_settings_error( $this->plugin_name.'_stripe_live_secret_key', 'invalid-api-key', __( 'Make sure you Connect to Stripe.', $this->plugin_name ) );
	  }
	  return $output;
	} 
	/**
	 * Sanitization callback for the api key option.
	 *
	 * @param  $input  The api key user inputed
	 *
	 * @return         The sanitized api key.
	 *
	 * @since 1.0.0
	 */
	public function wpmerchant_validate_test_public_api_key( $input ) {
	  // Get old value
	  	//$output = get_option( 'wpl_stripe_payments_stripe_api_key');

	  // Don't trust users
	  // Strip all HTML and PHP tags and properly handle quoted strings
	  // Leave a-z, A-Z, 0-9 only
	  /*$input = preg_replace('/[^a-zA-Z0-9]/', '' , strip_tags( stripslashes( $input ) ) );*/
	  if( !empty( $input ) ) {
	    $output = $input;
	  } else {
	    add_settings_error( $this->plugin_name.'_stripe_test_public_key', 'invalid-api-key', __( 'Make sure your Stripe Test Public Key is not empty.', $this->plugin_name ) );
	  }
	  return $output;
	} 
	/**
	 * Sanitization callback for the api key option.
	 *
	 * @param  $input  The api key user inputed
	 *
	 * @return         The sanitized api key.
	 *
	 * @since 1.0.0
	 */
	public function wpmerchant_validate_test_secret_api_key( $input ) {
	  // Get old value
	  	//$output = get_option( 'wpl_stripe_payments_stripe_api_key');

	  // Don't trust users
	  // Strip all HTML and PHP tags and properly handle quoted strings
	  // Leave a-z, A-Z, 0-9 only
	  /*$input = preg_replace('/[^a-zA-Z0-9]/', '' , strip_tags( stripslashes( $input ) ) );*/
  	  //$stripeAuthentication = Wpmerchant_Public::stripeFunction('setApiKey',$input);
	  if( !empty( $input )) {
	    $output = $input;
	  } else {
	    add_settings_error( $this->plugin_name.'_stripe_test_secret_key', 'invalid-api-key', __( 'Make sure your Stripe Test Secret Key is not empty.', $this->plugin_name ) );
	  }
	  return $output;
	} 
	public function wpmerchant_plugin_row_links($links, $file){
		if ( strpos( $file, plugin_basename(WPMERCHANT_FILE) ) !== false ) {
			$links[] = '<a href="https://wordpress.org/support/plugin/wpmerchant" target="_blank">' . __( 'Support', $this->plugin_name) . '</a>';
			$links[] = '<a href="https://wordpress.org/plugins/wpmerchant/faq/" target="_blank">' . __( 'FAQ', $this->plugin_name) . '</a>';
			$links[] = '<a href="https://www.wpmerchant.com/" target="_blank">' . __( 'Sell Subscriptions', $this->plugin_name) . '</a>';
		}
		return $links;
	}
	public function wpmerchant_add_action_links( $links ) {
		array_unshift(
			$links,
			'<a href="'. get_admin_url( null, '/admin.php?page=wpmerchant-settings').'">' . __( 'Settings', $this->plugin_name) . '</a>'
		);
		array_unshift(
			$links,
			'<a href="'. get_admin_url( null, '/admin.php?page=wpmerchant').'">' . __( 'Configure', $this->plugin_name) . '</a>'
		);
		return $links;
	}
	
	/**
	GET SETTINGS FIELD SELECT OPTION LISTS
	**/
	public function get_order_status(){
    	  $orderStatusList[] = array('value'=> '', 'name' => 'Select Order Status');
		  $orderStatusList[] = array('value'=> 'pending-payment', 'name' => 'Pending Payment');
		  $orderStatusList[] = array('value'=> 'processing', 'name' => 'Processing');
		  $orderStatusList[] = array('value'=> 'on-hold', 'name' => 'On Hold');
		  $orderStatusList[] = array('value'=> 'completed', 'name' => 'Completed');
		  $orderStatusList[] = array('value'=> 'cancelled', 'name' => 'Cancelled');
		  $orderStatusList[] = array('value'=> 'refunded', 'name' => 'Refunded');
		  $orderStatusList[] = array('value'=> 'failed', 'name' => 'Failed');
  	  return $orderStatusList;
	}
	/**
	* Get Payment Processor List for the Payment Processor Settings function
	* @since 1.0.2
	*/
	public function get_payment_processor_list(){
  	  $paymentProcessorList = array(		
  						  0 => array('value'=> 'stripe', 'name' => 'Stripe')
  					);
	  return $paymentProcessorList;
	}
	/**
	* Get Email List Processor List for the Email Processor Settings function
	* @since 1.0.2
	*/
	public function get_email_list_processor_list(){
		$emailListProcessorList = array(		
							  0 => array('value'=> 'mailchimp', 'name' => 'MailChimp')
						);
	  return $emailListProcessorList;
	}
	public function get_order_actions_completed_list($order_status){	
  	  $orderActionsList[] = array('value'=> '', 'name' => 'Select Action');
  	  $orderActionsList[] = array('value'=> 'refund', 'name' => 'Refund');
	  return $orderActionsList;
	}
	public function get_order_actions_list($order_status){
    	  $orderActionsList[] = array('value'=> '', 'name' => 'Select Action');
    	  $orderActionsList[] = array('value'=> 'remove', 'name' => 'Remove');
  	  return $orderActionsList;
	}
	/**
	* Get Stripe Config Listing for the STripeStatus Settings function
	* @since 1.0.2
	*/
	public function get_stripe_config_list(){
		$stripeConfigList = array(		
							  0 => array('value'=> 'false', 'name' => 'No'),
							  1 => array('value'=> 'true', 'name' => 'Yes'),	  
						);
	  return $stripeConfigList;
	}
	/**
	* Get Stripe Status List for the STripeStatus Settings function
	* @since 1.0.2
	*/
	public function get_stripe_status_list(){
		$stripeStatusList = array(		
							  0 => array('value'=> 'test', 'name' => 'Test'),
							  1 => array('value'=> 'live', 'name' => 'Live'),	  
						);
	  return $stripeStatusList;
	}
	/**
	* Get STock Status List for the Post Meta Product function
	* @since 1.0.2
	*/
	public function get_stock_status_list(){
		$stockStatusList = array(		
							  0 => array('value'=> '1', 'name' => 'In Stock'),	  
							  1 => array('value'=> '0', 'name' => 'Out of Stock'),
						);
	  return $stockStatusList;
	}
	public function get_customers(){
		require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/class-wpmerchant-wpdb.php';
		$wordpress_class = new Wpmerchant_Wpdb($this->plugin_name);
		$wpmCustomers = $wordpress_class->getWPMCustomers();
		$customer_list[] = array('value'=> '', 'name' => 'Select a Customer');
		foreach($wpmCustomers AS $c){
			if($c->name && $c->email){
				$name = $c->name.'('.$c->email.')';
			} else {
				$name = $c->email;
			}
			$customer_list[] = array('value'=> $c->ID, 'name' => $name);
		}
		return $customer_list;
	}
	/**
	* Get Mailchimp lists function
	* @since 1.0.2
	*/
	public function get_mailchimp_list($args){
		//$args['api_key']
		$mailchimpLists = array(		
							  0 => array('value'=> '', 'name' => '&nbsp;')
						);
		return $mailchimpLists;
		
	}
	/**
	* Get Page lists function
	* @since 1.0.2
	*/
	public function get_page_list($args){
		// this manages all wordpress database interactions
		require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/class-wpmerchant-wpdb.php';
		$wordpress_class = new Wpmerchant_Wpdb($this->plugin_name);
		$pages = $wordpress_class->getPosts('page');
		//$args['api_key']
		$page_list[] = array('value'=> '', 'name' => 'Select a Page');
		foreach($pages AS $p){
			$page_list[] = array('value'=> $p->ID, 'name' => $p->post_title);
		}
		return $page_list;
		
	}
	public function get_product_list(){
		require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/class-wpmerchant-wpdb.php';
		$wordpress_class = new Wpmerchant_Wpdb($this->plugin_name);
		$products = $wordpress_class->getPosts('wpmerchant_product');
		//$args['api_key']
		$product_list[] = array('value'=> '', 'name' => 'Select a Product');
		if($products){
			foreach($products AS $p){
				$product_list[] = array('value'=> $p->ID, 'name' => $p->post_title);
			}
		}
		return $product_list;
		
	}
	/**
	* Get STock Status List for the Post Meta Product function
	* @since 1.0.2
	*/
	public function get_interval_list(){
		$intervalList = array(		
							  0 => array('value'=> 'day', 'name' => 'Day(s)'),
							  1 => array('value'=> 'week', 'name' => 'Week(s)'),	  
							  2 => array('value'=> 'month', 'name' => 'Month(s)'),	  
							  3 => array('value'=> 'year', 'name' => 'Year'),	  
						);
	  return $intervalList;
	}
	/**
	* Currency List
	* @since 1.0.2
	*/
	public function get_currency_details($currency){
		$currency_list = Wpmerchant_Admin::get_currency_list();
		foreach($currency_list AS $c){
			if($c['value'] == $currency){
				return $c;
				break;
			}
		}
	}
	public function get_country_list(){
		$countries[] = array('value'=> 'USA', 'name'=>'United States');
		$countries[] = array('value'=> 'ABW', 'name'=>'Aruba');
		$countries[] = array('value'=> 'AFG', 'name'=>'Afghanistan');
		$countries[] = array('value'=> 'AGO', 'name'=>'Angola');
		$countries[] = array('value'=> 'AIA', 'name'=>'Anguilla');
		$countries[] = array('value'=> 'ALA', 'name'=>'Åland Islands');
		$countries[] = array('value'=> 'ALB', 'name'=>'Albania');
		$countries[] = array('value'=> 'AND', 'name'=>'Andorra');
		$countries[] = array('value'=> 'ARE', 'name'=>'United Arab Emirates');
		$countries[] = array('value'=> 'ARG', 'name'=>'Argentina');
		$countries[] = array('value'=> 'ARM', 'name'=>'Armenia');
		$countries[] = array('value'=> 'ASM', 'name'=>'American Samoa');
		$countries[] = array('value'=> 'ATA', 'name'=>'Antarctica');
		$countries[] = array('value'=> 'ATF', 'name'=>'French Southern Territories');
		$countries[] = array('value'=> 'ATG', 'name'=>'Antigua and Barbuda');
		$countries[] = array('value'=> 'AUS', 'name'=>'Australia');
		$countries[] = array('value'=> 'AUT', 'name'=>'Austria');
		$countries[] = array('value'=> 'AZE', 'name'=>'Azerbaijan');
		$countries[] = array('value'=> 'BDI', 'name'=>'Burundi');
		$countries[] = array('value'=> 'BEL', 'name'=>'Belgium');
		$countries[] = array('value'=> 'BEN', 'name'=>'Benin');
		$countries[] = array('value'=> 'BES', 'name'=>'Bonaire, Sint Eustatius and Saba');
		$countries[] = array('value'=> 'BFA', 'name'=>'Burkina Faso');
		$countries[] = array('value'=> 'BGD', 'name'=>'Bangladesh');
		$countries[] = array('value'=> 'BGR', 'name'=>'Bulgaria');
		$countries[] = array('value'=> 'BHR', 'name'=>'Bahrain');
		$countries[] = array('value'=> 'BHS', 'name'=>'Bahamas');
		$countries[] = array('value'=> 'BIH', 'name'=>'Bosnia and Herzegovina');
		$countries[] = array('value'=> 'BLM', 'name'=>'Saint Barthélemy');
		$countries[] = array('value'=> 'BLR', 'name'=>'Belarus');
		$countries[] = array('value'=> 'BLZ', 'name'=>'Belize');
		$countries[] = array('value'=> 'BMU', 'name'=>'Bermuda');
		$countries[] = array('value'=> 'BOL', 'name'=>'Bolivia, Plurinational State of');
		$countries[] = array('value'=> 'BRA', 'name'=>'Brazil');
		$countries[] = array('value'=> 'BRB', 'name'=>'Barbados');
		$countries[] = array('value'=> 'BRN', 'name'=>'Brunei Darussalam');
		$countries[] = array('value'=> 'BTN', 'name'=>'Bhutan');
		$countries[] = array('value'=> 'BVT', 'name'=>'Bouvet Island');
		$countries[] = array('value'=> 'BWA', 'name'=>'Botswana');
		$countries[] = array('value'=> 'CAF', 'name'=>'Central African Republic');
		$countries[] = array('value'=> 'CAN', 'name'=>'Canada');
		$countries[] = array('value'=> 'CCK', 'name'=>'Cocos (Keeling) Islands');
		$countries[] = array('value'=> 'CHE', 'name'=>'Switzerland');
		$countries[] = array('value'=> 'CHL', 'name'=>'Chile');
		$countries[] = array('value'=> 'CHN', 'name'=>'China');
		$countries[] = array('value'=> 'CIV', 'name'=>'Côte d\'Ivoire');
		$countries[] = array('value'=> 'CMR', 'name'=>'Cameroon');
		$countries[] = array('value'=> 'COD', 'name'=>'Congo, the Democratic Republic of the');
		$countries[] = array('value'=> 'COG', 'name'=>'Congo');
		$countries[] = array('value'=> 'COK', 'name'=>'Cook Islands');
		$countries[] = array('value'=> 'COL', 'name'=>'Colombia');
		$countries[] = array('value'=> 'COM', 'name'=>'Comoros');
		$countries[] = array('value'=> 'CPV', 'name'=>'Cabo Verde');
		$countries[] = array('value'=> 'CRI', 'name'=>'Costa Rica');
		$countries[] = array('value'=> 'CUB', 'name'=>'Cuba');
		$countries[] = array('value'=> 'CUW', 'name'=>'Curaçao');
		$countries[] = array('value'=> 'CXR', 'name'=>'Christmas Island');
		$countries[] = array('value'=> 'CYM', 'name'=>'Cayman Islands');
		$countries[] = array('value'=> 'CYP', 'name'=>'Cyprus');
		$countries[] = array('value'=> 'CZE', 'name'=>'Czech Republic');
		$countries[] = array('value'=> 'DEU', 'name'=>'Germany');
		$countries[] = array('value'=> 'DJI', 'name'=>'Djibouti');
		$countries[] = array('value'=> 'DMA', 'name'=>'Dominica');
		$countries[] = array('value'=> 'DNK', 'name'=>'Denmark');
		$countries[] = array('value'=> 'DOM', 'name'=>'Dominican Republic');
		$countries[] = array('value'=> 'DZA', 'name'=>'Algeria');
		$countries[] = array('value'=> 'ECU', 'name'=>'Ecuador');
		$countries[] = array('value'=> 'EGY', 'name'=>'Egypt');
		$countries[] = array('value'=> 'ERI', 'name'=>'Eritrea');
		$countries[] = array('value'=> 'ESH', 'name'=>'Western Sahara');
		$countries[] = array('value'=> 'ESP', 'name'=>'Spain');
		$countries[] = array('value'=> 'EST', 'name'=>'Estonia');
		$countries[] = array('value'=> 'ETH', 'name'=>'Ethiopia');
		$countries[] = array('value'=> 'FIN', 'name'=>'Finland');
		$countries[] = array('value'=> 'FJI', 'name'=>'Fiji');
		$countries[] = array('value'=> 'FLK', 'name'=>'Falkland Islands (Malvinas)');
		$countries[] = array('value'=> 'FRA', 'name'=>'France');
		$countries[] = array('value'=> 'FRO', 'name'=>'Faroe Islands');
		$countries[] = array('value'=> 'FSM', 'name'=>'Micronesia, Federated States of');
		$countries[] = array('value'=> 'GAB', 'name'=>'Gabon');
		$countries[] = array('value'=> 'GBR', 'name'=>'United Kingdom');
		$countries[] = array('value'=> 'GEO', 'name'=>'Georgia');
		$countries[] = array('value'=> 'GGY', 'name'=>'Guernsey');
		$countries[] = array('value'=> 'GHA', 'name'=>'Ghana');
		$countries[] = array('value'=> 'GIB', 'name'=>'Gibraltar');
		$countries[] = array('value'=> 'GIN', 'name'=>'Guinea');
		$countries[] = array('value'=> 'GLP', 'name'=>'Guadeloupe');
		$countries[] = array('value'=> 'GMB', 'name'=>'Gambia');
		$countries[] = array('value'=> 'GNB', 'name'=>'Guinea-Bissau');
		$countries[] = array('value'=> 'GNQ', 'name'=>'Equatorial Guinea');
		$countries[] = array('value'=> 'GRC', 'name'=>'Greece');
		$countries[] = array('value'=> 'GRD', 'name'=>'Grenada');
		$countries[] = array('value'=> 'GRL', 'name'=>'Greenland');
		$countries[] = array('value'=> 'GTM', 'name'=>'Guatemala');
		$countries[] = array('value'=> 'GUF', 'name'=>'French Guiana');
		$countries[] = array('value'=> 'GUM', 'name'=>'Guam');
		$countries[] = array('value'=> 'GUY', 'name'=>'Guyana');
		$countries[] = array('value'=> 'HKG', 'name'=>'Hong Kong');
		$countries[] = array('value'=> 'HMD', 'name'=>'Heard Island and McDonald Islands');
		$countries[] = array('value'=> 'HND', 'name'=>'Honduras');
		$countries[] = array('value'=> 'HRV', 'name'=>'Croatia');
		$countries[] = array('value'=> 'HTI', 'name'=>'Haiti');
		$countries[] = array('value'=> 'HUN', 'name'=>'Hungary');
		$countries[] = array('value'=> 'IDN', 'name'=>'Indonesia');
		$countries[] = array('value'=> 'IMN', 'name'=>'Isle of Man');
		$countries[] = array('value'=> 'IND', 'name'=>'India');
		$countries[] = array('value'=> 'IOT', 'name'=>'British Indian Ocean Territory');
		$countries[] = array('value'=> 'IRL', 'name'=>'Ireland');
		$countries[] = array('value'=> 'IRN', 'name'=>'Iran, Islamic Republic of');
		$countries[] = array('value'=> 'IRQ', 'name'=>'Iraq');
		$countries[] = array('value'=> 'ISL', 'name'=>'Iceland');
		$countries[] = array('value'=> 'ISR', 'name'=>'Israel');
		$countries[] = array('value'=> 'ITA', 'name'=>'Italy');
		$countries[] = array('value'=> 'JAM', 'name'=>'Jamaica');
		$countries[] = array('value'=> 'JEY', 'name'=>'Jersey');
		$countries[] = array('value'=> 'JOR', 'name'=>'Jordan');
		$countries[] = array('value'=> 'JPN', 'name'=>'Japan');
		$countries[] = array('value'=> 'KAZ', 'name'=>'Kazakhstan');
		$countries[] = array('value'=> 'KEN', 'name'=>'Kenya');
		$countries[] = array('value'=> 'KGZ', 'name'=>'Kyrgyzstan');
		$countries[] = array('value'=> 'KHM', 'name'=>'Cambodia');
		$countries[] = array('value'=> 'KIR', 'name'=>'Kiribati');
		$countries[] = array('value'=> 'KNA', 'name'=>'Saint Kitts and Nevis');
		$countries[] = array('value'=> 'KOR', 'name'=>'Korea, Republic of');
		$countries[] = array('value'=> 'KWT', 'name'=>'Kuwait');
		$countries[] = array('value'=> 'LAO', 'name'=>'Lao People\'s Democratic Republic');
		$countries[] = array('value'=> 'LBN', 'name'=>'Lebanon');
		$countries[] = array('value'=> 'LBR', 'name'=>'Liberia');
		$countries[] = array('value'=> 'LBY', 'name'=>'Libya');
		$countries[] = array('value'=> 'LCA', 'name'=>'Saint Lucia');
		$countries[] = array('value'=> 'LIE', 'name'=>'Liechtenstein');
		$countries[] = array('value'=> 'LKA', 'name'=>'Sri Lanka');
		$countries[] = array('value'=> 'LSO', 'name'=>'Lesotho');
		$countries[] = array('value'=> 'LTU', 'name'=>'Lithuania');
		$countries[] = array('value'=> 'LUX', 'name'=>'Luxembourg');
		$countries[] = array('value'=> 'LVA', 'name'=>'Latvia');
		$countries[] = array('value'=> 'MAC', 'name'=>'Macao');
		$countries[] = array('value'=> 'MAF', 'name'=>'Saint Martin (French part)');
		$countries[] = array('value'=> 'MAR', 'name'=>'Morocco');
		$countries[] = array('value'=> 'MCO', 'name'=>'Monaco');
		$countries[] = array('value'=> 'MDA', 'name'=>'Moldova, Republic of');
		$countries[] = array('value'=> 'MDG', 'name'=>'Madagascar');
		$countries[] = array('value'=> 'MDV', 'name'=>'Maldives');
		$countries[] = array('value'=> 'MEX', 'name'=>'Mexico');
		$countries[] = array('value'=> 'MHL', 'name'=>'Marshall Islands');
		$countries[] = array('value'=> 'MKD', 'name'=>'Macedonia, the former Yugoslav Republic of');
		$countries[] = array('value'=> 'MLI', 'name'=>'Mali');
		$countries[] = array('value'=> 'MLT', 'name'=>'Malta');
		$countries[] = array('value'=> 'MMR', 'name'=>'Myanmar');
		$countries[] = array('value'=> 'MNE', 'name'=>'Montenegro');
		$countries[] = array('value'=> 'MNG', 'name'=>'Mongolia');
		$countries[] = array('value'=> 'MNP', 'name'=>'Northern Mariana Islands');
		$countries[] = array('value'=> 'MOZ', 'name'=>'Mozambique');
		$countries[] = array('value'=> 'MRT', 'name'=>'Mauritania');
		$countries[] = array('value'=> 'MSR', 'name'=>'Montserrat');
		$countries[] = array('value'=> 'MTQ', 'name'=>'Martinique');
		$countries[] = array('value'=> 'MUS', 'name'=>'Mauritius');
		$countries[] = array('value'=> 'MWI', 'name'=>'Malawi');
		$countries[] = array('value'=> 'MYS', 'name'=>'Malaysia');
		$countries[] = array('value'=> 'MYT', 'name'=>'Mayotte');
		$countries[] = array('value'=> 'NAM', 'name'=>'Namibia');
		$countries[] = array('value'=> 'NCL', 'name'=>'New Caledonia');
		$countries[] = array('value'=> 'NER', 'name'=>'Niger');
		$countries[] = array('value'=> 'NFK', 'name'=>'Norfolk Island');
		$countries[] = array('value'=> 'NGA', 'name'=>'Nigeria');
		$countries[] = array('value'=> 'NIC', 'name'=>'Nicaragua');
		$countries[] = array('value'=> 'NIU', 'name'=>'Niue');
		$countries[] = array('value'=> 'NLD', 'name'=>'Netherlands');
		$countries[] = array('value'=> 'NOR', 'name'=>'Norway');
		$countries[] = array('value'=> 'NPL', 'name'=>'Nepal');
		$countries[] = array('value'=> 'NRU', 'name'=>'Nauru');
		$countries[] = array('value'=> 'NZL', 'name'=>'New Zealand');
		$countries[] = array('value'=> 'OMN', 'name'=>'Oman');
		$countries[] = array('value'=> 'PAK', 'name'=>'Pakistan');
		$countries[] = array('value'=> 'PAN', 'name'=>'Panama');
		$countries[] = array('value'=> 'PCN', 'name'=>'Pitcairn');
		$countries[] = array('value'=> 'PER', 'name'=>'Peru');
		$countries[] = array('value'=> 'PHL', 'name'=>'Philippines');
		$countries[] = array('value'=> 'PLW', 'name'=>'Palau');
		$countries[] = array('value'=> 'PNG', 'name'=>'Papua New Guinea');
		$countries[] = array('value'=> 'POL', 'name'=>'Poland');
		$countries[] = array('value'=> 'PRI', 'name'=>'Puerto Rico');
		$countries[] = array('value'=> 'PRK', 'name'=>'Korea, Democratic People\'s Republic of');
		$countries[] = array('value'=> 'PRT', 'name'=>'Portugal');
		$countries[] = array('value'=> 'PRY', 'name'=>'Paraguay');
		$countries[] = array('value'=> 'PSE', 'name'=>'Palestine, State of');
		$countries[] = array('value'=> 'PYF', 'name'=>'French Polynesia');
		$countries[] = array('value'=> 'QAT', 'name'=>'Qatar');
		$countries[] = array('value'=> 'REU', 'name'=>'Réunion');
		$countries[] = array('value'=> 'ROU', 'name'=>'Romania');
		$countries[] = array('value'=> 'RUS', 'name'=>'Russian Federation');
		$countries[] = array('value'=> 'RWA', 'name'=>'Rwanda');
		$countries[] = array('value'=> 'SAU', 'name'=>'Saudi Arabia');
		$countries[] = array('value'=> 'SDN', 'name'=>'Sudan');
		$countries[] = array('value'=> 'SEN', 'name'=>'Senegal');
		$countries[] = array('value'=> 'SGP', 'name'=>'Singapore');
		$countries[] = array('value'=> 'SGS', 'name'=>'South Georgia and the South Sandwich Islands');
		$countries[] = array('value'=> 'SHN', 'name'=>'Saint Helena, Ascension and Tristan da Cunha');
		$countries[] = array('value'=> 'SJM', 'name'=>'Svalbard and Jan Mayen');
		$countries[] = array('value'=> 'SLB', 'name'=>'Solomon Islands');
		$countries[] = array('value'=> 'SLE', 'name'=>'Sierra Leone');
		$countries[] = array('value'=> 'SLV', 'name'=>'El Salvador');
		$countries[] = array('value'=> 'SMR', 'name'=>'San Marino');
		$countries[] = array('value'=> 'SOM', 'name'=>'Somalia');
		$countries[] = array('value'=> 'SPM', 'name'=>'Saint Pierre and Miquelon');
		$countries[] = array('value'=> 'SRB', 'name'=>'Serbia');
		$countries[] = array('value'=> 'SSD', 'name'=>'South Sudan');
		$countries[] = array('value'=> 'STP', 'name'=>'Sao Tome and Principe');
		$countries[] = array('value'=> 'SUR', 'name'=>'Suriname');
		$countries[] = array('value'=> 'SVK', 'name'=>'Slovakia');
		$countries[] = array('value'=> 'SVN', 'name'=>'Slovenia');
		$countries[] = array('value'=> 'SWE', 'name'=>'Sweden');
		$countries[] = array('value'=> 'SWZ', 'name'=>'Swaziland');
		$countries[] = array('value'=> 'SXM', 'name'=>'Sint Maarten (Dutch part)');
		$countries[] = array('value'=> 'SYC', 'name'=>'Seychelles');
		$countries[] = array('value'=> 'SYR', 'name'=>'Syrian Arab Republic');
		$countries[] = array('value'=> 'TCA', 'name'=>'Turks and Caicos Islands');
		$countries[] = array('value'=> 'TCD', 'name'=>'Chad');
		$countries[] = array('value'=> 'TGO', 'name'=>'Togo');
		$countries[] = array('value'=> 'THA', 'name'=>'Thailand');
		$countries[] = array('value'=> 'TJK', 'name'=>'Tajikistan');
		$countries[] = array('value'=> 'TKL', 'name'=>'Tokelau');
		$countries[] = array('value'=> 'TKM', 'name'=>'Turkmenistan');
		$countries[] = array('value'=> 'TLS', 'name'=>'Timor-Leste');
		$countries[] = array('value'=> 'TON', 'name'=>'Tonga');
		$countries[] = array('value'=> 'TTO', 'name'=>'Trinidad and Tobago');
		$countries[] = array('value'=> 'TUN', 'name'=>'Tunisia');
		$countries[] = array('value'=> 'TUR', 'name'=>'Turkey');
		$countries[] = array('value'=> 'TUV', 'name'=>'Tuvalu');
		$countries[] = array('value'=> 'TWN', 'name'=>'Taiwan, Province of China');
		$countries[] = array('value'=> 'TZA', 'name'=>'Tanzania, United Republic of');
		$countries[] = array('value'=> 'UGA', 'name'=>'Uganda');
		$countries[] = array('value'=> 'UKR', 'name'=>'Ukraine');
		$countries[] = array('value'=> 'UMI', 'name'=>'United States Minor Outlying Islands');
		$countries[] = array('value'=> 'URY', 'name'=>'Uruguay');
		$countries[] = array('value'=> 'UZB', 'name'=>'Uzbekistan');
		$countries[] = array('value'=> 'VAT', 'name'=>'Holy See (Vatican City State)');
		$countries[] = array('value'=> 'VCT', 'name'=>'Saint Vincent and the Grenadines');
		$countries[] = array('value'=> 'VEN', 'name'=>'Venezuela, Bolivarian Republic of');
		$countries[] = array('value'=> 'VGB', 'name'=>'Virgin Islands, British');
		$countries[] = array('value'=> 'VIR', 'name'=>'Virgin Islands, U.S.');
		$countries[] = array('value'=> 'VNM', 'name'=>'Viet Nam');
		$countries[] = array('value'=> 'VUT', 'name'=>'Vanuatu');
		$countries[] = array('value'=> 'WLF', 'name'=>'Wallis and Futuna');
		$countries[] = array('value'=> 'WSM', 'name'=>'Samoa');
		$countries[] = array('value'=> 'YEM', 'name'=>'Yemen');
		$countries[] = array('value'=> 'ZAF', 'name'=>'South Africa');
		$countries[] = array('value'=> 'ZMB', 'name'=>'Zambia');
		$countries[] = array('value'=> 'ZWE', 'name'=>'Zimbabwe');
		return $countries;
	}
	public function get_state_list(){
		$states[] = array('value'=> 'US-AL',	 'name'=>'Alabama');
		$states[] = array('value'=> 'US-AK',	 'name'=>'Alaska');
		$states[] = array('value'=> 'US-AZ',	 'name'=>'Arizona');
		$states[] = array('value'=> 'US-AR',	 'name'=>'Arkansas');
		$states[] = array('value'=> 'US-CA',	 'name'=>'California');
		$states[] = array('value'=> 'US-CO',	 'name'=>'Colorado');
		$states[] = array('value'=> 'US-CT',	 'name'=>'Connecticut');
		$states[] = array('value'=> 'US-DE',	 'name'=>'Delaware');
		$states[] = array('value'=> 'US-FL',	 'name'=>'Florida');
		$states[] = array('value'=> 'US-GA',	 'name'=>'Georgia');
		$states[] = array('value'=> 'US-HI',	 'name'=>'Hawaii');
		$states[] = array('value'=> 'US-ID',	 'name'=>'Idaho	');
		$states[] = array('value'=> 'US-IL',	 'name'=>'Illinois');
		$states[] = array('value'=> 'US-IN',	 'name'=>'Indiana');
		$states[] = array('value'=> 'US-IA',	 'name'=>'Iowa');
		$states[] = array('value'=> 'US-KS',	 'name'=>'Kansas');
		$states[] = array('value'=> 'US-KY',	 'name'=>'Kentucky');
		$states[] = array('value'=> 'US-LA',	 'name'=>'Louisiana');
		$states[] = array('value'=> 'US-ME',	 'name'=>'Maine	');
		$states[] = array('value'=> 'US-MD',	 'name'=>'Maryland');
		$states[] = array('value'=> 'US-MA',	 'name'=>'Massachusetts');
		$states[] = array('value'=> 'US-MI',	 'name'=>'Michigan');
		$states[] = array('value'=> 'US-MN',	 'name'=>'Minnesota');
		$states[] = array('value'=> 'US-MS',	 'name'=>'Mississippi');
		$states[] = array('value'=> 'US-MO',	 'name'=>'Missouri');
		$states[] = array('value'=> 'US-MT',	 'name'=>'Montana');
		$states[] = array('value'=> 'US-NE',	 'name'=>'Nebraska');
		$states[] = array('value'=> 'US-NV',	 'name'=>'Nevada');
		$states[] = array('value'=> 'US-NH',	 'name'=>'New Hampshire');
		$states[] = array('value'=> 'US-NJ',	 'name'=>'New Jersey');
		$states[] = array('value'=> 'US-NM',	 'name'=>'New Mexico');
		$states[] = array('value'=> 'US-NY',	 'name'=>'New York');
		$states[] = array('value'=> 'US-NC',	 'name'=>'North Carolina');
		$states[] = array('value'=> 'US-ND',	 'name'=>'North Dakota');
		$states[] = array('value'=> 'US-OH',	 'name'=>'Ohio');
		$states[] = array('value'=> 'US-OK',	 'name'=>'Oklahoma');
		$states[] = array('value'=> 'US-OR',	 'name'=>'Oregon');
		$states[] = array('value'=> 'US-PA',	 'name'=>'Pennsylvania');
		$states[] = array('value'=> 'US-RI',	 'name'=>'Rhode Island');
		$states[] = array('value'=> 'US-SC',	 'name'=>'South Carolina');
		$states[] = array('value'=> 'US-SD',	 'name'=>'South Dakota');
		$states[] = array('value'=> 'US-TN',	 'name'=>'Tennessee');
		$states[] = array('value'=> 'US-TX',	 'name'=>'Texas');
		$states[] = array('value'=> 'US-UT',	 'name'=>'Utah');
		$states[] = array('value'=> 'US-VT',	 'name'=>'Vermont');
		$states[] = array('value'=> 'US-VA',	 'name'=>'Virginia');
		$states[] = array('value'=> 'US-WA',	 'name'=>'Washington');
		$states[] = array('value'=> 'US-WV',	 'name'=>'West Virginia');
		$states[] = array('value'=> 'US-WI',	 'name'=>'Wisconsin');
		$states[] = array('value'=> 'US-WY',	 'name'=>'Wyoming');
		$states[] = array('value'=> 'US-DC',	 'name'=>'District of Columbia');
		$states[] = array('value'=> 'US-AS',	 'name'=>'American Samoa');
		$states[] = array('value'=> 'US-GU',	 'name'=>'Guam');
		$states[] = array('value'=> 'US-MP',	 'name'=>'Northern Mariana Islands');
		$states[] = array('value'=> 'US-PR',	 'name'=>'Puerto Rico');
		$states[] = array('value'=> 'US-UM',	 'name'=>'United States Minor Outlying Islands');
		$states[] = array('value'=> 'US-VI',	 'name'=>'Virgin Islands, U.S.');
		
		return $states;
	}
	/**
	* Currency List
	* @since 1.0.2
	*/
	public function get_currency_list(){
	  	  $currencyList = array(		
			  0 => array('value'=> 'AED', 'symbol' =>'', 'name' => 'AED - United Arab Emirates Dirham' 				),
			  1 => array('value'=> 'AFN', 'symbol' =>'؋', 'name' => 'AFN - Afghan Afghani*' 							),
			  2 => array('value'=> 'ALL', 'symbol' =>'Lek', 'name' => 'ALL - Albanian Lek' 							),
			  3 => array('value'=> 'AMD', 'symbol' =>'', 'name' => 'AMD - Armenian Dram' 							),
			  4 => array('value'=> 'ANG', 'symbol' =>'ƒ', 'name' => 'ANG - Netherlands Antillean Gulden' 			),
			  5 => array('value'=> 'AOA', 'symbol' =>'', 'name' => 'AOA - Angolan Kwanza*' 							),
			  6 => array('value'=> 'ARS', 'symbol' =>'$', 'name' => 'ARS - Argentine Peso*' 							),
			  7 => array('value'=> 'AUD', 'symbol' =>'$', 'name' => 'AUD - Australian Dollar' 						),
			  8 => array('value'=> 'AWG', 'symbol' =>'ƒ', 'name' => 'AWG - Aruban Florin' 							),
			  9 => array('value'=> 'AZN', 'symbol' =>'ман', 'name' => 'AZN - Azerbaijani Manat' 						),
			 10 => array('value'=> 'BAM', 'symbol' =>'KM', 'name' => 'BAM - Bosnia & Herzegovina Convertible Mark'	),
			 11 => array('value'=> 'BBD', 'symbol' =>'$', 'name' => 'BBD - Barbadian Dollar' 						),
			 12 => array('value'=> 'BDT', 'symbol' =>'', 'name' => 'BDT - Bangladeshi Taka' 						),
			 13 => array('value'=> 'BGN', 'symbol' =>'лв', 'name' => 'BGN - Bulgarian Lev' 							),
			 14 => array('value'=> 'BIF', 'symbol' =>'', 'name' => 'BIF - Burundian Franc' 							),
			 15 => array('value'=> 'BMD', 'symbol' =>'$', 'name' => 'BMD - Bermudian Dollar' 						),
			 16 => array('value'=> 'BND', 'symbol' =>'$', 'name' => 'BND - Brunei Dollar' 							),
			 17 => array('value'=> 'BOB', 'symbol' =>'$b', 'name' => 'BOB - Bolivian Boliviano*' 						),
			 18 => array('value'=> 'BRL', 'symbol' =>'R$', 'name' => 'BRL - Brazilian Real*' 							),
			 19 => array('value'=> 'BSD', 'symbol' =>'$', 'name' => 'BSD - Bahamian Dollar' 							),
			 20 => array('value'=> 'BWP', 'symbol' =>'P', 'name' => 'BWP - Botswana Pula' 							),
			 21 => array('value'=> 'BZD', 'symbol' =>'BZ$', 'name' => 'BZD - Belize Dollar' 							),
			 22 => array('value'=> 'CAD', 'symbol' =>'$', 'name' => 'CAD - Canadian Dollar' 							),
			 23 => array('value'=> 'CDF', 'symbol' =>'', 'name' => 'CDF - Congolese Franc' 							),
			 24 => array('value'=> 'CHF', 'symbol' =>'CHF', 'name' => 'CHF - Swiss Franc' 								),
			 25 => array('value'=> 'CLP', 'symbol' =>'$', 'name' => 'CLP - Chilean Peso*' 							),
			 26 => array('value'=> 'CNY', 'symbol' =>'¥', 'name' => 'CNY - Chinese Renminbi Yuan' 					),
			 27 => array('value'=> 'COP', 'symbol' =>'$', 'name' => 'COP - Colombian Peso*' 							),
			 28 => array('value'=> 'CRC', 'symbol' =>'₡', 'name' => 'CRC - Costa Rican Colón*' 						),
			 29 => array('value'=> 'CVE', 'symbol' =>'', 'name' => 'CVE - Cape Verdean Escudo*' 					),
			 30 => array('value'=> 'CZK', 'symbol' =>'Kč', 'name' => 'CZK - Czech Koruna*' 							),
			 31 => array('value'=> 'DJF', 'symbol' =>'', 'name' => 'DJF - Djiboutian Franc*' 						),
			 32 => array('value'=> 'DKK', 'symbol' =>'kr', 'name' => 'DKK - Danish Krone' 							),
			 33 => array('value'=> 'DOP', 'symbol' =>'RD$', 'name' => 'DOP - Dominican Peso' 							),
			 34 => array('value'=> 'DZD', 'symbol' =>'', 'name' => 'DZD - Algerian Dinar' 							),
			 35 => array('value'=> 'EEK', 'symbol' =>'kr', 'name' => 'EEK - Estonian Kroon*' 							),
			 36 => array('value'=> 'EGP', 'symbol' =>'£', 'name' => 'EGP - Egyptian Pound' 							),
			 37 => array('value'=> 'ETB', 'symbol' =>'', 'name' => 'ETB - Ethiopian Birr' 							),
			 38 => array('value'=> 'EUR', 'symbol' =>'€', 'name' => 'EUR - Euro' 									),
			 39 => array('value'=> 'FJD', 'symbol' =>'$', 'name' => 'FJD - Fijian Dollar' 							),
			 40 => array('value'=> 'FKP', 'symbol' =>'£', 'name' => 'FKP - Falkland Islands Pound*' 					),
			 41 => array('value'=> 'GBP', 'symbol' =>'£', 'name' => 'GBP - British Pound' 							),
			 42 => array('value'=> 'GEL', 'symbol' =>'', 'name' => 'GEL - Georgian Lari' 							),
			 43 => array('value'=> 'GIP', 'symbol' =>'£', 'name' => 'GIP - Gibraltar Pound' 							),
			 44 => array('value'=> 'GMD', 'symbol' =>'', 'name' => 'GMD - Gambian Dalasi' 							),
			 45 => array('value'=> 'GNF', 'symbol' =>'', 'name' => 'GNF - Guinean Franc*' 							),
			 46 => array('value'=> 'GTQ', 'symbol' =>'Q', 'name' => 'GTQ - Guatemalan Quetzal*' 						),
			 47 => array('value'=> 'GYD', 'symbol' =>'$', 'name' => 'GYD - Guyanese Dollar' 							),
			 48 => array('value'=> 'HKD', 'symbol' =>'$', 'name' => 'HKD - Hong Kong Dollar' 						),
			 49 => array('value'=> 'HNL', 'symbol' =>'L', 'name' => 'HNL - Honduran Lempira*' 						),
			 50 => array('value'=> 'HRK', 'symbol' =>'kn', 'name' => 'HRK - Croatian Kuna' 							),
			 51 => array('value'=> 'HTG', 'symbol' =>'', 'name' => 'HTG - Haitian Gourde' 							),
			 52 => array('value'=> 'HUF', 'symbol' =>'Ft', 'name' => 'HUF - Hungarian Forint*' 						),
			 53 => array('value'=> 'IDR', 'symbol' =>'Rp', 'name' => 'IDR - Indonesian Rupiah' 						),
			 54 => array('value'=> 'ILS', 'symbol' =>'₪', 'name' => 'ILS - Israeli New Sheqel' 						),
			 55 => array('value'=> 'INR', 'symbol' =>'', 'name' => 'INR - Indian Rupee*' 							),
			 56 => array('value'=> 'ISK', 'symbol' =>'kr', 'name' => 'ISK - Icelandic Króna' 							),
			 57 => array('value'=> 'JMD', 'symbol' =>'J$', 'name' => 'JMD - Jamaican Dollar' 							),
			 58 => array('value'=> 'JPY', 'symbol' =>'¥', 'name' => 'JPY - Japanese Yen' 							),
			 59 => array('value'=> 'KES', 'symbol' =>'', 'name' => 'KES - Kenyan Shilling' 							),
			 60 => array('value'=> 'KGS', 'symbol' =>'лв', 'name' => 'KGS - Kyrgyzstani Som' 							),
			 61 => array('value'=> 'KHR', 'symbol' =>'៛', 'name' => 'KHR - Cambodian Riel' 							),
			 62 => array('value'=> 'KMF', 'symbol' =>'', 'name' => 'KMF - Comorian Franc' 							),
			 63 => array('value'=> 'KRW', 'symbol' =>'₩', 'name' => 'KRW - South Korean Won' 						),
			 64 => array('value'=> 'KYD', 'symbol' =>'$', 'name' => 'KYD - Cayman Islands Dollar' 					),
			 65 => array('value'=> 'KZT', 'symbol' =>'лв', 'name' => 'KZT - Kazakhstani Tenge' 						),
			 66 => array('value'=> 'LAK', 'symbol' =>'₭', 'name' => 'LAK - Lao Kip*' 								),
			 67 => array('value'=> 'LBP', 'symbol' =>'£', 'name' => 'LBP - Lebanese Pound' 							),
			 68 => array('value'=> 'LKR', 'symbol' =>'₨', 'name' => 'LKR - Sri Lankan Rupee' 						),
			 69 => array('value'=> 'LRD', 'symbol' =>'$', 'name' => 'LRD - Liberian Dollar' 							),
			 70 => array('value'=> 'LSL', 'symbol' =>'', 'name' => 'LSL - Lesotho Loti' 							),
			 71 => array('value'=> 'LTL', 'symbol' =>'Lt', 'name' => 'LTL - Lithuanian Litas' 						),
			 72 => array('value'=> 'LVL', 'symbol' =>'Ls', 'name' => 'LVL - Latvian Lats' 							),
			 73 => array('value'=> 'MAD', 'symbol' =>'', 'name' => 'MAD - Moroccan Dirham' 							),
			 74 => array('value'=> 'MDL', 'symbol' =>'', 'name' => 'MDL - Moldovan Leu' 							),
			 75 => array('value'=> 'MGA', 'symbol' =>'', 'name' => 'MGA - Malagasy Ariary' 							),
			 76 => array('value'=> 'MKD', 'symbol' =>'ден', 'name' => 'MKD - Macedonian Denar' 						),
			 77 => array('value'=> 'MNT', 'symbol' =>'₮', 'name' => 'MNT - Mongolian Tögrög' 						),
			 78 => array('value'=> 'MOP', 'symbol' =>'', 'name' => 'MOP - Macanese Pataca' 							),
			 79 => array('value'=> 'MRO', 'symbol' =>'', 'name' => 'MRO - Mauritanian Ouguiya' 						),
			 80 => array('value'=> 'MUR', 'symbol' =>'₨', 'name' => 'MUR - Mauritian Rupee*' 						),
			 81 => array('value'=> 'MVR', 'symbol' =>'', 'name' => 'MVR - Maldivian Rufiyaa' 						),
			 82 => array('value'=> 'MWK', 'symbol' =>'', 'name' => 'MWK - Malawian Kwacha' 							),
			 83 => array('value'=> 'MXN', 'symbol' =>'$', 'name' => 'MXN - Mexican Peso*' 							),
			 84 => array('value'=> 'MYR', 'symbol' =>'RM', 'name' => 'MYR - Malaysian Ringgit' 						),
			 85 => array('value'=> 'MZN', 'symbol' =>'MT', 'name' => 'MZN - Mozambican Metical' 						),
			 86 => array('value'=> 'NAD', 'symbol' =>'$', 'name' => 'NAD - Namibian Dollar' 							),
			 87 => array('value'=> 'NGN', 'symbol' =>'₦', 'name' => 'NGN - Nigerian Naira' 							),
			 88 => array('value'=> 'NIO', 'symbol' =>'C$', 'name' => 'NIO - Nicaraguan Córdoba*' 						),
			 89 => array('value'=> 'NOK', 'symbol' =>'kr', 'name' => 'NOK - Norwegian Krone' 							),
			 90 => array('value'=> 'NPR', 'symbol' =>'₨', 'name' => 'NPR - Nepalese Rupee' 							),
			 91 => array('value'=> 'NZD', 'symbol' =>'$', 'name' => 'NZD - New Zealand Dollar' 						),
			 92 => array('value'=> 'PAB', 'symbol' =>'B/.', 'name' => 'PAB - Panamanian Balboa*' 						),
			 93 => array('value'=> 'PEN', 'symbol' =>'S/.', 'name' => 'PEN - Peruvian Nuevo Sol*' 						),
			 94 => array('value'=> 'PGK', 'symbol' =>'', 'name' => 'PGK - Papua New Guinean Kina' 					),
			 95 => array('value'=> 'PHP', 'symbol' =>'₱', 'name' => 'PHP - Philippine Peso' 							),
			 96 => array('value'=> 'PKR', 'symbol' =>'₨', 'name' => 'PKR - Pakistani Rupee' 							),
			 97 => array('value'=> 'PLN', 'symbol' =>'zł', 'name' => 'PLN - Polish Złoty' 							),
			 98 => array('value'=> 'PYG', 'symbol' =>'Gs', 'name' => 'PYG - Paraguayan Guaraní*' 						),
			 99 => array('value'=> 'QAR', 'symbol' =>'﷼', 'name' => 'QAR - Qatari Riyal' 							),
			100 => array('value'=> 'RON', 'symbol' =>'lei', 'name' => 'RON - Romanian Leu' 							),
			101 => array('value'=> 'RSD', 'symbol' =>'Дин.', 'name' => 'RSD - Serbian Dinar' 						),
			102 => array('value'=> 'RUB', 'symbol' =>'руб', 'name' => 'RUB - Russian Ruble' 						),
			103 => array('value'=> 'RWF', 'symbol' =>'', 'name' => 'RWF - Rwandan Franc' 							),
			104 => array('value'=> 'SAR', 'symbol' =>'﷼', 'name' => 'SAR - Saudi Riyal' 							),
			105 => array('value'=> 'SBD', 'symbol' =>'$', 'name' => 'SBD - Solomon Islands Dollar' 					),
			106 => array('value'=> 'SCR', 'symbol' =>'₨', 'name' => 'SCR - Seychellois Rupee' 						),
			107 => array('value'=> 'SEK', 'symbol' =>'kr', 'name' => 'SEK - Swedish Krona' 							),
			108 => array('value'=> 'SGD', 'symbol' =>'$', 'name' => 'SGD - Singapore Dollar' 						),
			109 => array('value'=> 'SHP', 'symbol' =>'£', 'name' => 'SHP - Saint Helenian Pound*' 					),
			110 => array('value'=> 'SLL', 'symbol' =>'', 'name' => 'SLL - Sierra Leonean Leone' 					),
			111 => array('value'=> 'SOS', 'symbol' =>'S', 'name' => 'SOS - Somali Shilling' 						),
			112 => array('value'=> 'SRD', 'symbol' =>'$', 'name' => 'SRD - Surinamese Dollar*' 						),
			113 => array('value'=> 'STD', 'symbol' =>'', 'name' => 'STD - São Tomé and Príncipe Dobra' 				),
			114 => array('value'=> 'SVC', 'symbol' =>'$', 'name' => 'SVC - Salvadoran Colón*' 						),
			115 => array('value'=> 'SZL', 'symbol' =>'', 'name' => 'SZL - Swazi Lilangeni' 							),
			116 => array('value'=> 'THB', 'symbol' =>'฿', 'name' => 'THB - Thai Baht' 								),
			117 => array('value'=> 'TJS', 'symbol' =>'', 'name' => 'TJS - Tajikistani Somoni' 						),
			118 => array('value'=> 'TOP', 'symbol' =>'', 'name' => 'TOP - Tongan Paʻanga' 							),
			119 => array('value'=> 'TRY', 'symbol' =>'', 'name' => 'TRY - Turkish Lira' 							),
			120 => array('value'=> 'TTD', 'symbol' =>'TT$', 'name' => 'TTD - Trinidad and Tobago Dollar' 			),
			121 => array('value'=> 'TWD', 'symbol' =>'NT$', 'name' => 'TWD - New Taiwan Dollar' 					),
			122 => array('value'=> 'TZS', 'symbol' =>'', 'name' => 'TZS - Tanzanian Shilling' 						),
			123 => array('value'=> 'UAH', 'symbol' =>'₴', 'name' => 'UAH - Ukrainian Hryvnia' 						),
			124 => array('value'=> 'UGX', 'symbol' =>'', 'name' => 'UGX - Ugandan Shilling' 						),
			125 => array('value'=> 'USD', 'symbol' =>'$', 'name' => 'USD - United States Dollar' 					),
			126 => array('value'=> 'UYU', 'symbol' =>'$U', 'name' => 'UYU - Uruguayan Peso*' 						),
			127 => array('value'=> 'UZS', 'symbol' =>'лв', 'name' => 'UZS - Uzbekistani Som' 						),
			128 => array('value'=> 'VND', 'symbol' =>'₫', 'name' => 'VND - Vietnamese Đồng' 						),
			129 => array('value'=> 'VUV', 'symbol' =>'', 'name' => 'VUV - Vanuatu Vatu' 							),
			130 => array('value'=> 'WST', 'symbol' =>'', 'name' => 'WST - Samoan Tala' 								),
			131 => array('value'=> 'XAF', 'symbol' =>'', 'name' => 'XAF - Central African Cfa Franc' 				),
			132 => array('value'=> 'XCD', 'symbol' =>'$', 'name' => 'XCD - East Caribbean Dollar' 					),
			133 => array('value'=> 'XOF', 'symbol' =>'', 'name' => 'XOF - West African Cfa Franc*' 					),
			134 => array('value'=> 'XPF', 'symbol' =>'', 'name' => 'XPF - Cfp Franc*' 								),
			135 => array('value'=> 'YER', 'symbol' =>'﷼', 'name' => 'YER - Yemeni Rial' 							),
			136 => array('value'=> 'ZAR', 'symbol' =>'R', 'name' => 'ZAR - South African Rand' 						),
			137 => array('value'=> 'ZMW', 'symbol' =>'', 'name' => 'ZMW - Zambian Kwacha' 							),
		);
		return $currencyList;
	}
}
