function changeCompProdOption(selProd) 
{
    var selProdId = $('#product-options-'+selProd+' option:selected').attr('selProdId');
    var prodId = $('#product-options-'+selProd+' option:selected').attr('prodId');

    if (selProdId == '' || prodId == '') {
        alert('Option Not Available'); 
    } else if (selProdId == 0) {
        alert('Option already selected');
    } else {
        changeOption(selProdId, prodId);
    }
}

function moreDetail(selProdId) 
{
    $.facebox({ div: '#term-and-condition-'+selProdId }, 'faceboxWidth productQuickView rental-terms-and-conditions');
}


function DropDown(el) {
        this.dd = el;
        this.placeholder = this.dd.children('span');
        this.opts = this.dd.find('ul.drop li');
        this.val = '';
        this.index = -1;
        this.initEvents();
    }

    DropDown.prototype = {
        initEvents: function() {
            var obj = this;
            obj.dd.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).toggleClass('active');
            });
            obj.opts.on('click', function() {
                var opt = $(this);
                obj.val = opt.text();
                obj.index = opt.index();
                obj.placeholder.text(obj.val);
                opt.siblings().removeClass('selected');
                opt.filter(':contains("' + obj.val + '")').addClass('selected');
                var link = opt.filter(':contains("' + obj.val + '")').find('a').attr('href');
                window.location.replace(link);
            }).change();
        },
        getValue: function() {
            return this.val;
        },
        getIndex: function() {
            return this.index;
        }
    };
    
$(function() {

		$(".js-wrap-drop").each(function(index, element) {
            var div = '#js-wrap-drop' + index;
            new DropDown($(div));
        });
		/*  var dd1 = new DropDown($('.js-wrap-drop'));
         create new variable for each menu */
        $(document).click(function() {
           /*   close menu on document click */
            $('.wrap-drop').removeClass('active');
        });
		$('.js-wrap-drop').click(function() {
			$(this).parent().siblings().children('.js-wrap-drop').removeClass('active');
			/*  $(this).siblings().children('.js-wrap-drop').addClass('active'); */
		});
    });