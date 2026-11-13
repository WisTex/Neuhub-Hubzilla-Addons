<?php
/**
 * Home Base addon. Copyright (c) 2026 WisTex TechSero Ltd. Co.
 * License: MIT Expat License. See LICENSE.
 */

namespace Zotlabs\Module;

use App;
use Zotlabs\Web\Controller;

/**
 * Module controller: authentication and page metadata only.
 * Hubzilla's registered module PDL renders the page regions and widgets.
 * Do not rebuild widget layout here; administrators customize it through PDL.
 */
class Homebase extends Controller {
    public function init() {
        if (!local_channel()) {
            notice(t('Please log in to a local channel to access Home Base.') . EOL);
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
        App::$page['title'] = t('Home Base');
        // Hubzilla renders the registered PDL and its widgets.
        return '';
    }
}
