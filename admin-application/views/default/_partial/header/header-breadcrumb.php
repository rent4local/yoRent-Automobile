<?php defined('SYSTEM_INIT') or die('Invalid usage'); ?>

<ul class="breadcrumb ">
    <li>
        <a href="<?php echo UrlHelper::generateUrl('') ?>">
            <?php echo labels::getLabel('LBL_Home', $adminLangId); ?>
        </a>
    </li>
    <?php
    $titleToRemove = '';
    if (isset($removeHref)) {
        $titleToRemove = str_replace('-', '', $removeHref);
    }

    if (isset($hrefRental)) {
        if (isset($this->variables['nodes'][0]['href'])) {
            $this->variables['nodes'][0]['href'] .= $hrefRental;
        } else {
            $this->variables['nodes'][0]['href'] = $hrefRental;
        }
    }
    if (!empty($this->variables['nodes'])) {
        foreach ($this->variables['nodes'] as $nodes) { ?>
            <?php
            if (!empty($nodes['href'])) {
                if ($titleToRemove != str_replace(' ', '', strtolower($nodes['title']))) {
            ?>
                    <li>
                        <a href="<?php echo $nodes['href']; ?>" <?php echo (!empty($nodes['other'])) ? $nodes['other'] : ''; ?>>
                            <?php $title = str_replace(' ', '_', $nodes['title']);
                            echo Labels::getLabel('LBL_' . $title, $adminLangId); ?>
                        </a>
                    </li>

                <?php
                }
            } else { ?>
                <li>
                    <?php $title = str_replace(' ', '_', $nodes['title']);
                    echo (isset($nodes['title'])) ? Labels::getLabel('LBL_' . $title, $adminLangId) : ''; ?>
                </li>
    <?php }
        }
    } ?>
</ul>