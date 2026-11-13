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

/**
 * Called by Hubzilla when the administrator enables this addon.
 * Routes expose modules at /homebase and /quickaccess; a URL subpath is not
 * a separate module and therefore does not get its own module PDL.
 * Widgets must also be registered to resolve [widget=Homebase...] in PDL.
 * The load_pdl hook supplies addon-bundled defaults (not auto-discovered).
 * Keep registrations symmetric with homebase_unload().
 */
function homebase_load() {
    Hook::register('load_pdl', 'addon/homebase/homebase.php', 'homebase_load_pdl');
    Route::register('addon/homebase/Mod_Homebase.php', 'homebase');
    Route::register('addon/homebase/Mod_Quickaccess.php', 'quickaccess');
    foreach (['HomebaseNav','HomebaseWelcome','HomebaseCommunications','HomebaseContacts','HomebaseChannel','HomebaseConfiguration','HomebaseNetworks'] as $name) {
        Widget::register('addon/homebase/widgets/' . $name . '.php', $name);
    }
}

/** Remove every registration made in homebase_load() on disable. */
function homebase_unload() {
    Hook::unregister('load_pdl', 'addon/homebase/homebase.php', 'homebase_load_pdl');
    Route::unregister('addon/homebase/Mod_Homebase.php', 'homebase');
    Route::unregister('addon/homebase/Mod_Quickaccess.php', 'quickaccess');
    foreach (['HomebaseNav','HomebaseWelcome','HomebaseCommunications','HomebaseContacts','HomebaseChannel','HomebaseConfiguration','HomebaseNetworks'] as $name) {
        Widget::unregister('addon/homebase/widgets/' . $name . '.php', $name);
    }
}

/**
 * Provide the default module layout through Hubzilla's native load_pdl hook.
 * Addon-local pdl/ files are not automatically discovered as module layouts.
 * Hubzilla passes the current module and any layout already resolved; never
 * replace a nonempty layout (which may be a site/theme/channel override).
 * Keep this module-scoped: do not intercept unrelated Hubzilla modules.
 * The PDL owns widget placement; controllers must not duplicate that layout.
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
