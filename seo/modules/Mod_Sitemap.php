<?php

/**
 * * SEO Sitemap Module
 * This is a module that is part of the "SEO" addon.
 * This module's URL is example.com/sitemap
*/

namespace Zotlabs\Module;

use App;
use Zotlabs\Lib\Apps;
use Zotlabs\Web\Controller;
use Zotlabs\Lib\Config;

// Sitemap class "controller" logic for the plugin's "sitemap" route
class Sitemap extends Controller {

	// Class Fields
	private string $_moduleName = '';
	
	// Method executed during page initialization
	public function init(): void {
		// Set pluginName string to this class's name 
		$this->_moduleName = strtolower(trim(strrchr(__CLASS__, '\\'), '\\'));
	}

	// Generic handler for a HTTP GET request (e.g., viewing the page normally)
	public function get(): void {
        $urls = ['', 'login', 'register', 'rmagic', 'directory'];

        // Get CustomPage URLs
        $addons = Config::get('system', 'addon', '');
        if (!empty($addons)) {
            $addons = array_flip(explode(", ", $addons));
            //die(print_r($addons));
            if (isset($addons['custompage'])) {
                require_once('addon/custompage/custompage.php');
                $urls = array_merge($urls, \CustomPage::_CUSTOM_PAGES);
            }
        }

        // Get Channel URLs
		$r = q("SELECT item.* FROM item
			WHERE item.item_private = 0 AND item.obj_type = 'Note' AND item.verb = 'Create' AND item.item_deleted = 0 AND item.item_hidden = 0 AND item.item_type = 0
            GROUP BY item.uuid
			ORDER BY item.created DESC LIMIT %d",
			45000
		);
		if ($r) {
			//die(print_r($r));
            $urls[] = "profile/" . App::$config['app']['primary_channel_address'];
            $urls[] = "channel/" . App::$config['app']['primary_channel_address'];
            require_once('addon/seo/seo.php');
            foreach ($r as $post) {
                $urls[] = \SEO::generatePermalink($post);
            }
        }        

		// Create page sections, inserting template vars
		$content = replace_macros(get_markup_template($this->_moduleName . ".tpl", 'addon/seo'), [
			'$urls' => $urls,
			'$xsl' => 'addon/seo/view/xsl/sitemap.xsl',
            '$urlRoot' => z_root(),
            '$timeUTC' => date('Y-m-d') . 'T' . date('H:i:s') . '+00:00'
		]);

        // $reflection = new \ReflectionClass('App');
        // $staticVars = $reflection->getStaticProperties();
        // foreach ($staticVars as $key => $property) {
        //     echo "$key: " . print_r($property, true) . "<br>\n";
        // }
        // die();       

		// Return/Render content in the plugin template's "content" region
        //die("<pre>" . print_r($urls, true) . "</pre>");
        header('Content-Type: application/xml; charset=UTF-8');
        //App::$page['content'] = $content;
        die($content);
	}

}
