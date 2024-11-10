<?php
/**
 * Name: CustomPage
 * Description: Add a custom page to a theme.
 * Version: 1.0
 * Depends: Core
 * Recommends: None
 * Category: CustomPage
 * Author: Randall Jaffe
*/

/**
 * * CustomPage Addon
 * This is the primary file defining the addon.
 * It defines the name of the addon and gives information about the addon to other components of Hubzilla.
*/

use Zotlabs\Lib\Apps;
use Zotlabs\Extend\Hook;
use Zotlabs\Extend\Route;

class CustomPage {
    const _CUSTOM_PAGES = ['webdesign', 'hubzilla'];
}

/**
 * * This function registers (adds) the hook handler and route.
 * The custompage_customize_header() hook handler is registered for the "page_header" hook
 * The custompage_customize_footer() hook handler is registered for the "page_end" hook
 * The "webdesign" route is created for Mod_Webdesign module 
*/
function custompage_load() {
    Hook::register('page_header', 'addon/custompage/custompage.php', 'custompage_customize_header');
    Hook::register('page_end', 'addon/custompage/custompage.php', 'custompage_customize_footer');
	Route::register('addon/custompage/modules/Mod_Webdesign.php', 'webdesign');
    Route::register('addon/custompage/modules/Mod_Hubzilla.php', 'hubzilla');
}

// * This function unregisters (removes) the hook handler and route.
function custompage_unload() {
	Hook::unregister('page_header', 'addon/custompage/custompage.php', 'custompage_customize_header');
    Hook::unregister('page_end', 'addon/custompage/custompage.php', 'custompage_customize_footer');
	Route::unregister('addon/custompage/modules/Mod_Webdesign.php', 'webdesign');
    Route::unregister('addon/custompage/modules/Mod_Hubzilla.php', 'hubzilla');
}

/** 
 * * This function runs when the hook handler is executed.
 * @param $content: A reference to page header content
*/
function custompage_customize_header(&$content) {
    // Replace Neuhub page header with a custom header
    if (in_array(App::$module, CustomPage::_CUSTOM_PAGES)) {
        $content = replace_macros(get_markup_template('header_custom.tpl', 'addon/custompage'), []);
    }
}

/** 
 * * This function runs when the hook handler is executed.
 * @param $content: A reference to page footer content
*/
function custompage_customize_footer(&$content) {
    // Replace Neuhub page header with a custom header
    if (in_array(App::$module, CustomPage::_CUSTOM_PAGES)) {
        //$content .= replace_macros(get_markup_template('footer_custom.tpl', 'addon/custompage'), []);
    }
}

