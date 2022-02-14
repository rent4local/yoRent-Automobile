$(document).ready(function(){
	searchThreadMessages(document.frmMessageSrch);
});
(function() {
	var dv = '#messageListing';
	var currPage = 1;
	
	searchThreadMessages = function(frm, append ){
		if(typeof append == undefined || append == null){
			append = 0;
		}
		
		/*[ this block should be written before overriding html of 'form's parent div/element, otherwise it will through exception in ie due to form being removed from div */
		var data = fcom.frmData(frm);
		/*]*/
		if( append == 1 ){
			$(dv).find('.loader-yk').remove();
		} else {
			$(dv).html(fcom.getLoader());
		}
		
		fcom.updateWithAjax(fcom.makeUrl('Account','threadMessageSearch'), data, function(ans){
			$.mbsmessage.close();
			if( append == 1 ){
				$(dv).find('.loader-yk').remove();
				$(dv).prepend(ans.html);
			} else {
				$(dv).html(ans.html);
			}
			$("#loadMoreBtnDiv").html( ans.loadMoreBtnHtml );
		}); 
	};
	
	goToLoadPrevious = function(page) {
		if(typeof page == undefined || page == null){
			page = 1;
		}
		currPage = page;
		var frm = document.frmMessageSrch;		
		$(frm.page).val(page);
		searchThreadMessages(frm,1);
		$(dv).find('.loader-yk').remove();
	};	
	
	sendMessage = function (frm) {
		fcom.addTrailingSlash();
		if (!$(frm).validate())
			return;
		$.mbsmessage(langLbl.processing, true, 'alert--process alert');
		$.ajax({
			url: fcom.makeUrl('Account', 'sendMessage'),
			type: 'post',
			dataType: 'json',
			data: new FormData($(frm)[0]),
			cache: false,
			contentType: false,
			processData: false,
	
			success: function (ans) {
				if (ans.status == true) {
					$.mbsmessage(ans.msg, true, 'alert--success');
					setTimeout(function () {
						document.location.reload();
					}, 2000);
				} else {
					$.mbsmessage(ans.msg, true, 'alert--danger');
				}
			},
			error: function (xhr, textStatus, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	} 


	
})();