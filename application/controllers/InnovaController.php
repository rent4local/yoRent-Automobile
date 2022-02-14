<?php

class InnovaController extends LoggedUserController
{
    public function assetmanager()
    {
        include_once CONF_THEME_PATH . 'assets/assetmanager/' . implode('/', func_get_args());
    }
}
