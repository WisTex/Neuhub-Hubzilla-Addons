<?php
/**
 * Name: CustomPage
 * Description: Add a custom page to a theme (with a custom URL).
 * Version: 1.0
 * Depends: Core
 * Recommends: None
 * Category: CustomPage
 * Author: Randall Jaffe
 * Maintainer: Scott M. Stolz
*/

/**
 * * CustomPage Addon
 * This is the primary file defining the addon.
 * It defines the name of the addon and gives information about the addon to other components of Hubzilla.
*/

/**
 * TODO: To Add Pages
 * If you would like to add pages: 
 * 
 * 1. Add your custom page to the `const` variable below.
 * 2. Add routes for each custom URL (both register and unregister).
 * 3. Create a module for each URL. Change the class name in the module.
 * 4. Create a template for each URL.
 * 
 * ! Note: If you already have this addon installed on a site: 
 * You will need to deactivate it and reenable it in the Hubzilla admin area (addons section)
 * for it to recognize the new pages. (It needs to register the new pages.)
 */

use Zotlabs\Lib\Apps;
use Zotlabs\Extend\Hook;
use Zotlabs\Extend\Route;
use Zotlabs\Module\Webdesign;
use Zotlabs\Module\Hubzilla;

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
    Hook::register('module_loaded', 'addon/custompage/custompage.php', 'custompage_load_module');
    Hook::register('load_pdl', 'addon/custompage/custompage.php', 'custompage_load_pdl');
    Hook::register('page_header', 'addon/custompage/custompage.php', 'custompage_customize_header');
    Hook::register('page_end', 'addon/custompage/custompage.php', 'custompage_customize_footer');
	/* You will need a route and a corresponding module for every custom URL */
    Route::register('addon/custompage/modules/Mod_Webdesign.php', 'webdesign');
    Route::register('addon/custompage/modules/Mod_Hubzilla.php', 'hubzilla');
}

// * This function unregisters (removes) the hook handler and route.
function custompage_unload() {
    Hook::unregister('module_loaded', 'addon/custompage/custompage.php', 'custompage_load_module');
    Hook::unregister('load_pdl', 'addon/custompage/custompage.php', 'custompage_load_pdl');
	Hook::unregister('page_header', 'addon/custompage/custompage.php', 'custompage_customize_header');
    Hook::unregister('page_end', 'addon/custompage/custompage.php', 'custompage_customize_footer');
    /* You will need a route and a corresponding module for every custom URL */
	Route::unregister('addon/custompage/modules/Mod_Webdesign.php', 'webdesign');
    Route::unregister('addon/custompage/modules/Mod_Hubzilla.php', 'hubzilla');
}

/** 
 * * This function runs when the hook handler is executed.
 * @param $arr: A reference to current module
*/
function custompage_load_module(&$arr) {
	if (in_array($arr['module'], CustomPage::_CUSTOM_PAGES)) {
        //$type = ucfirst($arr['module']);
		//require_once('addon/custompage/modules/Mod_' . $type . '.php');
        //$arr['controller'] = new $type();
		//$arr['installed']  = true;
	}
}

/** 
 * * This function runs when the hook handler is executed.
 * @param $arr: A reference to current module and layout
*/
function custompage_load_pdl(&$arr) {
    //die(print_r($arr));
	$pdl = 'addon/custompage/pdl/mod_' . $arr['module'] . '.pdl';
    if (in_array($arr['module'], CustomPage::_CUSTOM_PAGES) && file_exists($pdl)) {
        $arr['layout'] = @file_get_contents($pdl);
	}
}

/** 
 * * This function runs when the hook handler is executed.
 * @param $content: A reference to page header content
*/
function custompage_customize_header(&$content) {
    // Replace Neuhub page header with a custom header
    if (in_array(App::$module, CustomPage::_CUSTOM_PAGES)) {
        //$content = replace_macros(get_markup_template('header_custom.tpl', 'addon/custompage'), []);
        head_add_css('/addon/custompage/view/css/custompage.css');
    }
}

/** 
 * * This function runs when the hook handler is executed.
 * @param $content: A reference to page footer content
*/
function custompage_customize_footer(&$content) {
    // Replace Neuhub page footer with a custom footer
    if (in_array(App::$module, CustomPage::_CUSTOM_PAGES)) {
        //$content .= replace_macros(get_markup_template('footer_custom.tpl', 'addon/custompage'), []);
    }
}

