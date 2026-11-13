<?php
/**
 * Home Base addon. Copyright (c) 2026 WisTex TechSero Ltd. Co.
 * License: MIT Expat License. See LICENSE.
 */
namespace Zotlabs\Widget;

require_once('addon/homebase/lib.php');

class HomebaseWelcome {
    /** Comanche: [widget=HomebaseWelcome] or [widget=HomebaseWelcome][var=orientation]vertical[/var][/widget] */
    public function widget($args) {
        $ctx = \homebase_context();
        if (!$ctx['authenticated']) return '';
        return replace_macros(get_markup_template('homebase_welcome.tpl', 'addon/homebase'), [
            '$welcome' => htmlspecialchars(t('Welcome'), ENT_QUOTES, 'UTF-8'),
            '$name' => htmlspecialchars($ctx['name'], ENT_QUOTES, 'UTF-8'),
            '$message' => htmlspecialchars(t('Your Home Base for Hubzilla navigation and resources.'), ENT_QUOTES, 'UTF-8')
        ]);
    }
}
