<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div id="body" class="body">
    <div class="bg-brand-light pt-5 pb-5">
        <div class="container">
            <div class="row align-items-center justify-content-center">
                <div class="col-md-6">
                    <div class="section-head section--white--head section--head--center mb-0">
                        <div class="section__heading">
                            <h1><?php echo Labels::getLabel('LBL_Frequently_Asked_Questions', $siteLangId);?></h1>
                        </div>
                    </div>
                    <div class="faqsearch">
                        <form name="frmSearchFaqs" method="post" onsubmit="searchFaqsListing(this); return(false);" class="form">
                            <input placeholder="<?php echo Labels::getLabel('LBL_SEARCH', $siteLangId); ?>" id="faqQuestionJs" class="faq-input no-focus" data-field-caption="<?php echo Labels::getLabel('LBL_ENTER_YOUR_QUESTION', $siteLangId); ?>" type="search" name="question" value="">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <section class="section bg--white">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 faqSectionJs">
                    <?php if ($recordCount > 0) { ?>
                    <div class="faq-filters mb-4" id="categoryPanel"></div>
                    <?php } ?>
                    <ul class="faqlist" id="listing"></ul>
                </div>
            </div>
        </div>
    </section>

<?php if($faqCatId > 0) { ?>
    <script>
        setTimeout(function () {
            $("#"+<?php echo $faqCatId;?>).trigger("click");
        },1000);
    </script>
<?php } ?>
<script>
    var $linkMoreText = '<?php echo Labels::getLabel('Lbl_SHOW_MORE', $siteLangId); ?>';
    var $linkLessText = '<?php echo Labels::getLabel('Lbl_SHOW_LESS', $siteLangId); ?>';
    const faqsSearchStringLengthMsg = '<?php  echo CommonHelper::replaceStringData(Labels::getLabel('LBL_TYPE_ATLEAST_{LEN}_CHARACTERS_TO_SEARCH_IN_FAQS.', $siteLangId), ['{LEN}' => Faq::FAQS_SEARCH_STRING_LENGTH]);?>';
    const faqsSearchStringLength = '<?php echo Faq::FAQS_SEARCH_STRING_LENGTH; ?>';
</script>
<script>
    var clics = 0;
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
        }); //Close Function Click
    }); //Close Function Ready
    $(document).on("click", '#faqcloseall', function() {
        $('.faqanswer').fadeOut(200);
        $('h3').removeClass('faqclose');
        $('#faqcloseall').fadeOut('fast');
    });
    //search box
    $(function() {
        $(document).on("keyup", '.faq-input', function() {
            // Get user input from search box
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
</script>