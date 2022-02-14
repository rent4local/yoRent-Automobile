<?php
$frm->setFormTagAttribute('class', 'web_form form_horizontal layout--' . $formLayout);
$frm->setFormTagAttribute('onsubmit', 'setupTheme(this); return(false);');

echo $frm->getFormTag();
?>

<div class="form">
    <div class="form-group">
        <label class="labeled">
            <?php
            $fld = $frm->getField('theme_font_family');
            $fld->addFieldTagAttribute('data-value', $fld->value);
            echo $frm->getField('theme_font_family')->getCaption();
            ?>
        </label>

        <div class="dropdown dropdown-font-family">
            <?php
            echo $frm->getFieldHtml('theme_font_family_url');
            echo $frm->getFieldHtml('theme_font_family');
            ?>
        </div>
    </div>
    <div class="form-group">
        <label class="labeled">
            <?php
            $fld = $frm->getField('theme_font_weight[]');
            $fld->addFieldTagAttribute('id', 'font-weight-js');
            echo $frm->getField('theme_font_weight[]')->getCaption();
            ?>
        </label>

        <div class="dropdown dropdown-font-weight-js">
            <?php echo $frm->getFieldHtml('theme_font_weight[]'); ?>
        </div>
    </div>

<div class="form-group">
    <label class="labeled">
        <?php
        $fld = $frm->getField('theme_color');
        $fld->addFieldTagAttribute('class', 'form-control jscolor themeColor--js');
        $fld->addFieldTagAttribute('data-bg', 'none');
        $fld->addFieldTagAttribute('data-jscolor', '{}');
        echo $fld->getCaption();
        ?>
    </label>
    <div class="">
        <div>
            <div class="input-group color-picker">
                <?php echo $frm->getFieldHtml('theme_color'); ?>
            </div>
        </div>
    </div>
</div>
<div class="form-group">
    <label class="labeled">
        <?php
        $fld = $frm->getField('theme_color_inverse');
        $fld->addFieldTagAttribute('class', 'form-control jscolor themeColorInverse--js');
        $fld->addFieldTagAttribute('data-bg', 'none');
        $fld->addFieldTagAttribute('data-jscolor', '{}');
        echo $fld->getCaption();
        ?>
    </label>
    <div class="">
        <div name="backendbgcolorprimaryinverse">
            <div class="input-group color-picker">
                <?php echo $frm->getFieldHtml('theme_color_inverse'); ?>
            </div>
        </div>
    </div>
</div>
    <div class="form-group ">
        <div class="">
            <p class="img-disclaimer disclaimer-alert">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 512 512" xml:space="preserve">
                    <g transform="translate(1 1)">
                        <g>
                            <g>
                                <path d="M436.016,73.984c-99.979-99.979-262.075-99.979-362.033,0.002c-99.978,99.978-99.978,262.073,0.004,362.031     c99.954,99.978,262.05,99.978,362.029-0.002C535.995,336.059,535.995,173.964,436.016,73.984z M405.848,405.844     c-83.318,83.318-218.396,83.318-301.691,0.004c-83.318-83.299-83.318-218.377-0.002-301.693     c83.297-83.317,218.375-83.317,301.691,0S489.162,322.549,405.848,405.844z">
                                </path>
                                <path d="M254.996,84.338c-11.782,0-21.333,9.551-21.333,21.333v213.333c0,11.782,9.551,21.333,21.333,21.333     c11.782,0,21.333-9.551,21.333-21.333V105.671C276.329,93.889,266.778,84.338,254.996,84.338z">
                                </path>
                                <path d="M254.996,383.004c-11.776,0-21.333,9.557-21.333,21.333s9.557,21.333,21.333,21.333c11.776,0,21.333-9.557,21.333-21.333     S266.772,383.004,254.996,383.004z">
                                </path>
                            </g>
                        </g>
                    </g>
                </svg>
        <?php echo Labels::getLabel('LBL_DISCLAIMER:_INVERSE_COLOR_SHOULD_BE_IN_CONTRAST_TO_THE_THEME_COLOR', $adminLangId); ?>
            </p>
        </div>
    </div>
    <div class="form-group ">
        <?php echo $frm->getFieldHtml('theme_id'); ?>
        <?php
        $fld = $frm->getField('btn_submit');
        $fld->addFieldTagAttribute('class', 'btn-block mt-2');

        echo $frm->getFieldHtml('btn_submit');
        ?>
    </div>
</div>
</form>
<?php echo $frm->getExternalJS(); ?>
<script>
    var font_weight_html = $('.dropdown-font-weight-js').html();
    var font_weights = '<?php echo json_encode($fontWeights); ?>';
</script>