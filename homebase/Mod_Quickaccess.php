<?php
/**
 * Home Base addon. Copyright (c) 2026 WisTex TechSero Ltd. Co.
 * License: MIT Expat License. See LICENSE.
 */
namespace Zotlabs\Module;

use App;
use Zotlabs\Web\Controller;

class Quickaccess extends Controller {
    public function init() {
        if (!local_channel()) {
            notice(t('Please log in to a local channel to access Quick Access.') . EOL);
            goaway(z_root() . '/login');
        }
        App::$profile_uid = local_channel();
    }

    public function get() {
        require_once('addon/homebase/lib.php');
        if (argc() > 1) {
            http_response_code(404);
            return '<p>' . htmlspecialchars(t('Page not found.'), ENT_QUOTES, 'UTF-8') . '</p>';
        }
        App::$page['title'] = t('Quick Access');
        if (!App::$pdl) {
            return homebase_widget('nav', '', ['orientation' => 'horizontal'])
                . homebase_widget('communications', 'Communications')
                . homebase_widget('contacts', 'Contacts')
                . homebase_widget('channel', 'Channel Features')
                . homebase_widget('configuration', 'Configuration')
                . homebase_widget('networks', 'Networks');
        }
        return '';
    }
}
