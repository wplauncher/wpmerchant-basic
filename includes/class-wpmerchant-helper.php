<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       wpmerchant.com/team
 * @since      1.0.0
 *
 * @package    Wpmerchant
 * @subpackage Wpmerchant/helper
 */

/**
 * herlper functions
 *
 *
 * @package    Wpmerchant
 * @subpackage Wpmerchant/helper
 * @author     Ben Shadle <ben@wpmerchant.com>
 */
class Wpmerchant_Helper {
	
	public $plugin_name;
	public $currency_symbol;
	public function __construct($plugin_name) {
		$this->plugin_name = $plugin_name;
	}
	
	public function sendEmail($to, $subject, $content, $headers, $template){
		if($template == 'normal'){
			$logo = get_option('wpmerchant_logo');
			$company_name = get_option('wpmerchant_company_name');
			$body = "<table cellspacing='0' cellpadding='0' border='0' style='color:#333;font:14px/18px 'Helvetica Neue',Arial,Helvetica;background:#fff;padding:0;margin:0;width:100%'> 
				<tbody>
					<tr width='100%'> 
						<td valign='top' align='left' style='background:#ffffff'> 
							<table style='border:none;padding:0 16px;margin:50px auto;width:500px'> 
								<tbody> 
									<tr width='100%' height='60'> 
										<td valign='top' align='center' style='border-top-left-radius:4px;border-top-right-radius:4px;background:#ffffff url('') bottom left repeat-x;padding:10px 16px;text-align:center'> 
											<img src='".$logo."' title='".$company_name."' style='font-weight:bold;font-size:18px;color:#fff;vertical-align:top'> 
										</td> 
									</tr> 
									<tr width='100%'> 
										<td valign='top' align='left' style='border-bottom-left-radius:4px;border-bottom-right-radius:4px;background:#fff;padding:18px 16px'>
											<h1 style='margin-top:0'>".$content['title']."</h1> <hr style='clear:both;min-height:1px;border:0;border:none;width:100%;background:#dcdcdc;color:#dcdcdc;margin:16px 0;padding:0'> 
											<div> 
												".$content['body']."
												<br style='clear:both'> 
											</div> 
											<hr style='clear:both;min-height:1px;border:0;border:none;width:100%;background:#dcdcdc;color:#dcdcdc;margin:16px 0;padding:0'> 
										</td> 
									</tr> 
									<!--<tr width='100%'> <td valign='top' align='left' style='padding:16px'> <p style='color:#999'>Control how often you receive notification emails on your <a href='https://templatelauncher.com//' target='_blank'>account page</a>.</p> <p style='color:#999'>Follow <a href='https://twitter.com/intent/follow?user_id=' target='_blank'>@</a> on Twitter or like us on <a href='https://www.facebook.com/' target='_blank'>Facebook</a></p> <p style='color:#999'></p> 
											</td> </tr> -->
								</tbody> 
							</table> 
						</td> 
					</tr>
				</tbody> 
			</table>";
		}
		add_filter( 'wp_mail_content_type', array($this,'set_html_content_type'));
		wp_mail( $to, $subject, $body, $headers );
		remove_filter( 'wp_mail_content_type', array($this,'set_html_content_type'));
	}
	public function logError($data){
		$to = 'support@wpmerchant.com';
		$subject = $data['subject'];
		$content['title'] = __('Error Info', $this->plugin_name);
 		$content['body'] = '<strong>'.__('Response:', $this->plugin_name).'</strong>'.__($data['response'],$this->plugin_name).'<br>
			<br><strong>'.__('Response:', $this->plugin_name).'</strong>'.__($data['response'],$this->plugin_name).'<br>
		<br><strong>'.__('Line:', $this->plugin_name).'</strong>'.$data['line'].'<br>
		<br><strong>'.__('Full:', $this->plugin_name).'</strong>'.__($data['full'],$this->plugin_name).'<br>
		<br><strong>'.__('Message:', $this->plugin_name).'</strong>'.__($data['message'],$this->plugin_name).'<br>
		<br><strong>'.__('Message2:', $this->plugin_name).'</strong>'.__($data['message2'],$this->plugin_name).'<br>
		<br><strong>'.__('Action:', $this->plugin_name).'</strong>'.__($data['action'],$this->plugin_name).'<br>
		<br><strong>'.__('Date:', $this->plugin_name).'</strong>'.date_i18n('M j, Y @ G:i', strtotime( 'now' ) ).'<br>';
		$template = 'normal';
		$this->sendEmail($to, $subject, $content, $headers, $template);
	}
	public function set_html_content_type(){
		return 'text/html';
	}
	
	public function slugify($text)
	{
		// Thanks to http://stackoverflow.com/users/20010/mez - http://stackoverflow.com/questions/3984983/php-code-to-generate-safe-url
	    // Swap out Non "Letters" with a -
	    $text = preg_replace('/[^\\pL\d]+/u', '-', $text); 

	    // Trim out extra -'s
	    $text = trim($text, '-');

	    // Convert letters that we have left to the closest ASCII representation
	    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

	    // Make text lowercase
	    $text = strtolower($text);

	    // Strip out anything we haven't been able to convert
	    $text = preg_replace('/[^-\w]+/', '', $text);

	    return $text;
	}
	public function get_currency_symbol(){
		if(!$this->currency_symbol){
			$currency1 = get_option( $this->plugin_name.'_currency' );
			$currency = WPMerchant_Admin::get_currency_details($currency1);
			$currency_symbol = ($currency['symbol']) ? $currency['symbol'] : $currency['value'];
			$this->currency_symbol = $currency_symbol;
		}
		return $this->currency_symbol;
	}
}
