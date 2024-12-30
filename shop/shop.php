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
    const _SHOP_PAGES = ['shop'];  // For all "/shop/*" pages
    const _PLAN_PAGES = ['starter', 'premium'];  // For all "/starter/*" or "/premium/*" pages
    const _TERM_LENGTH = '1';
    const _TERM_UNITS = 'MONTH';  // YEAR, MONTH, DAY, HOUR, MINUTE, or SECOND
    const _PLANS = [
        // Must be listed in ascending order!
        '9.95' => 'starter',
        '19.95' => 'premium'
    ];
    const _PAYPAL_EMAILS = ['chump2877-facilitator@yahoo.com'];  // Include both sandbox and live emails
    const _PAYPAL_PDT_TOKEN = 'UpM6gyqLdAvKjBNRyEjLP3GF82et_W0uPYStNIxefauuo1lqAJqvlLPb5Qi';
    const _PAYPAL_SANDBOX = true;
    public static function getAllPages(): array {
        return array_merge(self::_SHOP_PAGES, self::_PLAN_PAGES);
    }
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
                    KEY sub_expires (sub_expires),
                    KEY sub_disabled (sub_disabled)
                ) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4;";
        $r = q($sql);
        if (!$r) {
            logger('[shop] Error running Shop::init() CREATE TABLE sql query: ' . $sql);
        }
        Config::Set('system', 'default_service_class', 'default');
        $serviceClassVals = self::generateCumulativeValues(count(self::_PLANS));
        Config::Set('service_class', 'default', self::buildServiceClass(array_shift($serviceClassVals)));
        $plans = array_values(self::_PLANS);
        foreach ($plans as $k => $plan) {
            Config::Set('service_class', $plan, self::buildServiceClass($serviceClassVals[$k]));
            logger('[shop] Shop::init(): Created service class: ' . $plan);
        }
        /* To-Do: Consider setting a "default" service level for all existing accounts with no/empty service level? */
    }
    private static function buildServiceClass(array $values): string {
        $serviceClass = 'json:{';
        $values = (count($values) != count(self::_PLANS)) ? array_fill(0, count(self::_PLANS), '0') : $values;
        $plans = array_values(self::_PLANS);
        foreach ($plans as $k => $plan) {
            $serviceClass .= '"' . $plan . '":"' . $values[$k] . '"';
            $serviceClass .= ($plan != end($plans)) ? ',' : '';
        }
        return $serviceClass . '}';
    }
    private static function generateCumulativeValues($n): array {
        $result = [];
        $result[] = array_fill(0, $n, 0);
        for ($i = 1; $i <= $n; $i++) {
            $array = array_fill(0, $n, 0); 
            for ($j = 0; $j < $i; $j++) {
                $array[$j] = 1;
            }
            $result[] = $array;
        }
        return $result;
    } 
    private static function sendPdtRequest($transactionId): array {
        $data = [];
        $sandboxStr = (self::_PAYPAL_SANDBOX) ? 'sandbox.' : '';
        $paypalUrl = 'https://www.' . $sandboxStr . 'paypal.com/cgi-bin/webscr';
        $postVars = "cmd=_notify-synch&tx=" . $transactionId . "&at=" . self::_PAYPAL_PDT_TOKEN;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $paypalUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postVars);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        $response = curl_exec($ch);
        if (curl_errno($ch) == 0 && curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200 && !empty($response)) {
            //die($response);
            $lines = preg_split('/\n|\r/', trim($response), -1, PREG_SPLIT_NO_EMPTY);
            $first_line = array_shift($lines);
            if ($first_line == "SUCCESS") {
                foreach ($lines as $line) {
                    $lineParts = explode("=", $line, 2);
                    $data[urldecode($lineParts[0])] = urldecode($lineParts[1]);
                }
            }
        }
        curl_close($ch);
        return $data;
    }
    public static function processPayment(): bool {
        $success = false;
        if (isset($_GET['tx'])) {
            App::$cache['shop_payment_data'] = $data = self::sendPdtRequest($_GET['tx']);
            if (!empty($data)) {
                $aid = get_account_id();
                if ($aid !== false && isset($data['payment_status'], $data['txn_id'], $data['payment_gross'])) {
                    switch ($data['payment_status']) {
                        case 'Completed':
                            // Payment completed
                            $r = q("INSERT INTO shop_subscriptions (aid, sub_transaction_token, sub_pdt, sub_created, sub_expires) 
                                VALUES (%d, '%s', '%s', NOW(), NOW() + INTERVAL %s);",
                                intval($aid),
                                dbesc($data['txn_id']),
                                dbesc(json_encode($data)),
                                dbesc(self::_TERM_LENGTH . " " . self::_TERM_UNITS)
                            );
                            if (!$r) {
                                logger('[shop] Shop::processPayment(): DB shop_subscriptions INSERT failed.');
                                if (isset(DBA::$dba->error) && preg_match('/1062 Duplicate entry/i', DBA::$dba->error) == 1) {
                                    $r = q("SELECT * FROM shop_subscriptions WHERE sub_transaction_token = '%s'",
                                        dbesc($data['txn_id'])
                                    );
                                    if ($r !== false && !empty($r)) {
                                        App::$cache['shop_payment_duplicate'] = current($r);
                                        $success = true;
                                    }
                                }
                            } 
                            else {
                                // Disable other account subscriptions, if any exist
                                $r = q("UPDATE shop_subscriptions SET sub_disabled = 1 WHERE aid = %d AND sub_transaction_token <> '%s'",
                                    intval($aid),
                                    dbesc($data['txn_id'])
                                );
                                if (!$r) {
                                    logger('[shop] Shop::processPayment(): DB shop_subscriptions UPDATE failed.');
                                }
                                
                                $r = q("UPDATE account SET account_service_class = '%s' WHERE account_id = %d", 
                                    dbesc(self::_PLANS[$data['payment_gross']]),
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
            }
        }
        return $success;    
    }
    private static function sendIpnRequest(array $data): bool {
        $sandboxStr = (self::_PAYPAL_SANDBOX) ? 'sandbox.' : '';
        $paypalUrl = 'https://ipnpb.' . $sandboxStr . 'paypal.com/cgi-bin/webscr';        
        $postVars = 'cmd=_notify-validate';
        foreach ($data as $key => $value) {
            $postVars .= '&' . $key . '=' . urlencode($value);
        }
        $ch = curl_init($paypalUrl);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postVars);
        curl_setopt($ch, CURLOPT_SSLVERSION, 6);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: PHP-IPN-Verification-Script',
            'Connection: Close'
        ]);
        $response = curl_exec($ch);
        $retval = curl_errno($ch) == 0 && curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200 && $response == 'VERIFIED';
        curl_close($ch);
        return $retval;
    }
    public static function processIPN(): void {
        $rawPostData = file_get_contents('php://input');
        $rawPostArr = explode('&', $rawPostData);
        $data = [];
        foreach ($rawPostArr as $keyval) {
            $keyval = explode('=', $keyval, 2);
            $data[$keyval[0]] = urldecode($keyval[1]);
        }
        logger("[shop] PayPal IPN variables: " . print_r($data, true));
        if (!empty($data) && self::sendIpnRequest($data) && isset($data['receiver_email']) && in_array($data['receiver_email'], self::_PAYPAL_EMAILS)) {
            if (isset($data['payment_status'], $data['txn_id'], $data['payment_gross'], $data['custom'])) {
                $aid = (int)$data['custom'];
                if ($data['payment_status'] == 'Completed') {
                    // Payment completed
                    $r = q("INSERT INTO shop_subscriptions (aid, sub_transaction_token, sub_ipn, sub_created, sub_expires) 
                        VALUES (%d, '%s', '%s', NOW(), NOW() + INTERVAL %s);",
                        intval($aid),
                        dbesc($data['txn_id']),
                        dbesc(json_encode([$data])),
                        dbesc(self::_TERM_LENGTH . " " . self::_TERM_UNITS)
                    );
                    if (!$r) {
                        logger('[shop] Shop::processIPN(): DB shop_subscriptions INSERT failed.');
                    } 
                    else {
                        // Disable other account subscriptions, if any exist
                        $r = q("UPDATE shop_subscriptions SET sub_disabled = 1 WHERE aid = %d AND sub_transaction_token <> '%s'",
                            intval($aid),
                            dbesc($data['txn_id'])
                        );
                        if (!$r) {
                            logger('[shop] Shop::processIPN(): DB shop_subscriptions UPDATE failed.');
                        }
                        
                        $r = q("UPDATE account SET account_service_class = '%s' WHERE account_id = %d", 
                            dbesc(self::_PLANS[$data['payment_gross']]),
                            intval($aid)
                        );
                        if (!$r) {
                            logger('[shop] Shop::processIPN(): DB account_service_class UPDATE failed.');
                        }
                    }
                }
                else {
                    if (isset($data['parent_txn_id'])) {
                        $r = q("SELECT * FROM shop_subscriptions WHERE aid = %d AND sub_transaction_token = '%s'",
                            intval($aid),
                            dbesc($data['parent_txn_id'])
                        );
                        if ($r !== false && count($r) == 1) {
                            $ipn = (!empty($r[0]['sub_ipn'])) ? json_decode($r[0]['sub_ipn'], true) : [];
                            $ipn = (json_last_error() == JSON_ERROR_NONE && !empty($ipn)) ? json_encode(array_merge([$data], $ipn)) : json_encode([$data]);
                            switch ($data['payment_status']) {
                                case 'Refunded':
                                case 'Reversed':                                
                                    $u = q("UPDATE shop_subscriptions, account 
                                        SET shop_subscriptions.sub_disabled = 1, shop_subscriptions.sub_ipn = '%s', account.account_service_class = 'default'
                                        WHERE shop_subscriptions.aid = account.account_id AND shop_subscriptions.aid = %d AND shop_subscriptions.sub_transaction_token = '%s'",
                                        dbesc($ipn),
                                        intval($aid),
                                        dbesc($data['parent_txn_id'])
                                    );
                                    if (!$u) {
                                        logger('[shop] Shop::processIPN(): Refund/Reversal: DB shop_subscriptions UPDATE failed.');
                                    }
                                    break;
                                case 'Canceled_Reversal':
                                    $paymentGross = (float)$data['payment_gross'] + (float)$data['payment_fee'];
                                    $u = q("UPDATE shop_subscriptions, account 
                                        SET shop_subscriptions.sub_disabled = 0, shop_subscriptions.sub_ipn = '%s', account.account_service_class = '%s'
                                        WHERE shop_subscriptions.aid = account.account_id AND shop_subscriptions.aid = %d AND shop_subscriptions.sub_transaction_token = '%s'",
                                        dbesc($ipn),
                                        dbesc(self::_PLANS[(string)$paymentGross] ?? 'default'),
                                        intval($aid),
                                        dbesc($data['parent_txn_id'])
                                    );
                                    if (!$u) {
                                        logger('[shop] Shop::processIPN(): Reversal Canceled: DB shop_subscriptions UPDATE failed.');
                                    }                                
                                    break;
                            }
                        }
                    } 
                }
            }           
        }
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
    Hook::register('load_pdl', 'addon/shop/shop.php', 'shop_load_pdl');
    Hook::register('cron', 'addon/shop/shop.php', 'shop_cron');
    foreach (Shop::getAllPages() as $page) {
        Route::register('addon/custompage/modules/shop/Mod_' . ucfirst($page) . '.php', $page);
    }
    Shop::init();
}

// * This function unregisters (removes) the hook handler and route.
function shop_unload() {
    Hook::unregister('module_loaded', 'addon/shop/shop.php', 'shop_load_module');
    Hook::unregister('load_pdl', 'addon/shop/shop.php', 'shop_load_pdl');
    Hook::unregister('cron', 'addon/shop/shop.php', 'shop_cron');
    foreach (Shop::getAllPages() as $page) {
        Route::unregister('addon/custompage/modules/shop/Mod_' . ucfirst($page) . '.php', $page);
    }
}

/** 
 * * This function runs when the hook handler is executed.
 * @param $arr: A reference to current module
*/
function shop_load_module(&$arr) {
    if (Shop::checkDependency('custompage')) {
        CustomPage::setCustomPages(array_merge(CustomPage::getCustomPages(), Shop::getAllPages()));
    }
    if ($arr['module'] == 'shop' && argc() > 1 && argv(1) == 'completed') {
        App::$cache['shop_payment_success'] = Shop::processPayment();
        //App::$cache['shop_payment_success'] = false;
    }
    if ($arr['module'] == 'shop' && argc() > 1 && argv(1) == 'ipn') {
        Shop::processIPN();
        killme();
    }    
    if (in_array($arr['module'], Shop::_PLAN_PAGES)) {
        $aid = get_account_id();
        App::$cache['shop_access_allowed'] = ($aid !== false) ? account_service_class_allows($aid, $arr['module'], 0) : false;
    }
}

/** 
 * * This function runs when the hook handler is executed.
 * @param $arr: A reference to current module and layout
*/
function shop_load_pdl(&$arr) {
    //die(print_r($arr));
	$pdl = 'addon/custompage/pdl/shop/mod_' . $arr['module'] . '.pdl';
    if (in_array($arr['module'], Shop::getAllPages()) && file_exists($pdl)) {
        $arr['layout'] = @file_get_contents($pdl);
	}
}

/** 
 * * This function runs when the hook handler is executed.
 * @param $arr: A reference to current date/time as datetime value
*/
function shop_cron(&$datetime) {
    $u = q("UPDATE shop_subscriptions, account 
        SET shop_subscriptions.sub_disabled = 1, account.account_service_class = 'default'
        WHERE shop_subscriptions.aid = account.account_id AND shop_subscriptions.sub_disabled = 0 AND shop_subscriptions.sub_expires < NOW()");
    if (!$u) {
        logger('[shop] shop_cron(): DB shop_subscriptions UPDATE failed.');
    }
}
