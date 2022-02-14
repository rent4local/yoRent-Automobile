$(document).ready(function(){
	profileInfoForm();
});

(function() {
	var runningAjaxReq = false;
	var dv = '#profileInfoFrmBlock';
	var imgdv = '#profileImageFrmBlock';

	profileInfoForm = function(){
		$(dv).html(fcom.getLoader());
		$("#tab-myaccount").parents().children().removeClass("is-active");
		$("#tab-myaccount").addClass("is-active");
		fcom.ajax(fcom.makeUrl('Account', 'profileInfoForm'), '', function(t) {
            $(dv).html(t);
            stylePhoneNumberFld();
		});
	};

	profileImageForm = function(){
		$(imgdv).html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('Account', 'profileImageForm'), '', function(t) {
            location.reload();
			/* $(imgdv).html(t); */
		});
	};

	updateProfileInfo = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Account', 'updateProfileInfo'), data, function(t) {
			profileInfoForm();
			$.mbsmessage.close();
		});
	};

	setPreferredDashboad = function (id){
		fcom.updateWithAjax(fcom.makeUrl('Account','setPrefferedDashboard',[id]),'',function(res){
		});
	};

	bankInfoForm = function(){
		$(dv).html(fcom.getLoader());
		$("#tab-bankaccount").parents().children().removeClass("is-active");
		$("#tab-bankaccount").addClass("is-active");
		fcom.ajax(fcom.makeUrl('Account','bankInfoForm'),'',function(t){
			$(dv).html(t);
		});
	};
	settingsForm = function(){
		$(dv).html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('Account','settingsInfo'),'',function(t){
			$(dv).html(t);
		});
	};
	setSettingsInfo = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Account', 'updateSettingsInfo'), data, function(t) {
			settingsForm();
		});
	};
	setBankInfo = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Account', 'updateBankInfo'), data, function(t) {
			bankInfoForm();
		});
	};

	removeProfileImage = function(){
		fcom.ajax(fcom.makeUrl('Account','removeProfileImage'),'',function(t){
			profileImageForm();
		});
	};

	sumbmitProfileImage = function(){
		$("#frmProfile").ajaxSubmit({
			delegation: true,
			success: function(json){
				json = $.parseJSON(json);
				profileImageForm();
				
				$("#exampleModal .close").click()
			}
		});
	};

	affiliatePaymentInfoForm = function(){
		$(dv).html(fcom.getLoader());
		$("#tab-paymentinfo").parents().children().removeClass("is-active");
		$("#tab-paymentinfo").addClass("is-active");
		fcom.ajax(fcom.makeUrl('Affiliate','paymentInfoForm'),'',function(t){
			$(dv).html(t);
		});
	}

	setUpAffiliatePaymentInfo = function( frm ){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Affiliate', 'setUpPaymentInfo'), data, function(t) {
			
		});
	}

	popupImage = function(inputBtn){
		if (inputBtn) {
			if(inputBtn.files && inputBtn.files[0]){
				fcom.ajax(fcom.makeUrl('Account', 'imgCropper'), '', function(t) {
					
					$('#exampleModal').html(t);
        			$('#exampleModal').modal('show');
					var file = inputBtn.files[0];
					var options = {
					aspectRatio: 1 / 1,
					preview: '.img-preview',
					imageSmoothingQuality: 'high',
					imageSmoothingEnabled: true,
					crop: function (e) {
					  var data = e.detail;
					}
				  };
				  $(inputBtn).val('');
				  return cropImage(file, options, 'saveProfileImage', inputBtn);
				});
			}
		} else {
			fcom.ajax(fcom.makeUrl('Account', 'imgCropper'), '', function(t) {
				
				$('#exampleModal').html(t);
        		$('#exampleModal').modal('show');
				var container = document.querySelector('.img-container');
				var image = container.getElementsByTagName('img').item(0);
				var options = {
				aspectRatio: 1 / 1,
				preview: '.img-preview',
				imageSmoothingQuality: 'high',
				imageSmoothingEnabled: true,
				crop: function (e) {
				  var data = e.detail;
				}
			  };
			  return cropImage(image, options, 'saveProfileImage');
			});
		}
	};
    
	saveProfileImage = function(formData){
		$.ajax({
			url: fcom.makeUrl('Account', 'uploadProfileImage'),
			type: 'post',
			dataType: 'json',
			data: formData,
			cache: false,
			contentType: false,
			processData: false,
			beforeSend: function() {
				$('#loader-js').html(fcom.getLoader());
			},
            complete: function() {
                $('#loader-js').html(fcom.getLoader());
            },
			success: function(ans) {
				if(ans.status > 0) {
					$.mbsmessage(ans.msg, true, 'alert--success');
				}else{
					$.mbsmessage(ans.msg, true, 'alert--danger');
				}
					profileInfoForm();		
					$("#exampleModal .close").click();
					return;
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
		});
	}

	truncateDataRequestPopup = function(){
			fcom.ajax(fcom.makeUrl('Account', 'truncateDataRequestPopup'), '', function(t) {
				$('#exampleModal').html(t);
        		$('#exampleModal').modal('show');
			});
		
	};

	sendTruncateRequest = function(){
		/* var agree = confirm( langLbl.confirmDeletePersonalInformation );
		if( !agree ){
			return false;
		} */
		fcom.updateWithAjax(fcom.makeUrl('Account', 'sendTruncateRequest'), '', function(t) {
			profileInfoForm();
			$("#exampleModal .close").click();
		});
	};

	cancelTruncateRequest = function(){
		$("#exampleModal .close").click();
	};

	requestData = function(){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Account', 'requestDataForm'), '', function(t) {
				$('#exampleModal').html(t);
        		$('#exampleModal').modal('show');
			});
		});
	};

	setupRequestData = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Account', 'setupRequestData'), data, function(t) {
			$("#exampleModal .close").click();
            if ($('#gdpr-block--js').hasClass('is-active')) {
                getGdprData();
            }
		});
    };
    
    pluginForm = function(keyName){
		$(dv).html(fcom.getLoader());
		$("ul.tabs-js li").removeClass("is-active");
		$("#tab-" +keyName ).addClass("is-active");
		fcom.ajax(fcom.makeUrl(keyName, 'form'),'',function(t){
			$(dv).html(t);
		});
    };
    
    setupPluginForm = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl(frm.keyName.value, 'setupAccountForm'), data, function(t) {
			pluginForm(frm.keyName.value);
		});
	};
    
    getGdprData = function() {
		$(dv).html(fcom.getLoader());
		$("#gdpr-block--js").parents().children().removeClass("is-active");
		$("#gdpr-block--js").addClass("is-active");
		fcom.ajax(fcom.makeUrl('GdprRequests'), '', function(t) {
            $(dv).html(t);
        });
	};
    
    downloadData = function(actionType) {
		/* document.frmImportExport.action = fcom.makeUrl('GdprRequests', 'downloadRequestData', [actionType]);
		document.frmImportExport.submit(); */
	};
    
    
})();
