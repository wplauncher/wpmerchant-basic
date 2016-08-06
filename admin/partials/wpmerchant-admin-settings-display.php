<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       wpmerchant.com/team
 * @since      1.0.0
 *
 * @package    Wpmerchant
 * @subpackage Wpmerchant/admin/partials
 */
?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
		        <div id="icon-themes" class="icon32"></div>  
		        <h2>WPMerchant Settings</h2>  
		         <!--NEED THE settings_errors below so that the errors/success messages are shown after submission - wasn't working once we started using add_menu_page and stopped using add_options_page so needed this-->
				<?php settings_errors(); ?>  
				<?php /*active tab variable set in the class-wpmerchant-admin.php*/ ?>
		        <h2 class="nav-tab-wrapper">  
		            <a href="?page=wpmerchant-settings&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>">General</a>  
		            <a href="?page=wpmerchant-settings&tab=payment" class="nav-tab <?php echo $active_tab == 'payment' ? 'nav-tab-active' : ''; ?>">Payment</a> 
					<a href="?page=wpmerchant-settings&tab=emails" class="nav-tab <?php echo $active_tab == 'emails' ? 'nav-tab-active' : ''; ?>">Email List</a>
					<a href="?page=wpmerchant-settings&tab=post-checkout" class="nav-tab <?php echo $active_tab == 'post-checkout' ? 'nav-tab-active' : ''; ?>">After Checkout</a>
					<a href="?page=wpmerchant-settings&tab=external-services" class="nav-tab <?php echo $active_tab == 'external-services' ? 'nav-tab-active' : ''; ?>">External Services</a>
		        </h2>  


		        <form method="POST" action="options.php">  
		            <?php 
		            if( $active_tab == 'general' ) {  
		                settings_fields( 'wpmerchant_general_settings' );
		                do_settings_sections( 'wpmerchant_general_settings' ); 
		            } else if( $active_tab == 'payment' ) {
		                settings_fields( 'wpmerchant_stripe_settings' );
		                do_settings_sections( 'wpmerchant_stripe_settings' ); 

		            } else if( $active_tab == 'emails' ) {
		                settings_fields( 'wpmerchant_mailchimp_settings' );
		                do_settings_sections( 'wpmerchant_mailchimp_settings' ); 

		            } else if($active_tab == 'post-checkout' ){
		                settings_fields( 'wpmerchant_post_checkout_settings' );
		                do_settings_sections( 'wpmerchant_post_checkout_settings' ); 
		            } else if($active_tab == 'external-services' ){
						/*Wpmerchant_Public::create_public_purchase_script();	*/
						
						echo '<h1>Using on a Page that Doesn\'t Interact with WordPress</h1>';
						echo '<p>An example of this is LeadPages.net</p>';
						echo '<p>1. Add a link to your page and make the href equal #wpmerchant-{PRODUCT_ID}.  The PRODUCT_ID must be associated with a product that you have created with WPMerchant.';
						echo '<p>2. Copy the code below and include it on the page that you want to have purchasing functionality.</p>';
						echo '<p>';
						$public_script = $this->create_public_purchase_script();
						echo $public_script;
						echo '</p>';
		            } 
		            ?>
					<?php if($active_tab != 'external-services'): ?>             
		            <?php submit_button(); ?>  
					<?php endif ;?>
		        </form> 
</div>