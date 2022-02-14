
function test_fun() {
    var lastSearchString = '';
    var fontFamilyElement = $("select[name='theme_font_family']");
    $.ajax({
        url: fcom.makeUrl('ThemeColor', 'getGoogleFonts'),
        type: 'post',
        dataType: 'json',
        success: function (data) {
            fontFamilyElement.select2({

                closeOnSelect: true,
                dir: layoutDirection,
                allowClear: true,
                placeholder: fontFamilyElement.attr('placeholder'),
                data: data.fonts,
                multiple: false,
                minimumInputLength: 0,
                templateResult: function (result) {
                    return result.name;
                },
                templateSelection: function (result) {
                    return result.name || result.text;
                }
            }).on('select2:selecting', function (e) {
                var item = e.params.args.data;
                lastSearchString = $('.select2-search__field').val();
                updateFontWeightOptions(data.variantsArr[item.id]);
                /* loadGoogleFont(item); */
                return;
            }).on('select2:unselecting', function (e) {
                lastSearchString = '';
                $('link[data-font="googleFontCss--js"]').remove();
            });

            setTimeout(() => {
                var defaultFontFamily = fontFamilyElement.data('value');
                if ('undefined' != typeof defaultFontFamily && '' != defaultFontFamily) {
                    fontFamilyElement.val(defaultFontFamily).trigger('change.select2').trigger('select.select2');
                    updateFontWeightOptions(data.variantsArr[defaultFontFamily]);
                    $("#font-weight-js").val($.parseJSON(font_weights)).trigger('change');
                }
            }, 200);
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });

    $(document).on('select2:open', "select[name='theme_font_family']", function () {
        setTimeout(() => {
            if (lastSearchString) {
                $('.select2-search').find('input').val(lastSearchString).trigger('paste');
            }
            document.getElementById($(".select2-results__options").attr("id")).scrollTop = $(".select2-results__option[aria-selected=true]").outerHeight() * $(".select2-results__option[aria-selected=true]").index() - 100;
        }, 10);
    });

    $('.jscolor').trigger("input");
}

$(document).on('input', '.jscolor', function () {
    if ($(this).hasClass('themeColor--js')) {
        $("[data-jscolorSelector]:not(text)").attr('fill', $(this).val());
    }
    if ($(this).hasClass('themeColorInverse--js')) {
        $("text[data-jscolorSelector]").attr('fill', $(this).val());
    }
});

$(document).on('change', '#font-weight-js', function () {
    var name = $("select[name='theme_font_family']").val();
    var selected_weight = $("#font-weight-js").val();
    $.ajax({
        url: fcom.makeUrl('ThemeColor', 'loadGoogleFont'),
        data: {fIsAjax: 1, name: name, weight: selected_weight},
        dataType: 'json',
        type: 'post',
        success: function (resp) {
            if (null != resp.html) {
                $('link[data-font="googleFontCss--js"]').remove();
                $('head').append(resp.html);
                $('.googleFonts--js').find('text').attr('font-family', name);
                $("input[name='theme_font_family_url']").val($(resp.html).attr('href'));
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });
});

setupTheme = function (frm) {
    $('.select2-search__field').attr('name', 'testing');
    if (!$(frm).validate())
        return;
    var data = fcom.frmData(frm);
    fcom.updateWithAjax(fcom.makeUrl('ThemeColor', 'setup'), data, function (t) { });
};

resetToDefault = function () {
    if (!confirm(langLbl.confirmReset)) {
        return;
    }
    fcom.updateWithAjax(fcom.makeUrl('ThemeColor', 'resetToDefault'), '', function (t) {
        location.reload();
    });
};

$(document).on('change', 'input[name=theme_option]', function () {
    $.ajax({
        url: fcom.makeUrl('ThemeColor', 'themeForm'),
        data: {fIsAjax: 1, themeId: this.value},
        type: 'post',
        success: function (resp) {
            $('#theme-form-js').html(resp);
            test_fun();
            jscolor.install();
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });
});

function updateFontWeightOptions(variants)
{
    var data = [];
    $.each(variants, function (key, variant) {
        data.push({
            id: variant,
            name: variant,
        });
    });

    $('.dropdown-font-weight-js').html(font_weight_html);
    $("#font-weight-js").html('');
    $("#font-weight-js").select2({
        closeOnSelect: false,
        dir: layoutDirection,
        allowClear: true,
        multiple: true,
        placeholder: $("#font-weight-js").attr('placeholder'),
        data: data,

        minimumInputLength: 0,
        templateResult: function (result) {
            return result.name;
        },
        templateSelection: function (result) {
            return result.name || result.text;
        }
    });
}