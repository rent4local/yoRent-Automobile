var facebookScope = "email";
jQuery.fn.reset = function() {
    $(this).each(function() { this.reset(); });
}
$(document).ready(function() {

    $('form[rel=action]').submit(function(event) {
        event.preventDefault();
        var me = $(this);
        var frm = this;
        v = me.attr('validator');
        window[v].validate();
        if (!window[v].isValid()) return;
        var data = getFrmData(frm);
        callAjax($(this).attr('action'), data, function(response) {
            var ans = parseJsonData(response);
            if (ans.status == true) {
                $("#frmCustomShare").reset();
                $("#custom_ajax").html(ans.message);
            }
        })
        return false;
    });

    validateEmailAddress = function(e) {
        var email = e.detail.data.value;
        const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        if (false === re.test(String(email).toLowerCase())) {
            tagify.removeTag.call(tagify, e.detail.data.tag, false, false);
        } else {
            $(".submitBtnJs").removeAttr('disabled');
        }
    }

    checkEmpty = function(e) {
        if (0 == e.detail.tagify.value.length) {
            $(".submitBtnJs").attr('disabled', 'disabled');
        }
    }

    focusOut = function(e) {
        $(".submitBtnJs").addClass('disabled');
    }

    focusIn = function(e) {
        $(".submitBtnJs").removeClass('disabled');
    }

    tagifyEmailAddress = function() {
        tagify = new Tagify($(".emailAddressJs")[0], {
            whitelist: [],
            delimiters: "#",
            editTags: true,
        }).on('add', validateEmailAddress).on('remove', checkEmpty).on('blur', focusOut).on('focus', focusIn);
    };
    tagifyEmailAddress();
});

(function() {
    sendMailShareEarn = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Buyer', 'sendMailShareEarn'), data, function(t) {
            frm.reset();
            tagify.removeAllTags();
            $(".submitBtnJs").attr('disabled', 'disabled');
        });
    };

    copy = function(obj) {
        var copyText = obj.data('url');
        document.addEventListener('copy', function(e) {
            e.clipboardData.setData('text/plain', copyText);
            e.preventDefault();
        }, true);
        document.execCommand('copy');
        $.mbsmessage(langLbl.copiedText + " : " + copyText, true, 'alert--info');
    }

})();


function fbSubmit2() {
    alert("called 1");
    FB.getLoginStatus(function(response) {
        if (response.status === 'connected') {
            alert("connected");
            //facebook_redirect(response);
        } else {
            alert("not connected");
        }
    });
    alert("called 2");
}

function fbSubmit() {
    FB.getLoginStatus(function(response) {
        if (response.status === 'connected') {
            facebook_redirect(response);
        } else if (response.status === 'not_authorized') {
            FB.login(function(response) {
                facebook_redirect(response);
            }, {
                scope: facebookScope
            });
        } else {
            FB.login(function(response) {
                if (response.authResponse) {

                    //$(window.parent.document).find("#facebook_btn2").trigger("click");;

                    /* window.parent.$('#facebook_btn2').trigger('click'); */
                    //window.parent.document.getElementById("facebook_btn2").onClick();
                    //window.parent.fbSubmit();
                    facebook_redirect(response);
                }
            }, {
                scope: facebookScope
            });
        }
    });
}