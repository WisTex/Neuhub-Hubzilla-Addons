<?php
/**
 * Home Base addon. Copyright (c) 2026 WisTex TechSero Ltd. Co.
 * License: MIT Expat License. See LICENSE.
 */

namespace Zotlabs\Module;

use App;
use Zotlabs\Web\Controller;

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
        $quick = argc() > 1 && argv(1) === 'quickaccess';
        if (argc() > 1 && !$quick) {
            http_response_code(404);
            return '<p>' . htmlspecialchars(t('Page not found.'), ENT_QUOTES, 'UTF-8') . '</p>';
        }
        App::$page['title'] = $quick ? t('Quick Access') : t('Home Base');
        // The PDL content region is populated by Hubzilla; return an accessible fallback
        // if no PDL layout has been loaded.
        if (!App::$pdl) {
            $out = homebase_widget('nav', '', ['orientation' => 'horizontal']);
            $out .= $quick
                ? homebase_widget('communications','Communications') . homebase_widget('contacts','Contacts') . homebase_widget('channel','Channel Features') . homebase_widget('configuration','Configuration') . homebase_widget('networks','Networks')
                : '<h2>' . htmlspecialchars(t('Home Base'), ENT_QUOTES, 'UTF-8') . '</h2>';
            return $out;
        }
        return '';
    }
}
