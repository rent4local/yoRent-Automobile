$(document).ready(function(){
	searchOptionValueListing(document.frmSearch);
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;

	goToSearchPage = function(page) {	
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmSearchOptionValuePaging;		
		$(frm.page).val(page);
		searchOptionValueListing(frm);
	}

	reloadList = function() {
		var frm = document.frmSearchOptionValuePaging;
		searchOptionValueListing(frm);
	}

	addOptionValueForm = function(optionId,id) {
		var frm = document.frmSearchOptionValuePaging;		
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('OptionValues', 'form', [optionId,id]), '', function(t) {
				try{
					res= jQuery.parseJSON(t);
					$.facebox(res.msg,'faceboxWidth');
				}catch (e){
					
					$.facebox(t,'faceboxWidth');
					
				}
					
				
			});
		});
	};

	setUpOptionValues = function(frm) {
		if (!$(frm).validate()) return;		
		var data = fcom.frmData(frm);		
		fcom.updateWithAjax(fcom.makeUrl('OptionValues', 'setup'), data, function(t) {			
			$.mbsmessage.close();
			reloadList();
			if (t.langId>0) {
				optionValueLangForm(t.optionValueId, t.langId);
				return ;
			}
			$("#exampleModal .close").click();
		});
	};

	optionValueLangForm = function(optionValueId, langId, autoFillLangData = 0) {		
		
			fcom.ajax(fcom.makeUrl('OptionValues', 'langForm', [optionValueId, langId, autoFillLangData = 0]), '', function(t) {
				$('#exampleModal').html(t);
            	$('#exampleModal').modal('show');
			});
	
	};
	
	setUpOptionValueLang=function(frm){ 
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);		
		fcom.updateWithAjax(fcom.makeUrl('OptionValues', 'langSetup'), data, function(t) {			
			$.mbsmessage.close();
			reloadList();				
			if (t.langId>0) {
				optionValueLangForm(t.optionValueId, t.langId);
				return ;
			}
			$("#exampleModal .close").click();
		});
	};

	searchOptionValueListing = function(form){		
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		$("#optionValueListing").html('Loading....');
		fcom.ajax(fcom.makeUrl('OptionValues','search'),data,function(res){
			$("#optionValueListing").html(res);
		});
	};
	
	deleteOptionValueRecord=function(id){
		if(!confirm(langLbl.confirmDelete)){return;}
		data='id='+id;
		fcom.ajax(fcom.makeUrl('OptionValues','deleteRecord'),data,function(res){		
			reloadList();
		});
	};
	
	clearOptionValueSearch = function(){
		document.frmSearch.reset();
		searchOptionValueListing(document.frmSearch);
	};

})();
