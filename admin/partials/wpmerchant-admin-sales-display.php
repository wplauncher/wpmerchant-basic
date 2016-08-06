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
<script src="<?= $chart_js_src ?>"></script>
<div class="wrap">
		            <?php settings_errors(); ?>  
					<h1 style="padding-top:10px;padding-bottom:10px;text-align:center;">Sales</h1>
					<div class="sales_container" style="text-align:right;margin-left:10%;width:80%;margin-right:10%;">
						<input type="date"> to <input type="date">
					</div>
					 <div class="chartContainer" style="margin-left:10%;width:80%;margin-right:10%;margin-bottom:20px;"><canvas id="salesChart"></canvas></canvas></div>
					<div class="sales_container" style="margin-left:10%;width:80%;margin-right:10%;">
						<ul class="wpm_well" style="width:96%;float:left;padding:2%;text-align:center;">
							<li style="font-weight:bold;padding: 5px 0px 5px;margin-bottom: 10px;font-size:20px;" >$220,000</li>
							<li style="font-weight:bold;padding: 5px 0px 5px;margin-bottom: 0px;" >Total Sales</li>
						</ul>
					</div>
					<div class="sales_container" style="margin-left:10%;width:80%;margin-right:10%;">
						<ul class="wpm_well" style="width:28%;float:left;padding:2%;text-align:center;">
							<li style="font-weight:bold;padding: 5px 0px 5px;margin-bottom: 10px;font-size:20px;" >$22</li>
							<li style="font-weight:bold;padding: 5px 0px 5px;margin-bottom: 0px;" >Today's Sales</li>
						</ul>
						<ul class="wpm_well" style="width:28%;margin-left:2%;float:left;padding:2%;text-align:center;">
							<li style="font-weight:bold;padding: 5px 0px 5px;margin-bottom: 10px;font-size:20px;" >$220</li>
							<li style="font-weight:bold;padding: 5px 0px 5px;margin-bottom: 0px;" >Weekly Sales</li>
						</ul>
						<ul class="wpm_well" style="width:28%;margin-left:2%;float:left;padding:2%;text-align:center;">
							<li style="font-weight:bold;padding: 5px 0px 5px;margin-bottom: 10px;font-size:20px;" >$2,200</li>
							<li style="font-weight:bold;padding: 5px 0px 5px;margin-bottom: 0px;" >Monthly Sales</li>
						</ul>
						
					</div>
					<?php 
		             
						/*$image_class = 'sales';
						$header = __('Sales',$this->plugin_name);
						$description = __('Check out yo sales.',$this->plugin_name).' <a href="https://dashboard.stripe.com" target="_blank" class="arrow">'.__('See more',$this->plugin_name).'</a>';
						$btn = '<a class="btn wpm_no_decoration" href="https://www.wpmerchant.com">'.__('WPMerchant',$this->plugin_name).'</a>';
						echo '<div class="no-data-img '.$image_class.'"></div><h2>'.$header.'</h2><p class="slider_description">'.$description.'</p><div class="controls"><p>'.$btn.'</p></div>';*/
					?>
				
</div>