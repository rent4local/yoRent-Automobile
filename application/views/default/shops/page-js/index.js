$(document).ready(function(){
	searchShops(document.frmSearchShops);
});

(function() {
    var dv = '#listing';
	var currPage = 1;
        let dragTimeOutEvent;
	
	reloadListing = function(){
		searchShops(document.frmSearchShops);
	};
	
        searchShops = function (frm, append) {
            if (typeof append == undefined || append == null) {
                append = 0;
            }

            var data = fcom.frmData(frm);
            if (append == 1) {
                $(dv).prepend(fcom.getLoader());
            } else {
                $(dv).html(fcom.getLoader());
            }

            fcom.updateWithAjax(fcom.makeUrl('Shops', 'search'), data, function (ans) {
                $.mbsmessage.close();
                if (append == 1) {
                    $(document.frmSearchShopsPaging).remove();
                    $(dv).find('.loader-yk').remove();
                    $(dv).append(ans.html);
                } else {
                    $(dv).html(ans.html);
                }
                $("#loadMoreBtnDiv").html(ans.loadMoreBtnHtml);
                $("#favShopCount").html(ans.totalRecords);
                if (typeof map == 'undefined') {
                    initMutipleMapMarker(markers, 'shopMap--js', USER_LAT, USER_LNG, dragCallback);
                } else {
                    clearMarkers();
                    createMarkers(markers);
                }

            });
        };
        
        goToShopSearchPage = function(page) {
            if (typeof page == undefined || page == null) {
                page = 1;
            }
            var frm = document.frmSearchShopsPaging;
            $(frm.page).val(page);
            searchShops(frm);
        };
		
	unFavoriteShopFavorite = function(shopId,e){
		toggleShopFavorite(shopId);
		$(e).attr('onclick','markShopFavorite('+shopId+',this)');
		$(e).html(langLbl.favoriteToShop);

	};
	
	markShopFavorite = function(shopId,e){
		toggleShopFavorite(shopId);
		$(e).attr('onclick','unFavoriteShopFavorite('+shopId+',this)');
		console.log(e);
				$(e).html(langLbl.unfavoriteToShop);
		
	};
        dragCallback = function(dragendMap){
                canSetCookie = true;
                codeLatLng(dragendMap.getCenter().lat(),dragendMap.getCenter().lng(),function(data){ 
                    displayGeoAddress(setGeoAddress(data));  
                    clearTimeout(dragTimeOutEvent);                    
                    dragTimeOutEvent = setTimeout(function(){  reloadListing(); }, 1000);
                });
	};
       
})();

$(document).on('mouseover mouseout', '#mapShops--js > li', function (e) {
    let shopId = $(this).data('shopid');   
    $.each(mapMarker, function (index, marker) {
        if(typeof marker != 'undefined'){                
            let iconImage = fcom.makeUrl()+'images/pin.png';
            if(marker['refId'] == shopId  && e.type == 'mouseover'){
                iconImage = fcom.makeUrl()+'images/pin2.png';
            }
            marker.setIcon(iconImage);           
        }
    });
})