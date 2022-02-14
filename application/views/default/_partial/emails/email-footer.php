        </td>
    </tr>

    <tr>
        <td>
        <?php if (!isset($defaultContent) || $defaultContent === false) { 
            echo FatApp::getConfig('CONF_EMAIL_TEMPLATE_FOOTER_HTML' . $langId, FatUtility::VAR_STRING, '');
        } else { ?>
            <table width="100%">
                <tr>
                    <td style="text-align:center;font-size: 14px;color: #535b61;padding:20px;line-height:1.5">
                        <p>Get in touch if you have any questions regarding our Services.
                            <br> Feel free to contact us 24/7. We are here to help.</p>
                        <p>All the best,
                            <br> The {website_name} Team</p>
                    </td>
                </tr>
                <tr>
                    <td style="background-color: #f9f9f9; padding: 30px;">
                        <table width="100%" style="text-align:center">
                            <tr>
                                <td style="padding-bottom:10px">
                                    <p style="font-size: 14px;color: #535b61;margin-bottom:5px;"> Need more help?</p>  
                                    <a href="{contact_us_url}" style="font-size: 14px;color: #535b61;margin:0">We are here, ready to talk</a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    {social_media_icons}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        <?php } ?>
        </td>
    </tr>
</table>
</div>                        
<?php /*                if (!isset($defaultContent) || $defaultContent === false) {
                            echo FatApp::getConfig('CONF_EMAIL_TEMPLATE_FOOTER_HTML' . $langId, FatUtility::VAR_STRING, '');
                        } else {
                            
                        ?>
                            <table width="100%" align="center" cellpadding="0" cellspacing="0" class='custom'>
                                <tbody>
                                    <tr style="background:#fff;padding:0 30px; text-align:center; color:#999;vertical-align:top;">
                                        <td style="padding:30px 0;">Get in touch in you have any questions regarding our Services.<br />
                                            Feel free to contact us 24/7. We are here to help.<br />
                                            <br />
                                            All the best,<br />
                                            The {website_name} Team<br />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <!--page footer start here-->

                                            <table width="600" border="0" align="center" cellpadding="0" cellspacing="0">
                                                <tbody>
                                                    <tr>
                                                        <td style="height:30px;"></td>
                                                    </tr>
                                                    <tr>
                                                        <td style="background:rgba(0,0,0,0.04);padding:0 30px; text-align:center; color:#999;vertical-align:top;">
                                                            <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                                                                <tbody>
                                                                    <tr>
                                                                        <td style="padding:30px 0; font-size:20px; color:#000;">Need more help?<br />
                                                                            <a href="{contact_us_url}" style="color:#ff3a59;">We are here, ready to talk</a></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="padding:0 15px 15px 15px;">{social_media_icons}</td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding:0; color:#999;vertical-align:top; line-height:20px;">
                                                            <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                                                                <tbody>
                                                                    <tr>
                                                                        <td style="padding:20px 0 30px; text-align:center; font-size:13px; color:#999;">{website_name} Inc.
                                                                            <!-- if these emails get annoying, please feel free to  <a href="#" style="text-decoration:underline; color:#666;">unsubscribe</a>. -->
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding:0; height:50px;"></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <!--page footer end here-->
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        <?php } ?>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div> */ ?>