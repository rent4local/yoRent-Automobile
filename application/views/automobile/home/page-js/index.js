$(document).ready(function () {
	var _tab = $('.js--tabs');
    _tab.each(function() {
	var _this = $(this),
      _tabTrigger = _this.find('a'),
      _tabTarget = [];
      _tabTrigger.each(function(){
        var _this = $(this),
        _target = $(_this.attr('href'));
        _tabTarget.push(_target);
        _this.on('click', function(e){
            e.preventDefault();
            _tabTrigger.removeClass('is-active');
            $.each(_tabTarget, function(index, _thisTarget){
              _thisTarget.removeClass('visible');
            });
            _this.addClass('is-active');
            _target.addClass('visible');
        });
    });
});
});

resendOtp = function (userId, getOtpOnly = 0) {
    $.mbsmessage(langLbl.processing, false, 'alert--process');
    fcom.ajax(fcom.makeUrl('GuestUser', 'resendOtp', [userId, getOtpOnly]), '', function (t) {
        t = $.parseJSON(t);
        if (1 > t.status) {
            $.mbsmessage(t.msg, false, 'alert--danger');
            return false;
        }
        $.mbsmessage(t.msg, true, 'alert--success');
        startOtpInterval();
    });
    return false;
};
validateOtp = function (frm) {
    if (!$(frm).validate())
        return;
    var data = fcom.frmData(frm);
    fcom.ajax(fcom.makeUrl('GuestUser', 'validateOtp'), data, function (t) {
        t = $.parseJSON(t);
        if (1 == t.status) {
            $.mbsmessage(t.msg, true, 'alert--success');
            window.location.href = t.redirectUrl;
        } else {
            invalidOtpField();
        }
    });
    return false;
};