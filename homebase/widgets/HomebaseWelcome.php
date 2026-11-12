<?php
/**
 * Home Base addon. Copyright (c) 2026 WisTex TechSero Ltd. Co.
 * License: MIT Expat License. See LICENSE.
 */
require_once('addon/homebase/lib.php');

class HomebaseWelcome {
    /** Comanche: [widget=HomebaseWelcome] or [widget=HomebaseWelcome][var=orientation]vertical[/var][/widget] */
    public function widget($args) {
        $ctx = homebase_context();
        if (!$ctx['authenticated']) return '';
        return '<section class="homebase-welcome mb-3"><h2 class="h4">' . htmlspecialchars(t('Welcome'), ENT_QUOTES, 'UTF-8') . ($ctx['name'] ? ', ' . htmlspecialchars($ctx['name'], ENT_QUOTES, 'UTF-8') : '') . '</h2><p>' . htmlspecialchars(t('Your Home Base for Hubzilla navigation and resources.'), ENT_QUOTES, 'UTF-8') . '</p></section>';
    }
}
