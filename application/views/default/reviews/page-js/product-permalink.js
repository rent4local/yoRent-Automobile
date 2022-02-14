function reviewAbuse(reviewId){
	if(reviewId){
	
			fcom.ajax(fcom.makeUrl('Reviews', 'reviewAbuse', [reviewId]), '', function(t) {
				$('#exampleModal').html(t);
            	$('#exampleModal').modal('show');
			});
	
	}
}

function setupReviewAbuse(frm){
	if (!$(frm).validate()) return;
	var data = fcom.frmData(frm);
	fcom.updateWithAjax(fcom.makeUrl('Reviews', 'setupReviewAbuse'), data, function(t) {
		$("#exampleModal .close").click();
	});
	return false;
}

(function() {

	markReviewHelpful = function(reviewId , isHelpful){
		if( isUserLogged() == 0 ){
			loginPopUpBox();
			return false;
		}
		isHelpful = (isHelpful) ? isHelpful : 0;
		var data = 'reviewId='+reviewId+'&isHelpful=' + isHelpful;
		fcom.updateWithAjax(fcom.makeUrl('Reviews','markHelpful'), data, function(ans){

		});
	}

})();
