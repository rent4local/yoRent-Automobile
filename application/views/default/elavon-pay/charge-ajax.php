<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$frm->setFormTagAttribute('class', 'form');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;
$frm->setFormTagAttribute('onSubmit', "pay(this); return false;");

$expMonthFld = $frm->getField('exp_month');
$expMonthFld->developerTags['col'] = 4;

$expYearFld = $frm->getField('exp_year');
$expYearFld->developerTags['col'] = 4;

$cvvFld = $frm->getField('cvv');
$cvvFld->developerTags['col'] = 4;

$btnPay = $frm->getField('btn_pay'); 
$btnPay->setFieldTagAttribute('class', "btn btn-brand btn-wide btn-pay-js");
echo $frm->getFormHtml();

?>

<?php if($env == Plugin::ENV_PRODUCTION ) { ?>
    <script src="https://api.convergepay.com/hosted-payments/Checkout.js"></script>
<?php }else if($env == Plugin::ENV_SANDBOX){ ?>
    <script src="https://api.demo.convergepay.com/hosted-payments/Checkout.js"></script>
<?php } ?>
<script type="text/javascript">
    function pay(frm) {
        if (!$(frm).validate()) {
            return false;
        }
        $.mbsmessage(langLbl.processing, false, 'alert--process alert');
        $(".btn-pay-js").attr('disabled', 'disabled');     
        var orderId = $('[name ="ssl_invoice_number"]').val();
        
        var paymentData = {
            ssl_txn_auth_token: $('[name ="token"]').val(),
            ssl_card_number: $('[name ="card_number"]').val(),
            ssl_exp_date: $('[name ="exp_month"]').val()+''+$('[name ="exp_year"]').val(),
            ssl_cvv2cvc2: $('[name ="cvv"]').val(),
            ssl_first_name: $('[name ="first_name"]').val(),          
            ssl_invoice_number: orderId,
        };
            
        var callback = {
            onError: function (error) { 
                $(".btn-pay-js").removeAttr('disabled');
                $.mbsmessage(error, true, 'alert--danger');                
                return false;
            },
            onDeclined: function (response) {   
                $(".btn-pay-js").removeAttr('disabled');
                var errMsg = response.errorName;
                if(errMsg == ''){
                    errMsg = langLbl.paymentDeclined;
                }
                $.mbsmessage(errMsg, true, 'alert--danger');
                return false;
            },
            onApproval: function (response) {   
                var data = {response : JSON.stringify(response)};
                fcom.ajax(fcom.makeUrl('ElavonPay', 'paymentApproved', [orderId]), data, function(rsp) {
                    var ans = JSON.parse(rsp);
                    if(ans.status == 1){
                        window.location.href = fcom.makeUrl('Custom', 'paymentSuccess', [orderId]);
                    }else{
                        $.mbsmessage(ans.msg, true, 'alert--danger');
                    }
                });
                return false;
            }
        };

        ConvergeEmbeddedPayment.pay(paymentData, callback);
        return false;
    }
</script>

