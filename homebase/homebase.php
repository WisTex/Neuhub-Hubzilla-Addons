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

function homebase_load() {
    Route::register('addon/homebase/Mod_Homebase.php', 'homebase');
    foreach (['HomebaseNav','HomebaseWelcome','HomebaseCommunications','HomebaseContacts','HomebaseChannel','HomebaseConfiguration','HomebaseNetworks'] as $name) {
        Widget::register('addon/homebase/widgets/' . $name . '.php', $name);
    }
}

function homebase_unload() {
    Route::unregister('addon/homebase/Mod_Homebase.php', 'homebase');
    foreach (['HomebaseNav','HomebaseWelcome','HomebaseCommunications','HomebaseContacts','HomebaseChannel','HomebaseConfiguration','HomebaseNetworks'] as $name) {
        Widget::unregister('addon/homebase/widgets/' . $name . '.php', $name);
    }
}
