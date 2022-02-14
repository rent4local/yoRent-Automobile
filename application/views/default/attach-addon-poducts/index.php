<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php $this->includeTemplate('_partial/seller/sellerDashboardNavigation.php'); ?>

<main id="main-area" class="main" role="main">
    <div class="content-wrapper content-space">
        <div class="content-header row">
            <div class="col">
                <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Link_Rental_Addons', $siteLangId); ?><i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="<?php echo Labels::getLabel('LBL_Attach_addons_to_the_rented_products.', $siteLangId); ?>"></i></h2>
            </div>
        </div>
        <div class="content-body">
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="replaced">
                                <?php
                                $searchForm->setFormTagAttribute('id', 'frmSearchAddonProduct');
                                $searchForm->setFormTagAttribute('class', 'form');
                                $searchForm->setFormTagAttribute('onsubmit', 'searchProducts(this); return(false);');
                                $searchForm->getField('keyword')->addFieldTagAttribute('placeholder', Labels::getLabel('LBL_Search_By_Addon_Name', $siteLangId));
                                $searchForm->developerTags['colClassPrefix'] = 'col-md-';

                                $keywordFld = $searchForm->getField('keyword');
                                $keywordFld->developerTags['col'] = 8;
                                $keywordFld->developerTags['noCaptionTag'] = true;

                                $submitFld = $searchForm->getField('btn_submit');
                                $submitFld->setFieldTagAttribute('class', 'btn-block btn btn-brand');
                                $submitFld->setWrapperAttribute('class', 'col-6');
                                $submitFld->developerTags['col'] = 2;
                                $submitFld->developerTags['noCaptionTag'] = true;

                                $fldClear = $searchForm->getField('btn_clear');
                                $fldClear->setFieldTagAttribute('onclick', 'clearSearch()');
                                $fldClear->setFieldTagAttribute('class', 'btn-block btn btn-outline-brand');
                                $fldClear->setWrapperAttribute('class', 'col-6');
                                $fldClear->developerTags['col'] = 2;
                                $fldClear->developerTags['noCaptionTag'] = true;
                                echo $searchForm->getFormHtml();
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php if ($canEdit) { ?>
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <form class="form" method="post" name="attachAddonForm" onsubmit="saveAddonWithProducts(this); return(false);">
                                <div class="row">
                                    <div class="col-sm-5">
                                        <input name="addon_product_name" id="addon_product_name" type="text" placeholder="<?php echo Labels::getLabel('LBL_Search_Rental_Addons', $siteLangId); ?>" />
                                        <input name="addon_product_id" id="addon_product_id" type="hidden" />
                                    </div>
                                    <div class="col-sm-5">
                                        <input id="filterText" type="text" placeholder="<?php echo Labels::getLabel('LBL_Search_Product', $siteLangId); ?>" id="openWindow" />
                                        <div class="search-card-pro search-card-pro--js">
                                            <div class="selectAll">
                                                <input name="select_all" type="checkbox" id="chbAll" class="k-checkbox" onchange="chbAllOnChange()" />
                                                <label class="k-checkbox-label" for="chbAll"><?php echo Labels::getLabel('LBL_Select_All', $siteLangId); ?></label>
                                                <span id="result">0 <?php echo Labels::getLabel('LBL_Products_Selected', $siteLangId); ?></span>
                                            </div>
                                            <div id="treeview"></div>
                                        </div>
                                    </div>
                                    <div class="col-sm-2">
                                        <input type="submit" class="btn btn-brand btn-block" value="<?php echo Labels::getLabel('LBL_Save', $siteLangId); ?>" />
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div id="addon-products-listing-js"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    $(document).on('keyup', "input[name='addon_product_name']", function () {
        var currObj = $(this);
        if ('' != currObj.val()) {
            currObj.siblings('ul.dropdown-menu').remove();
            currObj.autocomplete({'source': function (request, response) {
                    $('input[name="addon_product_id"]').val(0);
                    $.ajax({
                        url: fcom.makeUrl('AttachAddonPoducts', 'addonProductsAutoComplete', [<?php echo SellerProduct::PRODUCT_TYPE_ADDON . ',' . true; ?>]),
                        data: {fIsAjax: 1, keyword: currObj.val()},
                        dataType: 'json',
                        type: 'post',
                        success: function (json) {
                            response($.map(json, function (item) {
                                return {label: item['value'], value: item['value'], id: item['id']};
                            }));
                        },
                    });
                },
                select: function (event, ui) {
                    $('input[name="addon_product_id"]').val(ui.item.id);
                }
            });
        } else {
            $('input[name="addon_product_id"]').val('');
        }
    });

    var myDataSource = new yomultiselect.data.HierarchicalDataSource({
        data: []
    });

    $("#treeview").yomultiselectTreeView({
        loadOnDemand: false,
        checkboxes: {
            checkChildren: true
        },
        dataSource: myDataSource,
        check: onCheck,
        expand: onExpand
    });
    $(".selectAll").css("display", "none");
    function checkUncheckAllNodes(nodes, checked) {
        for (var i = 0; i < nodes.length; i++) {
            nodes[i].set("checked", checked);
            if (nodes[i].hasChildren) {
                checkUncheckAllNodes(nodes[i].children.view(), checked);
            }
        }
    }

    function chbAllOnChange() {
        var checkedNodes = [];
        var treeView = $("#treeview").data("yomultiselectTreeView");
        var isAllChecked = $('#chbAll').prop("checked");
        checkUncheckAllNodes(treeView.dataSource.view(), isAllChecked)

        if (isAllChecked) {
            setMessage($('#treeview input[type="checkbox"]').length);
        } else {
            setMessage(0);
        }
    }

    function getCheckedNodes(nodes, checkedNodes) {
        var node;
        for (var i = 0; i < nodes.length; i++) {
            node = nodes[i];
            if (node.checked && node.id > 0) {
                checkedNodes.push(node.id);
            }

            if (node.hasChildren) {
                getCheckedNodes(node.children.view(), checkedNodes);
            }
        }
    }

    function onCheck() {
        var checkedNodes = [];
        var treeView = $("#treeview").data("yomultiselectTreeView");
        getCheckedNodes(treeView.dataSource.view(), checkedNodes);
        setMessage(checkedNodes.length);
    }

    function onExpand(e) {
        if ($("#filterText").val() == "") {
            $(e.node).find("li").show();
        }
    }

    function setMessage(checkedNodes) {
        var message;
        if (checkedNodes > 0) {
            message = checkedNodes + " <?php echo Labels::getLabel('LBL_Products_Selected', $siteLangId); ?>";
        } else {
            message = "0 <?php echo Labels::getLabel('LBL_Products_Selected', $siteLangId); ?>";
        }

        $("#result").html(message);
    }

    $("#filterText").keyup(function (e) {
        var filterText = $(this).val();
        if (filterText !== "") {
            var treeView = $("#treeview").data("yomultiselectTreeView");
            $.ajax({
                url: fcom.makeUrl('AttachAddonPoducts', 'sellerProducts'),
                data: {fIsAjax: 1, keyword: filterText},
                dataType: 'json',
                type: 'post',
                success: function (json) {
                    if (json.length > 0) {
                        $(".selectAll").css("visibility", "visible");
                        $(".selectAll").css("display", "block");
                    } else {
                        $(".selectAll").css("display", "none");
                    }
                    treeView.dataSource.data(json);
                }
            });

        } else {
            var treeView = $("#treeview").data("yomultiselectTreeView");
            treeView.dataSource.data([]);
            $(".selectAll").css("display", "none");
        }
    });
</script>
<script>
    $(document).ready(function () {
        $('body').on('click', 'span.k-in', function () {
            $(this).parents('li').find('input[type="checkbox"]').first().trigger('click');
        });
    });
</script>

