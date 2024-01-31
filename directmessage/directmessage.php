<?php
/**
 * Name: Direct Message Alternate UI
 * Description: Alternate user interface for direct messages
 * Version: 1.0
 * Depends: Core
 * Recommends: None
 * Category: DM control
 * Author: Randall Jaffe
*/

/**
 * * DirectMessage Addon
 * This is the primary file defining the addon.
 * It defines the name of the addon and gives information about the addon to other components of Hubzilla.
*/

use Zotlabs\Lib\Apps;
use Zotlabs\Extend\Hook;
use Zotlabs\Extend\Route;

/**
 * * These functions register (add) the hook handlers and admin route.
*/
function directmessage_load() {
	Hook::register('status_editor', 'addon/directmessage/directmessage.php', 'DirectMessage::status_editor');
}

// * These functions unregister (remove) the hook handlers and admin route.
function directmessage_unload() {
	Hook::unregister('status_editor', 'addon/directmessage/directmessage.php', 'DirectMessage::status_editor');
}

/** 
 * * These functions run when the hook handlers are executed.
*/
class DirectMessage {
	public static function status_editor(&$hook_arr) {
		//$valid_modules = ['Network', 'Rpost', 'Editpost', 'Hq'];
		$valid_modules = ['Network'];
		if (!in_array($hook_arr['module'], $valid_modules)) {
			return;
		}
		head_add_js('/addon/directmessage/js/custom.js', 200);
	}
}
