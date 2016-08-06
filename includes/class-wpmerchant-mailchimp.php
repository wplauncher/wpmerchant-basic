<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       wpmerchant.com/team
 * @since      1.0.0
 *
 * @package    Wpmerchant
 * @subpackage Wpmerchant/mailchimp
 */

/**
 * The mailchimp functionality of the plugin.
 *
 *
 * @package    Wpmerchant
 * @subpackage Wpmerchant/mailchimp
 * @author     Ben Shadle <ben@wpmerchant.com>
 */
class Wpmerchant_MailChimp {
	public $email_list_processor_config;
	public $EmailAPI;
	public $plugin_name;
	public function __construct($plugin_name) {
		require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/MailChimp-API-Class/mail_chimp.php';
		$this->plugin_name = $plugin_name;
		$email_list_processor_config['apiKey'] = get_option( $this->plugin_name.'_mailchimp_api' );
		$email_list_processor_config['genListId'] = get_option( $this->plugin_name.'_mailchimp_gen_list_id' );
		$this->email_list_processor_config = $email_list_processor_config;
		//Wpmerchant_MailChimp::call('setApiKey',$payment_processor_config);
		$this->EmailAPI = new MailChimp($email_list_processor_config['apiKey']);
	}
	public function call($action, $data){
		// Morning Meditation Option - because last and first and zip are included (and in the others it isn't)
		// this file is included in the config.php file

		// subscribe the user to the mettagroup contacts adn morning meditation 1 A lists

		/*$result = $MailChimp->call('lists/subscribe', array(
		                'id'                => $mc['MMListId'],
		                'email'             => array('email'=>$email),
		                'merge_vars'        => array('FNAME'=>trim(strip_tags($_POST['firstName'])), 'LNAME'=>trim(strip_tags($_POST['lastName']))),
		                'double_optin'      => false,
		                'update_existing'   => true,
		                'replace_interests' => false,
		                'send_welcome'      => false,
		            ));*/
			$MailChimp = $this->EmailAPI;
			switch ($action) {
				case 'listSubscribe':
					if(isset($data['grouping_name'])){
						$groupings['name'] = $data['grouping_name'];
					}
					if(isset($data['group_name'])){
						$groupings['groups'] = array($data['group_name']);
					} 
					if(isset($groupings)){
						$merge_vars = array(
									'FNAME'=>$data['first_name'], 
									'LNAME'=>$data['last_name'], 
									'groupings'=>array(
		                               $groupings
		                             ));
					} else {
						$merge_vars = array(
									'FNAME'=>$data['first_name'], 
									'LNAME'=>$data['last_name']
								);
					}
					$result = $MailChimp->call('lists/subscribe', array(
				                'id'                => $data['list_id'],
				                'email'             => array('email'=>$data['email']),
				                'merge_vars'        => $merge_vars,
								'double_optin'      => false,
				                'update_existing'   => true,
				                'replace_interests' => true,
				                'send_welcome'      => false,
				            ));
					break;
				case 'addInterestGrouping':
					$result = $MailChimp->call('lists/interest-grouping-add', array(
						'id'=> $data['list_id'],
						'name'=>$data['name']
					));
					break;
				case 'getInterestGroups':
					$result = $MailChimp->call('lists/interest-groupings', array('id'=> $data['list_id']));
					break;
				case 'getLists':
					$result = $MailChimp->call('lists/list', array('limit'=>100));
					break;
				default:
					# code...
					break;
			}
			if(isset($result['status']) && $result['status'] == 'error'){
				$data['action'] = $action;
				$data['response'] = 'error';
				$data['line'] = __LINE__;
				if($err){
					$data['full'] = serialize($result);
					$data['message'] =  $result['status'];
				} else {
					$data['full'] = '';
					$data['message'] =  '';
				}
				$data['subject'] =  'MailChimp Error';
				$data['message2'] =  'General Error';
				Wpmerchant_Helper::logError($data);	
				// don't throw an error to the user because			
				return $result;
			} else {
				return $result;
			}
			
	}
}
