$("document").ready(function(){	

	
	$(".btnProductBuy--js").on('click', function(event){
		event.preventDefault();
		
		var selprod_id = $(this).attr('data-id');
		var quantity = $(this).attr('data-min-qty');
		cart.add( selprod_id, quantity, true);
		return false;
	});
	
	$(".btnAddToCart--js").on('click', function(event){
        event.preventDefault();
	 	var data = $("#frmBuyProduct").serialize();
			var selprod_id = $(this).attr('data-id');
			var quantity = $(this).attr('data-min-qty');
            data = "selprod_id="+selprod_id+"&quantity="+quantity;
            events.addToCart();
			fcom.updateWithAjax(fcom.makeUrl('cart', 'add' ),data, function(ans) {
                if (ans['redirect']) {
					alert('dfadsf');
                    location = ans['redirect'];
                    return false;
                }
                $('span.cartQuantity').html(ans.total);
                $('#cartSummary').load(fcom.makeUrl('cart', 'getCartSummary'));
			});
			return false;
		}); 
});

