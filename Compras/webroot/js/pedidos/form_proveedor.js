(function($){

	var $provSelect = $("#PedidoProveedorId");

	var $provInfoContainer = $("#proveedor-data");

	var textoInicial = $("#btn-submit").val();

	function getProveedorData( ) {
		var proveedor = $provSelect.val();

		if ( proveedor ) {
			$provInfoContainer.load( urlProveedorPedidoInfo+"/"+proveedor, function(){
				if ( $('#checkbox-enviar-mail', $provInfoContainer).length ) {
					// si tienen btn de enviar mail 
					// (porque si el proveedor no tiene el mail configurado no aparece el boton)
				   
				    $('#checkbox-enviar-mail', $provInfoContainer).bootstrapToggle();

				    $('#checkbox-enviar-mail', $provInfoContainer).on('change', function( ev ){
				    	if (ev.target.checked) {
				    		$("#btn-submit").val(textoInicial + " ... y enviar Mail al Proveedor");
				    		$("#mensaje-enviar-mail").attr("disabled", false);
				    		
				    		$(".textarea-mail").show();

				    	} else {
				    		$("#btn-submit").val(textoInicial);
				    		$("#mensaje-enviar-mail").attr("disabled", true);
				    		$(".textarea-mail").hide();
				    	}

				    });
				    
				}
			} );
		}
	}


	$(function(){
		$("#PedidoProveedorId").on('change', getProveedorData);
	});

	getProveedorData();
}(jQuery));