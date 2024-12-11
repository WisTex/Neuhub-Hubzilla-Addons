<?php
/**
 * Name: SEO
 * Description: Adds various SEO functionality.
 * Version: 1.0
 * Depends: Core
 * Recommends: None
 * Category: SEO
 * Author: Randall Jaffe
*/

/**
 * * SEO Addon
 * This is the primary file defining the addon.
 * It defines the name of the addon and gives information about the addon to other components of Hubzilla.
*/

// Hubzilla
use Zotlabs\Lib\Apps;
use Zotlabs\Extend\Hook;
use Zotlabs\Extend\Route;
use Zotlabs\Render\Comanche;

class SEO {
	public static function ellipsify($s, $maxlen, $ellipsis='...'): string {
		if($maxlen & 1)
			$maxlen --;
		if($maxlen < 4)
			$maxlen = 4;
	
		if(mb_strlen($s) < $maxlen)
			return $s;
	
		return mb_substr(strip_tags($s), 0, $maxlen) . $ellipsis;
	}

    public static function generatePermalink(array $item): string {
        $url = 'display/' . $item['uuid'];
        $urlSlug = '';
        $slugSrc = (empty($item['title'])) ? ((empty($item['summary'])) ? $item['body'] : $item['summary']) : $item['title'];
        if (!empty($slugSrc)) {
            $urlSlug = preg_replace('/\s+/', "-", preg_replace('/\p{P}/u', "", trim(strip_tags($slugSrc))));
            $urlSlug = "/" . self::ellipsify($urlSlug, 100, "");
        }
        return $url . $urlSlug;
    }

    public static function updateItemMetadata($uuid, array &$pagemeta): void {
        $r = q("SELECT item.* FROM item 
            WHERE item.uuid = '%s' AND item.item_private = 0 AND item.obj_type = 'Note' AND item.item_deleted = 0", 
            dbesc($uuid)
        );
        if ($r) {
            //die(print_r($r[0]));
            $pagemeta['description'] = self::ellipsify(strip_tags(trim($r[0]['body'])), 150);
            $pageTitle = trim(App::$page['title'], '- ');
            $isChildItem = $r[0]['mid'] != $r[0]['parent_mid'];
            App::$page['title'] = (empty($pageTitle) || $isChildItem) ? $pagemeta['description'] : App::$page['title'];
            head_add_link(['rel' => 'canonical', 'href' => z_root() . "/" . self::generatePermalink($r[0])]);
        }
    }

    public static function installOrUninstall($isInstall): void {
        $robotsTxtFile = PROJECT_BASE . '/robots.txt';
        $sitemapRule = "\nSitemap: " . z_root() . "/sitemap.xml\n";
        if ($isInstall) {
            if (!file_exists($robotsTxtFile)) {
                file_put_contents($robotsTxtFile, $sitemapRule);
            } else {
                $robotTxtBody = file_get_contents($robotsTxtFile);
                if (preg_match('/' . preg_quote($sitemapRule, "/") . '/', $robotTxtBody) != 1) {
                    file_put_contents($robotsTxtFile, $sitemapRule, FILE_APPEND);
                }
            }
        } else {
            if (file_exists($robotsTxtFile)) {
                $robotTxtBody = file_get_contents($robotsTxtFile);
                $robotTxtBody = trim(preg_replace('/' . preg_quote($sitemapRule, "/") . '/', "", $robotTxtBody));
                if (!empty($robotTxtBody)) {
                    file_put_contents($robotsTxtFile, $robotTxtBody);
                } else {
                    unlink($robotsTxtFile);
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
function seo_load() {
    Hook::register('page_meta', 'addon/seo/seo.php', 'seo_add_metadata');
    Hook::register('conversation_start', 'addon/seo/seo.php', 'seo_conversation_start');
    Hook::register('display_item', 'addon/seo/seo.php', 'seo_display_item');
    Hook::register('load_pdl', 'addon/seo/seo.php', 'seo_load_pdl');
    Route::register('addon/seo/modules/Mod_Sitemap.php', 'sitemap');
    SEO::installOrUninstall(true);
}

// * This function unregisters (removes) the hook handler and route.
function seo_unload() {
    Hook::unregister('page_meta', 'addon/seo/seo.php', 'seo_add_metadata');
    Hook::unregister('conversation_start', 'addon/seo/seo.php', 'seo_conversation_start');
    Hook::unregister('display_item', 'addon/seo/seo.php', 'seo_display_item');
    Hook::unregister('load_pdl', 'addon/seo/seo.php', 'seo_load_pdl');
    Route::unregister('addon/seo/modules/Mod_Sitemap.php', 'sitemap');
    SEO::installOrUninstall(false);
}

/** 
 * * This function runs when the hook handler is executed.
 * @param $arr: A reference to current module and layout
*/
function seo_load_pdl(&$arr) {
    //die(print_r($arr));
    $pdl = 'addon/seo/pdl/mod_' . $arr['module'] . '.pdl';
    if ($arr['module'] == 'sitemap' && file_exists($pdl)) {
        //$arr['layout'] = @file_get_contents($pdl);
    }
}

/** 
 * * This function runs when the hook handler is executed.
 * @param $arr: A reference to array containing 'items', 'mode', 'update', and 'preview'
*/
function seo_conversation_start(&$arr) {
    //die(print_r($arr));
    if (!empty($arr['items'])) {
        for ($i = 0; $i < count($arr['items']); $i++) {
            $arr['items'][$i]['plink'] = $arr['items'][$i]['llink'] = z_root() . "/" . SEO::generatePermalink($arr['items'][$i]);
            if (!empty($arr['items'][$i]['children'])) {
                for ($j = 0; $j < count($arr['items'][$i]['children']); $j++) {
                    $arr['items'][$i]['children'][$j]['plink'] = $arr['items'][$i]['children'][$j]['llink'] = z_root() . "/" . SEO::generatePermalink($arr['items'][$i]['children'][$j]);
                }
            }
        }
    }
}

/** 
 * * This function runs when the hook handler is executed.
 * @param $arr: A reference to array containing current 'item' and item template 'output'
*/
function seo_display_item(&$arr) {
    //die(print_r($arr));
    if (isset($arr['output']['conv']['href'])) {
        $arr['output']['conv']['href'] = z_root() . "/" . SEO::generatePermalink($arr['item']);
    }
}

/** 
 * * This function runs when the hook handler is executed.
 * @param $pagemeta: A reference to array that contains meta tags
*/
function seo_add_metadata(&$pagemeta) {
    $thisUrl = z_root() . $_SERVER['REQUEST_URI'];
    $cleanUrl = z_root() . parse_url($thisUrl, PHP_URL_PATH);
    $uuidPattern = '/^([0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12})$/i';
    switch (App::$module) {
        case 'display':
            //die(print_r(App::$argv));
            $hasMid = argc() > 2 && preg_match($uuidPattern, argv(1)) == 1;  // Test for valid UUID v4
            if ($hasMid) SEO::updateItemMetadata(argv(1), $pagemeta);
            break;
        case 'item':
            $hasMid = argc() > 1 && preg_match($uuidPattern, argv(1)) == 1;  // Test for valid UUID v4
            if ($hasMid) SEO::updateItemMetadata(argv(1), $pagemeta);
            break;
        case 'channel':
            if (!empty($_GET['mid'])) {
                SEO::updateItemMetadata($_GET['mid'], $pagemeta);            
            } else {
                $pagemeta['description'] = 'All posts to channel owned by ' . App::$page['title'];
                App::$page['title'] = (preg_match('/^(Channel Home - )/i', App::$page['title']) != 1) ? 'Channel Home - ' . App::$page['title'] : App::$page['title'];
                head_add_link(['rel' => 'canonical', 'href' => $cleanUrl]);                
            }
            break;
        case 'profile':
            $pagemeta['description'] = (!empty(App::$profile['pdesc'])) ? SEO::ellipsify(strip_tags(App::$profile['pdesc']), 150) : 'Channel profile of ' . App::$page['title'];
            App::$page['title'] = (preg_match('/^(Profile - )/i', App::$page['title']) != 1) ? 'Profile - ' . App::$page['title'] : App::$page['title'];
            head_add_link(['rel' => 'canonical', 'href' => $cleanUrl]);
            break;
        case 'search':
            $searchTerm = htmlspecialchars(rawurldecode($_GET['search']), ENT_QUOTES, 'UTF-8');
            App::$page['title'] = 'Search Results for "' . $searchTerm . '"';
            $pagemeta['description'] = 'Displaying all search results for \'' . $searchTerm . '\'';
            head_add_link(['rel' => 'canonical', 'href' => $cleanUrl . '?search=' . $_GET['search']]);
            break;
        case 'login':
            App::$page['title'] = App::$config['system']['sitename'] . ' Member Login';
            $pagemeta['description'] = 'Member login for ' . App::$config['system']['sitename'];
            head_add_link(['rel' => 'canonical', 'href' => $cleanUrl]);
            break;
        case 'register':
            App::$page['title'] = App::$config['system']['sitename'] . ' Member Registration';
            $pagemeta['description'] = 'Member registration for ' . App::$config['system']['sitename'];
            head_add_link(['rel' => 'canonical', 'href' => $cleanUrl]);
            break;
        case 'rmagic':
            App::$page['title'] = App::$config['system']['sitename'] . ' Magic Sign On';
            $pagemeta['description'] = 'Magic Sign On for ' . App::$config['system']['sitename'];
            head_add_link(['rel' => 'canonical', 'href' => $cleanUrl]);
            break;
        case 'directory':
            App::$page['title'] = App::$config['system']['sitename'] . ' Directory';
            $pagemeta['description'] = 'Directory of sites for ' . App::$config['system']['sitename'];
            head_add_link(['rel' => 'canonical', 'href' => $cleanUrl]);
            break;
    }
}
