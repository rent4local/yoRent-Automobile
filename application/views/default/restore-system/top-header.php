<div class="demo-header no-print">
    <div class="restore-wrapper">
        <a href="javascript:void(0)" onclick="showRestorePopup()">

            <p class="restore__content">Database Restores in</p>
            <div class="restore__progress">
                <div class="restore__progress-bar" style="width:25%" aria-valuenow="25" aria-valuemin="0"
                     aria-valuemax="100">
                </div>
            </div>
            <span class="restore__counter" id="restoreCounter">00:00:00</span>
        </a>
    </div>
    <div class="demo-cta">
        <div class="version-num animate-flicker">  
            <a target="_blank" rel="noopener" href="https://www.yo-rent.com/online-rental-software.html">Learn about V3</a>
        </div>
    
        <a target="_blank" href="https://www.yo-rent.com/" class=" btn btn-brand btn-sm ripplelink" rel="noopener">Start Your Marketplace</a> &nbsp;
        <a <?php if (isset($isAdmin) && $isAdmin) { ?> onClick="demoRequestPopup()" <?php } else { ?>data-toggle="modal" data-target="#demoFormPopup" <?php } ?> href="javascript:void(0);" class="request-demo btn btn-outline-brand btn-sm  ripplelink" rel="noopener">
            Request a Demo
        </a>
        <a href="javascript:void(0)" class="close-layer" id="demoBoxClose"></a>
    </div>

</div>