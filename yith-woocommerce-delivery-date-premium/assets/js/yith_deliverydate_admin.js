jQuery(document).ready(function($) {

	var unblock_option = function (field) {

         if (field.length) {
             field.removeClass('ywcdd_block');
         }
     },
     block_option = function (field) {

         if (field.length) {
             field.addClass('ywcdd_block');
         }
     };

     $('#yith_delivery_date_panel_general-settings').on('change','',function(e){
    	 var enable_system = $('#yith_delivery_date_enable_carrier_system'),
    	 	 range = $('#yith_delivery_date_range_day').parents('tr'),
    	 	 multiselect = $('#yith_delivery_date_workday').parents('tr'),
    	 	 max_range = $('#yith_delivery_date_max_range').parents('tr'),
    	 	 timeslot = $('#yith_wcdd_panel_timeslot');
    	 	 

    	 if( enable_system.is( ':checked' ) ){
    		 block_option( range );
    		 block_option( multiselect);
    		 block_option( max_range );
    		 
    	 }else{
    		 unblock_option( range );
    		 unblock_option( multiselect);
    		 unblock_option( max_range );
    		
    	 }
     }).trigger('change');
        
    //MULTISELECT DAY

    var multiselectday = $('#yith_delivery_date_workday').select2();

    $('.yith_select_all_day').on('click', function (e) {
        e.preventDefault();
        multiselectday.val(['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat']).trigger('change');
    });
    $('.yith_select_clear').on('click', function (e) {
        e.preventDefault();
        multiselectday.val(null).trigger('change');
    });
    var add_slot = $('#yith_add_slot'),
        panel_page = $('#yith_delivery_date_panel_general-settings'),
        table_slot = $('.wp-list-table.timeslots'),
        block_params = {
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
            },
            ignoreIfBlocked: true
        };

    var initialize_time_picker = function () {

        $('.yith_timepicker').timepicker({
            'timeFormat': yith_delivery_parmas.timeformat,
            'step': yith_delivery_parmas.timestep
        });
    };


    initialize_time_picker();

   
    //UPDATE SINGLE TIMESLOT ROW

    var adjust_index = function () {

            var timeslot_up = table_slot.find('a.yith_update_time_slot'),
                timeslot_delete = table_slot.find('a.yith_delete_time_slot');

            timeslot_up.each(function (index) {

                $(this).data('item_id', index);

            });

            timeslot_delete.each(function (index) {

                $(this).data('item_id', index);

            });
        },
        update_time_slot_row = function () {
            update_button = table_slot.find('.yith_update_time_slot');
            update_button.on('click', function (e) {

                e.preventDefault();
                var row = $(this).parents('tr'),
                    time_from = row.find('.timepicker_timefrom').val(),
                    time_to = row.find('.timepicker_timeto').val(),
                    max_order = row.find('.yith_max_order').val(),
                    fee = row.find('.yith_fee').val(),
                    override_day = row.find('.yith_override_day').is(':checked') ? 'yes' : 'no',
                    days = row.find('.yith_dayworkselect').select2('val'),
                    item_id = $(this).data('item_id');
              
                if (time_from != '' && time_to != '') {

                    var data = {
                        ywcdd_time_from: time_from,
                        ywcdd_time_to: time_to,
                        ywcdd_max_order: max_order,
                        ywcdd_fee: fee,
                        ywcdd_day:days,
                        override_days: override_day,
                        item_id: item_id,
                        slot_action: 'update_slot',
                        action: yith_delivery_parmas.actions.update_time_slot,
                        plugin_nonce: yith_delivery_parmas.plugin_nonce
                    };

                    table_slot.block(block_params);

                    $.ajax({
                        type: 'POST',
                        url: yith_delivery_parmas.ajax_url,
                        data: data,
                        dataType: 'json',
                        success: function (response) {

                            table_slot.unblock();

                        }

                    });
                }

            });

        };


    //DELETE SINGLE TIMESLOT ROW
    var delete_time_slot_row = function () {

        delete_button = table_slot.find('.yith_delete_time_slot');

        delete_button.on('click', function (e) {
            e.preventDefault();
            var row = $(this).parents('tr'),
                item_id = $(this).data('item_id'),
                table = $('#the-list');

            var data = {

                item_id: item_id,
                action: yith_delivery_parmas.actions.delete_time_slot,
                slot_action : 'delete_slot',
                plugin_nonce: yith_delivery_parmas.plugin_nonce
            };

            table_slot.block(block_params);

            $.ajax({
                type: 'POST',
                url: yith_delivery_parmas.ajax_url,
                data: data,
                dataType: 'json',
                success: function (response) {
                    table_slot.unblock();
                    row.remove();


                    if( table.find('tr').length == 0 ){
                     
                        table.html( yith_delivery_parmas.empty_row );
                    }
                    adjust_index();

                }

            });

        });
    };

    update_time_slot_row();
    delete_time_slot_row();

    //UPDATE-DELETE SHIPPING CATEGORY DAY
    
    var ajax_update_category_day = function( row ){
    	
	    	table = $('.shippingcategorydays'),
	        category_id = $(row).find('.ywcdd_category').val(),
	        category_day = $(row).find('.ywcdd_category_day').val();
	
			if (category_id != '' && category_day != '') {
			
			    var data = {
			        ywcdd_category_id: category_id,
			        ywcdd_category_day: category_day,
			        action: yith_delivery_parmas.actions.update_category_day,
			        plugin_nonce: yith_delivery_parmas.plugin_nonce
			    };
			
			    table.block(block_params);	
			
			    $.ajax({
			        type: 'POST',
			        url: yith_delivery_parmas.ajax_url,
			        data: data,
			        dataType: 'json',
			        success: function (response) {
		
			         	$('.shippingcategorydays').unblock();
			
			        }
			    });
			    
			}
	    },
    update_category_day = function(){
    	
    	$('.yith_update_category_day').on('click',function(e){
    		e.preventDefault();
            var row = $(this).parents('tr');
            	
            ajax_update_category_day( row );
            
           	});
    };
    var delete_category_day = function () {

        $('.yith_delete_category_day').on('click', function (e) {
            e.preventDefault();
            var row = $(this).parents('tr'),
        	
                item_id = $(this).data('item_id'),
                table = $('.shippingcategorydays>#the-list');

            var data = {

                item_id: item_id,
                action: yith_delivery_parmas.actions.delete_category_day,
                plugin_nonce: yith_delivery_parmas.plugin_nonce
            };

            table.block(block_params);

            $.ajax({
                type: 'POST',
                url: yith_delivery_parmas.ajax_url,
                data: data,
                dataType: 'json',
                success: function (response) {
                	table.unblock();
                    row.remove();


                    if( table.find('tr').length == 0 ){
                     
                        table.html( yith_delivery_parmas.empty_row );
                    }
                }

            });

        });
    };
    
  var  	ajax_update_product_day = function( row ){
		  table = $('.shippingproductdays'),
	      product_id = $(row).find('.ywcdd_product').val(),
	      product_day =  $(row).find('.ywcdd_product_day').val();

		if (product_id != '' && product_day != '') {
		
		  var data = {
		      ywcdd_product_id: product_id,
		      ywcdd_product_day: product_day,
		      action: yith_delivery_parmas.actions.update_product_day,
		      plugin_nonce: yith_delivery_parmas.plugin_nonce
		  };
		
		  table.block(block_params);
		
		  $.ajax({
		      type: 'POST',
		      url: yith_delivery_parmas.ajax_url,
		      data: data,
		      dataType: 'json',
		      success: function (response) {
		
		      	table.unblock();
		
		      }
		
		  });
		}
  },
     	update_product_day = function(){
    	
    	$('.yith_update_product_day').on('click',function(e){
    		e.preventDefault();
            var row = $(this).parents('tr');
            ajax_update_product_day( row );
    	});
    };
    var delete_product_day = function () {

        $('.yith_delete_product_day').on('click', function (e) {
            e.preventDefault();
            var row = $(this).parents('tr'),
                item_id = $(this).data('item_id'),
                table = $('.shippingproductdays>#the-list');

            var data = {

                item_id: item_id,
                action: yith_delivery_parmas.actions.delete_product_day,
                plugin_nonce: yith_delivery_parmas.plugin_nonce
            };

            row.block(block_params);

            $.ajax({
                type: 'POST',
                url: yith_delivery_parmas.ajax_url,
                data: data,
                dataType: 'json',
                success: function (response) {
                    row.unblock();
                    row.remove();


                    if( table.find('tr').length == 0 ){
                     
                        table.html( yith_delivery_parmas.empty_row );
                    }
                }

            });

        });
    };
    
    update_category_day();
    update_product_day();
    delete_category_day();
    delete_product_day();
    
    //SHOW/HIDE MULTISELECT DAY

    var show_hide_select = function () {
        
        $('.yith_override_day').on('change', function (e) {
            var t = $(this),
                div = t.parents('tr').find('.yith_single_multiworkday');

            if (t.is(':checked')) {

                div.show('slow');
            }
            else {
                div.hide('slow');
            }
        }).trigger('change');
    };
    
    show_hide_select();
    var wrapper  = $( '#the-list' );

    wrapper.on( 'click', '.yith_add_time_slot', function ( e ) {
            var current_target = $( e.target ),
                parent         = current_target.closest( 'tr' ),
                parent_clone   = parent.clone();

                parent_clone.find('div.yith_dayworkselect').remove();

         var select2 = parent_clone.find('select.yith_dayworkselect').val(null).select2();

            parent_clone.find( 'input' ).val( '' );

            parent.after( parent_clone );

            initialize_time_picker();
            show_hide_select();
        } );
    
    $('.yith-update-all').on('click',function(e){
    	e.preventDefault();
    	var category_row = $('.shippingcategorydays>#the-list').find('tr').not('.no-items'),
    		product_row = $('.shippingproductdays>#the-list').find('tr').not('.no-items');
    	
    	$.each( category_row, function(index, value ){
    		
    		ajax_update_category_day( value );
    	});
    	
$.each( product_row, function(index, value ){
    		
    		ajax_update_product_day( value );
    	});
    });
    
$('.yith-add-new-cat-day').on('click',function(e){
	var category = $('input[type="hidden"].yith-categories-select'),
		days = $('.ywcdd_day_cat'),
		error = false;


	if( category.val() == '' ){
		
		$('div.yith-categories-select').addClass('ywcdd_required_field');
		error = true;
	}
	
	if( days.val() == '' ){
		days.addClass('ywcdd_required_field');
		error = true;
	}
	
	if( error ){
		e.preventDefault();
		return false;
	}
	
	
});
$('.yith-add-new-prod-day').on('click',function(e){
	var category = $('input[type="hidden"].yith-products-select'),
		days = $('.ywcdd_day_prod'),
		error = false;
	if( category.val() == '' ){
		
		$('div.yith-products-select').addClass('ywcdd_required_field');
		error = true;
	}
	
	if( days.val() == '' ){
		days.addClass('ywcdd_required_field');
		error = true;
	}
	
	if( error ){
		e.preventDefault();
		return false;
	}
	
	
});
});