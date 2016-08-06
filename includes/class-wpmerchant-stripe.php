<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       wpmerchant.com/team
 * @since      1.0.0
 *
 * @package    Wpmerchant
 * @subpackage Wpmerchant/stripe
 */

/**
 * The stripe functionality of the plugin.
 *
 *
 * @package    Wpmerchant
 * @subpackage Wpmerchant/stripe
 * @author     Ben Shadle <ben@wpmerchant.com>
 */
class Wpmerchant_Stripe {
	public $payment_processor_config;
	public $plugin_name;
	public $helper_class;
	public function __construct($plugin_name) {
		require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/class-wpmerchant-helper.php';
		$this->helper_class = new Wpmerchant_Helper($plugin_name);
		require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/stripe-php-2.2.0/init.php';
		$this->plugin_name = $plugin_name;
		$payment_processor_config['status'] = get_option($this->plugin_name.'_stripe_status' );
		
		if($payment_processor_config['status'] == 'live'){
			$payment_processor_config['secret_key'] = get_option( $this->plugin_name.'_stripe_live_secret_key' );
			$payment_processor_config['public_key'] = get_option( $this->plugin_name.'_stripe_live_public_key' );
		} else {
			$payment_processor_config['secret_key'] = get_option( $this->plugin_name.'_stripe_test_secret_key' );
			$payment_processor_config['public_key'] = get_option( $this->plugin_name.'_stripe_live_public_key' );
		}
		$this->payment_processor_config = $payment_processor_config;
		$this->call('setApiKey',$payment_processor_config);

	}
	public function call($action, $data){
		$helper_class = $this->helper_class;
		$currency1 = get_option( $this->plugin_name.'_currency' );
		$currency2 = Wpmerchant_Admin::get_currency_details($currency1);
		$currency = $currency2['value'];
		try {
			switch ($action) {
				case 'setApiKey':
					\Stripe\Stripe::setApiKey($data['secret_key']);
					break;
				case 'getCustomer':
					$stripeCustomer = \Stripe\Customer::retrieve($data['stripe_id']);
					break;
				case 'addCustomer':
					$stripeCustomer = \Stripe\Customer::create(array(
					  "email" => $data['email'],
					  "metadata" => array("name" =>$data['name'])));
					  //"source" => $data['token'],
					break;
					case 'addCard':
						$card = $data['customer']->sources->create(array("source" => $data['token']));
						break;
				case 'addCharge':
					$charge = \Stripe\Charge::create(array(
					  "amount" => $data['amount'],
					  "currency" => $currency,
					  "source" => $data['card']->id, // obtained with Stripe.js,
					  "customer"=>$data['customer'],
					  "metadata" => $data['metadata'],
					  "description" => $data['description']
					));
					break;
				default:
					# code...
					break;
			}
		  
		} catch(\Stripe\Error\Card $e) {
		  // Since it's a decline, \Stripe\Error\Card will be caught
		  /*$body = $e->getJsonBody();
		  $err  = $body['error'];

		  print('Status is:' . $e->getHttpStatus() . "\n");
		  print('Type is:' . $err['type'] . "\n");
		  print('Code is:' . $err['code'] . "\n");
		  // param is '' in this case
		  print('Param is:' . $err['param'] . "\n");
		  print('Message is:' . $err['message'] . "\n");*/
		  	$body = $e->getJsonBody();
		  	$err  = $body['error'];
			$data['action'] = $action;
			$data['response'] = 'error';
			$data['line'] = __LINE__;
			if($err){
				$data['full'] = serialize($err);
				$data['message'] =  $err['message'];
			} else {
				$data['full'] = '';
				$data['message'] =  '';
			}
			$data['message2'] = '';
			// Set content type
			header('Content-type: application/json');

			// Prevent caching
			header('Expires: 0');
			echo json_encode($data);
			exit();
		} catch (\Stripe\Error\InvalidRequest $e) {
		  // Invalid parameters were supplied to Stripe's API
		  	$body = $e->getJsonBody();
		  	$err  = $body['error'];
			$data['action'] = $action;
			$data['response'] = 'error';
			$data['line'] = __LINE__;
			if($err){
				$data['full'] = serialize($err);
				$data['message'] =  $err['message'];
			} else {
				$data['full'] = '';
				$data['message'] =  '';
			}
			$data['message2'] =  'Invalid parameters were supplied to Stripe\'s API';
			$data['subject'] =  'Stripe Error';
			switch ($action) {
				default:
					$helper_class->logError($data);
					// Set content type
					header('Content-type: application/json');

					// Prevent caching
					header('Expires: 0');
					echo json_encode($data);
					exit();
					break;
			}
		} catch (\Stripe\Error\Authentication $e) {
		  // Authentication with Stripe's API failed
		  // (maybe you changed API keys recently)
		  	$body = $e->getJsonBody();
		  	$err  = $body['error'];
			$data['action'] = $action;
			$data['response'] = 'error';
			$data['line'] = __LINE__;
			if($err){
				$data['full'] = serialize($err);
				$data['message'] =  $err['message'];
			} else {
				$data['full'] = '';
				$data['message'] =  '';
			}
			//$data['message2'] =  'Authentication with Stripe\'s API failed';
			$stripeAuth =  false;
			// Set content type
			/*header('Content-type: application/json');

			// Prevent caching
			header('Expires: 0');
			echo json_encode($data);
			exit();*/
		} catch (\Stripe\Error\ApiConnection $e) {
		  // Network communication with Stripe failed
		  	$body = $e->getJsonBody();
		  	$err  = $body['error'];
			$data['action'] = $action;
			$data['response'] = 'error';
			$data['line'] = __LINE__;
			if($err){
				$data['full'] = serialize($err);
				$data['message'] =  $err['message'];
			} else {
				$data['full'] = '';
				$data['message'] =  '';
			}
			$data['message2'] =  'Network communication with Stripe failed';
			$data['subject'] =  'Stripe Error';
			$helper_class->logError($data);
			// Set content type
			header('Content-type: application/json');

			// Prevent caching
			header('Expires: 0');
			echo json_encode($data);
			exit();
		} catch (\Stripe\Error\Base $e) {
		  // Display a very generic error to the user, and maybe send
		  // yourself an email
		  	$body = $e->getJsonBody();
		  	$err  = $body['error'];
			$data['action'] = $action;
			$data['response'] = 'error';
			$data['line'] = __LINE__;
			if($err){
				$data['full'] = serialize($err);
				$data['message'] =  $err['message'];
			} else {
				$data['full'] = '';
				$data['message'] =  '';
			}
			$data['message2'] =  'General Error';
			$data['subject'] =  'Stripe Error';
			$helper_class->logError($data);
			// Set content type
			header('Content-type: application/json');

			// Prevent caching
			header('Expires: 0');
			echo json_encode($data);
			exit();
		} catch (Exception $e) {
		  // Something else happened, completely unrelated to Stripe
		  	$body = $e->getJsonBody();
		  	$err  = $body['error'];
			$data['action'] = $action;
			$data['response'] = 'error';
			$data['line'] = __LINE__;
			if($err){
				$data['full'] = serialize($err);
				$data['message'] =  $err['message'];
			} else {
				$data['full'] = '';
				$data['message'] =  '';
			}
			$data['message2'] =  'General Other Error';
			$data['subject'] =  'Stripe Error';
			$helper_class->logError($data);
			// Set content type
			header('Content-type: application/json');

			// Prevent caching
			header('Expires: 0');
			echo json_encode($data);
			exit();
		}
		switch ($action) {
			case 'getCustomer':
			case 'addCustomer':
				return $stripeCustomer;
				break;
			case 'addCard':
				return $card;
				break;
			case 'addCharge':
				return $charge;
				break;
			default:
				# code...
				break;
		}
		
	}
}
