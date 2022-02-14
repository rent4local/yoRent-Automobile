<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$variables = array('siteLangId' => $siteLangId, 'action' => $action, 'canEditImportExport' => $canEditImportExport, 'canUploadBulkImages' => $canUploadBulkImages);
$this->includeTemplate('import-export/_partial/top-navigation.php', $variables, false);
?>
<div class="card">
    <div class="card-body">
        <div class="cms">
            <?php
            if (ALLOW_SALE) {
                $frm->setFormTagAttribute('id', 'frmImportExportSettings');
                $frm->setFormTagAttribute('class', 'form form--horizontal');
                $frm->developerTags['colClassPrefix'] = 'col-lg-6 col-md-';
                $frm->developerTags['fld_default_col'] = 6;
                $fld = $frm->getField('csvfile');
                $fld->htmlBeforeField = '<div class="btn-group">';
                $fld->htmlAfterField = '</div>';
                $fld->developerTags['noCaptionTag'] = true;
                $fld->addFieldTagAttribute('class', 'btn btn-brand btn-sm h-auto csvFile-Js');
                $fld->addFieldTagAttribute('id', 'csvFile-Js');
                $fld->htmlAfterField = ' <a class = "btn btn-outline-brand btn-sm" href="' . UrlHelper::generateUrl('ImportExport', 'exportInventory') . '">' . Labels::getLabel('LBL_Export_CSV_File', $siteLangId) . '</a>';
                ?>
                <h3 class="mb-4"><?php echo Labels::getLabel('LBL_Sale_Inventory', $siteLangId); ?> </h3>
                <div id="productInventory"><?php echo $frm->getFormHtml(); ?></div>
                <div class="mt-4">
                    <?php if (!empty($pageData['epage_content'])) { ?>
                        <h3 class="mb-4"><?php echo $pageData['epage_label']; ?></h3>
                        <?php
                        echo FatUtility::decodeHtmlEntities($pageData['epage_content']);
                    }
                    ?>
                </div>
                <hr class="mb-3">
            <?php } ?>
            <?php
            if (ALLOW_RENT) {
                $rentFrm->setFormTagAttribute('name', 'frmRentalInventoryUpdate');
                $rentFrm->setFormTagAttribute('id', 'frmImportExportSettings');
                $rentFrm->setFormTagAttribute('class', 'form form--horizontal');
                $rentFrm->developerTags['colClassPrefix'] = 'col-lg-6 col-md-';
                $rentFrm->developerTags['fld_default_col'] = 6;
                $fld = $rentFrm->getField('csvfile');
                $fld->htmlBeforeField = '<div class="btn-group">';
                $fld->htmlAfterField = '</div>';
                $fld->developerTags['noCaptionTag'] = true;
                $fld->addFieldTagAttribute('class', 'btn btn-brand btn-sm h-auto rentalCsvFile-Js');
                $fld->addFieldTagAttribute('id', 'rentalCsvFile-Js');
                $fld->htmlAfterField = ' <a class = "btn btn-outline-brand btn-sm" href="' . UrlHelper::generateUrl('ImportExport', 'exportRentalInventory') . '">' . Labels::getLabel('LBL_Export_CSV_File', $siteLangId) . '</a>';
                ?>
                <h3 class="mb-4"><?php echo Labels::getLabel('LBL_Rental_Inventory', $siteLangId); ?> </h3>
                <div id="productRentalInventory"><?php echo $rentFrm->getFormHtml(); ?></div>
                <div class="mt-4">
                    <?php if (!empty($pageRentalData['epage_content'])) { ?>
                        <h3 class="mb-4"><?php echo $pageRentalData['epage_label']; ?></h3>
                        <?php
                        echo FatUtility::decodeHtmlEntities($pageRentalData['epage_content']);
                    } else {
                        ?>
                        <h3 class="mb-4"><?php echo $pageRentalData['epage_identifier']; ?></h3>
                        <?php
                        echo FatUtility::decodeHtmlEntities($pageRentalData['epage_default_content']);
                    }
                    ?>
                </div>
            <?php } ?>


        </div>
    </div>
</div>
