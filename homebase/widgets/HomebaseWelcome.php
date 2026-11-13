<?php
/**
 * Home Base addon. Copyright (c) 2026 WisTex TechSero Ltd. Co.
 * License: MIT Expat License. See LICENSE.
 */
namespace Zotlabs\Widget;

require_once('addon/homebase/lib.php');

/**
 * Registered Comanche widget. The PDL decides where it appears; this class
 * supplies content and delegates shared rendering to addon/homebase/lib.php.
 * The PHP class name must match the name passed to Widget::register().
 */
class HomebaseWelcome {
    /** Comanche: [widget=HomebaseWelcome] or [widget=HomebaseWelcome][var=orientation]vertical[/var][/widget] */
    public function widget($args) {
        $ctx = \homebase_context();
        if (!$ctx['authenticated']) return '';
        return replace_macros(\homebase_template('homebase_welcome.tpl'), [
            '$welcome' => htmlspecialchars(t('Welcome'), ENT_QUOTES, 'UTF-8'),
            '$name' => htmlspecialchars($ctx['name'], ENT_QUOTES, 'UTF-8'),
            '$message' => htmlspecialchars(t('Your Home Base for Hubzilla navigation and resources.'), ENT_QUOTES, 'UTF-8')
        ]);
    }
}
