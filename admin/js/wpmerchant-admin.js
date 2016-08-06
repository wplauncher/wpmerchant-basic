(function( $ ) {
	'use strict';
	var WPMerchantAdmin = {
		construct:function(){
			$(function() {
				
				 if(location.pathname.search('wp-admin/post.php') != -1 || location.pathname.search('wp-admin/post-new.php') != -1){
					  /*This allows us to use the links as tabs to show the different fields in hte Product Data metabox */
					  $('.product_container_tabs a').click(function(){
						  // hide all tab content
						  $('.product_field_containers').each(function( index ) {
							   $( this ).parent().removeClass("wpm_show").addClass("wpm_hide");
						  });
						  // remove active classes from all li parenst of a links
						  $('.product_container_tabs li').each(function( index ) {
							   $( this ).removeClass("active");
						  });
						  //show the tab content that is clicked and add the active class to the li
						  var href = $(this).data("href");
						  $(this).parent().addClass('active');
						  $(href).removeClass('wpm_hide').addClass('wpm_show');
					  })
		  			  $('#wpmerchant_interval').change(function() {
		  					if($( this ).val() == 'day'){
		  						$('#wpmerchant_interval_count').attr('max','365');
		  					} else if($( this ).val() == 'week'){
		  						$('#wpmerchant_interval_count').attr('max','52');
		  					} else if($( this ).val() == 'month'){
		  						$('#wpmerchant_interval_count').attr('max','12');
		  					} else if($( this ).val() == 'year'){
		  						$('#wpmerchant_interval_count').attr('max','1');
		  					}
		  			  });
					  
					   if($("#wpmerchant_order_data_meta_box").length > 0){
						   $('#post-body-content').css("display","none");
						   $('[name="_shipping_state"]').select2();
						   $('[name="_shipping_country"]').select2();
						   $('[name="_billing_state"]').select2();
						   $('[name="_billing_country"]').select2();
						   $('[name="_order_status"]').select2();
						   $('[name="_customer"]').select2();
						   if(location.pathname.search('wp-admin/post-new.php') != -1){
							   $('.wpm_order_number').parent().remove();
						   } else {
							   var order_title = $('[name="post_title"]').val();
						   		$('.wpm_order_number').text(order_title);
						   }
						   $('.wpm-copy-billing-address').click(WPMerchantAdmin.copyBillingAddress);
						   $('.wpm-edit-billing').click(WPMerchantAdmin.showBillingFields)
						   $('.wpm-edit-shipping').click(WPMerchantAdmin.showShippingFields)
					   }
					   // remove hte footer because it goes over top of hte order item meta box and looks janky
					   $('#wpfooter').remove()
				  } else if(location.pathname.search('wp-admin/admin.php') != -1){
					  if(WPMerchantAdmin.getQueryVariable('page') == 'wpmerchant-settings' || WPMerchantAdmin.getQueryVariable('page') == 'wpmerchant'){
						  $('[name="wpmerchant_currency"]').select2();
						  $('[name="wpmerchant_payment_processor"]').select2();
						  $('[name="wpmerchant_email_list_processor"]').select2();
						  if(WPMerchantAdmin.getQueryVariable('tab') == 'emails' || WPMerchantAdmin.getQueryVariable('slide') == 'newsletter-list'){
	  						if($('.mailchimp-login').length <= 0){
	  							WPMerchantAdmin.getEmailData();
	  							$("#mailchimp-log-out").bind('click',WPMerchantAdmin.clearMailchimpAPI);
	  						}
		  					  if(WPMerchantAdmin.getQueryVariable('slide') == 'newsletter-list'){
		  						  $('[name="wpmerchant_mailchimp_gen_list_id"]').change(WPMerchantAdmin.updateEmailList);
						
		  					  }
						  }
						if($('.stripe-login').length <= 0){
							$("#stripe-log-out").bind('click',WPMerchantAdmin.clearStripeAPI);
						}  
	  					  if($('.wpm-admin-modal-btn').length > 0){
	  						  $('.wpm-admin-modal-btn').click(WPMerchantAdmin.adminModalOn);
	  					  }
					  } else if(WPMerchantAdmin.getQueryVariable('page') == 'wpmerchant-sales'){
						  WPMerchantAdmin.salesChart();
					  }
					  
					  
						
				  }
				 
 		 	 });
 		},
		clearMailchimpAPI: function(event){
			event.preventDefault();
			var clear= '';
			$("#wpmerchant_mailchimp_api").val(clear);
			$("#wpmerchant_mailchimp_gen_list_id option:selected").each(function() {
				$( this ).removeAttr('selected');
		    });
			$("#submit").click();
		},
		clearStripeAPI: function(event){
			event.preventDefault();
			var clear= '';
			$("#wpmerchant_stripe_test_public_key").val(clear);
			$("#wpmerchant_stripe_test_secret_key").val(clear);
			$("#wpmerchant_stripe_live_public_key").val(clear);
			$("#wpmerchant_stripe_live_secret_key").val(clear);
			
			$("#submit").click();
		},
		copyBillingAddress: function(){
			$('[name="_shipping_first_name"]').val($('[name="_billing_first_name"]').val());
			$('[name="_shipping_last_name"]').val($('[name="_billing_last_name"]').val());
			$('[name="_shipping_company"]').val($('[name="_billing_company"]').val());
			$('[name="_shipping_address_1"]').val($('[name="_billing_address_1"]').val());
			$('[name="_shipping_address_2"]').val($('[name="_billing_address_2"]').val());
			$('[name="_shipping_city"]').val($('[name="_billing_city"]').val());
			$('[name="_shipping_state"]').val($('[name="_billing_state"]').val()).trigger('change');
			$('[name="_shipping_country"]').val($('[name="_billing_country"]').val()).trigger('change');
			$('[name="_shipping_postcode"]').val($('[name="_billing_postcode"]').val()).trigger('change');
		},
		getEmailData: function(){ 
		  var dataString = "action=wpmerchant_get_email_data&security="+encodeURIComponent(wpm_ajax_object.get_email_data_nonce);
		  console.log(wpm_ajax_object);
		  console.log('getEmailData')
			$.ajax({
				url: wpm_ajax_object.ajax_url,  
				type: "GET",
				  data: dataString,
				  dataType:'json',
				  success: function(data){
				    if(data.response == 'success'){
					   console.log('success')
						var options = '';
						var existingValue = $("#wpmerchant_mailchimp_gen_list_id").data("value")
						for (var i = 0; i < data.lists.length; i++) { 
							if(data.lists[i].value == existingValue){
								var selected = 'selected'
							} else {
								var selected = '';
							}
						    options += '<option '+selected+' value="'+data.lists[i].value+'">'+data.lists[i].name+'</option>';
						}
						console.log(options)
						// this is just for hte polling version
						//$("#wpmerchant_mailchimp_gen_list_id").parent().siblings('th').text('General Interest List ID');
						//$("#wpmerchant_mailchimp_gen_list_id").css("display","block");
						$("#wpmerchant_mailchimp_gen_list_id").html(options);		
						$("#wpmerchant_mailchimp_gen_list_id").select2();					
			   	   } else if(data.response == 'empty'){
					    console.log(data)
					   // polling to see if the key has been received or not
					   // this response is only returned if no api key exists - so keep running it until we get one
					   //WPMerchantAdmin.getEmailData();
				   } else if(data.response == 'error'){
					   // number of polls has gone over the limit so we throw this instead of empty - prevent polling from continuing
				   	   console.log(data)
				   }
				  console.log( data );
				  },
				error: function(jqXHR, textStatus, errorThrown) { 
					console.log(jqXHR, textStatus, errorThrown); 
				    console.log('no lists')
					//$(".planExistsStatus").css("display","block");
					//$(".dashicon-container").empty().append('<span class="dashicons dashicons-no" style="color:#a00;"></span>');
				}
			});
		},
		adminModalOff:function(){
			$('#wpm-admin-container').css('display','none');
			$('.wpm_admin_modal').css('display','none').css('opacity','0');
		},
		adminModalOn:function(){
			$('#wpm-admin-container').css('display','block');
			$('.wpm_admin_modal').css('display','block').css('opacity','1');
			$('.wpm-admin-header-container').find('.close').click(WPMerchantAdmin.adminModalOff)
			if($('.wpm-create-post-button').length > 0){
				$('.wpm-create-post-button').click(WPMerchantAdmin.createPost);
			}
			if($('.wpm-update-post-button').length > 0){
				//var textAreaID = 'sectionContent_'+sectionID;           
				/*tinymce.execCommand('mceAddEditor', true, 'post_content');
				tinyMCE.execCommand('mceAddEditor', false, $('form[name="wpm_edit_page"]'));*/
				$('#wpmerchant_post_id').change(WPMerchantAdmin.getPost);
				$('.wpm-update-post-button').click(WPMerchantAdmin.updatePost);
			} else if($('.wpm-edit-post-link').length > 0){
				$('#wpmerchant_post_id').change(WPMerchantAdmin.changeEditButton);
			}
			
		},
		globalChartConfig:function(){
			Chart.defaults.global = {
			    // Boolean - Whether to animate the chart
			    animation: true,

			    // Number - Number of animation steps
			    animationSteps: 60,

			    // String - Animation easing effect
			    // Possible effects are:
			    // [easeInOutQuart, linear, easeOutBounce, easeInBack, easeInOutQuad,
			    //  easeOutQuart, easeOutQuad, easeInOutBounce, easeOutSine, easeInOutCubic,
			    //  easeInExpo, easeInOutBack, easeInCirc, easeInOutElastic, easeOutBack,
			    //  easeInQuad, easeInOutExpo, easeInQuart, easeOutQuint, easeInOutCirc,
			    //  easeInSine, easeOutExpo, easeOutCirc, easeOutCubic, easeInQuint,
			    //  easeInElastic, easeInOutSine, easeInOutQuint, easeInBounce,
			    //  easeOutElastic, easeInCubic]
			    animationEasing: "easeOutQuart",

			    // Boolean - If we should show the scale at all
			    showScale: true,

			    // Boolean - If we want to override with a hard coded scale
			    scaleOverride: true,

			    // ** Required if scaleOverride is true **
			    // Number - The number of steps in a hard coded scale
			    scaleSteps: 2,
			    // Number - The value jump in the hard coded scale
					// This number is dependent on the max value - max value in dataset/2
			    scaleStepWidth: 45,
			    // Number - The scale starting value
			    scaleStartValue: null,

			    // String - Colour of the scale line
			    scaleLineColor: "rgba(0,0,0,.1)",

			    // Number - Pixel width of the scale line
			    scaleLineWidth: 1,

			    // Boolean - Whether to show labels on the scale
			    scaleShowLabels: true,

			    // Interpolated JS string - can access value
			    scaleLabel: wpm_ajax_object.currency+"<%=value%>",

			    // Boolean - Whether the scale should stick to integers, not floats even if drawing space is there
			    scaleIntegersOnly: true,

			    // Boolean - Whether the scale should start at zero, or an order of magnitude down from the lowest value
			    scaleBeginAtZero: false,

			    // String - Scale label font declaration for the scale label
			    scaleFontFamily: "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif",

			    // Number - Scale label font size in pixels
			    scaleFontSize: 12,

			    // String - Scale label font weight style
			    scaleFontStyle: "normal",

			    // String - Scale label font colour
			    scaleFontColor: "#666",

			    // Boolean - whether or not the chart should be responsive and resize when the browser does.
			    responsive: true,

			    // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
			    maintainAspectRatio: true,

			    // Boolean - Determines whether to draw tooltips on the canvas or not
			    showTooltips: true,

			    // Function - Determines whether to execute the customTooltips function instead of drawing the built in tooltips (See [Advanced - External Tooltips](#advanced-usage-custom-tooltips))
			    customTooltips: false,

			    // Array - Array of string names to attach tooltip events
			    tooltipEvents: ["mousemove", "touchstart", "touchmove"],

			    // String - Tooltip background colour
			    tooltipFillColor: "rgba(0,0,0,0.8)",

			    // String - Tooltip label font declaration for the scale label
			    tooltipFontFamily: "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif",

			    // Number - Tooltip label font size in pixels
			    tooltipFontSize: 14,

			    // String - Tooltip font weight style
			    tooltipFontStyle: "normal",

			    // String - Tooltip label font colour
			    tooltipFontColor: "#fff",

			    // String - Tooltip title font declaration for the scale label
			    tooltipTitleFontFamily: "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif",

			    // Number - Tooltip title font size in pixels
			    tooltipTitleFontSize: 14,

			    // String - Tooltip title font weight style
			    tooltipTitleFontStyle: "bold",

			    // String - Tooltip title font colour
			    tooltipTitleFontColor: "#fff",

			    // Number - pixel width of padding around tooltip text
			    tooltipYPadding: 6,

			    // Number - pixel width of padding around tooltip text
			    tooltipXPadding: 6,

			    // Number - Size of the caret on the tooltip
			    tooltipCaretSize: 8,

			    // Number - Pixel radius of the tooltip border
			    tooltipCornerRadius: 6,

			    // Number - Pixel offset from point x to tooltip edge
			    tooltipXOffset: 10,

			    // String - Template string for single tooltips
			    tooltipTemplate: "<%if (label){%><%=label%>: <%}%>"+wpm_ajax_object.currency+"<%= value %>",

			    // String - Template string for multiple tooltips
			    multiTooltipTemplate: "<%= value %>",

			    // Function - Will fire on animation progression.
			    onAnimationProgress: function(){},

			    // Function - Will fire on animation completion.
			    onAnimationComplete: function(){}
			}
		},
		salesChart:function(){
			WPMerchantAdmin.globalChartConfig();
			  // Get context with jQuery - using jQuery's .get() method.
			  var ctx = $("#salesChart").get(0).getContext("2d");
			  // get data for the chart 
			  // if less than 6 months, show weeks for the labels
			  // if greater than 6 months, show months for the labels
			  
			  var data = {
			      labels: ["January", "February", "March", "April", "May", "June", "July"],
			      datasets: [
			          {
			              label: "My Second dataset",
			              fillColor: "rgba(151,187,205,0.2)",
			              strokeColor: "rgba(151,187,205,1)",
			              pointColor: "rgba(151,187,205,1)",
			              pointStrokeColor: "#fff",
			              pointHighlightFill: "#fff",
			              pointHighlightStroke: "rgba(151,187,205,1)",
			              data: [28, 48, 40, 19, 86, 27, 90]
			          }
			      ]
			  };
			  var options = {

			   	///Boolean - Whether grid lines are shown across the chart
			    scaleShowGridLines : true,

			    //String - Colour of the grid lines
			    scaleGridLineColor : "rgba(0,0,0,.05)",

			    //Number - Width of the grid lines
			    scaleGridLineWidth : 1,

			    //Boolean - Whether to show horizontal lines (except X axis)
			    scaleShowHorizontalLines: true,

			    //Boolean - Whether to show vertical lines (except Y axis)
			    scaleShowVerticalLines: true,

			    //Boolean - Whether the line is curved between points
			    bezierCurve : false,

			    //Number - Tension of the bezier curve between points
			    bezierCurveTension : 0.4,

			    //Boolean - Whether to show a dot for each point
			    pointDot : true,

			    //Number - Radius of each point dot in pixels
			    pointDotRadius : 4,

			    //Number - Pixel width of point dot stroke
			    pointDotStrokeWidth : 1,

			    //Number - amount extra to add to the radius to cater for hit detection outside the drawn point
			    pointHitDetectionRadius : 20,

			    //Boolean - Whether to show a stroke for datasets
			    datasetStroke : true,

			    //Number - Pixel width of dataset stroke
			    datasetStrokeWidth : 2,

			    //Boolean - Whether to fill the dataset with a colour
			    datasetFill : true,

			    //String - A legend template
			    legendTemplate : "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets[i].strokeColor%>\"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>"

			};
			  // This will get the first returned node in the jQuery collection.
			var myLineChart = new Chart(ctx).Line(data, options);
		},
		changeEditButton: function(){
			var post_id = $('#wpmerchant_post_id').val();
			$('.wpm-edit-post-link').attr('href','/wp-admin/post.php?post='+post_id+'&action=edit');
		},
		getPost: function(event){ 
			event.preventDefault();
		  var post_id = $(this).val();
		  var dataString = "post_id="+post_id+"&action=wpmerchant_get_post&security="+encodeURIComponent(wpm_ajax_object.get_post_nonce);
		  console.log(wpm_ajax_object);
		  console.log('getPost')
		  console.log(dataString)
		  $('.wpm_admin_body').find('.error').remove()
			$.ajax({
				url: wpm_ajax_object.ajax_url,  
				type: "GET",
				  data: dataString,
				  dataType:'json',
				  success: function(data){
				    if(data.response == 'success'){
					   console.log('success')
						console.log(data.post.post_content)
						// Click the text tab - because insertion doesn't work unless you do this.
						$('#wpmerchant_post_content-html').click();
						// the html is visible in the html editable after dynamic insertion because of the wpautop argument when initially calling wp_editor 
						// stripe slashes so that the content looks as it should and double quotes aren't escaped
						//var post_content = WPMerchantAdmin.stripslashes(data.post.post_content);
						var post_content = data.post.post_content;
						$('#wpmerchant_post_content').val(post_content);
						// switch back to the visual editor
						$('#wpmerchant_post_content-tmce').click();
						$('#wpmerchant_post_content_li').css("display","list-item")
						$('#wpmerchant_instructions').css("display","list-item")
						/*tinymce.editors.wpmerchant_post_content.getContent()*/
						
				   } else if(data.response == 'error'){
					    console.log(data)
					   //Error saving product. Please try again or 
					   $('.wpm_admin_body').prepend('<div class="error inline">'+data.message+'</div>');
					   $('#wpmerchant_post_content_li').css("display","none")
					   $('#wpmerchant_instructions').css("display","none")
				   }
				  console.log( data );
				  },
				error: function(jqXHR, textStatus, errorThrown) { 
					console.log(jqXHR, textStatus, errorThrown); 
				    console.log('error saving')
				   $('.wpm_admin_body').prepend('<div class="error inline">'+data.message+'</div>');
				   $('#wpmerchant_post_content_li').css("display","none")
				   $('#wpmerchant_instructions').css("display","none")
				}
			});
		},
		showShippingFields: function(){
			$(this).css('display','none')
			$('.wpm_shipping_display').css('display','none');
			$('.wpm_shipping_fields').css('display','list-item');
		},
		showBillingFields: function(){
			$(this).css('display','none')
			$('.wpm_billing_display').css('display','none');
			$('.wpm_billing_fields').css('display','list-item');
		},
		createPost: function(event){ 
			event.preventDefault();
		  var dataString = $('form[name="wpm_create_post"]').serialize();
		  dataString += "&action=wpmerchant_create_post&security="+encodeURIComponent(wpm_ajax_object.create_post_nonce);
		  console.log(wpm_ajax_object);
		  console.log('createPost')
		  console.log(dataString)
		  $('.wpm_admin_body').find('.error').remove()
		  $('.wpm_admin_body').find('.updated').remove()
			$.ajax({
				url: wpm_ajax_object.ajax_url,  
				type: "POST",
				  data: dataString,
				  dataType:'json',
				  success: function(data){
				    if(data.response == 'success'){
					   console.log('success')
						$('.wpm_admin_body').prepend('<div class="updated inline">'+data.message+' <a target="_blank" href="'+data.link+'">'+data.link_message+'</a></div>');
				   } else if(data.response == 'error'){
					    console.log(data)
					   //Error saving product. Please try again or 
					   $('.wpm_admin_body').prepend('<div class="error inline">'+data.message+'</div>');
				   }
				  console.log( data );
				  },
				error: function(jqXHR, textStatus, errorThrown) { 
					console.log(jqXHR, textStatus, errorThrown); 
				    console.log('error saving')
				   $('.wpm_admin_body').prepend('<div class="error inline">'+data.message+'</div>');
				}
			});
		},
		updatePost: function(event){ 
			event.preventDefault();
			if($('#wpmerchant_post_content').length>0){
				var post_content = $('#wpmerchant_post_content').val();
			} else {
				var post_content = '';
			}
		  //var dataString = $('form[name="wpm_update_post"]').serialize();
		  var wpmerchant_post_id = $('[name="wpmerchant_post_id"]').val();
		  var post_content = WPMerchantAdmin.stripslashes(post_content);
		  var dataString = "wpmerchant_post_id="+encodeURIComponent(wpmerchant_post_id)+"&wpmerchant_post_content="+encodeURIComponent(post_content)+"&action=wpmerchant_update_post&security="+encodeURIComponent(wpm_ajax_object.update_post_nonce);
		  console.log(wpm_ajax_object);
		  console.log('updatePost')
		  console.log(dataString)
		  $('.wpm_admin_body').find('.error').remove()
		  $('.wpm_admin_body').find('.updated').remove()
			$.ajax({
				url: wpm_ajax_object.ajax_url,  
				type: "POST",
				  data: dataString,
				  dataType:'json',
				  success: function(data){
				    if(data.response == 'success'){
					   console.log('success')
						$('.wpm_admin_body').prepend('<div class="updated inline">'+data.message+' <a target="_blank" href="'+data.link+'">'+data.link_message+'</a></div>');
				   } else if(data.response == 'error'){
					    console.log(data)
					   //Error saving product. Please try again or 
					   $('.wpm_admin_body').prepend('<div class="error inline">'+data.message+'</div>');
				   }
				  console.log( data );
				  },
				error: function(jqXHR, textStatus, errorThrown) { 
					console.log(jqXHR, textStatus, errorThrown); 
				    console.log('error saving')
				   $('.wpm_admin_body').prepend('<div class="error inline">'+data.message+'</div>');
				}
			});
		},
		updateEmailList: function(){ 
		  var list_id = $(this).val();
		  var dataString = "action=wpmerchant_update_gen_list&security="+encodeURIComponent(wpm_ajax_object.update_gen_list_nonce)+"&list_id="+encodeURIComponent(list_id);
		  console.log(wpm_ajax_object);
		  console.log('updateEmailList')
		  console.log(dataString)
			$.ajax({
				url: wpm_ajax_object.ajax_url,  
				type: "POST",
				  data: dataString,
				  dataType:'json',
				  success: function(data){
				    if(data.response == 'success'){
					   console.log('success')
						if(data.list_id){
							$('.slider_description').html('Now that you\'ve selected a newsletter list, go to the <a href="">next step</a>.');						
						} else {
							$('.slider_description').html('You haven\'t selected a newsletter list to subscribe customers to when a purchase is made. <a href="https://www.wpmerchant.com" target="_blank" class="arrow">Learn more</a>');						
						}
						
			   	   } else if(data.response == 'empty'){
					    console.log(data)
					   // polling to see if the key has been received or not
					   // this response is only returned if no api key exists - so keep running it until we get one
					   //WPMerchantAdmin.getEmailData();
				   } else if(data.response == 'error'){
					   // number of polls has gone over the limit so we throw this instead of empty - prevent polling from continuing
				   	   console.log(data)
				   }
				  console.log( data );
				  },
				error: function(jqXHR, textStatus, errorThrown) { 
					console.log(jqXHR, textStatus, errorThrown); 
				    console.log('error saving')
					//$(".planExistsStatus").css("display","block");
					//$(".dashicon-container").empty().append('<span class="dashicons dashicons-no" style="color:#a00;"></span>');
				}
			});
		},
		getQueryVariable:function(variableName) {
		       var query = window.location.search.substring(1);
		       var vars = query.split("&");
		       for (var i=0;i<vars.length;i++) {
		               var pair = vars[i].split("=");
		               if(pair[0] == variableName){return pair[1];}
		       }
		       return(false);
		},
		getCookie: function(cname) {
		    var name = cname + "=";
		    var ca = document.cookie.split(';');
		    for(var i=0; i<ca.length; i++) {
		        var c = ca[i];
		        while (c.charAt(0)==' ') c = c.substring(1);
		        if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
		    }
		    return "";
		},
		setCookie: function(cname, cvalue, exdays) {
		    var d = new Date();
		    d.setTime(d.getTime() + (exdays*24*60*60*1000));
		    var expires = "expires="+d.toUTCString();
		    document.cookie = cname + "=" + cvalue + "; " + expires;
		},
		stripslashes: function(str) {
		  //       discuss at: http://phpjs.org/functions/stripslashes/
		  //      original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
		  //      improved by: Ates Goral (http://magnetiq.com)
		  //      improved by: marrtins
		  //      improved by: rezna
		  //         fixed by: Mick@el
		  //      bugfixed by: Onno Marsman
		  //      bugfixed by: Brett Zamir (http://brett-zamir.me)
		  //         input by: Rick Waldron
		  //         input by: Brant Messenger (http://www.brantmessenger.com/)
		  // reimplemented by: Brett Zamir (http://brett-zamir.me)
		  //        example 1: stripslashes('Kevin\'s code');
		  //        returns 1: "Kevin's code"
		  //        example 2: stripslashes('Kevin\\\'s code');
		  //        returns 2: "Kevin\'s code"

		  return (str + '')
		    .replace(/\\(.?)/g, function(s, n1) {
		      switch (n1) {
		        case '\\':
		          return '\\';
		        case '0':
		          return '\u0000';
		        case '':
		          return '';
		        default:
		          return n1;
		      }
		    });
		}
	}
	WPMerchantAdmin.construct();
})( jQuery );
