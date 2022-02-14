<?php

defined('SYSTEM_INIT') or die('Invalid Usage.');
$dataToSend = [
    'heading' => Labels::getLabel('LBL_Recommended_Products', $siteLangId),
    'subheading' => Labels::getLabel('LBL_You_also_may_like', $siteLangId),
    'products' => $recommendedProducts,
    'siteLangId' => $siteLangId,
    'sectionId' => 3,
    'compProdCount' => (isset($compProdCount)) ? $compProdCount : 0,
    'prodInCompList' => (isset($prodInCompList)) ? $prodInCompList : 0,
    'comparedProdSpecCatId' => (isset($comparedProdSpecCatId)) ? $comparedProdSpecCatId : 0,
];
echo $this->includeTemplate('products/products-in-slider.php', $dataToSend);
?>