<?php
/**
 * Name: Shop
 * Description: Adds various ecommerce functionality.
 * Version: 1.0
 * Depends: Core, CustomPage
 * Recommends: None
 * Category: Ecommerce
 * Author: Randall Jaffe
*/

/**
 * * Shop Addon
 * This is the primary file defining the addon.
 * It defines the name of the addon and gives information about the addon to other components of Hubzilla.
*/

// Hubzilla
use Zotlabs\Lib\Apps;
use Zotlabs\Extend\Hook;
use Zotlabs\Extend\Route;
use Zotlabs\Lib\Config;

class Shop {
    const _SHOP_PAGES = ['shop'];
    const _TERM_LENGTH = '1';
    const _TERM_UNITS = 'MONTH';  // YEAR, MONTH, DAY, HOUR, MINUTE, or SECOND
    const _PLANS = [
        '9.95' => 'starter',
        '19.95' => 'premium'
    ];
    public static function checkDependency($addon): bool {
        $addons = Config::get('system', 'addon', '');
        if (!empty($addons)) {
            $addons = array_flip(explode(", ", $addons));
            //die(print_r($addons));
            if (isset($addons[$addon])) {
                require_once('addon/' . $addon . '/' . $addon . '.php');
                return true;
            }
        }
        return false;       
    }
    public static function init(): void {
        $sql = "CREATE TABLE IF NOT EXISTS shop_subscriptions (
                    id int(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                    aid int(10) UNSIGNED NOT NULL DEFAULT 0,
                    sub_transaction_token varchar(40) NOT NULL DEFAULT '',
                    sub_pdt mediumtext NOT NULL,
                    sub_ipn mediumtext NOT NULL,
                    sub_created datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
                    sub_expires datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
                    sub_disabled tinyint(1) NOT NULL DEFAULT 0,
                    UNIQUE (sub_transaction_token),
                    KEY aid (aid),
                    KEY sub_disabled (sub_disabled)
                ) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4;";
        $r = q($sql);
        if (!$r) {
            logger('[shop] Error running Shop::init() CREATE TABLE sql query: ' . $sql);
        } 
        foreach (self::_PLANS as $plan) {
            Config::Set('service_class', $plan, "json:{}");
            logger('[shop] Shop::init(): Created service class: ' . $plan);
        }       
    }
    public static function processPayment(): bool {
        $success = false;
        $aid = get_account_id();
        if ($aid !== false && isset($_GET['tx'], $_GET['st'], $_GET['amt'], self::_PLANS[$_GET['amt']])) {
            switch ($_GET['st']) {
                case 'COMPLETED':
                    // Payment completed
                    $r = q("INSERT INTO shop_subscriptions (aid, sub_transaction_token, sub_pdt, sub_created, sub_expires) 
                        VALUES (%d, '%s', '%s', NOW(), NOW() + INTERVAL %s);",
                        intval($aid),
                        dbesc($_GET['tx']),
                        dbesc($_SERVER['QUERY_STRING']),
                        dbesc(self::_TERM_LENGTH . " " . self::_TERM_UNITS)
                    );
                    if (!$r) {
                        logger('[shop] Shop::processPayment(): DB shop_subscriptions INSERT failed.');
                    } 
                    else {
                        $r = q("UPDATE account SET account_service_class = '%s' WHERE account_id = %d", 
                            dbesc(self::_PLANS[$_GET['amt']]),
                            intval($aid)
                        );
                        if (!$r) {
                            logger('[shop] Shop::processPayment(): DB account_service_class UPDATE failed.');
                        } else {
                            $success = true; 
                        }
                    }
                    break; 
                default:
                    break;
            } 
        }
        return $success;    
    }
}

/**
 * * This function registers (adds) the hook handler and route.
 * The custompage_customize_header() hook handler is registered for the "page_header" hook
 * The custompage_customize_footer() hook handler is registered for the "page_end" hook
 * The "webdesign" route is created for Mod_Webdesign module 
*/
function shop_load() {
    Hook::register('module_loaded', 'addon/shop/shop.php', 'shop_load_module');
    foreach (Shop::_SHOP_PAGES as $page) {
        Route::register('addon/custompage/modules/Mod_Shop.php', $page);
    }
    Shop::init();
}

// * This function unregisters (removes) the hook handler and route.
function shop_unload() {
    Hook::unregister('module_loaded', 'addon/shop/shop.php', 'shop_load_module');
    foreach (Shop::_SHOP_PAGES as $page) {
        Route::unregister('addon/custompage/modules/Mod_Shop.php', $page);
    }
}

/** 
 * * This function runs when the hook handler is executed.
 * @param $arr: A reference to current module
*/
function shop_load_module(&$arr) {
    if (Shop::checkDependency('custompage')) {
        CustomPage::setCustomPages(array_merge(CustomPage::getCustomPages(), Shop::_SHOP_PAGES));
    }
    if ($arr['module'] == 'shop' && argc() > 1 && argv(1) == 'completed') {
        App::$cache['shop_payment_success'] = Shop::processPayment();
    }
}
