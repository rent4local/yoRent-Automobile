<?php

class JsCssController
{
    private function checkModifiedHeader()
    {
        $headers = FatApp::getApacheRequestHeaders();
        if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) == $_GET['sid'])) {
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $_GET['sid']) . ' GMT', true, 304);
            exit;
        }
    }

    private function setHeaders($contentType)
    {
        header('Content-Type: ' . $contentType);
        header('Cache-Control: public, max-age=31536000, stale-while-revalidate=604800');
        header("Pragma: public");
        header("Expires: " . date('r', strtotime("+1 year")));
        $this->checkModifiedHeader();
        if (isset($_GET['sid'])) {
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $_GET['sid']) . ' GMT', true, 200);
        }
        if (!in_array('ob_gzhandler', ob_list_handlers())) {
            if (substr_count($_SERVER ['HTTP_ACCEPT_ENCODING'], 'gzip')) {
                ob_start("ob_gzhandler");
            } else {
                ob_start();
            }
        }
    }

    public function css()
    {
        $this->setHeaders('text/css');
        $arr = explode(',', $_GET['f']);
        $str = '';
        foreach ($arr as $fl) {
            if (substr($fl, '-4') != '.css') {
                continue;
            }
            if (file_exists(CONF_THEME_PATH . ACTIVE_THEME . '/' . $fl)) {
                $str .= file_get_contents(CONF_THEME_PATH . ACTIVE_THEME . '/' . $fl);
            } else if (file_exists(CONF_THEME_PATH . 'default/' . $fl)) {
                $str .= file_get_contents(CONF_THEME_PATH . 'default/' . $fl);
            } else if (file_exists(CONF_THEME_PATH . '/' . $fl)) {
                $str .= file_get_contents(CONF_THEME_PATH . '/' . $fl);
            }
        }

        $str = str_replace('../', '', $str);
        if (FatApplication::getInstance()->getQueryStringVar('min', FatUtility::VAR_INT) == 1) {
            $str = preg_replace('/([\n][\s]*)+/', " ", $str);
            $str = str_replace("\r", '', $str);
            $str = str_replace("\n", '', $str);
        }

        //$cacheKey = $_SERVER['REQUEST_URI'];
        //FatCache::set($cacheKey, $str, '.css');
        echo $str;
    }

    public function cssCommon()
    {

        /* if (empty($_SESSION['preview_theme']) && !isset($_SESSION['preview_theme']) ) {
        $this->checkModifiedHeader();
        } */
        $this->setHeaders('text/css');

        if (isset($_GET['f'])) {
            $files = $_GET['f'];
        } else {
            $pth = CONF_THEME_PATH . 'default/' . 'common-css';
            $files = '';

            $arrCommonfiles = scandir($pth, SCANDIR_SORT_ASCENDING);
            foreach ($arrCommonfiles as $fl) {
                if (!is_file($pth . DIRECTORY_SEPARATOR . $fl)) {
                    continue;
                }
                if ('.css' != substr($fl, -4)) {
                    continue;
                }
                if ('noinc-' == substr($fl, 0, 6)) {
                    continue;
                }

                if ('' != $files) {
                    $files .= ',';
                }
                $files .= $fl;
            }

            if (ACTIVE_THEME != '' && ACTIVE_THEME != 'default') {
                $pth = CONF_THEME_PATH . ACTIVE_THEME . '/' . 'common-css';

                $arrCommonfiles = scandir($pth, SCANDIR_SORT_ASCENDING);
                foreach ($arrCommonfiles as $fl) {
                    if (!is_file($pth . DIRECTORY_SEPARATOR . $fl)) {
                        continue;
                    }
                    if ('.css' != substr($fl, -4)) {
                        continue;
                    }
                    if ('noinc-' == substr($fl, 0, 6)) {
                        continue;
                    }

                    if ('' != $files) {
                        $files .= ',';
                    }
                    $files .= $fl;
                }
            }
        }

        $arr = explode(',', $files);
        $str = '';
        foreach ($arr as $fl) {
            if (substr($fl, '-4') != '.css') {
                continue;
            }

            $file = CONF_THEME_PATH . ACTIVE_THEME . '/common-css' . DIRECTORY_SEPARATOR . $fl;
            if (file_exists($file)) {
                $str .= file_get_contents($file);
            } else if (file_exists(CONF_THEME_PATH . 'default/common-css' . DIRECTORY_SEPARATOR . $fl)) {
                $str .= file_get_contents(CONF_THEME_PATH . 'default/common-css' . DIRECTORY_SEPARATOR . $fl);
            }
        }

        $str = str_replace('../', '', $str);
        if (FatApplication::getInstance()->getQueryStringVar('min', FatUtility::VAR_INT, 0) == 1) {
            $str = preg_replace('/([\n][\s]*)+/', " ", $str);
            $str = str_replace("\r", '', $str);
            $str = str_replace("\n", '', $str);
        }

        //$cacheKey = $_SERVER['REQUEST_URI'];
        //FatCache::set($cacheKey, $str, '.css');
        echo $str;
    }

    public function getPreviewThemeStr($Cfile)
    {
        $str = file_get_contents($Cfile);
        $selected_theme = $_SESSION['preview_theme'];
        $theme_detail = ThemeColor::getAttributesById($selected_theme);
        if (!$theme_detail) {
            $selected_theme = 1;
        }
        $replace_arr = array(
            "var(--first-color)" => $theme_detail['tcolor_first_color'],
            "var(--second-color)" => $theme_detail['tcolor_second_color'],
            "var(--third-color)" => $theme_detail['tcolor_third_color'],
            "var(--txt-color)" => $theme_detail['tcolor_text_color'],
            "var(--txt-color-light)" => $theme_detail['tcolor_text_light_color'],
            "var(--border-color)" => $theme_detail['tcolor_border_first_color'],
            "var(--border-color-second)" => $theme_detail['tcolor_border_second_color'],
            "var(--second-btn-color)" => $theme_detail['tcolor_second_btn_color'],
        );
        foreach ($replace_arr as $key => $val) {
            $str = str_replace($key, "#" . $val, $str);
        }
        return $str;
    }

    public function js()
    {
        $this->setHeaders('application/javascript');
        $arr = explode(',', $_GET['f']);
        $str = '';
        foreach ($arr as $fl) {
            if (substr($fl, '-3') != '.js') {
                continue;
            }
            if (file_exists(CONF_THEME_PATH . ACTIVE_THEME . '/' . $fl)) {
                $str .= file_get_contents(CONF_THEME_PATH . ACTIVE_THEME . '/' . $fl);
            } else if (file_exists(CONF_THEME_PATH . 'default/' . $fl)) {
                $str .= file_get_contents(CONF_THEME_PATH . 'default/' . $fl);
            } else if (file_exists(CONF_THEME_PATH . $fl)) {
                $str .= file_get_contents(CONF_THEME_PATH . $fl);
            }
        }
        
        //$cacheKey = $_SERVER['REQUEST_URI'];
        //FatCache::set($cacheKey, $str, '.js');
        echo($str);
    }

    public function jsCommon()
    {
        $this->setHeaders('application/javascript');
        if (isset($_GET['f'])) {
            $files = $_GET['f'];
        } else {
            $pth = CONF_THEME_PATH . 'default/common-js';
            $files = '';
            $arrCommonfiles = scandir($pth, SCANDIR_SORT_ASCENDING);
            foreach ($arrCommonfiles as $fl) {
                if (!is_file($pth . DIRECTORY_SEPARATOR . $fl)) {
                    continue;
                }
                if ('.js' != substr($fl, -3)) {
                    continue;
                }
                if ('noinc-' == substr($fl, 0, 6)) {
                    continue;
                }

                if ('' != $files) {
                    $files .= ',';
                }
                $files .= $fl;
            }

            if (ACTIVE_THEME != '' && ACTIVE_THEME != 'default') {
                $pth = CONF_THEME_PATH . ACTIVE_THEME . '/common-js';
                $arrCommonfiles = scandir($pth, SCANDIR_SORT_ASCENDING);

                foreach ($arrCommonfiles as $fl) {
                    if (!is_file($pth . DIRECTORY_SEPARATOR . $fl)) {
                        continue;
                    }
                    if ('.js' != substr($fl, -3)) {
                        continue;
                    }
                    if ('noinc-' == substr($fl, 0, 6)) {
                        continue;
                    }
                    if ('' != $files) {
                        $files .= ',';
                    }
                    $files .= $fl;
                }
            }
        }
        $arr = explode(',', $files);
        $str = '';
        foreach ($arr as $fl) {
            if (substr($fl, '-3') != '.js') {
                continue;
            }
            if (file_exists(CONF_THEME_PATH . ACTIVE_THEME . '/common-js' . DIRECTORY_SEPARATOR . $fl)) {
                $str .= '/* */' . file_get_contents(CONF_THEME_PATH . ACTIVE_THEME . '/common-js' . DIRECTORY_SEPARATOR . $fl);
            } else if (file_exists(CONF_THEME_PATH . 'default/' . 'common-js' . DIRECTORY_SEPARATOR . $fl)) {
                $str .= '/* */' . file_get_contents(CONF_THEME_PATH . 'default/' . 'common-js' . DIRECTORY_SEPARATOR . $fl);
            }
        }
        //$cacheKey = $_SERVER['REQUEST_URI'];
        //FatCache::set($cacheKey, $str, '.js');
        echo($str);
    }

}
