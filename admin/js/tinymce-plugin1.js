(function( $ ) {
	'use strict';
	var WPMTinyMCE = {
		construct:function(){
			$(function() {
				WPMTinyMCE.productPlugin();
 		 	 });
 		},
		productPlugin: function(){
		    console.log(wpm_localized_tinymce.products)
			var wpm_img = wpm_localized_tinymce.plugin_dir_url+'img/tinymce-icon.png';
			console.log(wpm_img)
			tinymce.PluginManager.add( 'wpmerchant_product_plugin', function( editor, url ) {

		        // Add a button that opens a window
		        editor.addButton( 'wpmerchant_button', {

		            text: 'WPMerchant',
					classes: 'widget btn wpmerchant_tinymce_button',
		            image: wpm_img,
		            onclick: function() {
						// Open up the custom admin modal here and remove the windowManager.open functionality below
						// have a checkbox for adding a new product (which will reveal those fields)
						// Also have the same functionality as the windoManager - products list and button text and hten insert that content into the tinymce editor
						var modal_title = 'Add Buy Shortcode';
						var modal_content = '<form name="wpm_shortcode_form">'+wpm_localized_tinymce.shortcode_form+'</form>';
						var modal_button = '<a class="button button-primary wpm-admin-button wpm-shortcode-button">Add</a>';
						WPMTinyMCE.adminModalOn(modal_title,modal_content,modal_button);
						$('.wpm-add-new-product').click(WPMTinyMCE.addProductModal);
						var wpm_tinymce_editor = editor;
						$('a.wpm-shortcode-button').click(function(){
							var post_id = $('[name="wpmerchant_product"]').val();
							var button_text = $('[name="wpmerchant_button_text"]').val();
							// if problems see http://stackoverflow.com/questions/17973020/custom-modal-screen-in-tinymce-4-plugin-in-inline-mode
							wpm_tinymce_editor.insertContent('[wpmerchant_button products="' + post_id +'"]' + button_text +'[/wpmerchant_button]');
							$(this).parents('.wpm-admin-container').remove();
						})
						
						
						
							// get products via ajax so that they'll be more up to date than the localized product version
							// AND so that a product can be created in the admin modal and then shown in the windowManager
						/*var product_list = WPMTinyMCE.getProductsList();*/
							/*editor.windowManager.open({
														title: 'WPMerchant',
														body: [
															{type: 'listbox', 
										    name: 'wpmerchant_post_id', 
										    label: 'Product', 
										    'values': wpm_localized_tinymce.products
										},{type: 'textbox', name: 'wpmerchant_button_text', label: 'Button Text'}
														],
														onsubmit: function(e) {
															if(e.data.wpmerchant_button_text){
																var wpmerchant_button_text = e.data.wpmerchant_button_text;
															} else {
																var wpmerchant_button_text = 'Buy';
															}
															if(e.data.wpmerchant_post_id == 'create_product'){
																//create the product
																//WPMerchantAdmin.adminModalOn();
															} else {
																// add the shortcode to the page here
																editor.insertContent('[wpmerchant_button products="' + e.data.wpmerchant_post_id +'"]' + wpmerchant_button_text +'[/wpmerchant_button]');
															}
														}
													});*/
													/**,{
													                    type   : 'checkbox',
													                    name   : 'checkbox',
													                    label  : 'Create a New Product',
													                    text   : '',
													                    checked : false
													                }
{
													                    type   : 'container',
													                    name   : 'container',
													                    label  : '',
													                    html   : '<h1>Create Post Form<h1><p>'+wpm_localized_tinymce.create_post_form+'</p>'
													                },
													{
													                    type   : 'listbox',
													                    name   : 'listbox',
													                    label  : 'listbox',
													                    values : [
													                        { text: 'Test', value: 'test' },
													                        { text: 'Test2', value: 'test2', selected: true }
													                    ]
													                },
													                {
													                    type   : 'combobox',
													                    name   : 'combobox',
													                    label  : 'combobox',
													                    values : [
													                        { text: 'Test', value: 'test' },
													                        { text: 'Test2', value: 'test2' }
													                    ]
													                },
													                {
													                    type   : 'textbox',
													                    name   : 'textbox',
													                    label  : 'textbox',
													                    tooltip: 'Some nice tooltip to use',
													                    value  : 'default value'
													                },
													                {
													                    type   : 'container',
													                    name   : 'container',
													                    label  : 'container',
													                    html   : '<h1>container<h1> is <i>ANY</i> html i guess...<br/><br/><pre>but needs some styling?!?</pre>'
													                },
													                {
													                    type   : 'tooltip',
													                    name   : 'tooltip',
													                    label  : 'tooltip ( you dont use it like this check textbox params )'
													                },
													                {
													                    type   : 'button',
													                    name   : 'button',
													                    label  : 'button ( i dont know the other params )',
													                    text   : 'My Button'
													                },
													                {
													                    type   : 'buttongroup',
													                    name   : 'buttongroup',
													                    label  : 'buttongroup ( i dont know the other params )',
													                    items  : [
													                        { text: 'Button 1', value: 'button1' },
													                        { text: 'Button 2', value: 'button2' }
													                    ]
													                },
													                {
													                    type   : 'checkbox',
													                    name   : 'checkbox',
													                    label  : 'checkbox ( it doesn`t seem to accept more than 1 )',
													                    text   : 'My Checkbox',
													                    checked : true
													                },
													                {
													                    type   : 'colorbox',
													                    name   : 'colorbox',
													                    label  : 'colorbox ( i have no idea how it works )',
													                    // text   : '#fff',
													                    values : [
													                        { text: 'White', value: '#fff' },
													                        { text: 'Black', value: '#000' }
													                    ]
													                },
													                {
													                    type   : 'panelbutton',
													                    name   : 'panelbutton',
													                    label  : 'panelbutton ( adds active state class to it,visible only on hover )',
													                    text   : 'My Panel Button'
													                },
													                {
													                    type   : 'colorbutton',
													                    name   : 'colorbutton',
													                    label  : 'colorbutton ( no idea... )',
													                    // text   : 'My colorbutton'
													                },
													                {
													                    type   : 'colorpicker',
													                    name   : 'colorpicker',
													                    label  : 'colorpicker'
													                },
													                {
													                    type   : 'radio',
													                    name   : 'radio',
													                    label  : 'radio ( defaults to checkbox, or i`m missing something )',
													                    text   : 'My Radio Button'
													                }
																				**/
		            }

		        } );

		    } );
		},
		getProductsList: function(event){ 
			event.preventDefault();
		  var dataString = "action=wpmerchant_get_products_list&security="+encodeURIComponent(wpm_localized_tinymce.get_products_list_nonce);
		  console.log('getProductsList')
		  console.log(dataString)
			$.ajax({
				url: wpm_localized_tinymce.ajax_url,  
				type: "GET",
				  data: dataString,
				  dataType:'json',
				  success: function(data){
				    if(data.response == 'success'){
					   console.log('success')
						console.log(data.product_list)
						return data.product_list
				   } else if(data.response == 'error'){
					   console.log('error')
					    console.log(wpm_localized_tinymce.products)
					   return wpm_localized_tinymce.products
				   }
				  console.log( data );
				  },
				error: function(jqXHR, textStatus, errorThrown) { 
					console.log(jqXHR, textStatus, errorThrown); 
				    console.log('error saving')
					    console.log(wpm_localized_tinymce.products)
					   return wpm_localized_tinymce.products
				}
			});
		},
		addProductModal:function(){
			var modal_title = 'Add Product';
			var modal_content = '<form name="wpm_create_post">'+wpm_localized_tinymce.create_post_form+'</form>';
			var modal_button = '<a class="button button-primary wpm-admin-button wpm-create-post-form">Save</a>';
			WPMTinyMCE.adminModalOn(modal_title,modal_content,modal_button);
			$('.wpm-create-post-form').click(WPMTinyMCE.createPost);
		},
		createPost: function(event){ 
			event.preventDefault();
		  var dataString = $('form[name="wpm_create_post"]').serialize();
		  dataString += "&action=wpmerchant_create_post&security="+encodeURIComponent(ajax_object.create_post_nonce);
		  console.log(ajax_object);
		  console.log('createPost')
		  console.log(dataString)
		  $('.wpm_admin_body').find('.error').remove()
		  $('.wpm_admin_body').find('.updated').remove()
			$.ajax({
				url: ajax_object.ajax_url,  
				type: "POST",
				  data: dataString,
				  dataType:'json',
				  success: function(data){
				    if(data.response == 'success'){
					   console.log('success')
					   // add the new product ot hte existing products list
						console.log(data.post)
						$('[name="wpmerchant_product"]').append($("<option></option>").attr("value",data.post.id).text(data.post.post_title)); 
					   // Select that product
						$('[name="wpmerchant_product"] > option').each(function() {
							if($(this).val() == data.post.id){
								$(this).attr('selected','selected');
							} else {
								$(this).removeAttr('selected');
							}
						    
						});
						$('form[name="wpm_create_post"]').parents('.wpm-admin-container').remove();
				   } else if(data.response == 'error'){
					    console.log(data)
					   //Error saving product. Please try again or 
					   $('form[name="wpm_create_post"]').parents('.wpm_admin_body').prepend('<div class="error inline">'+data.message+'</div>');
				   }
				  console.log( data );
				  },
				error: function(jqXHR, textStatus, errorThrown) { 
					console.log(jqXHR, textStatus, errorThrown); 
				    console.log('error saving')
				   $('form[name="wpm_create_post"]').parents('.wpm_admin_body').prepend('<div class="error inline">'+data.message+'</div>');
				}
			});
		},
		adminModalOff:function(){
			$(this).parents('.wpm-admin-container').remove();
		},
		adminModalOn:function(title, content, button){
			
			$('body').append('<div class="wpm-admin-container"><div class="wpm_admin_modal"><div class="wpm_admin_header"><div class="wpm-admin-header-container"><h1>'+title+'</h1><a class="close dashicons dashicons-no"></a></div></div><div class="wpm_admin_body"><div class="wpm_clear">'+content+'</div></div><div class="wpm_admin_footer"><div class="wpm-admin-button-container">'+button+'</div></div></div></div>')
			$('.wpm-admin-container').css('display','block');
			$('.wpm_admin_modal').css('display','block').css('opacity','1');
			$('.wpm-admin-header-container').find('.close').click(WPMTinyMCE.adminModalOff)
			/*if($('.wpm-create-post-button').length > 0){
				$('.wpm-create-post-button').click(WPMerchantAdmin.createPost);
			}
			if($('.wpm-update-post-button').length > 0){
				//var textAreaID = 'sectionContent_'+sectionID;           
				/*tinymce.execCommand('mceAddEditor', true, 'post_content');
				tinyMCE.execCommand('mceAddEditor', false, $('form[name="wpm_edit_page"]'));***
				$('#wpmerchant_post_id').change(WPMerchantAdmin.getPost);
				$('.wpm-update-post-button').click(WPMerchantAdmin.updatePost);
			}*/
			
		},
	}
	WPMTinyMCE.construct();
})( jQuery );