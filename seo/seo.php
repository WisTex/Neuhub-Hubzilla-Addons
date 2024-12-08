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
	public static function ellipsify($s, $maxlen): string {
		if($maxlen & 1)
			$maxlen --;
		if($maxlen < 4)
			$maxlen = 4;
	
		if(mb_strlen($s) < $maxlen)
			return $s;
	
		return mb_substr(strip_tags($s), 0, $maxlen) . '...';
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
}

// * This function unregisters (removes) the hook handler and route.
function seo_unload() {
    Hook::unregister('page_meta', 'addon/seo/seo.php', 'seo_add_metadata');
}

/** 
 * * This function runs when the hook handler is executed.
 * @param $pagemeta: A reference to array that contains meta tags
*/
function seo_add_metadata(&$pagemeta) {
    $thisUrl = z_root() . $_SERVER['REQUEST_URI'];
    $cleanUrl = z_root() . parse_url($thisUrl, PHP_URL_PATH);
    switch (App::$module) {
        case 'channel':
            if (!empty($_GET['mid']) && (int)preg_match_all('/<div.*?id="opendiv-[^"]+"[^>]*>(.+?)<\/div>/is', App::$page['content'], $matches) > 0) {
                $pagemeta['description'] = SEO::ellipsify(strip_tags(end($matches[1])), 150);
                head_add_link(['rel' => 'canonical', 'href' => $cleanUrl . '?mid=' . htmlspecialchars($_GET['mid'], ENT_QUOTES, 'UTF-8')]);                
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
