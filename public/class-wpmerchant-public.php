<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       wpmerchant.com/team
 * @since      1.0.0
 *
 * @package    Wpmerchant
 * @subpackage Wpmerchant/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wpmerchant
 * @subpackage Wpmerchant/public
 * @author     Ben Shadle <ben@wpmerchant.com>
 */
class Wpmerchant_Public {

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
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		
		// Ajax functionality to actually purchase the product or subscription
			// each ajax function should also have a nonce value in hte enqueue scripts section and include that in hte ajax call (for security purposes)
		//  Nopriv should also have a normal version so that logged out users work (the nopriv) and logged in users work (the norm)
		add_action( 'wp_ajax_nopriv_wpmerchant_purchase', array($this,'purchase'));
		add_action( 'wp_ajax_wpmerchant_purchase', array($this,'purchase'));
		// this nonce values are set in the admin.php file and included in the admin.js file
		add_action( 'wp_ajax_wpmerchant_get_email_data', array($this,'get_email_data'));
		add_action( 'wp_ajax_wpmerchant_get_payment_data', array($this,'get_payment_data'));
		//NO PRIV - means logged out users can run this functionality - need this to be the case bc the call is jcoming from outside wordpress
		//  Nopriv should also have a normal version so that logged out users work (the nopriv) and logged in users work (the norm)
		add_action( 'wp_ajax_nopriv_wpmerchant_save_email_api', array($this,'save_email_api_key'));
		add_action( 'wp_ajax_nopriv_wpmerchant_save_payment_api', array($this,'save_payment_api_key'));
		add_action( 'wp_ajax_wpmerchant_save_email_api', array($this,'save_email_api_key'));
		add_action( 'wp_ajax_wpmerchant_save_payment_api', array($this,'save_payment_api_key'));
		add_action( 'wp_ajax_wpmerchant_create_post', array($this,'create_post'));
		add_action( 'wp_ajax_wpmerchant_update_post', array($this,'update_post'));
		add_action( 'wp_ajax_wpmerchant_get_post', array($this,'get_post'));
		add_action( 'wp_ajax_wpmerchant_get_products_list', array($this,'get_products_list'));
		
		add_action( 'wp_ajax_nopriv_wpmerchant_update_gen_list', array($this,'update_gen_list'));
		add_action( 'wp_ajax_wpmerchant_update_gen_list', array($this,'update_gen_list'));
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wpmerchant-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
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

		//wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wpmerchant-public.js', array( 'jquery' ), $this->version, false );
		// Include the stripe checkout script on all pages
		wp_enqueue_script( $this->plugin_name.'-stripe-checkout', 'https://checkout.stripe.com/checkout.js', array( 'jquery' ), $this->version, false );
  	  
		// include the stripe functionality in this js file
    	  wp_register_script( $this->plugin_name,  plugin_dir_url( __FILE__ ) . 'js/wpmerchant-public.js', array( 'jquery' ), $this->version, false );
    	  wp_enqueue_script( $this->plugin_name);
		  
		  $localizedVars = $this->getLocalizedVariables();
		  
    	  // pass ajax object to this javascript file
		  // Add nonce values to this object so that we can access them in hte public.js javascript file
		  //wp_localize_script() MUST be called after the script it's being attached to has been registered using
		  wp_localize_script( $this->plugin_name, 'ajax_object', 
		  	array( 
				'ajax_url' => $localizedVars['ajax_url'],
				'purchase_nonce'=> $localizedVars['purchaseNonce'],
				'stripe_public_key'=>$localizedVars['stripe_public_key'],
				'stripe_shippingAddress'=>$localizedVars['stripe_shippingAddress'],
				'stripe_billingAddress'=>$localizedVars['stripe_billingAddress'],
				'stripe_zipCode'=>$localizedVars['stripe_zipCode'],
				'currency'=>$localizedVars['currency'],
				'stripe_checkout_image'=>$localizedVars['image'],
				'company_name'=>$localizedVars['companyName'],
				'loading_gif'=>$localizedVars['loading_gif'],
				'close_btn_image'=>$localizedVars['close_btn_img'],
				'post_checkout_msg'=>$localizedVars['thank_you_msg'],
				'icon_border_image'=>$localizedVars['icon_border_img']
			) 
		  );

	}
	/**
	* Get Localized Variables so that we can pass those to the public.js file
	* @since 1.0.0
	*/
	public function getLocalizedVariables(){
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
	/**
	 * Ajax Function to get payment api data -  apikeys
	 *
	 * @since    1.0.0
	*/
	public function get_payment_data(){
		if(!check_ajax_referer( 'wpmerchant_get_payment_data', 'security', false )){
			$data = 'error'.__LINE__;
			// Set content type
			header('Content-type: application/json');

			// Prevent caching
			header('Expires: 0');
			echo json_encode($data);
			exit();
		}
		$payment_processor = get_option( $this->plugin_name.'_payment_processor' );
		if($payment_processor == 'stripe'){
			
				$test_public_key = get_option( $this->plugin_name.'_'.$payment_processor.'_test_public_key' );
				$test_secret_key = get_option( $this->plugin_name.'_'.$payment_processor.'_test_secret_key' );
				$live_public_key = get_option( $this->plugin_name.'_'.$payment_processor.'_live_public_key' );
				$live_secret_key = get_option( $this->plugin_name.'_'.$payment_processor.'_live_secret_key' );
			
			
			if(!$test_public_key || !$test_secret_key || !$live_public_key || !$live_secret_key){
				// notify the js that hte api key doesn't exist yet
				if(!isset($_COOKIE['wpm_sc_count'])){
					$count = 1;
				} else {
					$count = $_COOKIE['wpm_sc_count'] + 1;
				}
				// This limits the number of polling calls that can be made to 5000
				if($count <= 5000){
					$data['response'] = 'empty';
					setcookie( "wpm_sc_count", $count, strtotime( '+1 days' ) );
				} else {
					$data['response'] = 'error';
					setcookie( "wpm_sc_count", '0', time() - 3600 );
				}
				$data['count'] = $count;
				// Set content type
				header('Content-type: application/json');

				// Prevent caching
				header('Expires: 0');
				echo json_encode($data);
				exit();
			}
			
	  		$data['response'] = 'success';
			// don't need to pass the apikeys to the front end because we can just reload the page to get that info
			/*$data['secret_key'] = $secret_key;
			$data['public_key'] = $public_key;*/
	  		setcookie( "wpm_sc_count", '0', time() - 3600 );
			// Set content type
	  		header('Content-type: application/json');

	  		// Prevent caching
	  		header('Expires: 0');
	  		echo json_encode($data);
	  		/* close connection */
	  		exit();		
		}
		
	}
	/**
	 * Ajax Function to get email api data - lists and apikey
	 *
	 * @since    1.0.0
	*/
	public function get_email_data(){
		if(!check_ajax_referer( 'wpmerchant_get_email_data', 'security', false )){
			$data = 'error'.__LINE__;
			// Set content type
			header('Content-type: application/json');

			// Prevent caching
			header('Expires: 0');
			echo json_encode($data);
			exit();
		}
		$email_list_processor = get_option( $this->plugin_name.'_email_list_processor' );
		if($email_list_processor == 'mailchimp'){
			$email_list_processor_config['apiKey'] = get_option( $this->plugin_name.'_'.$email_list_processor.'_api' );
			
			if(!$email_list_processor_config['apiKey']){
				// notify the js that hte api key doesn't exist yet
				if(!isset($_COOKIE['wpm_mc_count'])){
					$count = 1;
				} else {
					$count = $_COOKIE['wpm_mc_count'] + 1;
				}
				// This limits the number of polling calls that can be made to 1000
				if($count <= 1000){
					$data['response'] = 'empty';
					setcookie( "wpm_mc_count", $count, strtotime( '+1 days' ) );
				} else {
					$data['response'] = 'error';
					setcookie( "wpm_mc_count", '0', time() - 3600 );
				}
				$data['count'] = $count;
				// Set content type
				header('Content-type: application/json');

				// Prevent caching
				header('Expires: 0');
				echo json_encode($data);
				exit();
			}
			require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/class-wpmerchant-mailchimp.php';
			$newsletter_class = new Wpmerchant_MailChimp($this->plugin_name);
			$data = array();
			$lists = $newsletter_class->call('getLists',$data);
			
			$mailchimpLists[] = array('value'=> '', 'name' => __('Select MailChimp List',$this->plugin_name));
			if($lists){
				foreach($lists['data'] AS $l){
					$mailchimpLists[] = array('value'=> $l['id'], 'name' => __($l['name'],$this->plugin_name));
				}	
			} 
			
	  		$data['response'] = 'success';
	  		$data['lists'] = $mailchimpLists;
			$data['lists2'] = $lists;
			// don't need to pass the apikey to the front end because we can just reload the page to get that info
				//$data['apikey'] = $email_list_processor_config['apiKey'];
				
	  		// Set content type
			setcookie( "wpm_mc_count", '0', time() - 3600 );
	  		header('Content-type: application/json');

	  		// Prevent caching
	  		header('Expires: 0');
	  		echo json_encode($data);
	  		/* close connection */
	  		exit();		
		}
		
	}
	/**
	 * Ajax Function Run to save email api key
	 *
	 * @since    1.0.0
	*/
	public function save_email_api_key(){		
		$nonce = sanitize_text_field($_POST['security']);
		$user_id = intval($_POST['uid']);
		// verify the self created nonce - don't use wp_verify_nonce bc it checks the referring url and it is different 
		$email_processor_nonce = get_option( $this->plugin_name.'_save_email_api_nonce' );
		wp_set_current_user( $user_id );
		
		if(!current_user_can( 'administrator' ) || $email_processor_nonce != $nonce){
		//wp_set_current_user( $user_id );
		//if(!$user_id || !wp_verify_nonce($nonce,'wpmerchant_save_email_api')){
			$data['response'] = 'error'.__LINE__;
			$data['vars'] = $_POST;
			// Set content type
			header('Content-type: application/json');

			// Prevent caching
			header('Expires: 0');
			echo json_encode($data);
			exit();
		}
		
		$email_list_processor = get_option( $this->plugin_name.'_email_list_processor' );
		// Set the or conditional so that if hte user hasn't saved the first section of hte settings mailchimp connect will still work
		if($email_list_processor == 'mailchimp' || !$email_list_processor){
			$apiKey = sanitize_text_field($_POST['apikey']);
			update_option( $this->plugin_name.'_'.$email_list_processor.'_api', $apiKey );
	  		$data['response'] = 'success';
	  		// Set content type
	  		header('Content-type: application/json');

	  		// Prevent caching
	  		header('Expires: 0');
	  		echo json_encode($data);
	  		/* close connection */
	  		exit();		
		}
		
	}
	/**
	 * Ajax Function Run to save payment api key
	 *
	 * @since    1.0.0
	*/
	public function save_payment_api_key(){		
		$nonce = sanitize_text_field($_POST['security']);
		$user_id = intval($_POST['uid']);
		// verify the self created nonce - don't use wp_verify_nonce bc it checks the referring url and it is different 
		$payment_processor_nonce = get_option( $this->plugin_name.'_save_payment_api_nonce' );
		wp_set_current_user( $user_id );
		
		if(!current_user_can( 'administrator' ) || $payment_processor_nonce != $nonce){
		//wp_set_current_user( $user_id );
		//if(!$user_id || !wp_verify_nonce($nonce,'wpmerchant_save_email_api')){
			$data['response'] = 'error'.__LINE__;
			$data['vars'] = $_POST;
			// Set content type
			header('Content-type: application/json');

			// Prevent caching
			header('Expires: 0');
			echo json_encode($data);
			exit();
		}
		
		$payment_processor = get_option( $this->plugin_name.'_payment_processor' );
		// Set the or conditional so that if hte user hasn't saved the first section of hte settings stripe connect will still work
		if($payment_processor == 'stripe' || !$payment_processor){
			$live_secret_key = sanitize_text_field($_POST['live_secret_key']);
			$live_public_key = sanitize_text_field($_POST['live_public_key']); 
			$test_secret_key = sanitize_text_field($_POST['test_secret_key']);
			$test_public_key = sanitize_text_field($_POST['test_public_key']);
			
			update_option( $this->plugin_name.'_'.$payment_processor.'_test_public_key', $test_public_key );
			update_option( $this->plugin_name.'_'.$payment_processor.'_test_secret_key', $test_secret_key );
			update_option( $this->plugin_name.'_'.$payment_processor.'_live_public_key', $live_public_key );
			update_option( $this->plugin_name.'_'.$payment_processor.'_live_secret_key', $live_secret_key );
			
	  		$data['response'] = 'success';
	  		// Set content type
	  		header('Content-type: application/json');

	  		// Prevent caching
	  		header('Expires: 0');
	  		echo json_encode($data);
	  		/* close connection */
	  		exit();		
		}
		
	}
	/**
	 * Ajax Function Run to update post content
	 *
	 * @since    1.0.0
	*/
	public function update_post(){		
		if(!check_ajax_referer( 'wpmerchant_update_post', 'security', false )){
			$data['response'] = 'error';
			$data['message'] = 'Error updating page.';
			// Set content type
			header('Content-type: application/json');

			// Prevent caching
			header('Expires: 0');
			echo json_encode($data);
			exit();
		}
		if(!intval($_POST['wpmerchant_post_id'])){
			$data['response'] = 'error';
			$data['message'] = 'Error updating page.';
			// Set content type
			header('Content-type: application/json');

			// Prevent caching
			header('Expires: 0');
			echo json_encode($data);
			exit();
		}
		if(!$_POST['wpmerchant_post_content']){
			$data['response'] = 'error';
			$data['message'] = 'Don\'t leave page content empty.';
			// Set content type
			header('Content-type: application/json');

			// Prevent caching
			header('Expires: 0');
			echo json_encode($data);
			exit();
		}
		$post_id = $_POST['wpmerchant_post_id'];
		$output = 'OBJECT';
		$filter = 'db';
		$post = get_post( $post_id, $output, $filter);
		// Set the or conditional so that if hte user hasn't saved the first section of hte settings stripe connect will still work
		if($post){
			$post->post_content = wp_kses_post($_POST['wpmerchant_post_content']);			
			$post->post_content = stripslashes($post->post_content);
			$updated_post_id = wp_update_post( $post );
			if($updated_post_id){
				$data['response'] = 'success';
				$data['message'] = 'Page saved.';
				$data['link'] = '/wp-admin/admin.php?page=wpmerchant&slide=subscriptions';
				$data['link_message'] = 'Check out the next step.';
		  		// Set content type
		  		header('Content-type: application/json');

		  		// Prevent caching
		  		header('Expires: 0');
		  		echo json_encode($data);
		  		/* close connection */
		  		exit();		
			} else {
				$data['response'] = 'error';
				$data['message'] = 'Error updating page.';
				// Set content type
				header('Content-type: application/json');

				// Prevent caching
				header('Expires: 0');
				echo json_encode($data);
				exit();
			}
		} else {
			$data['response'] = 'error';
			$data['message'] = 'Error updating page.';
			// Set content type
			header('Content-type: application/json');

			// Prevent caching
			header('Expires: 0');
			echo json_encode($data);
			exit();
		}
		
	}
	/**
	 * Ajax Function Run to get post content
	 *
	 * @since    1.0.0
	*/
	public function get_post(){		
		if(!check_ajax_referer( 'wpmerchant_get_post', 'security', false )){
			$data['response'] = 'error';
			$data['message'] = 'Error getting contents.';
			// Set content type
			header('Content-type: application/json');

			// Prevent caching
			header('Expires: 0');
			echo json_encode($data);
			exit();
		}
		if(!intval($_GET['post_id'])){
			$data['response'] = 'error';
			$data['message'] = 'Error getting contents.';
			// Set content type
			header('Content-type: application/json');

			// Prevent caching
			header('Expires: 0');
			echo json_encode($data);
			exit();
		}
		$post_id = $_GET['post_id'];
		$output = 'OBJECT';
		$filter = 'display';
		$post = get_post( $post_id, $output, $filter);
		// Set the or conditional so that if hte user hasn't saved the first section of hte settings stripe connect will still work
		if($post){
			$data['response'] = 'success';
			$data['post'] = $post;
	  		// Set content type
	  		header('Content-type: application/json');

	  		// Prevent caching
	  		header('Expires: 0');
	  		echo json_encode($data);
	  		/* close connection */
	  		exit();		
		} else {
			$data['response'] = 'error';
			$data['message'] = 'Error getting contents.';
			// Set content type
			header('Content-type: application/json');

			// Prevent caching
			header('Expires: 0');
			echo json_encode($data);
			exit();
		}
		
	}
	/**
	 * Get Product lists function
	 *
	 * @since    1.0.0
	*/
	public function get_products_list(){		
		if(!check_ajax_referer( 'wpmerchant_get_products_list', 'security', false )){
			$data['response'] = 'error';
			$data['message'] = 'Error getting contents.';
			// Set content type
			header('Content-type: application/json');

			// Prevent caching
			header('Expires: 0');
			echo json_encode($data);
			exit();
		}
		require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/class-wpmerchant-wpdb.php';
		$wordpress_class = new Wpmerchant_Wpdb($this->plugin_name);
		$products = $wordpress_class->getPosts('wpmerchant_product');
		//$args['api_key']
		$product_list[] = array('value'=> '', 'text' => 'Select a Product');
		if($products){
			foreach($products AS $p){
				$product_list[] = array('value'=> $p->ID, 'text' => $p->post_title);
			}
		}
		$data['response'] = 'success';
		$data['product_list'] = $product_list;
  		// Set content type
  		header('Content-type: application/json');

  		// Prevent caching
  		header('Expires: 0');
  		echo json_encode($data);
  		/* close connection */
  		exit();	
	}
	/**
	 * Ajax Function Run to update email list
	 *
	 * @since    1.0.0
	*/
	public function update_gen_list(){		
		if(!check_ajax_referer( 'wpmerchant_update_gen_list', 'security', false )){
			$data = 'error'.__LINE__;
			// Set content type
			header('Content-type: application/json');

			// Prevent caching
			header('Expires: 0');
			echo json_encode($data);
			exit();
		}
		$email_list_processor = get_option( $this->plugin_name.'_email_list_processor' );
		// Set the or conditional so that if hte user hasn't saved the first section of hte settings stripe connect will still work
		if($email_list_processor == 'mailchimp'){
			$gen_list_id = sanitize_text_field($_POST['list_id']);
			update_option( $this->plugin_name.'_'.$email_list_processor.'_gen_list_id', $gen_list_id );
			
	  		$data['response'] = 'success';
			$data['list_id'] = $gen_list_id;
	  		// Set content type
	  		header('Content-type: application/json');

	  		// Prevent caching
	  		header('Expires: 0');
	  		echo json_encode($data);
	  		/* close connection */
	  		exit();		
		} else {
			$data = 'error'.__LINE__;
			// Set content type
			header('Content-type: application/json');

			// Prevent caching
			header('Expires: 0');
			echo json_encode($data);
			exit();
		}
		
	}
	/**
	 * Ajax Function Run following product purchase
	 *
	 * @since    1.0.0
	 */
	public function purchase() {
		/* UNCOMMENT IF THERE ARE NO OUTSIDE TRANSACTIONS - only if need purchasing capability on a page not monitored by wordpress
		$nonce = sanitize_text_field($_POST['security']);
		// verify the self created nonce - don't use wp_verify_nonce bc it checks the referring url and it is different bc the user may not exist
		$payment_processor_nonce = get_option( $this->plugin_name.'_purchase_nonce' );
		
		if($payment_processor_nonce != $nonce){
		//wp_set_current_user( $user_id );
		//if(!$user_id || !wp_verify_nonce($nonce,'wpmerchant_save_email_api')){
			$data['response'] = 'error'.__LINE__;
			$data['vars'] = $_POST;
			// Set content type
			header('Content-type: application/json');

			// Prevent caching
			header('Expires: 0');
			echo json_encode($data);
			exit();
		}*/
		
		// this manages all wordpress database interactions
		require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/class-wpmerchant-wpdb.php';
		$wordpress_class = new Wpmerchant_Wpdb($this->plugin_name);
		//These payment processor and email list processor variables allow us to eventually create an option where users can select the payment processor and teh email list processor.  Then, we just need to create functions for each payment processor and email list processor that we provide user's access to.
		$payment_processor = get_option( $this->plugin_name.'_payment_processor' );
		$email_list_processor = get_option( $this->plugin_name.'_email_list_processor' );
		
		if($payment_processor == 'stripe'){
			require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/class-wpmerchant-stripe.php';
			$payment_class = new Wpmerchant_Stripe($this->plugin_name);
		}
		if($email_list_processor == 'mailchimp'){
			require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/class-wpmerchant-mailchimp.php';
			$newsletter_class = new Wpmerchant_MailChimp($this->plugin_name);
		}
		/**
		FREE VERSION
		**/
		// Created WPL Payments table - in the activator file
			// Have it include all of the user information (so that we can convert those users into wordpress someone upgrades to a pro plugin)
			// THis is for us to track which email addresses are associated iwth which stripe customer while also allowing us to separate the default wordpress user with a wpl user (so that the pro version has more value)
			
		// SEE IF THE CUSTOMER EXISTS IN THE WPMERCHANT_CUSTOMERS TABLE
			// this table was created in the activation hook
		// if customer doesn't exist in wp table then insert new record into wpmerchant customer table
		//$inputAmount = intval($_POST['amount']);
		$inputWPMCustomer['token'] = sanitize_text_field($_POST['token']);
		if(isset($_POST['firstName']) && isset($_POST['lastName'])){
			$inputWPMCustomer['name'] = sanitize_text_field($_POST['firstName']). ' '. sanitize_text_field($_POST['lastName']);
			$inputWPMCustomer['first_name'] = sanitize_text_field($_POST['firstName']);
			$inputWPMCustomer['last_name'] = sanitize_text_field($_POST['lastName']);
		} else {
			$inputWPMCustomer['first_name'] = '';
			$inputWPMCustomer['last_name'] = '';
			$inputWPMCustomer['name'] = '';
		}
		if(isset($_POST['zip'])){
			$inputWPMCustomer['zip'] = sanitize_text_field($_POST['zip']);
		} else {
			$inputWPMCustomer['zip'] = '';
		}
		$inputWPMCustomer['email'] = sanitize_email($_POST['email']);
		// checking if customer exists in wp database
		$wpMCustomer = $wordpress_class->getWPMCustomer($inputWPMCustomer);
		if($payment_class->payment_processor_config['status'] == 'live'){
			// only get and write customers to the database if it is the live mode
			// we don't want to hvae emails assoc with test stripe ids - this screws up the switching from test to live 
			// if we reuse customer stripe ids from one mode to another they are wrong so the functinoality won't work
			
		
			// if customer doesn't already exist in the db
			if(!$wpMCustomer){
				// NEED TO ADD USER AS A STRIPE CUSTOMER FIRST before adding to wp table
				$payment_processor_customer = $payment_class->call('addCustomer',$inputWPMCustomer);
				// Then, add user to the wpmerchant_customer table
				if($payment_processor == 'stripe'){
					$inputWPMCustomer['stripe_id'] = $payment_processor_customer->id;
				}
				$affectedRows = $wordpress_class->addWPMCustomer($inputWPMCustomer);
				if(!$affectedRows){
					//include 'error.html.php';
					$data = 'error'.__LINE__;
					// Set content type
					header('Content-type: application/json');

					// Prevent caching
					header('Expires: 0');
					echo json_encode($data);
					exit();
				}
			} else {
				// Get Stripe Customer - check by making sure the existing or new user exists as a stripe customer
				unset($data);
				if($payment_processor == 'stripe'){
					$data = array('stripe_id'=>$wpMCustomer->stripe_id);
				}
				$payment_processor_customer = $payment_class->call('getCustomer',$data);
				if(!$payment_processor_customer){
					// add stripe customer
					$payment_processor_customer = $payment_class->call('addCustomer',$inputWPMCustomer);
				}
			}
		} else {
			// create a test stripe user (it doesn't matter if they already exist we don't care)
			// DONT ADD TO WORDPRESS DATABASE BC WE DONT WANT TEST STRIPE IDS THERE
			$payment_processor_customer = $payment_class->call('addCustomer',$inputWPMCustomer);
		}
		// Add card to Stripe Customer
		unset($data);
		$data = array('token'=>$inputWPMCustomer['token'], 'customer'=>$payment_processor_customer);
			// If you're running into an error in the addCard functionality - it's likely because you deleted the customer in stripe.  Need to set up a webhook to make sure the customer is deleted here as well. Fix this by deleting the customer that you're trying to add the card to in the wpmerchant_customers table
		$card = $payment_class->call('addCard',$data);
		/* var_dump('blah'.__LINE__);
		exit();*/
		if(isset($_POST['products']) && $_POST['products']){
			// we're sent a json_encoded array with quantity and product id in each array
			// this function is for a singular product or multiple products
			$products = json_decode(stripslashes($_POST['products']));
			foreach($products AS $key=>$value){
				$p = trim($value->id);
				$p = intval($p);
				$quantity = trim($value->quantity);
				$quantity = intval($quantity);
				if(!$p || !$quantity){
					continue;
				}
				$displayKey = $key+1;
				//$product = get_post($product_id)
				$cost = get_post_meta( $p, '_regular_price', true );
				$title = get_the_title( $p );
				// add this product's amount onto the sum of all previous products
				if($key == 0){
					$amount = $cost*100*$quantity;
					$description = $title;
				} else {
					$amount += $cost*100*$quantity;
					$description .= '. '.$title;
				}
		
				// Check inventory - is the item in stock.  If in stock continue.
				$stock_status = get_post_meta( $p, '_stock_status', true );	
				if(!$stock_status){
					// if the item is out of stock, check to see if they allow backorders
					//if they do allow bakcorders the user continues below to charge
					$allow_backorders = get_post_meta( $p, '_allow_backorders', true );		
					if(!$allow_backorders){
						// if they don't allow backorders, send sold out message back for display
						$sold_out_message = get_post_meta( $p, '_sold_out_message', true );
						$sold_out_message = do_shortcode($sold_out_message); 
						// dont need this because you want shortcode result to display NOT the shortcode itself
						//$sold_out_message = apply_filters('the_content', $sold_out_message); 
						$data['response'] = 'sold_out';
						$data['product'] = $products[$key];
						$data['message'] = $sold_out_message;
						// Set content type
						header('Content-type: application/json');

						// Prevent caching
						header('Expires: 0');
						echo json_encode($data);
						exit();
					}
				}
				$inventory = get_post_meta( $p, '_stock', true );
				// use three = signs because it shows that it's 0 rather than null
				if($inventory || $inventory === 0){
					$products[$key]->new_inventory = $inventory - $quantity;
				}
				
				$metadata["product_id_$displayKey"] = $p;
				$metadata["product_name_$displayKey"] = $title;
				$metadata["product_quantity_$displayKey"] = $quantity;
			}
			
			
			// charge user for all of the products at once
			unset($data);
			$data = array('card'=>$card,'customer'=>$payment_processor_customer,'product_description'=>$description,'metadata'=>$metadata,'amount'=>$amount,'token'=>$inputWPMCustomer['token']);
			$charge = $payment_class->call('addCharge',$data);
		
			// CREATE ORDER
			// create the order so that a user can see that in each order in their wpmerchant dashboard and so that they can see sales based on those orders
			// this positioning needs to change when plans are added so only one order is made per transaction
			/* UNCOMMENT FOR ORDER AND SALES
			$order_post_id = $this->create_wpmerchant_order();*/
		
			// Mark Inventory - Since hte charge was successful put hte inventory number htat was obtained from above
			// Add order items to the wpmerchant_order_items table
			foreach($products AS $key=>$value){
				$p = trim($value->id);
				if($key == 0){
					// WILL USE LATER - GET THE first products thank you information
					// only use the general thank you info if the first product thank you info doesn't exist
					$thank_you_modal = $this->get_post_checkout_details($p);
				}
				if(isset($value->new_inventory)){
					update_post_meta( $p, '_stock', $value->new_inventory );
				}
				// Add Order Items so that they're associated with the WPMerchant Order created above
				/* UNCOMMENT FOR ORDER AND SALES
				$wpmOrder['order_id'] = $order_post_id;
				$wpmOrder['product_id'] = $p;
				$WPMOrder = $wordpress_class->addWPMOrderItem($wpmOrder);*/
			}
			// IF THE MailChimp genListId exists then Subscribe user to MailChimp General Interest List 
			if($newsletter_class->email_list_processor_config['genListId']){
				unset($data);
				$data = array('list_id'=> $newsletter_class->email_list_processor_config['genListId'], 'first_name'=> $inputWPMCustomer['first_name'], 'last_name'=>$inputWPMCustomer['last_name'],'email'=>$inputWPMCustomer['email']);
				$newsletter_class->call('listSubscribe',$data);
			}
			
			//Redirect user to this page upon successful purchase
			$successRedirect = get_option($this->plugin_name.'_post_checkout_redirect');
			$data['thanks_desc'] = (isset($thank_you_modal)) ? $thank_you_modal['description'] : '';
			$data['thanks_title'] = (isset($thank_you_modal)) ? $thank_you_modal['title'] : '';
			$data['thanks_content'] = (isset($thank_you_modal)) ? $thank_you_modal['content'] : '';
			$data['redirect'] = (isset($successRedirect)) ? $successRedirect : '';
			$data['response'] = 'success';
			// Set content type
			header('Content-type: application/json');

			// Prevent caching
			header('Expires: 0');
			echo json_encode($data);
			/* close connection */
			exit();		
			
		}
		
		$data['response'] = 'success';
		// Set content type
		header('Content-type: application/json');

		// Prevent caching
		header('Expires: 0');
		echo json_encode($data);
		/* close connection */
		exit();		
	}
	public function create_wpmerchant_order(){
		require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/class-wpmerchant-helper.php';
		$helper_class = new Wpmerchant_Helper($this->plugin_name);
		$post_type = 'wpmerchant_order';
		$latest_post_id = $wordpress_class->getLatestPostID();
		$title = 'New Order #'.$latest_post_id;
		$title2 = 'new-order-'.$latest_post_id;
		$post_name = $helper_class->slugify($title2);
		$post = array(
		  'post_content'   => '', // The full text of the post.
		  'post_name'      => $post_name, // The name (slug) for your post
		  'post_title'     => $title, // The title of your post.
		  'post_status'    => 'publish',//[ 'draft' | 'publish' | 'pending'| 'future' | 'private' | custom registered status ] // Default 'draft'.
		  'post_type'      => $post_type,//[ 'post' | 'page' | 'link' | 'nav_menu_item' | custom post type ] // Default 'post'.
		  'post_author'    => '', // The user ID number of the author. Default is the current user ID.
		  'ping_status'    => 'closed', // Pingbacks or trackbacks allowed. Default is the option 'default_ping_status'.
		  'post_excerpt'   => '', // For all your post excerpt needs.
		  'post_date'      => date('Y-m-d H:i:s'), // The time post was made.
		  'post_date_gmt'  => date('Y-m-d H:i:s') // The time post was made, in GMT.
		);  
		
		//post_name, post_title, post_content, and post_excerpt are required
		$order_post_id = wp_insert_post($post);
		if(!$order_post_id){
			// throw an error
			$data['response'] = 'error';
			$data['message'] = 'Error creating order.';
			// Set content type
			header('Content-type: application/json');

			// Prevent caching
			header('Expires: 0');
			echo json_encode($data);
			exit();
		}
		$saved_post = array( 'ID' => $order_post_id, 'post_title' => 'Order #'.$order_post_id, 'post_name' => 'order-'.$order_post_id,);
		wp_update_post( $saved_post );
		/*if(isset($_POST['_regular_price'])){
			update_post_meta( $order_post_id, '_regular_price', sanitize_text_field($_POST['_regular_price']));
		}
		if(isset($_POST['_stock_status'])){
			update_post_meta( $order_post_id, '_stock_status', sanitize_text_field($_POST['_stock_status']) );
		}*/
			return $order_post_id;
	}
	public function get_post_checkout_details($post_id){
		// GET THE first products thank you information
		$thank_you_modal_content = get_post_meta($post_id, '_post_checkout_content',true);
		// allows us to place shortcodes in the pre_checkout_content as well
		$thanks_modal_content = do_shortcode($thank_you_modal_content); 
		$thanks_title = get_post_meta($post_id,'_post_checkout_title',true );
		$thanks_description = get_post_meta($post_id,'_post_checkout_description',true);
		
		// if the first products thank you information doesn't exist then get the general informaiont
		if(!$thanks_modal_content){
			// use this instead of the general message, title and description
			$thank_you_modal_content= get_option($this->plugin_name.'_post_checkout_content');
			// allows us to place shortcodes in the pre_checkout_content as well
			$thanks_modal_content = do_shortcode($thank_you_modal_content); 
		}
		if(!$thanks_title){
			$thanks_title = get_option($this->plugin_name.'_post_checkout_title' );
		}
		if(!$thanks_description){
			$thanks_description = get_option($this->plugin_name.'_post_checkout_description');
		}
		$thank_you_modal = array('content'=>$thanks_modal_content,'description'=>$thanks_description,'title'=>$thanks_title);
		return $thank_you_modal;
	}
	public function create_post(){
		if(!check_ajax_referer( 'wpmerchant_create_post', 'security', false )){
			$data['response'] = 'error';
			$data['message'] = 'Error creating product.';
			// Set content type
			header('Content-type: application/json');

			// Prevent caching
			header('Expires: 0');
			echo json_encode($data);
			exit();
		}
		
		$user_id = get_current_user_id();
		if(!$user_id || !current_user_can( 'administrator' )){
			$data['response'] = 'error';
			$data['message'] = 'Error creating product.';
			$data['vars'] = $_POST;
			// Set content type
			header('Content-type: application/json');

			// Prevent caching
			header('Expires: 0');
			echo json_encode($data);
			exit();
		}
		$title = sanitize_text_field($_POST['post_title']);
		if( null != get_page_by_title( $title ) ) {
			// throw an error - choose a new title
			$data['response'] = 'error';
			$data['message'] = 'Choose a new product name.';
			// Set content type
			header('Content-type: application/json');

			// Prevent caching
			header('Expires: 0');
			echo json_encode($data);
			exit();
		} // end if
		require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/class-wpmerchant-helper.php';
		$helper_class = new Wpmerchant_Helper($this->plugin_name);
		$post_name = $helper_class->slugify($title);
		$post_type = sanitize_text_field($_POST['post_type']);
		// next version needs to add wpmerchant_plan to this conditional
		if($post_type != 'wpmerchant_product'){
			// throw an error - can't save a post that is not a wpmerchant_product
			$data['response'] = 'error';
			$data['message'] = 'Error creating product.';
			// Set content type
			header('Content-type: application/json');

			// Prevent caching
			header('Expires: 0');
			echo json_encode($data);
			exit();
		}
		
		
		$post = array(
		  'post_content'   => '', // The full text of the post.
		  'post_name'      => $post_name, // The name (slug) for your post
		  'post_title'     => $title, // The title of your post.
		  'post_status'    => 'publish',//[ 'draft' | 'publish' | 'pending'| 'future' | 'private' | custom registered status ] // Default 'draft'.
		  'post_type'      => $post_type,//[ 'post' | 'page' | 'link' | 'nav_menu_item' | custom post type ] // Default 'post'.
		  'post_author'    => $user_id, // The user ID number of the author. Default is the current user ID.
		  'ping_status'    => 'closed', // Pingbacks or trackbacks allowed. Default is the option 'default_ping_status'.
		  'post_excerpt'   => '', // For all your post excerpt needs.
		  'post_date'      => date('Y-m-d H:i:s'), // The time post was made.
		  'post_date_gmt'  => date('Y-m-d H:i:s') // The time post was made, in GMT.
		);  
		
		//post_name, post_title, post_content, and post_excerpt are required
		$post_id = wp_insert_post($post);
		if(!$post_id){
			// throw an error
			$data['response'] = 'error';
			$data['message'] = 'Error creating product.';
			// Set content type
			header('Content-type: application/json');

			// Prevent caching
			header('Expires: 0');
			echo json_encode($data);
			exit();
		}
		$post['id'] = $post_id;
		if(isset($_POST['_regular_price'])){
			update_post_meta( $post_id, '_regular_price', sanitize_text_field($_POST['_regular_price']));
		}
		if(isset($_POST['_stock_status'])){
			update_post_meta( $post_id, '_stock_status', sanitize_text_field($_POST['_stock_status']) );
		}
		
		$data['response'] = 'success';
		$data['message'] = 'Product Created.';
		$data['link'] = '/wp-admin/admin.php?page=wpmerchant&slide=product-page';
		$data['link_message'] = 'Add a buy button to a page.';
		$data['post'] = $post;
		// Set content type
		header('Content-type: application/json');

		// Prevent caching
		header('Expires: 0');
		echo json_encode($data);
		exit();
		
	}
}
