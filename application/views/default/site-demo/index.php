<?php defined('SYSTEM_INIT') or die('Invalid Usage');
$this->includeTemplate('restore-system/top-header.php');
$this->includeTemplate('restore-system/page-content.php');
?>

<main    id="main">
    <div class="device-preview">
        <div class="device-preview__container <?php echo $deviceClass; ?>">
            <div class="device-preview__content">
                <iframe class="device-preview__iframe"
                    src="<?php echo UrlHelper::generateFullUrl(); ?>"
                    scrolling="yes" frameborder="0"
                    width="<?php echo $width; ?>"
                    height="<?php echo $height; ?>">
                </iframe>
            </div>
        </div>
    </div>
</main>