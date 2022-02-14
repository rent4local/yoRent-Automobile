<div class="modal-dialog modal-dialog-centered" role="document" id="instruction-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><?php echo Labels::getLabel('LBL_Instructions', $siteLangId); ?></h5>

            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">

            <div class="delivery-term">
                <div id="catalogToolTip">
                    <?php
                    $obj = new Extrapage();
                    switch ($type) {
                        case Extrapage::MARKETPLACE_PRODUCT_INSTRUCTIONS:
                            $pageData = $obj->getContentByPageType(Extrapage::MARKETPLACE_PRODUCT_INSTRUCTIONS, $siteLangId);
                            break;
                        case Extrapage::SELLER_INVENTORY_INSTRUCTIONS:
                            $pageData = $obj->getContentByPageType(Extrapage::SELLER_INVENTORY_INSTRUCTIONS, $siteLangId);
                            break;
                        case Extrapage::PRODUCT_REQUEST_INSTRUCTIONS:
                            $pageData = $obj->getContentByPageType(Extrapage::PRODUCT_REQUEST_INSTRUCTIONS, $siteLangId);
                            break;
                        case Extrapage::SELLER_PRODUCT_INSTRUCTIONS:
                            $pageData = $obj->getContentByPageType(Extrapage::SELLER_PRODUCT_INSTRUCTIONS, $siteLangId);
                            break;
                    }
                    if (!empty($pageData)) {
                        echo isset($pageData['epage_content']) && !empty($pageData['epage_content']) ? $pageData['epage_content'] : $pageData['epage_default_content'];
                    } else {
                        echo Labels::getLabel('LBL_No_Instructions_Added', $siteLangId);
                    }

                    ?>
                </div>
            </div>
        </div>
    </div>
</div>