(function( $ ) {
	'use strict';
	
	var loader = '<img src="img/loading.gif"/> ';

	$(function() {
		
		$('.fy-invoice-button').click(function(e){
			
			$(this).css('pointer-events', 'none').css('cursor', 'default');
			
			e.preventDefault();
			
			if(jQuery("#post_ID").length>0){
			
				var order_id = jQuery("#post_ID").val();
			
			}else{
			
				var order_aux = jQuery(this).closest('tr').attr('id').split('-');
					
				var order_id = order_aux[1];
			
			}
			
			var td = $(this).closest('p');
			
			var esto = $(this);
			
			var data = {
				
				action: 'woo_facturante_do_ajax_request',
				
				order: order_id
			
			}
			
			/*Para addon condicion venta*/
			if($("#wf-selling-condition-metabox").length>0){
				
				data.pm = $("#wf-selling-condition-metabox").val();
			
			}
			
		
			jQuery.post( ajaxurl, data, function( data ) {
				
				console.log(data);
				
				var obj = JSON.parse(data);
				
				
				
				console.log(obj);
				
				if(obj.CrearComprobanteSinImpuestosResult.Estado=="OK"){
					
					
					var viewButton = '<a class="button tips fy-view-invoice-button" data-invoice="'+obj.CrearComprobanteSinImpuestosResult.IdComprobante+'" href="#">View invoice</a>';
					
					var awaitingButton = '<a class="button tips fy-awaiting-button" data-invoice="'+obj.CrearComprobanteSinImpuestosResult.IdComprobante+'" href="#">Awaiting invoice</a>';
					
					$.when(td.append(awaitingButton)).then(function(){
						
						
						$(".fy-view-invoice-button").click(viewInvoice);
						
						esto.remove();
						
						jQuery('<div class="notice notice-success is-dismissible"><p><strong>'+obj.CrearComprobanteSinImpuestosResult.Mensaje+'</strong></p></div>').insertAfter( jQuery('.wp-header-end') );
						
					});
					
					
					
				}else{
					alert(obj.CrearComprobanteSinImpuestosResult.Mensaje);
					jQuery('<div class="notice notice-warning is-dismissible"><p><strong>'+obj.CrearComprobanteSinImpuestosResult.Mensaje+'</strong></p></div>').insertAfter( jQuery('.wp-header-end') );
				    esto.css('pointer-events', 'none').css('cursor', 'default');
				}
				
			});
			
		});
		
		jQuery(".fy-view-invoice-button").click(viewInvoice);
		
	 });
	 
	

})( jQuery );

function viewInvoice(){
	
	
		if(jQuery("#post_ID").length>0){
			
			var order_id = jQuery("#post_ID").val();
			
		}else{
		
			var order_aux = jQuery(this).closest('tr').attr('id').split('-');
				
			var order_id = order_aux[1];
		
		}
		
		var data = {
				
				action: 'woo_facturante_view_ajax_request',
				
				order: order_id
			
		}
		
		console.log(data);
		
		jQuery.ajax({ 
			type: "POST",
			url: ajaxurl,
			async:  false,
			data: data,
			
		}).then(
		
			function( data ) {
			
				var obj = JSON.parse(data);
				
				//Verificar los estados en EstadoComprobante
				
				/*
				
				8: ESPERANDO CAE: indica que el comprobante está esperando para ser validado en AFIP. (Estado temporal)
				7: COMUNICACIÓN CON AFIP: Se está realizando la comunicación con el WS de AFIP para obtener el CAE. (Estado temporal)
				9: TIMEOUT AFIP: AFIP no respondió a tiempo la solicitud y se está resolviendo la situación. (Estado temporal)
				2: ENVIANDO: el comprobante ya obtuvo el CAE y está siendo enviado al cliente. (Estado temporal)
				4: PROCESADO: se realizó el envío al cliente con éxito y está esperando que el cliente lo abra. (Estado Final)
				6: ERROR EN COMPROBANTE: indica que el comprobante NO fue validado por AFIP. De modo que deberá evaluar el error para rehacer el comprobante salvando el mismo. (Estado Final)
				10: ESPERANDO RESPUESTA AFIP: indica que ya se solicitó la validación al WS de AFIP y se está esperando la respuesta. (Estado temporal)

				*/
				
				if(obj.DetalleComprobanteResult.Estado=="ERROR"){
					
					alert(obj.DetalleComprobanteResult.Mensaje);
					
					return false;
					
				}else{
					
					if(obj.DetalleComprobanteResult.Comprobante.EstadoComprobante==4){
					
						jQuery.when(
								true					
						).done(function(){
							
							window.open(obj.DetalleComprobanteResult.Comprobante.URLPDF);
							
						});
					
					}else{
						
						alert(obj.DetalleComprobanteResult.Comprobante.EstadoAnalitico);
						
					}
				
					
				}
				
				
			
			
			}
		
		
		);
}
