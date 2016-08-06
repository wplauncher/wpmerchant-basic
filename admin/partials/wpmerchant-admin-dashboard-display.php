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
				<div id="no-data-view">
					<?php settings_errors(); ?>  
		            <?php 
		            if( $active_slide == 'payments' ) {  
						$image_class = 'payments';
						$header = __('Payments',$this->plugin_name);
						$description = __('You haven\'t linked any payment processors.',$this->plugin_name).' <a href="https://www.wpmerchant.com" target="_blank" class="arrow">'.__('Learn more',$this->plugin_name).'</a>';
						$btn = '';
					    $this->dashboard_slide_contents($image_class, $header, $description, $btn);
		            } elseif( $active_slide == 'newsletters' ) {
						$image_class = 'newsletters';
						$header = __('Newsletters',$this->plugin_name);
						$description = __('You haven\'t linked any newsletter providers.',$this->plugin_name).' <a href="https://www.wpmerchant.com" target="_blank" class="arrow">'.__('Learn more',$this->plugin_name).'</a>';
						$btn = '';
					    $this->dashboard_slide_contents($image_class, $header, $description, $btn);
		            }  elseif( $active_slide == 'newsletter-list' ) {
						$image_class = 'newsletter-list';
						$header = __('Newsletter List',$this->plugin_name);
						$description = __('You haven\'t selected a newsletter list to subscribe customers to when a purchase is made.',$this->plugin_name).' <a href="https://www.wpmerchant.com" target="_blank" class="arrow">'.__('Learn more',$this->plugin_name).'</a>';
						$btn = '';
					    $this->dashboard_slide_contents($image_class, $header, $description, $btn);
		            } elseif( $active_slide == 'products' ) {
						$image_class = 'products';
						$header = __('Products',$this->plugin_name);
						$description = __('You haven\'t created any products.',$this->plugin_name).' <a href="https://www.wpmerchant.com" target="_blank" class="arrow">'.__('Learn more',$this->plugin_name).'</a>';
						$btn = '';
					    $this->dashboard_slide_contents($image_class, $header, $description, $btn);
		            } elseif( $active_slide == 'subscriptions' ) {
				   						$image_class = 'subscriptions';
				   						$header = __('Subscriptions',$this->plugin_name);
				   						$description = __('You need to upgrade to create subscriptions.',$this->plugin_name).' <a href="https://www.wpmerchant.com" target="_blank" class="arrow">'.__('Learn more',$this->plugin_name).'</a>';
										//$image = plugin_dir_url( __FILE__ ).'public/img/plus-sign.png';
										$btn = __('<a class="btn wpm_no_decoration" href="https://www.wpmerchant.com">Upgrade WPMerchant</a>',$this->plugin_name);
				   					    $this->dashboard_slide_contents($image_class, $header, $description, $btn);
				    } elseif($active_slide == 'product-page'){
   						$image_class = 'product-page';
   						$header = __('Add Buy Button to Page',$this->plugin_name);
   						$description = __('Add a buy button for an existing product to a page.',$this->plugin_name).' <a href="https://www.wpmerchant.com" target="_blank" class="arrow">'.__('Learn more',$this->plugin_name).'</a>';
						//$image = plugin_dir_url( __FILE__ ).'public/img/plus-sign.png';
						$btn = '';
   					    $this->dashboard_slide_contents($image_class, $header, $description, $btn);
				    } elseif($active_slide == 'settings'){
				      						$image_class = 'settings';
				      						$header = __('Change WPMerchant Settings',$this->plugin_name);
				      						$description = __('Now that you know how WPMerchant works, you can update and change additional settings on the WPMerchant Settings page.',$this->plugin_name).' <a href="/wp-admin/admin.php?page=wpmerchant-settings" target="_blank" class="arrow">'.__('Learn more',$this->plugin_name).'</a>';
				   						//$image = plugin_dir_url( __FILE__ ).'public/img/plus-sign.png';
				   						$btn = '<a target="_blank" class="btn wpm_no_decoration" href="/wp-admin/admin.php?page=wpmerchant-settings">'.__('WPMerchant Settings',$this->plugin_name).'</a>';
				      					    $this->dashboard_slide_contents($image_class, $header, $description, $btn);
   				    } elseif($active_slide == 'product-settings'){
				   				      						$image_class = 'product-settings';
				   				      						$header = __('Change Product Settings',$this->plugin_name);
				   				      						$description = __('Now that you know how WPMerchant works, you can update and change additional settings for each Product on the Edit Product page.',$this->plugin_name).' <a href="/wp-admin/admin.php?page=/wp-admin/edit.php?post_type=wpmerchant_product" target="_blank" class="arrow">'.__('Learn more',$this->plugin_name).'</a>';
				   				   						//$image = plugin_dir_url( __FILE__ ).'public/img/plus-sign.png';
				   				   						$btn = '<a class="btn wpm_no_decoration wpm-admin-modal-btn">'.__('Edit WPMerchant Products', $this->plugin_name).'</a>';
				   				      					    $this->dashboard_slide_contents($image_class, $header, $description, $btn);
				   	}
		            ?>
				</div>
				<div class="wpm_bullets">
		            <a class="<?= ( $active_slide == 'payments' ) ? 'active' : ''; ?>" href="/wp-admin/admin.php?page=wpmerchant&slide=payments"></a>
		            <a class="<?= ( $active_slide == 'newsletters' ) ? 'active' : ''; ?>" href="/wp-admin/admin.php?page=wpmerchant&slide=newsletters"></a>
					<a class="<?= ( $active_slide == 'newsletter-list' ) ? 'active' : ''; ?>" href="/wp-admin/admin.php?page=wpmerchant&slide=newsletter-list"></a>
					<a class="<?= ( $active_slide == 'products' ) ? 'active' : ''; ?>" href="/wp-admin/admin.php?page=wpmerchant&slide=products"></a>
					<a class="<?= ( $active_slide == 'product-page' ) ? 'active' : ''; ?>" href="/wp-admin/admin.php?page=wpmerchant&slide=product-page"></a>
					<a class="<?= ( $active_slide == 'subscriptions' ) ? 'active' : ''; ?>" href="/wp-admin/admin.php?page=wpmerchant&slide=subscriptions"></a>
					<a class="<?= ( $active_slide == 'product-settings' ) ? 'active' : ''; ?>" href="/wp-admin/admin.php?page=wpmerchant&slide=product-settings"></a>
					<a class="<?= ( $active_slide == 'settings' ) ? 'active' : ''; ?>" href="/wp-admin/admin.php?page=wpmerchant&slide=settings"></a>
				</div>
				<?php if( $active_slide == 'products' ): ?>
				<div id="wpm-admin-container"><div class="wpm_admin_modal"><div class="wpm_admin_header"><div class="wpm-admin-header-container"><h1><?= __('Add a Product',$this->plugin_name) ?></h1><a class="close dashicons dashicons-no"></a></div></div><div class="wpm_admin_body"><div class="wpm_clear"><form name="wpm_create_post"><?= $this->create_post_form('wpmerchant_product') ?></form></div></div><div class="wpm_admin_footer"><div class="wpm-admin-button-container"><a class="button button-primary wpm-admin-button wpm-create-post-button"><?= __('Save',$this->plugin_name) ?></a></div></div></div></div>
			<?php elseif($active_slide == 'product-page' ): ?>
				<div id="wpm-admin-container"><div class="wpm_admin_modal"><div class="wpm_admin_header"><div class="wpm-admin-header-container"><h1><?= __('Add Buy Button to Page',$this->plugin_name) ?></h1><a class="close dashicons dashicons-no"></a></div></div><div class="wpm_admin_body"><div class="wpm_clear"><form name="wpm_update_post"><?php $this->update_post_form(); ?></form></div></div><div class="wpm_admin_footer"><div class="wpm-admin-button-container"><a class="button button-primary wpm-admin-button wpm-update-post-button"><?= __('Save',$this->plugin_name) ?></a></div></div></div></div>
				<?php $this->localize_tinymce_script(); ?>
			<?php elseif($active_slide == 'product-settings' ): ?>
				<div id="wpm-admin-container"><div class="wpm_admin_modal"><div class="wpm_admin_header"><div class="wpm-admin-header-container"><h1><?= __('Go to Edit Product Page',$this->plugin_name) ?></h1><a class="close dashicons dashicons-no"></a></div></div><div class="wpm_admin_body"><div class="wpm_clear"><form name="wpm_edit_product"><?php $this->product_settings_form(); ?></form></div></div><div class="wpm_admin_footer"><div class="wpm-admin-button-container"><a class="button button-primary wpm-admin-button wpm-admin-close-btn"><?= __('Done',$this->plugin_name) ?></a></div></div></div></div>
				<?php endif; ?>
				
</div>