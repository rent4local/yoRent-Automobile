window.recordCount = 0;
$(document).ready(function(){
	/* searchFaqs('faq', 0); */
	faqRightPanel();
});

$(document).on("keyup", "#faqQuestionJs", function (e) {	
	if ("" == $(this).val()) {
		faqRightPanel();
	}
});

(function() {
	var dv = '#listing';
	var dvCategoryPanel = '#categoryPanel';
	var currPage = 1;
	var faqCatId = 1;
	
	reloadListing = function(){
		searchFaqs('faq', 0);
	};

	$(document).on('click','a.selectedCat',function(){
		var catId=$(this).attr('id');
		var faqId=0;
		window.location.href = fcom.makeUrl('Custom','faqDetail', [catId,faqId]);
	});
	
	$(document).on('click','a.selectedFaq',function(){
		var faqId=$(this).attr('data-id');
		var faqcatId=$(this).attr('data-cat-id');
		window.location.href = fcom.makeUrl('Custom','faqDetail', [faqcatId,faqId]);
	});

	searchFaqsListing = function (frm) {
		let ques = frm.question.value;
		if ('' == ques || 'undefined' == typeof ques) {
			return;
		}

		if (faqsSearchStringLength > ques.length) {		
			$.systemMessage(faqsSearchStringLengthMsg, 'alert--danger');
			return;
		}

		$(dv).prepend(fcom.getLoader());
		fcom.updateWithAjax(fcom.makeUrl('Custom', 'searchFaqsListing'), 'question=' + ques, function (ans) {
			$.mbsmessage.close();	
			
			$('.faqSectionJs').replaceWith(ans.html);
			highlightSearchedString();
		});
	};

	highlightSearchedString = function () {
		var filter_text = $('#faqQuestionJs').val();
		$('#listing .faqHeading').each(function () {
			if ('' !== filter_text) {
				let headingText = $(this).text();
				var startAt = headingText.toLowerCase().indexOf(filter_text
					.toLowerCase());

				if (startAt >= 0) {
					var endAt = filter_text.length;
					filter_text = headingText.substr(startAt, endAt);
					var replaceWith = "<mark>" + filter_text +
						"</mark>";
					$(this).html(headingText.replace(filter_text, replaceWith));
				} else {
					$(this).text(headingText);
				}
			

				let faqTextEle = $(this).siblings('.faqText');
				let faqTextContent = faqTextEle.find('p').text();
				console.log(faqTextContent);
				var startAt = faqTextContent.toLowerCase().indexOf(filter_text
					.toLowerCase());

				if (startAt >= 0) {
					var endAt = filter_text.length;
					filter_text = faqTextContent.substr(startAt, endAt);
					var replaceWith = "<mark>" + filter_text +
						"</mark>";
					faqTextEle.closest('.collapse').collapse('show');
					faqTextEle.find('p').html(faqTextContent.replace(filter_text, replaceWith));
				} else {
					faqTextEle.find('p').text(faqTextContent);
					faqTextEle.closest('.collapse').collapse('hide');
				}
			} else {
				$(this).text($(this).text());
				$(this).siblings('.faqText').text($(this).siblings('.faqText').find('p').text());
				$('#listing .faqText').collapse('hide');
			}
		});
	}

	searchFaqs = function(page, catId){
		if (catId < 0) {
			catId = 0;
		}
		$(dv).html(fcom.getLoader());
		if (0 < catId) {
			$('.is--active').removeClass('is--active');
			$('#'+catId).addClass('is--active');
		}
		fcom.updateWithAjax(fcom.makeUrl('Custom','SearchFaqs', [page, catId]), '', function(ans){
			$.mbsmessage.close();

				$(dv).find('.loader-yk').remove();
				$(dv).html(ans.html);

			window.recordCount = ans.recordCount;

		});
	};

	faqRightPanel = function(){

		fcom.updateWithAjax(fcom.makeUrl('Custom','faqCategoriesPanel'), '', function(ans){
			$.mbsmessage.close();

				$(dv).find('.loader-yk').remove();
				$(dvCategoryPanel).html(ans.categoriesPanelHtml);

			window.recordCount = ans.recordCount;
			searchFaqs('faq', ans.catId);
			
		},'',false);
	}
	
	goToLoadMore = function(page){
		if(typeof page == undefined || page == null){
			page = 1;
		}
		currPage = page;

		var frm = document.frmSearchFaqsPaging;
		$(frm.page).val(page);
		searchFaqs('faq', 1);
	};

})();

 /******** for faq accordians  ****************/

$(document).on('click','.accordians__trigger-js',function(){
  if($(this).hasClass('is-active')){
      $(this).removeClass('is-active');
      $(this).siblings('.accordians__target-js').slideUp();
      return false;
  }
 $('.accordians__trigger-js').removeClass('is-active');
 $(this).addClass("is-active");
 $('.accordians__target-js').slideUp();
 $(this).siblings('.accordians__target-js').slideDown();
});

$(document).on('click','.nav--vertical-js li',function(){

	if(!window.recordCount)
	{
		document.frmSearchFaqs.reset();
		$this = $(this).find('a');
		searchFaqs(document.frmSearchFaqs , 0 ,function(){$this.trigger('click');});
		event.stopPropagation();
		return false;
	}
	else{
		$('.nav--vertical-js li').removeClass('is-active');
		$(this).addClass('is-active');
	}
});

/* for click scroll function */
$(document).on('click',".scroll",function(event){

	if(!window.recordCount)
	{
		document.frmSearchFaqs.reset();
		$this = $(this);/* .find('a'); */
		searchFaqs(document.frmSearchFaqs , 0 ,function(){$this.trigger('click');});
		event.stopPropagation();
		return false;
	}
	event.preventDefault();
	var full_url = this.href;
	var parts = full_url.split("#");
	var trgt = parts[1];
	if($("#"+trgt).length){
		var target_offset = $("#"+trgt).offset();
		var target_top = target_offset.top;
		$('html, body').animate({scrollTop:target_top}, 1000);
	}
});