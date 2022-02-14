$(document).ready(function () {
    var type = $('input[name="fulfillment_type"]:checked').val();
    listCartProducts(type);
});

(function () {
    listCartProducts = function (fulfilmentType = 2, activeToggle = '') {
        if (fulfilmentType == 2) {
            $("#shipping").prop("checked", true);
            $("#pickup").prop("checked", false);
            $("#shipping").closest("label").addClass('is-active');
            $("#pickup").closest("label").removeClass('is-active');
        }
        if (fulfilmentType == 1) {
            $("#pickup").prop("checked", true);
            $("#shipping").prop("checked", false);
            $("#pickup").closest("label").addClass('is-active');
            $("#shipping").closest("label").removeClass('is-active');
        }
        if (activeToggle == '') {
            $('#cartList').html(fcom.getLoader());
        }
        
        fcom.ajax(fcom.makeUrl('Cart', 'listing', [fulfilmentType]), '', function (res) {
            var json = $.parseJSON(res);
            if (json.hasPhysicalProduct == false) {
                $("#js-shiporpickup").remove();
            }

            if (json.cartProductsCount == 0) {
                $("#js-cart-listing").html(json.html);
            } else {
                $("#cartList").html(json.html);
                getCartFinancialSummary(fulfilmentType);
            }

            /* if (json.shipProductsCount == 0) {
                $("#pickup").prop("checked", true);
                $("#shipping").prop("checked", false).prop("disabled", true).next('label').addClass("disabled").parent().attr("onclick", null);
            } else {
                $("#shipping").prop("disabled", false).next('label').removeClass("disabled").parent().attr("onclick", "listCartProducts(2)");
            } */

            /* if (json.pickUpProductsCount == 0) {
                $("#shipping").prop("checked", true);
                $("#pickup").prop("checked", false).prop("disabled", true).next('label').addClass("disabled").parent().attr("onclick", null);
            } else {
                $("#pickup").prop("disabled", false).next('label').removeClass("disabled").parent().attr("onclick", "listCartProducts(1)");
            } */
            
            if (activeToggle != '') {
                $('#collapseExample_'+ activeToggle).addClass('show');
            }
        });
    };

    getPromoCode = function () {
        if (isUserLogged() == 0) {
            loginPopUpBox(true);
            return false;
        }

        /* $.facebox(function () { */
            fcom.ajax(fcom.makeUrl('Checkout', 'getCouponForm'), '', function (t) {
                try {
                    t = $.parseJSON(t);
                    if (typeof t.status != 'undefined' && 1 > t.status) {
                        $.systemMessage(t.msg, 'alert--danger', false);
                        /* $("#facebox .close").trigger('click'); */
                        $("#exampleModal .close").click();
                        if (typeof t.url != 'undefined') {
                            setTimeout(function () {
                                document.location.href = t.url;
                            }, 1000);
                        }
                        return false;
                    }
                } catch (exc) {
                }
                /* $.facebox(t, 'faceboxWidth medium-fb-width'); */
                $('#exampleModal').html(t);
                $('#exampleModal').modal('show');
                $("input[name='coupon_code']").focus();
            });
        /* }); */
    };

    triggerApplyCoupon = function (coupon_code) {
        document.frmPromoCoupons.coupon_code.value = coupon_code;
        applyPromoCode(document.frmPromoCoupons);
        return false;
    };

    applyPromoCode = function (frm) {
        if (isUserLogged() == 0) {
            loginPopUpBox(true);
            return false;
        }
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        var type = $('input[name="fulfillment_type"]:checked').val();
        fcom.updateWithAjax(fcom.makeUrl('Cart', 'applyPromoCode'), data, function (res) {
            /* $("#facebox .close").trigger('click'); */
            $("#exampleModal .close").click();
            $.systemMessage.close();
            listCartProducts(type);
        });
        };

    goToCheckout = function () {
        var type = $('input[name="fulfillment_type"]:checked').val();
        var data = "type=" + type;
        fcom.updateWithAjax(fcom.makeUrl('Cart', 'setCartCheckoutType'), data, function (ans) {
            if (isUserLogged() == 0) {
                loginPopUpBox(true);
                return false;
            }
            document.location.href = fcom.makeUrl('Checkout');
        });
    };

    removePromoCode = function () {
        var type = $('input[name="fulfillment_type"]:checked').val();
        fcom.updateWithAjax(fcom.makeUrl('Cart', 'removePromoCode'), '', function (res) {
            listCartProducts(type);
        });
    };

    moveToWishlist = function (selprod_id, event, key) {
        event.stopPropagation();
        if (isUserLogged() == 0) {
            loginPopUpBox();
            return false;
        }
        var data = 'kay=' + key;

        fcom.ajax(fcom.makeUrl('Account', 'moveToWishList', [selprod_id]), data, function (resp) {
            removeFromCart(key);
        });
    };

    addToFavourite = function (key, selProdId) {
        if (isUserLogged() == 0) {
            loginPopUpBox();
            return false;
        }
        $.mbsmessage.close();
        fcom.updateWithAjax(fcom.makeUrl('Account', 'markAsFavorite', [selProdId]), '', function (ans) {
            if (ans.status) {
                $("[data-id=" + selProdId + "]").addClass("is-active");
                $("[data-id=" + selProdId + "]").attr("onclick", "removeFromFavorite(" + selProdId + ")");
                $("[data-id=" + selProdId + "] span").attr('title', langLbl.RemoveProductFromFavourite);
                $('.heart-wrapper--js .icon-unchecked--js').hide();
                $('.heart-wrapper--js .icon-checked--js').show();
            }
            /* if (ans.status) {
                removeFromCart(key);
            } */
        });
    };

    moveToSaveForLater = function (key, selProdId, fulfilmentType) {
        if (isUserLogged() == 0) {
            loginPopUpBox();
            return false;
        }
        $.mbsmessage.close();
        var data = 'key=' + key;
        fcom.updateWithAjax(fcom.makeUrl('Account', 'moveToSaveForLater', [selProdId]), data, function (ans) {
            if (ans.status) {
                listCartProducts(fulfilmentType);
                $.mbsmessage(langLbl.MovedSuccessfully, true, 'alert--success');
				if (1 > ans.totalProducts) {
					$('#js-cart-listing').removeClass('col-xl-10').addClass('col-xl-9');
					$('#js-cart-listing').parents('.container').addClass('container--narrow');
				}
			}
		});
    };

    removeFromWishlist = function (selprod_id, wish_list_id, event) {
        if (!confirm(langLbl.confirmDelete)) {
            return false;
        }
        ;
        addRemoveWishListProduct(selprod_id, wish_list_id, event);
        listCartProducts();
    };

    moveToCart = function (selprod_id, wish_list_id, event, fulfilmentType) {
        var data = 'selprod_id[0]=' + selprod_id;
        fcom.updateWithAjax(fcom.makeUrl('cart', 'addSelectedToCart'), data, function (ans) {
            addRemoveWishListProduct(selprod_id, wish_list_id, event);
            listCartProducts(fulfilmentType);
            $('#cartSummary').load(fcom.makeUrl('cart', 'getCartSummary'));
            setTimeout(function () {
                if (1 > $("#cartList").length) {
                    location.reload();
                }
            }, 500);
        });
    };

    removePickupOnlyProducts = function () {
        if (confirm(langLbl.confirmPickOnlyItems)) {
            fcom.updateWithAjax(fcom.makeUrl('Cart', 'removePickupOnlyProducts'), '', function (ans) {
                listCartProducts(2);
                $('#cartSummary').load(fcom.makeUrl('cart', 'getCartSummary'));
            });
        }
    }

    removeShippedOnlyProducts = function () {
        if (confirm(langLbl.confirmShipOnlyItems)) {
            fcom.updateWithAjax(fcom.makeUrl('Cart', 'removeShippedOnlyProducts'), '', function (ans) {
                listCartProducts(1);
                $('#cartSummary').load(fcom.makeUrl('cart', 'getCartSummary'));
            });
        }
    }

    setCheckoutType = function (type) {
        var data = "type=" + type;
        fcom.updateWithAjax(fcom.makeUrl('Cart', 'setCartCheckoutType'), data, function (ans) {
            if (isUserLogged() == 0) {
                loginPopUpBox(true);
                return false;
            }
            document.location.href = fcom.makeUrl('Checkout');
        });
    }

    getCartFinancialSummary = function (type) {
        fcom.ajax(fcom.makeUrl('Cart', 'getCartFinancialSummary', [type]), '', function (res) {
            $("#js-cartFinancialSummary").html(res);
            if (type == 1 && $('input[name="pickup_product_count"]').val() == 0) {
                $('.checkout-btn--js').attr('disabled', 'disabled');
                $('.checkout-btn--js').addClass('disabled-input');
                
                $('.btn-coupon--js').attr('disabled', 'disabled');
                $('.btn-coupon--js').addClass('disabled-input');
                
            } else if(type == 2 && $('input[name="ship_product_count"]').val() == 0) {
                $('.checkout-btn--js').attr('disabled', 'disabled');
                $('.checkout-btn--js').addClass('disabled-input');
                
                $('.btn-coupon--js').attr('disabled', 'disabled');
                $('.btn-coupon--js').addClass('disabled-input');
                
            } else if($('input[name="ship_product_count"]').val() > 0 || $('input[name="pickup_product_count"]').val() > 0) {
                $('.checkout-btn--js').removeAttr('disabled');
                $('.checkout-btn--js').removeClass('disabled-input');
                
                $('.btn-coupon--js').removeAttr('disabled');
                $('.btn-coupon--js').removeClass('disabled-input');
                
            }
        });
    }

    addAddonToCart = function (obj) {
        var type = $('input[name="fulfillment_type"]:checked').val();
        var mainProductKey = $(obj).data('mainproductkey');
        if ($(obj).prop("checked") == true) {
            var selprod_id = $(obj).data('selprodid');
            var data = "selprodId=" + selprod_id + "&mainProductKey=" + mainProductKey;
            fcom.updateWithAjax(fcom.makeUrl('Cart', 'addRentalAddons'), data, function (ans) {
               listCartProducts(type, mainProductKey);
            });
        } else {
            cart.remove($(obj).data('cartkey'), 'cart', '', mainProductKey);
        } 
    }

    showFullfillmentPopup = function(fullfillmentType) {
        if (fullfillmentType == 1) {
            var msg = langLbl.cartPickupNotAvailMsg;
        } else {
            var msg = langLbl.cartShipNotAvailMsg;
        }
    
    
        var msgHtml = `<div class="modal-dialog modal-dialog-centered" role="document" id="fullfillment-warning">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p>`+ msg +`</p>
                            </div>
                        </div>
                    </div>`;
    
        $('#exampleModal').html(msgHtml);
        $('#exampleModal').modal('show');
    }
    
    
})();
