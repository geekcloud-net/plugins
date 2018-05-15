var wpeae_shipping_api = {
	wpeae_shipping_data:'', 
	$state_block:'', 
	show_shipping_method_block:function(){},
	init:function(){}};

(function ($) {

	   wpeae_shipping_api.show_shipping_method_block = function(){
		
		   var quantity = $('input[name="quantity"]').val(),  product_id = $('[name="add-to-cart"]').val();
		   
		   var data = {'action': 'wpeae_get_shipping_method_data', 'id': product_id, 'country': wpeae_shipping_api.wpeae_shipping_data.country, 'quantity': quantity};
		   
		   jQuery.post(wpeae_ali_ship_data.ajaxurl, data, function (response) {
			  
				$shipping_select = $('#wpeae_shipping_field');
				$shipping_select.empty();
				
				wpeae_shipping_api.$shipping_method_block.find('.wpeae-small').remove();
					
				var json = jQuery.parseJSON(response);
				
				if (json.state){
					
					$shipping_select.append('<option value="">'+wpeae_ali_ship_data.lang.select_shipping_method+'</option>');
					
					if (json.state == "ok"){
						for (a in json.data){ 
							$shipping_select.append('<option value="' + a + '">' + json.data[a] + '</option>'); 
						}
					}
					
					if (json.state == "error"){
						$shipping_select.after('<div class="wpeae-small wpeae-error">'+json.message+'</div>');    
					}
			
					wpeae_shipping_api.$shipping_method_block.show();         
				}
				
		   });
				
		  
	   }
	   
	   wpeae_shipping_api.init = function(t){
			$('.wpeae_shipping').html(t); 
			
			wpeae_shipping_api.wpeae_shipping_data = {country:'', shipping_method:''};
			wpeae_shipping_api.$state_block = $('#wpeae_to_state'),
			wpeae_shipping_api.$shipping_method_block = $('#wpeae_shipping'); 
			
			  $('#wpeae_to_country_field').on('change', function() {
					wpeae_shipping_api.$shipping_method_block.hide();
					wpeae_shipping_api.wpeae_shipping_data.country = this.value;            
					wpeae_shipping_api.show_shipping_method_block();
			  });
				   
			  $('input[name="quantity"]').on('change', function() {
					wpeae_shipping_api.$shipping_method_block.hide();
					wpeae_shipping_api.show_shipping_method_block();    
			  });
			  
			if ($('#wpeae_to_country_field').val() !== ""){  
				country_set = 1;
				wpeae_shipping_api.$shipping_method_block.hide();
			   
				wpeae_shipping_api.wpeae_shipping_data.country = $('#wpeae_to_country_field').val();
				//$('#wpeae_to_country_field').prop('disabled', 'disabled');
				$('#wpeae_to_country_field').after('<div class="wpeae-small">'+wpeae_ali_ship_data.lang.shipping_country_should_be_the_same+'</div>');
						
				wpeae_shipping_api.show_shipping_method_block(); 
			}    
	   }
			
})(jQuery);