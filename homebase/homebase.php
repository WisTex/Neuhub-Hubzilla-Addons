<?php
/**
 * Name: Home Base
 * Description: Personal Hubzilla dashboard and reusable Quick Access widgets.
 * Version: 0.1.0
 * MinVersion: 8.0
 * Author: WisTex
 * License: MIT Expat License
 * Copyright (c) 2026 WisTex TechSero Ltd. Co.
 */
use Zotlabs\Extend\Widget;
use Zotlabs\Extend\Route;
use Zotlabs\Extend\Hook;

function homebase_load() {
    Hook::register('load_pdl', 'addon/homebase/homebase.php', 'homebase_load_pdl');
    Route::register('addon/homebase/Mod_Homebase.php', 'homebase');
    Route::register('addon/homebase/Mod_Quickaccess.php', 'quickaccess');
    foreach (['HomebaseNav','HomebaseWelcome','HomebaseCommunications','HomebaseContacts','HomebaseChannel','HomebaseConfiguration','HomebaseNetworks'] as $name) {
        Widget::register('addon/homebase/widgets/' . $name . '.php', $name);
    }
}

function homebase_unload() {
    Hook::unregister('load_pdl', 'addon/homebase/homebase.php', 'homebase_load_pdl');
    Route::unregister('addon/homebase/Mod_Homebase.php', 'homebase');
    Route::unregister('addon/homebase/Mod_Quickaccess.php', 'quickaccess');
    foreach (['HomebaseNav','HomebaseWelcome','HomebaseCommunications','HomebaseContacts','HomebaseChannel','HomebaseConfiguration','HomebaseNetworks'] as $name) {
        Widget::unregister('addon/homebase/widgets/' . $name . '.php', $name);
    }
}

/**
 * Supply bundled defaults only when Hubzilla has not already selected a layout.
 * Site, theme and channel-customized PDLs must retain precedence.
 */
function homebase_load_pdl(&$arr) {
    if (!is_array($arr) || !in_array($arr['module'] ?? '', ['homebase', 'quickaccess'], true)) {
        return;
    }
    if (!empty($arr['layout'])) {
        return;
    }
    $file = 'addon/homebase/pdl/mod_' . $arr['module'] . '.pdl';
    if (is_readable($file)) {
        $arr['layout'] = file_get_contents($file);
    }
}
