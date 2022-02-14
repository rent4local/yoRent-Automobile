<?php /*?>
<div class="-fixed-wrap">
    <a href="javascript:void(0)" onClick="showRestorePopup()">
        <small>Database Will Restore in</small>
        <span id="restoreCounter">00:00:00</span>
    </a>
</div><?php */?>
<script>
    $(document).on("click", "#demoBoxClose", function(e) {
        $('.demo-header').hide();
        $('html').removeClass('sticky-demo-header');
        if (0 < $(".nav-detail-js").length) {
                var headerHeight = $("#header").height();
                $(".nav-detail-js").css('top', headerHeight);
        }
    });
    <?php
        $dateTime = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' +4 hours'));
        $restoreTime = FatApp::getConfig('CONF_RESTORE_SCHEDULE_TIME', FatUtility::VAR_STRING, $dateTime);
    ?>
    // Set the date we're counting down to
    var restoreAjaxSent = false;
    var countDownDate = new Date('<?php echo $restoreTime;?>').getTime();
    // Update the count down every 1 second
    var x = setInterval(function() {

        // Get today's date and time
        //var now = new Date().getTime();
        var currentTime = new Date();
        var currentOffset = currentTime.getTimezoneOffset();
        var ISTOffset = 330; // IST offset UTC +5:30
        var now = new Date(currentTime.getTime() + (ISTOffset + currentOffset)*60000);

        // Find the distance between now and the count down date
        var distance = countDownDate - now;

        // Time calculations for days, hours, minutes and seconds
        // var days = Math.floor(distance / (1000 * 60 * 60 * 24));
        var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((distance % (1000 * 60)) / 1000);

        var str = ('0' + hours).slice(-2) + ":" + ('0' + minutes).slice(-2) + ":" + ('0' + seconds).slice(-2);
        // Display the result in the element with id="demo"
        document.getElementById("restoreCounter").innerHTML = str;
        //$('#restoreCounter').html(str);
        var progressPercentage = 100 - (parseFloat(hours + '.' + parseFloat(minutes / 15 * 25)) * 100 / 4);
        $('.restore__progress-bar').css('width', progressPercentage + '%');
        // If the count down is finished, write some text
        if (distance < 0) {
            clearInterval(x);
            $('#restoreCounter').html("Process...");
            if(restoreAjaxSent == false) {
                showRestorePopup();
                restoreSystem();
            }
        }

    }, 1000);

    function showRestorePopup() {
        $('#restore-content-js').modal('show');       
    }
    
    function restoreSystem() {
        if (restoreAjaxSent == false) {
            restoreAjaxSent = true;
            $.mbsmessage('Restore is in process..', false, 'alert--process alert');
            setTimeout(function() {
                $("#restore-content-js .close").click();
            }, 5000);
            fcom.updateWithAjax(fcom.makeUrl('RestoreSystem', 'index', '',
                '<?php echo CONF_WEBROOT_FRONT_URL;?>'), '', function(
                resp) {
                setTimeout(function() {
                    window.location.reload();
                }, 5000);
            }, false, false);
        }
    }
</script>
<div class="modal fade" id="restore-content-js" tabindex="-1" role="dialog" aria-labelledby="restoreContentLabel" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="heading">Yo!Rent<span></span></div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="demo-data-inner">
                    <p>To enhance your demo experience, we periodically  restore our database every 4 hours.</p><br> 
                    <p>For technical issues :-</p> <ul> <li><strong>Call us at: </strong>+1 469 844 3346, +91 85919 19191, +91 95555 96666, +91 73075 70707, +91 93565 35757</li> 
                        <li><strong>Mail us at : </strong> <a href="mailto:sales@fatbit.com">sales@fatbit.com</a></li> 
                    </ul> <br> Create Your Dream Multi-vendor Ecommerce Store With Yo!Rent 
                    <a href="https://www.yo-rent.com/contact-us.html" target="_blank">Click here</a></li>
                </div>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>