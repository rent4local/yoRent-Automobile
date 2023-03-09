window.recordCount = 0;
$(document).ready(function(){
	$(document).on('click','.acc_ctrl',function(e){
		/* $(".questions-section").hide(); */
		e.preventDefault();
		if ($(this).hasClass('active')) {
			$(this).removeClass('active');
			$(this).next()
			.stop()
			.slideUp(300);
		} else {
			$(this).addClass('active');
			$(this).next()
			.stop()
			.slideDown(300);
		}
	});
	searchFaqs('seller', 0);
	faqRightPanel();
});

$(document).on("keyup", "#faqQuestionJs", function (e) {
	if ("" == $(this).val()) {
		faqRightPanel();
		searchFaqs('seller', 0);
	}
});

(function() {
	var dv = '#listing';
	var dvCategoryPanel = '#categoryPanel';
	var currPage = 1;
	var faqCatId=1;
	reloadListing = function(){
		searchFaqs('seller', 0);
	};

	$(document).on('click','a.selectedCat',function(){
		var catId=$(this).attr('id');
		searchFaqs('seller', catId);
	});

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
		fcom.updateWithAjax(fcom.makeUrl('Custom', 'searchFaqsListing',[1]), 'question=' + ques, function (ans) {
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

	faqRightPanel = function(){
		fcom.updateWithAjax(fcom.makeUrl('supplier','faqCategoriesPanel'), '', function(ans){
			$.mbsmessage.close();
			$(dv).find('.loader-yk').remove();
			$(dvCategoryPanel).html(ans.categoriesPanelHtml);
			if(ans.recordCount!=0){
				$(".questions-section").show();
			}
			window.recordCount = ans.recordCount;
		});
	}

	goToLoadMore = function(page){
		if(typeof page == undefined || page == null){
			page = 1;
		}
		currPage = page;

		var frm = document.frmSearchFaqsPaging;
		$(frm.page).val(page);
		searchFaqs('seller',1);
	};
})();

/******** for faq accordians  ****************/
$(document).on('click','.accordians__trigger-js',function(){
	if( $(this).hasClass('is-active') ){
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
	if( !window.recordCount ){
		document.frmSearchFaqs.reset();
		$this = $(this).find('a');
		searchFaqs(document.frmSearchFaqs , 0 ,function(){$this.trigger('click');});
		event.stopPropagation();
		return false;
	}else{
		$('.nav--vertical-js li').removeClass('is-active');
		$(this).addClass('is-active');
	}
});

/* for click scroll function */
$(document).on('click',".scroll",function( event ){
	if( !window.recordCount ){
		document.frmSearchFaqs.reset();
		$this = $(this);
		searchFaqs(document.frmSearchFaqs , 0 ,function(){$this.trigger('click');});
		event.stopPropagation();
		return false;
	}
	event.preventDefault();
	var full_url = this.href;
	var parts = full_url.split("#");
	var trgt = parts[1];
	if( $("#"+trgt).length ){
		var target_offset = $("#"+trgt).offset();
		var target_top = target_offset.top;
		$('html, body').animate({scrollTop:target_top}, 1000);
	}
});
$(document).ready(function() {
	$('.faqanswer').hide();
	$('#faqcloseall').hide();
	$(document).on("click", 'h3', function() {
		$(this).next('.faqanswer').toggle(function() {
			$(this).next('.faqanswer');
		}, function() {
			$(this).next('.faqanswer').fadeIn('fast');
		});
		if ($(this).hasClass('faqclose')) {
			$(this).removeClass('faqclose');
		} else {
			$(this).addClass('faqclose');
		};
		if ($('.faqclose').length >= 3) {
			$('#faqcloseall').fadeIn('fast');
		} else {
			$('#faqcloseall').hide();
			var yolo = $('.faqclose').length
		}
	}); 
}); 
$(document).on("click", '#faqcloseall', function() {
	$('.faqanswer').fadeOut(200);
	$('h3').removeClass('faqclose');
	$('#faqcloseall').fadeOut('fast');
});

$(function() {
	$(document).on("keyup", '.faq-input', function() {
		
		var filter_text = $(this).val();
		var replaceWith = "<span class='js--highlightText'>"+filter_text+"</span>";
		var re = new RegExp(filter_text, 'g');

		$('.faqlist h3').each(function() {
			if ('' !== filter_text) {
				if ($(this).text().toLowerCase().indexOf(filter_text) >= 0) {
					var content = $(this).text();
					$(this).siblings( ".faqanswer" ).slideDown();
					$(this).html(content.replace(re, replaceWith));
				} else {
					$(this).text($(this).text());
					$(this).siblings( ".faqanswer" ).slideUp();
				}
			} else {
				$(this).text($(this).text());
				$('.faqlist h3').siblings( ".faqanswer" ).slideUp();
			}
		})
	});
});