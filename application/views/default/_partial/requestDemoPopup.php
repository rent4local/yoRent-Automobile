<?php 
$requestServer = 'v3';
if (isset($_SERVER['HTTP_HOST']) && trim($_SERVER['HTTP_HOST']) != '') {
    $urlArr = explode('.', $_SERVER['HTTP_HOST']);
    if (trim(strtolower($urlArr[0])) != "v3") {
        $requestServer = 'v3_'. trim(strtolower($urlArr[0]));
    }
} 
?>
<div class="modal fade" id="demoFormPopup" tabindex="-1" role="dialog" aria-labelledby="demoFormPopupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered " role="document">
        <div class="modal-content modal-content__getDemo">
            <div class="modal-body p-5">
                <div class="modal-title__wrapper">
                    <h3 class="modal-title" id="demoFormPopupModalLabel">Have questions? Get a <span>Free</span> Personalized Demo</h3>
                    <p class="modal-subtitle">Earliest date for the demo will be atleast 2 days later from the current working day.</p>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div class="pt-4">
                    <form method="post" onSubmit="submitDemoRequest(this, '<?php echo $requestServer; ?>'); return false;" id="popupOpenForm" name="openForm" class="form request-form form--horizontal" style="border-width: 0;">
                        <div class="right--desc text-right text-danger">* Required Fields</div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="field-set">
                                    <div class="caption-wraper">
                                        <label class="field_label">Your Name</label>
                                        <span class="spn_must_field">*</span>
                                    </div>
                                    <div class="field-wraper">
                                        <div class="field_cover">
                                            <input type="text" class="{required:true}" value="" title="Please enter your name" id="your_name" name="your_name" data-fatreq='{"required":true}' data-field-caption="Your Name">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="field-set">
                                    <div class="caption-wraper">
                                        <label class="field_label">E-Mail ID</label>
                                        <span class="spn_must_field">*</span>
                                    </div>
                                    <div class="field-wraper">
                                        <div class="field_cover">
                                            <input type="text" value="" id="your_email" name="your_email" title="Please enter a valid email address" data-fatreq='{"required":true}' data-field-caption="E-Mail ID">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="field-set">
                                    <div class="caption-wraper">
                                        <label class="field_label">Phone</label>
                                        <span class="spn_must_field">*</span>
                                    </div>
                                    <div class="field-wraper">
                                        <div class="field_cover">
                                            <input type="text" value="" title="Please Enter a Valid Phone Number i.e (+91)-999-999-9999" id="phone" name="phone" data-fatreq='{"required":true}' data-field-caption="Phone">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="field-set">
                                    <div class="caption-wraper">
                                        <label class="field_label">Your Industry</label>
                                    </div>
                                    <div class="field-wraper">
                                        <div class="field_cover">
                                            <select name="your_rental_industry">
                                                <option value="" selected="selected">Please choose your rental industry</option>
                                                <option value="Heavy/Construction Equipment">Heavy/Construction Equipment</option>
                                                <option value="Dress">Dress</option>
                                                <option value="Vehicle">Vehicle</option>
                                                <option value="Party Supplies">Party Supplies</option>
                                                <option value="Adventure/Sports Gear">Adventure/Sports Gear</option>
                                                <option value="Furniture">Furniture</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="field-set">
                                    <div class="caption-wraper">
                                        <label class="field_label">Your Timezone</label>
                                        <span class="spn_must_field">*</span>
                                    </div>
                                    <div class="field-wraper">
                                        <div class="field_cover">
                                            <select name="time_zone" id="popDropDownTimezone" class="field--time " title="Please select timezone" data-fatreq='{"required":true}' data-field-caption="Your Timezone">
                                                <option value="">Select Timezone</option>
                                                <optgroup label="US">
                                                    <option data-supportedtime="America/Los_Angeles" data-timezone="-08:00" value="(GMT -8:00) Pacific Time (US)">(GMT -8:00) Pacific Time (US)</option>
                                                    <option data-supportedtime="America/Phoenix" data-timezone="-07:00" value="(GMT -7:00) Mountain Time (US)">(GMT -7:00) Mountain Time (US)</option>
                                                    <option data-supportedtime="America/Mexico_City" data-timezone="-06:00" value="(GMT -6:00) Central Time (US), Mexico City">(GMT -6:00) Central Time (US), Mexico City</option>
                                                    <option data-supportedtime="America/New_York" data-timezone="-05:00" value="(GMT -5:00) Eastern Time (US), Bogota, Lima">(GMT -5:00) Eastern Time (US), Bogota, Lima</option>
                                                </optgroup>
                                                <optgroup label="Europe">
                                                    <option data-supportedtime="Europe/Dublin" data-timezone="GMT" value="(GMT +00:00)Dublin, London, and Lisbon">(GMT +00:00)Dublin, London, and Lisbon</option>
                                                    <option data-supportedtime="Europe/Paris" data-timezone="+01:00" value="(GMT +1:00) Brussels, Copenhagen, Madrid, Paris">(GMT +1:00) Brussels, Copenhagen, Madrid, Paris</option>
                                                    <option data-supportedtime="Europe/Vilnius" data-timezone="+02:00" value="(GMT +2:00) Eastern European time">(GMT +2:00) Eastern European time</option>
                                                    <option data-supportedtime="Europe/Moscow" data-timezone="+03:00" value="(GMT +3:00) Istanbul, Kirov, Minsk, and Moscow">(GMT +3:00) Istanbul, Kirov, Minsk, and Moscow</option>
                                                    <option data-supportedtime="Asia/Tehran" data-timezone="+03:30" value="(GMT +3:30) Tehran">(GMT +3:30) Tehran</option>
                                                    <option data-supportedtime="Asia/Muscat" data-timezone="+04:00" value="(GMT +4:00) Abu Dhabi, Muscat, Baku, Tbilisi">(GMT +4:00) Abu Dhabi, Muscat, Baku, Tbilisi</option>
                                                    <!-- <option data-timezone="+04:30" value="(GMT +4:30) Kabul">(GMT +4:30) Kabul</option> -->
                                                </optgroup>
                                                <optgroup label="Canada">
                                                    <option data-supportedtime="america/vancouver" data-timezone="-08:00" value="(GMT -8:00) Yukon (Canada )">(GMT -8:00) Pacific (Canada)</option>
                                                    <option data-timezone="-07:00" value="(GMT -7:00) Mountain (Canada)">(GMT -7:00) Mountain (Canada)</option>
                                                    <option data-supportedtime="America/Creston" data-timezone="-06:00" value="(GMT -6:00) Central (Canada)">(GMT -6:00) Central(Canada)</option>
                                                    <option data-supportedtime="Canada/Saskatchewan" data-timezone="-06:00" value="(GMT -6:00) Saskatchewan (Canada)">(GMT -6:00) Saskatchewan (Canada)</option>
                                                    <option data-supportedtime="Canada/Eastern" data-timezone="-05:00" value="(GMT -5:00) Eastern (Canada)">(GMT -5:00) Eastern (Canada)</option>
                                                    <option data-supportedtime="Canada/Atlantic" data-timezone="-04:00" value="(GMT -4:00) Atlantic Time (Canada), Caracas, La Paz">(GMT -4:00) Atlantic Time (Canada), Caracas, La Paz</option>
                                                    <option data-supportedtime="Canada/Newfoundland" data-timezone="-03:30" value="(GMT -3:30) Newfoundland">(GMT -3:30) Newfoundland</option>
                                                </optgroup>
                                                <optgroup label="Africa">
                                                    <option data-supportedtime="Europe/London" data-timezone="GMT" value="(GMT +00:00) Greenwich Mean Time (Africa)">(GMT +00:00) Greenwich Mean Time (Africa)</option>
                                                    <option data-supportedtime="Africa/Lagos" data-timezone="+01:00" value="(GMT +1:00) Central European Time (Africa)">(GMT +1:00) Central European Time (Africa)</option>
                                                    <option data-supportedtime="Africa/Casablanca" data-timezone="+01:00" value="(GMT +1:00) Casablanca and El Aaiun (Africa)">(GMT +1:00) Casablanca and El Aaiun (Africa)</option>
                                                    <option data-supportedtime="Africa/Ceuta" data-timezone="+01:00" value="(GMT +1:00) Ceuta(Africa)">(GMT +1:00) Ceuta(Africa)</option>
                                                    <option data-supportedtime="Africa/Khartoum" data-timezone="+02:00" value="(GMT +2:00) Kaliningrad, South Africa">(GMT +2:00) South Africa time</option>
                                                    <option data-supportedtime="Europe/Moscow" data-timezone="+03:00" value="(GMT +3:00) Baghdad, Riyadh, Moscow, St. Petersburg">(GMT +3:00) Baghdad, Riyadh, Moscow, St. Petersburg</option>
                                                </optgroup>
                                                <optgroup label="America">
                                                    <option data-supportedtime="America/Adak" data-timezone="-10:00" value="(GMT -10:00) Adak and Atka">(GMT -10:00) Adak and Atka</option>
                                                    <option data-supportedtime="America/Anchorage" data-timezone="-09:00" value="(GMT -09:00) Anchorage, Juneau, Metlakatla, and Nome">(GMT -09:00) Anchorage, Juneau, Metlakatla, and Nome</option>
                                                    <option data-supportedtime="America/Los_Angeles" data-timezone="-08:00" value="(GMT -08:00) Pacific Time">(GMT -08:00) Pacific Time</option>
                                                    <option data-supportedtime="America/Phoenix" data-timezone="-07:00" value="(GMT -07:00) Mountain Time">(GMT -07:00) Mountain Time</option>
                                                    <option data-supportedtime="America/Chihuahua" data-timezone="-07:00" value="(GMT -07:00) Chihuahua and Mazatlan">(GMT -07:00) Chihuahua and Mazatlan</option>
                                                    <option data-supportedtime="America/Phoenix" data-timezone="-07:00" value="(GMT -07:00) Arizona Time">(GMT -07:00) Arizona Time</option>
                                                    <option data-supportedtime="America/Bahia_Banderas" data-timezone="-06:00" value="(GMT -06:00) Bahia Banderas, Merida, Mexico City, and Monterrey">(GMT -06:00) Bahia Banderas, Merida, Mexico City, and Monterrey</option>
                                                    <option data-supportedtime="America/Costa_Rica" data-timezone="-06:00" value="(GMT -06:00) Belize, Costa Rica, El Salvador, and Guatemala">(GMT -06:00) Belize, Costa Rica, El Salvador, and Guatemala</option>
                                                    <option data-supportedtime="America/Mexico_City" data-timezone="-06:00" value="(GMT -06:00) Central Time">(GMT -06:00) Central Time</option>
                                                    <option data-supportedtime="America/Jamaica" data-timezone="-05:00" value="(GMT -05:00) Jamaica and Panama">(GMT -05:00) Jamaica and Panama</option>
                                                    <option data-supportedtime="America/New_York" data-timezone="-05:00" value="(GMT -05:00) Eastern Time">(GMT -05:00) Eastern Time</option>
                                                    <option  data-supportedtime="America/Havana" data-timezone="-05:00" value="(GMT -05:00) Havana">(GMT -05:00) Havana</option>
                                                    <option data-supportedtime="America/New_York" data-timezone="-04:00" value="(GMT -04:00) Eastern Caribbean Time">(GMT -04:00) Eastern Caribbean Time</option>
                                                    <option data-supportedtime="America/Halifax" data-timezone="-04:00" value="(GMT -04:00) Glace Bay, Goose Bay, Halifax, and Moncton">(GMT -04:00) Glace Bay, Goose Bay, Halifax, and Moncton</option>
                                                    <option data-supportedtime="America/Buenos_Aires" data-timezone="-03:00" value="(GMT -03:00) Argentinian Time">(GMT -03:00) Argentinian Time</option>
                                                    <option data-supportedtime="America/Asuncion" data-timezone="-03:00" value="(GMT -03:00) Asuncion">(GMT -03:00) Asuncion</option>
                                                    <option data-supportedtime="America/Godthab" data-timezone="-03:00" value="(GMT -03:00) Godthab">(GMT -03:00) Godthab</option>
                                                    <option data-supportedtime="America/Miquelon" data-timezone="-03:00" value="(GMT -03:00) Miquelon">(GMT -03:00) Miquelon</option>
                                                    <option data-supportedtime="America/Santiago" data-timezone="-03:00" value="(GMT -03:00) Santiago">(GMT -03:00) Santiago</option>
                                                    <option data-supportedtime="America/St_Johns" data-timezone="-03:30" value="(GMT -03:30) St Johns">(GMT -03:30) St Johns</option>
                                                    <option data-supportedtime="America/Noronha" data-timezone="-02:00" value="(GMT -02:00) Noronha">(GMT -02:00) Noronha</option>
                                                    <option data-supportedtime="America/Scoresbysund" data-timezone="-01:00" value="(GMT -01:00) Scoresbysund">(GMT -01:00) Scoresbysund</option>
                                                    <option data-supportedtime="America/Danmarkshavn" data-timezone="GMT" value="(GMT +00:00) Danmarkshavn">(GMT +00:00) Danmarkshavn</option>
                                                </optgroup>
                                                <optgroup label="Antarctica">
                                                    <option data-supportedtime="Antarctica/Palmer" data-timezone="-03:00" value="(GMT -03:00) Palmer and Rothera">(GMT -03:00) Palmer and Rothera</option>
                                                    <option data-supportedtime="Antarctica/Troll" data-timezone="GMT" value="(GMT +00:00) Danmarkshavn">(GMT +00:00) Troll</option>
                                                    <option data-supportedtime="Antarctica/Syowa" data-timezone="+03:00" value="(GMT +03:00) Syowa">(GMT +03:00) Syowa</option>
                                                    <option data-supportedtime="Antarctica/Mawson" data-timezone="+05:00" value="(GMT +05:00) Mawson">(GMT +05:00) Mawson</option>
                                                    <option data-supportedtime="Antarctica/Vostok" data-timezone="+06:00" value="(GMT +06:00) Vostok">(GMT +06:00) Vostok</option>
                                                    <option data-supportedtime="Antarctica/Davis" data-timezone="+07:00" value="(GMT +07:00) Davis">(GMT +07:00) Davis</option>
                                                    <option data-supportedtime="Antarctica/Casey" data-timezone="+08:00" value="(GMT +08:00) Casey">(GMT +03:00) Casey</option>
                                                    <option data-supportedtime="Antarctica/DumontDUrville" data-timezone="+10:00" value="(GMT +10:00) DumontDUrville">(GMT +10:00) DumontDUrville</option>
                                                    <option data-supportedtime="Antarctica/Macquarie" data-timezone="+11:00" value="(GMT +11:00) Macquarie">(GMT +11:00) Macquarie</option>
                                                </optgroup>
                                                <optgroup label="Arctic">
                                                    <option data-supportedtime="Arctic/Longyearbyen" data-timezone="+01:00" value="(GMT +01:00) Syowa">(GMT +01:00) Longyearbyen</option>
                                                </optgroup>
                                                <optgroup label="Asia">
                                                    <option data-supportedtime="Asia/Amman" data-timezone="+02:00" value="(GMT +02:00) Amman">(GMT +02:00) Amman</option>
                                                    <option data-supportedtime="Asia/Beirut" data-timezone="+02:00" value="(GMT +02:00) Beirut">(GMT +02:00) Beirut</option>
                                                    <option data-supportedtime="Asia/Damascus" data-timezone="+02:00" value="(GMT +02:00) Damascus">(GMT +02:00) Damascus</option>
                                                    <option data-supportedtime="Asia/Famagusta" data-timezone="+02:00" value="(GMT +02:00) Famagusta and Nicosia">(GMT +02:00) Famagusta and Nicosia</option>
                                                    <option data-supportedtime="Asia/Hebron" data-timezone="+02:00" value="(GMT +02:00) Gaza and Hebron">(GMT +02:00) Gaza and Hebron</option>
                                                    <option data-supportedtime="Asia/Jerusalem" data-timezone="+02:00" value="(GMT +02:00) Jerusalem and Tel Aviv">(GMT +02:00) Jerusalem and Tel Aviv</option>
                                                    <option data-supportedtime="Asia/Baghdad" data-timezone="+03:00" value="(GMT +03:00) Baghdad, Bahrain, Istanbul, and Qatar">(GMT +03:00) Baghdad, Bahrain, Istanbul, and Qatar</option>
                                                    <option data-supportedtime="Asia/Tehran" data-timezone="+03:30" value="(GMT +03:30) Tehran">(GMT +03:30) Tehran</option>
                                                    <option data-supportedtime="Asia/Tbilisi" data-timezone="+04:00" value="(GMT +04:00) Baku, Dubai, Muscat, and Tbilisi">(GMT +04:00) Baku, Dubai, Muscat, and Tbilisi</option>
                                                    <option data-supportedtime="Asia/Kabul" data-timezone="+4:30" value="(GMT +4:30) Kabul">(GMT +4:30) Kabul</option>
                                                    <option data-supportedtime="Asia/Ashgabat" data-timezone="+05:00" value="(GMT +05:00) Aqtau, Aqtobe, Ashgabat, and Ashkhabad">(GMT +05:00) Aqtau, Aqtobe, Ashgabat, and Ashkhabad</option>
                                                    <option data-supportedtime="Asia/Kolkata" data-timezone="+05:30" value="(GMT +05:30) New Delhi, Mumbai, and Calcutta">(GMT +05:30) Aqtau, New Delhi, Mumbai, and Calcutta</option>
                                                    <option data-supportedtime="Asia/Kathmandu" data-timezone="+05:45" value="(GMT +05:45) Kathmandu and Katmandu">(GMT +05:45) Kathmandu and Katmandu</option>
                                                    <option data-supportedtime="Asia/Almaty" data-timezone="+06:00" value="(GMT +06:00) Almaty, Bishkek, Dacca, and Dhaka">(GMT +06:00) Almaty, Bishkek, Dacca, and Dhaka</option>
                                                    <option data-supportedtime="Asia/Yangon" data-timezone="+06:30" value="(GMT +06:30) Rangoon and Yangon">(GMT +06:30) Rangoon and Yangon</option>
                                                    <option data-supportedtime="Asia/Jakarta" data-timezone="+07:00" value="(GMT +07:00) Indochina Time">(GMT +07:00) Indochina Time</option>
                                                    <option data-supportedtime="Asia/Hong_Kong" data-timezone="+08:00" value="(GMT +08:00) China, Hong Kong, and Singapore">(GMT +08:00) China, Hong Kong, and Singapore</option>
                                                    <option data-supportedtime="Asia/Seoul" data-timezone="+09:00" value="(GMT +09:00) Seoul and Tokyo">(GMT +09:00) Seoul and Tokyo</option>
                                                    <option data-supportedtime="Asia/Ust-Nera" data-timezone="+10:00" value="(GMT +10:00) Ust-Nera and Vladivostok">(GMT +10:00) Ust-Nera and Vladivostok</option>
                                                    <option data-supportedtime="Asia/Magadan" data-timezone="+11:00" value="(GMT +11:00) Magadan, Sakhalin, and Srednekolymsk">(GMT +11:00) Magadan, Sakhalin, and Srednekolymsk</option>
                                                    <option data-supportedtime="Asia/Kamchatka" data-timezone="+12:00" value="(GMT +12:00) Anadyr and Kamchatka">(GMT +12:00) Anadyr and Kamchatka</option>
                                                </optgroup>
                                                <optgroup label="Atlantic">
                                                    <option data-supportedtime="Atlantic/Bermuda" data-timezone="-04:00" value="(GMT -04:00) Bermuda">(GMT -04:00) Bermuda</option>
                                                    <option data-supportedtime="Atlantic/Stanley" data-timezone="-03:00" value="(GMT -03:00) Stanley">(GMT -04:00) Stanley</option>
                                                    <option data-supportedtime="Atlantic/South_Georgia" data-timezone="-02:00" value="(GMT -02:00) South Georgia">(GMT -02:00) South Georgia</option>
                                                    <option data-supportedtime="Atlantic/Azores" data-timezone="-01:00" value="(GMT -01:00) Azores">(GMT -01:00) Azores</option>
                                                    <option data-supportedtime="Atlantic/Cape_Verde" data-timezone="-01:00" value="(GMT -01:00) Cape Verde">(GMT -01:00) Cape Verde</option>
                                                    <option data-supportedtime="Atlantic/Canary" data-timezone="GMT" value="(GMT 00:00)  Canary, Faeroe, Faroe, and Madeira">(GMT 00:00) Canary, Faeroe, Faroe, and Madeira</option>
                                                    <option data-supportedtime="Atlantic/Reykjavik" data-timezone="GMT" value="(GMT 00:00)  Reykjavik and St Helena">(GMT 00:00) Reykjavik and St Helena</option>
                                                    <option data-supportedtime="Atlantic/Jan_Mayen" data-timezone="+01:00" value="(GMT +01:00)  Jan Mayen">(GMT +01:00) Jan Mayen</option>
                                                </optgroup>
                                                <optgroup label="Australia">
                                                    <option data-supportedtime="Australia/Eucla" data-timezone="+08:45" value="(GMT +08:45) Eucla">(GMT +08:45) Eucla</option>
                                                    <option data-supportedtime="Australia/Perth" data-timezone="+08:00" value="(GMT +08:00) Perth and West">(GMT -04:00) Perth and West</option>
                                                    <option data-supportedtime="Australia/Darwin" data-timezone="+09:30" value="(GMT +09:30) Darwin and North">(GMT +09:30) Darwin and North</option>
                                                    <option data-supportedtime="Australia/Adelaide" data-timezone="+10:30" value="(GMT +10:30) Adelaide, Broken Hill, South, and Yancowinna">(GMT +10:30) Adelaide, Broken Hill, South, and Yancowinna</option>
                                                    <option data-supportedtime="Australia/Brisbane" data-timezone="+10:00" value="(GMT +10:00) Brisbane, Lindeman, and Queensland">(GMT +10:00) Brisbane, Lindeman, and Queensland</option>
                                                    <option data-supportedtime="Australia/Victoria" data-timezone="+11:00" value="(GMT +11:00)  Australian Eastern Time">(GMT +11:00) Australian Eastern Time</option>
                                                    <option data-supportedtime="Australia/Lord_Howe" data-timezone="+11:00" value="(GMT +11:00)  LHI and Lord Howe">(GMT 00:00) LHI and Lord Howe</option>
                                                </optgroup>
                                                <optgroup label="Brazil">
                                                    <option data-supportedtime="Brazil/Acre" data-timezone="-05:00" value="(GMT -05:00) Acre">(GMT -05:00) Acre</option>
                                                    <option data-supportedtime="Brazil/West" data-timezone="-04:00" value="(GMT -04:00) West">(GMT -04:00) West</option>
                                                    <option data-supportedtime="Brazil/East" data-timezone="-03:00" value="(GMT -03:00) East">(GMT -03:00) East</option>
                                                    <option data-supportedtime="Brazil/DeNoronha" data-timezone="-02:00" value="(GMT -02:00) DeNoronha">(GMT -02:00) DeNoronha</option>
                                                </optgroup>
                                                <optgroup label="Chile">
                                                    <option data-supportedtime="Chile/EasterIsland" data-timezone="-05:00" value="(GMT -05:00) EasterIsland">(GMT -05:00) EasterIsland</option>
                                                    <option data-supportedtime="Chile/Continental" data-timezone="-03:00" value="(GMT -03:00) Continental">(GMT -03:00) Continental</option>
                                                </optgroup>
                                                <optgroup label="Indian">
                                                    <option data-supportedtime="Indian/Antananarivo" data-timezone="+03:00" value="(GMT +03:00) Antananarivo, Comoro, and Mayotte">(GMT +03:00) Antananarivo, Comoro, and Mayotte</option>
                                                    <option data-supportedtime="Indian/Mahe" data-timezone="+04:00" value="(GMT +04:00) Mahe, Mauritius, and Reunion">(GMT +04:00) Mahe, Mauritius, and Reunion</option>
                                                    <option data-supportedtime="Indian/Maldives" data-timezone="+05:00" value="(GMT +05:00) Kerguelen and Maldives">(GMT +05:00) Kerguelen and Maldives</option>
                                                    <option data-supportedtime="Indian/Chagos" data-timezone="+06:00" value="(GMT +06:00) Chagos">(GMT +06:00) Chagos</option>
                                                    <option data-supportedtime="Indian/Cocos" data-timezone="+06:30" value="(GMT +06:30) Chagos">(GMT +06:30) Cocos</option>
                                                    <option data-supportedtime="Indian/Christmas" data-timezone="+07:00" value="(GMT +07:00) Christmas">(GMT +07:00) Christmas</option>
                                                </optgroup>
                                                <optgroup label="Mexico">
                                                    <option data-supportedtime="Mexico/BajaNorte" data-timezone="-08:00" value="(GMT -08:00) BajaNorte">(GMT -08:00) BajaNorte</option>
                                                    <option data-supportedtime="Mexico/BajaSur" data-timezone="-07:00" value="(GMT -07:00) BajaSur">(GMT -07:00) BajaSur</option>
                                                    <option data-supportedtime="Mexico/General" data-timezone="-06:00" value="(GMT -06:00) General">(GMT -06:00) General</option>
                                                </optgroup>
                                                <optgroup label="Pacific">
                                                    <option data-supportedtime="Pacific/Midway" data-timezone="-11:00" value="(GMT -11:00) Midway, Niue, Pago Pago, and Samoa">(GMT -11:00) Midway, Niue, Pago Pago, and Samoa</option>
                                                    <option data-supportedtime="Pacific/Honolulu" data-timezone="-10:00" value="(GMT -10:00) Honolulu, Johnston, Rarotonga, and Tahiti">(GMT -10:00) Honolulu, Johnston, Rarotonga, and Tahiti</option>
                                                    <option data-supportedtime="Pacific/Gambier" data-timezone="-09:00" value="(GMT -09:00) Gambier">(GMT -09:00) Gambier</option>
                                                    <option data-supportedtime="Pacific/Marquesas" data-timezone="-09:30" value="(GMT -09:30) Marquesas">(GMT -09:30) Marquesas</option>
                                                    <option data-supportedtime="Pacific/Pitcairn" data-timezone="-08:00" value="(GMT -08:00) Pitcairn">(GMT -08:00) Pitcairn</option>
                                                    <option data-supportedtime="Pacific/Galapagos" data-timezone="-06:00" value="(GMT -06:00) Galapagos">(GMT -06:00) Galapagos</option>
                                                    <option data-supportedtime="Pacific/Easter" data-timezone="-05:00" value="(GMT -05:00) Easter">(GMT -05:00) Easter</option>
                                                    <option data-supportedtime="Pacific/Palau" data-timezone="+09:00" value="(GMT +09:00) Palau">(GMT +09:00) Palau</option>
                                                    <option data-supportedtime="Pacific/Chuuk" data-timezone="+10:00" value="(GMT +10:00) Chuuk, Guam, Port Moresby, and Saipan">(GMT +10:00) Chuuk, Guam, Port Moresby, and Saipan</option>
                                                    <option data-supportedtime="Pacific/Bougainville" data-timezone="+11:00" value="(GMT +11:00) Bougainville, Efate, Guadalcanal, and Kosrae">(GMT +11:00) Bougainville, Efate, Guadalcanal, and Kosrae</option>
                                                    <option data-supportedtime="Pacific/Funafuti" data-timezone="+12:00" value="Funafuti, Kwajalein, Majuro, and Nauru">(GMT +12:00) Funafuti, Kwajalein, Majuro, and Nauru</option>
                                                </optgroup>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="field-set">
                                            <div class="caption-wraper">
                                                <label class="field_label">Preference</label>
                                                <span class="spn_must_field">*</span>
                                            </div>
                                            <div class="field-wraper ">
                                                <div class="field_cover date-selector datepickerdemo--js">
                                                    <input type="text" class="{required:true} field--calender" placeholder="Select Date" title="Please select  date" name="datetime" id="popupDatepicker1" autocomplete="off" style="" inputmode="none" data-fatreq='{"required":true}' data-field-caption="Preference">
                                                    <input name="date_Preference_1" type="hidden" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="field-set">
                                            <div class="caption-wraper">
                                                <label class="field_label">Preference time</label>
                                                <span class="spn_must_field">*</span>
                                            </div>
                                            <div class="field-wraper ">
                                                <div class="field_cover date-selector">
                                                    <?php /* <input name="time_Preference_1" type="text" /> */ ?>
                                                    <?php 
                                                    /* $timeArr = ["6:00 AM" ,"6:30 AM" ,"7:00 AM" ,"7:30 AM" ,"8:00 AM" ,"8:30 AM" ,"9:00 AM" ,"9:30 AM" ,"10:00 AM" ,"10:30 AM" ,"11:00 AM" ,"11:30 AM" ,"12:00 noon" ,"12:30 noon" ,"1:00 PM" ,"1:30 PM" ,"2:00 PM" ,"2:30 PM" ,"3:00 PM" ,"3:30 PM" ,"4:00 PM" ,"4:30 PM" ,"5:00 PM" ,"5:30 PM" ,"6:00 PM" ,"6:30 PM" ,"7:00 PM" ,"7:30 PM" ,"8:00 PM" ,"8:30 PM" ,"9:00 PM" ,"9:30 PM" ,"10:00 PM" ,"10:30 PM" ,"11:00 PM" ,"11:30 PM" ,"00:00 AM" ,"00:30 AM" ,"1:00 AM" ,"1:30 AM" ,"2:00 AM" ,"2:30 AM" ,"3:00 AM" ,"3:30 AM" ,"4:00 AM" ,"4:30 AM" ,"5:00 AM" ,"5:30 AM"];     */
                                                        
                                                    ?>
                                                    
                                                    <select name="time_Preference_1" id="popTime" class="field--time " title="Please select time" data-fatreq='{"required":true}' data-field-caption="Your Preference time">
                                                        <option value="">Select Timezone</option>
                                                        <?php /* foreach ($timeArr as $time) { ?>
                                                        <option value="<?php echo $time;?>"><?php echo $time;?></option>
                                                        <?php } */ ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="field-set">
                                    <div class="caption-wraper">
                                        <label class="field_label">Message</label>
                                        <span class="spn_must_field">*</span>
                                    </div>
                                    <div class="field-wraper">
                                        <div class="field_cover">
                                            <textarea id="message" name="message" placeholder="" data-fatreq='{"required":true}' data-field-caption="Message"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 text-center">
                                <div class="field-set mb-0">
                                    <div class="field-wraper">
                                        <div class="field_cover">
                                            <input type="hidden" name="request_demo_form" value="request_demo" />
                                            <input type="hidden" name="confirm" value="1" />
                                            <input type="submit" class="btn btn-brand btn-submit btn-wide-full" value="Send Demo Request" name="submitForm">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>


                <script type="text/javascript">
                    //<![CDATA[
                    openForm_formatting = {
                        "errordisplay": 3,
                        "summaryElementId": ""
                    };
                    openForm_validator = $("#popupOpenForm").validation(openForm_formatting);
                    //]]>
                </script>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    <?php $isRequestSent = (isset($_COOKIE['yorent_request_submitted']) && $_COOKIE['yorent_request_submitted'] == 1) ? 1 : 0; ?>;
    <?php $formLoaded = (isset($_COOKIE['form_loaded']) && $_COOKIE['form_loaded'] == 1) ? 1 : 0; ?>;
    <?php /* if ($isRequestSent == 0 && $formLoaded == 0) { ?>
        setTimeout(() => {
            document.cookie = 'form_loaded=1';
            $('#demoFormPopup').modal('show');
        }, 240000);
    <?php } */ ?>
    $(document).ready(function() {
        var today = new Date();
        var tomorrow = new Date();
        tomorrow.setDate(today.getDate() + 3);
        $('#popupDatepicker1').dateRangePicker({
            autoClose: true,
            startDate: tomorrow,
            endDate: moment().add(12, 'months').format('YYYY-MM-DD'),
            /* time: {
                enabled: true,
                step: 30
            },
            step: 30, */
            showShortcuts: false,
            /* beforeShowDay: function(t) {
                var valid = !(t.getDay() == 0 || t.getDay() == 6);  
                var _class = '';
                var _tooltip = valid ? '' : '';
                return [valid,_class,_tooltip];
            }, */
            customArrowPrevSymbol: '<i class="fa fa-arrow-circle-left"></i>',
            customArrowNextSymbol: '<i class="fa fa-arrow-circle-right"></i>',
            inline: true,
            container: '.datepickerdemo--js',
            singleDate: true,
            singleMonth: true
        }).bind('datepicker-change', function(event, obj) {
            var mydate = obj.date1;
            var time = moment(mydate).format("hh:mm A");
            var selectedDate = moment(mydate).format("YYYY-MM-DD");
            var date = moment(mydate).format("MMM DD, YYYY");
            $('input[name="date_Preference_1"]').val(date);
            /* $('input[name="time_Preference_1"]').val(time); */
            $('#popupDatepicker1').val(moment(mydate).format("MMM DD, YYYY"));
            $('input[name="time_Preference_1"]').val("");
            
            /* var selectedTimeZone = $('select[name="time_zone"]').find(':selected').data('timezone'); */
            var selectedTimeZone = $('select[name="time_zone"]').find(':selected').data('supportedtime');
            fcom.ajax(fcom.makeUrl('Home', 'getTimeSlots'), {timeZone : selectedTimeZone, start_date : selectedDate}, function (t) {
                var res = $.parseJSON(t);
                if (res.status == 1) {
                    var slots = res.slots;
                    var optionHtml = "";
                    if (slots.length > 0) {
                        $.each(slots, function (key, slot) {
                            optionHtml += '<option value="'+ slot +'">'+ slot +'</option>';
                        });
                    } else {
                        optionHtml = '<option value="">No Slots Available</option>';
                    }
                    $('select[name="time_Preference_1"]').html(optionHtml);
                }
            });
        });
    });
    
    $(document).on('change', 'select[name="time_zone"]', function() {
        $('select[name="time_Preference_1"]').html('<option value="">No Slots Available</option>');
    });
</script>