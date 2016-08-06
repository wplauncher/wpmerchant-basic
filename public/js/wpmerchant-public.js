(function( $ ) {
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
					$('body').append('<div class="wpm-overlay"><div id="wpm_loading_indicator" class="wpm-loading-indicator"><img src="'+ajax_object.loading_gif+'" width="50" height="50"></div><div id="wpm_message"><a class="wpm-close-link"><img class="wpm-close" src="'+ajax_object.close_btn_image+'"></a><h1>'+ajax_object.post_checkout_msg+'</h1><p><img src="'+ajax_object.stripe_checkout_image+'" height="128px" width="128px"></p></div></div>');
					$('.wpMerchantPurchase').bind('click', WPMerchant.purchase); 
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
			  $('#wpm_loading_indicator').css("display","block").css("opacity","1"); 
			  $('.wpm-overlay').css("display","block");
				break;
			case 'message':
				$('#wpm_message').css("display","block");
				$('.wpm-overlay').css("display","block");
				break;
			case 'stripe':
				$('.wpm_stripe_modal').css("display","block").css("opacity","1");
				$('.wpm-stripe-overlay').css("display","block");
				break;
			default:
				
			}
		},
		overlayOff:function(type){
			console.log('off')
			switch (type) {
				case 'loading':
				  $('#wpm_loading_indicator').css("display","none").css("opacity","0"); 
				  $('.wpm-overlay').css("display","none");
					break;
				case 'message':
					$('#wpm_message').css("display","none");
					$('.wpm-overlay').css("display","none");
					break;
				case 'stripe':
					$('.wpm_stripe_modal').css("display","none").css("opacity","0");
					$('.wpm-stripe-overlay').css("display","none");
					break;
				default:
    				  $('#wpm_loading_indicator').css("display","none").css("opacity","0"); 
    				  $('.wpm-overlay').css("display","none");
					  $('#thank-you-modal').css("display","none");
					$('#wpm_message').css("display","none");
					$('.wpm-overlay').css("display","none");
					$('.wpm_stripe_modal').css("display","none").css("opacity","0");
					$('.wpm-stripe-overlay').css("display","none");
				}
		},
		appendModal: function (event){
			event.preventDefault();
			  var productDescription = $(this).data("description");
			  var title = $(this).data("title");
			  // this is the content holder
		  if($(this).data("product")){
		  	var item_id = $(this).data("product");
			var modal_selector = $('.wpm-stripe-overlay[data-product="'+item_id+'"]');
		  }
		  
		  modal_selector.find('.wpm_stripe_modal').find(".image").attr("style","background:url('"+ajax_object.icon_border_image+"') no-repeat;");
		  modal_selector.find('.wpm_stripe_modal').find(".close").attr("style","background:url('"+ajax_object.close_btn_image+"') no-repeat;");
			
		  modal_selector.find('.wpm_stripe_modal').find(".close").unbind();
		  modal_selector.find('.wpm_stripe_modal').find(".close").bind("click",WPMerchant.overlayOff);
		   modal_selector.find('.wpm_stripe_modal').css("display","block").css("opacity","1");
		   modal_selector.css("display","block");
			// add this so any purchase buttons inside the modal will wokr
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
		    var stripeSocial = "<a target='_blank' class='share-on-link share-on-twitter' href='"+socialLinksTW+"'>Twitter</a>&nbsp;<a target='_blank' class='share-on-link share-on-facebook' href='"+socialLinksFB+"'>Facebook</a>&nbsp;<a target='_blank' class='share-on-link share-on-googleplus' href='"+socialLinksGP+"'>Google+</a>";
			if(!title){
				var title = 'Thank you!';
			}
			if(!description){
				var description = 'We\'d love your support.';
			}
			if(!content){
				var content = '<div class="stripeReceiptMsg3 wpm_clear"><p>You have been emailed a receipt for your purchase. Share your purchase of <span class="stripeProductDescription">'+productName+'</span> on your social networks.</p></div><div class="wpm_clear stripeSocial">'+stripeSocial+'</div>';
			}
		  var modal = '<div id="thank-you-modal"><div class="wpm_stripe_modal"><div class="sc_header"><a class="close" style="display: block;"></a><div class="image"><img src="'+ajax_object.stripe_checkout_image+'"></div><h1 class="stripeResponseMessage">'+title+'</h1><h2 class="stripeProductDescription">'+description+'</h2></div><div class="sc_body">'+content+'</div></div></div>';
		  var modal_selector = $('#thank-you-modal');
		  if(modal_selector.length > 0 ){
			  modal_selector.remove();
		  }
		  $('body').append(modal);
		  $('#thank-you-modal').find('.wpm_stripe_modal').find(".image").attr("style","background:url('"+ajax_object.icon_border_image+"') no-repeat;");
		  $('#thank-you-modal').find('.wpm_stripe_modal').find(".close").attr("style","background:url('"+ajax_object.close_btn_image+"') no-repeat;");
			
		  $('#thank-you-modal').find('.wpm_stripe_modal').find(".close").unbind();
		  $('#thank-you-modal').find('.wpm_stripe_modal').find(".close").bind("click",WPMerchant.overlayOff);
		   $('#thank-you-modal').find('.wpm_stripe_modal').css("display","block").css("opacity","1");
		   $('#thank-you-modal').css("display","block");
		   
			// add this so any purchase buttons inside the modal will wokr
			if($('.wpMerchantPurchase').length > 0){
				$('.wpMerchantPurchase').unbind();
				$('.wpMerchantPurchase').bind('click', WPMerchant.purchase);
			}
		},
		purchase: function(event) {
		  console.log('clickspp');
		  WPMerchant.overlayOn('loading');
		  //$(".overlayView2").css("display","none");
		  var receiptMsg1 = '';
		  var receiptMsg2 = '';
		  var companyName = ajax_object.company_name;
		  var stripePublicKey = ajax_object.stripe_public_key;
		  if($(this).data('products')){
			  var products = JSON.stringify($(this).data('products'));
		  } else {
			  var products = '';
		  }
		  
		  var amount = $(this).data('amount');
		  var description =  $(this).data('description');
		  var currency =  ajax_object.currency;
		  
		  var panelLabel = 'Purchase - {{amount}}';
		  
		  var spImage = ajax_object.stripe_checkout_image;
		  var shippingAddress = ajax_object.stripe_shippingAddress;
		  var billingAddress = ajax_object.stripe_billingAddress;
		  var zipCode = ajax_object.stripe_zipCode;
		  console.log(companyName+', '+description+', '+amount+', '+panelLabel+', '+receiptMsg1+', '+receiptMsg2+', '+stripePublicKey+', '+spImage+', '+products+', '+currency);
		  //display the loader gif
		  
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
					// this runs when the modal is closed
					console.log('opened');
					WPMerchant.overlayOff();
				},
				token: function(token, args) {
				  WPMerchant.overlayOn('loading');
			      // Use the token to create the charge with a server-side script.
			      // You can access the token ID with `token.id`
			      console.log(token);
				  console.log(products);
				  //WPMerchant.loadingModal();
				  if(typeof token.card !== 'undefined'){
					  var customer_name = token.card.name;
					  var customer_address_1 = token.card.address_line1;
					  var customer_address_country = token.card.address_country;
					  var customer_address_state = token.card.address_state;
					  var customer_address_city = token.card.address_city;
					  var customer_address_zip = token.card.address_zip;
				  } else {
					  var customer_name = '';
					  var customer_address_1 = '';
					  var customer_address_country = '';
					  var customer_address_state = '';
					  var customer_address_city = '';
					  var customer_address_zip = '';
				  }
				  var dataString = "token=" + encodeURIComponent(token.id) + "&email=" + encodeURIComponent(token.email) + "&products=" + encodeURIComponent(products)+"&action=wpmerchant_purchase&amount="+encodeURIComponent(amount)+"&security="+ajax_object.purchase_nonce+"&name=" + encodeURIComponent(customer_name) + "&address_1=" + encodeURIComponent(customer_address_1) + "&country=" + encodeURIComponent(customer_address_country) + "&state=" + encodeURIComponent(customer_address_state) + "&zip=" + encodeURIComponent(customer_address_zip);
				  console.log(ajax_object);
					$.ajax({
						url: ajax_object.ajax_url,  
						type: "POST",
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
									$(".wpm-close-link").bind("click",WPMerchant.overlayOff);*/
									WPMerchant.customModal(productDescription,data.thanks_title,data.thanks_desc,data.thanks_content);
								}
								var responseMessage = 'Purchase Complete';
							   var receiptMsg1 = 'We have emailed you a receipt.';
							   var receiptMsg2 = 'Support us by sharing this purchase on your social networks.';
					   		   //WPMerchant.updateModal(productDescription, responseMessage, receiptMsg1, receiptMsg2);
						   } else if (data.response == 'sold_out'){
							   WPMerchant.overlayOff;
   						      console.log('sold_out')
   								/*if(data.redirect){
   									console.log('redirect exists')
   									window.open(data.redirect,'_self');
   								} else {
   									console.log('no redirect exists')
							   		WPMerchant.overlayOn('message');
							   		$(".wpm-close-link").bind("click",WPMerchant.overlayOff);
								}*/
						   		$("#wpm_message").find('h1').empty().text('Sold Out!')
								WPMerchant.overlayOn('message');
						   		$(".wpm-close-link").bind("click",WPMerchant.overlayOff);
							   
					   	   } else {
							   WPMerchant.overlayOff;
   						      console.log('error')
   								/*if(data.redirect){
   									console.log('redirect exists')
   									window.open(data.redirect,'_self');
   								} else {
   									console.log('no redirect exists')
							   		WPMerchant.overlayOn('message');
							   		$(".wpm-close-link").bind("click",WPMerchant.overlayOff);
								}*/
						   		$("#wpm_message").find('h1').empty().text('Purchase Error')
								WPMerchant.overlayOn('message');
						   		$(".wpm-close-link").bind("click",WPMerchant.overlayOff);
							   var responseMessage = 'Purchase Error'
							   var receiptMsg1 = 'We\'re sorry! There was an error purchasing this product.  Please contact <a href="mailto:george@mettagroup.org">george@mettagroup.org</a>.';
							   var receiptMsg2 = '';
					   		   //WPMerchant.updateModal(productDescription, responseMessage, receiptMsg1, receiptMsg2);
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
						   		$(".wpm-close-link").bind("click",WPMerchant.overlayOff);
							}*/
					   		$("#wpm_message").find('h1').empty().text('Purchase Error')
							WPMerchant.overlayOn('message');
					   		$(".wpm-close-link").bind("click",WPMerchant.overlayOff);
							console.log(jqXHR, textStatus, errorThrown); 
						   var responseMessage = 'Purchase Error'
						   var receiptMsg1 = 'We\'re sorry! There was an error purchasing this product.  Please contact <a href="mailto:george@mettagroup.org">george@mettagroup.org</a>.';
						   var receiptMsg2 = '';
				   		   //WPMerchant.updateModal(productDescription, responseMessage, receiptMsg1, receiptMsg2);
						}
					});
		 	  	 }
		 	 }); 
		 	 handler2.open();
	  	}
	
	}
	WPMerchant.construct();

})( jQuery );
