<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
if (count($childCategories) > 0) {
    ?>

    <?php foreach ($childCategories as $sn => $row) { ?>
        <li id="<?php echo $row['prodcat_id']; ?>" class="sortableListsClosed child-category <?php if ($row['subcategory_count'] == 0) { ?>no-children<?php } ?>">
            <div>
                <div class="sorting-bar">
                    <div class="sorting-title"><span class="clickable" onClick="displaySubCategories(this);"><?php echo $row['prodcat_name']; ?></span> <a href="<?php echo commonHelper::generateUrl('Products', 'index', array($row['prodcat_id'])); ?>" class="badge badge-secondary badge-pill clickable" title="<?php echo Labels::getLabel('LBL_Category_Products', $adminLangId); ?>"><?php echo CommonHelper::displayBadgeCount($row['category_products']); ?></a></div>
                    <div class="sorting-actions">
                        <?php
                        $active = "";
                        if ($row['prodcat_active']) {
                            $active = 'checked';
                        }
                        $statusAct = ($canEdit === true) ? 'toggleStatus(event,this,' . applicationConstants::YES . ')' : 'toggleStatus(event,this,' . applicationConstants::NO . ')';
                        $statusClass = ($canEdit === false) ? 'disabled' : '';
                        ?>
                        <label class="statustab statustab-sm">
                            <input <?php echo $active; ?> type="checkbox" id="switch<?php echo $row['prodcat_id']; ?>" value="<?php echo $row['prodcat_id']; ?>" onclick="<?php echo $statusAct; ?>" class="switch-labels" />
                            <i class="switch-handles <?php echo $statusClass; ?> clickable"></i>
                        </label>
                        <?php if ($canEdit) { ?>
                            <button onClick="goToProduct(<?php echo $row['prodcat_id']; ?>)" title="<?php echo Labels::getLabel('LBL_Add_Product', $adminLangId); ?>" class="btn btn-clean btn-sm btn-icon clickable"><i class="fas fa-plus clickable"></i></button>
                            <?php /* <button onClick="categoryForm(<?php echo $row['prodcat_id']; ?>)" title="<?php echo Labels::getLabel('LBL_Edit', $adminLangId); ?>" class="btn btn-clean btn-sm btn-icon clickable"><i class="far fa-edit clickable"></i></button> */ ?>
                            <a href="<?php echo UrlHelper::generateUrl('ProductCategories', 'form', array($row['prodcat_id'])); ?>" title="<?php echo Labels::getLabel('LBL_Edit', $adminLangId); ?>" class="btn btn-clean btn-sm btn-icon clickable"><i class="far fa-edit clickable"></i></a>
                            <button title="<?php echo Labels::getLabel('LBL_Delete', $adminLangId); ?>" onclick="deleteRecord(<?php echo $row['prodcat_id']; ?>)" class="btn btn-clean btn-sm btn-icon clickable"><i class="fa fa-trash clickable"></i></button>
                        <?php } ?>
                    </div>
                </div>
                <?php if ($row['subcategory_count'] > 0) { ?>
                    <span class="sortableListsOpener"><i class="fa fa-plus clickable sort-icon" onClick="displaySubCategories(this)"></i></span>
                    <?php } ?>
            </div>
        </li>
    <?php } ?>

<?php } ?>