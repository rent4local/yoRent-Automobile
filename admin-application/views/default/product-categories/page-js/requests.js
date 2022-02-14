$(document).ready(function(){
	searchProductCategories(document.frmSearch);

	$('input[name=\'user_name\']').autocomplete({
        'classes': {
            "ui-autocomplete": "custom-ui-autocomplete"
        },
		'source': function(request, response) {
			$.ajax({
				url: fcom.makeUrl('Users', 'autoCompleteJson'),
				data: {keyword: request['term'], fIsAjax:1},
				dataType: 'json',
				type: 'post',
				success: function(json) {
					response($.map(json, function(item) {
						return { label: item['name'] +'(' + item['username'] + ')', value: item['name'] +'(' + item['username'] + ')', id: item['id'] };
					}));
				},
			});
		},
		select: function(event, ul) {
			$("input[name='user_id']").val( ul.item.id );
		}
	});
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;

	goToSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmCategorySearchPaging;
		$(frm.page).val(page);
		searchProductCategories(frm);
	}

	reloadList = function() {
		var frm = document.frmCategorySearchPaging;
		searchProductCategories(frm);
	}

	searchProductCategories = function(form){
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		$("#listing").html('Loading....');
		fcom.ajax(fcom.makeUrl('ProductCategories', 'searchRequests'),data,function(res){
			$("#listing").html(res);
		});
	};

	clearSearch = function(){
		document.frmSearch.reset();
		document.frmSearch.user_id.value = '';
		searchProductCategories(document.frmSearch);
	};
    
    setupCategory = function() {
        var frm = $('#frmProdCategory');
        var validator = $(frm).validation({errordisplay: 3});
        if (validator.validate() == false) {
            return false;
        }
        if (!$(frm).validate()) {
            return false;
        }
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('ProductCategories', 'setup', [1]), data, function(t) {
            if(t.status == 1){
                $(document).trigger('close.facebox');
                reloadList();
            }
        });
	};
    
    editProdCatRequestForm = function(id){
		$.facebox(function() {
            prodCatRequestForm(id);
		});
    }

    prodCatRequestForm = function(id) {
		fcom.displayProcessing();
		var frm = document.frmBrandSearchPaging;
		fcom.ajax(fcom.makeUrl('ProductCategories', 'form', [id, 1]), '', function(t) {
			fcom.updateFaceboxContent(t);
		});
	};

	publishCategory = function(id,status) {
		data = 'prodcat_id=' + id + '&prodcat_status=' + status;
		fcom.displayProcessing();
		fcom.updateWithAjax(fcom.makeUrl('ProductCategories', 'changeRequestStatus', []), data, function(t) {
            if(t.status == 1){
                reloadList();
            }
        });
	}

	cancelRequest = function(id) {
		data = 'prodCatId=' + id;
		fcom.displayProcessing();
		fcom.updateWithAjax(fcom.makeUrl('ProductCategories', 'cancelRequest', []), data, function(t) {
            if(t.status == 1){
                reloadList();
            }
        });
	}


	updateStatus = function (frm) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('ProductCategories', 'changeRequestStatus'), data, function (t) {
            reloadList();
            $(document).trigger('close.facebox');
            return;
        });
        return;
    };


	updateStatusForm = function (id) {
        fcom.ajax(fcom.makeUrl('ProductCategories', 'updateStatusForm', [id]), '', function (t) {
            $.facebox(t, 'faceboxWidth');
        });
    };

})();
