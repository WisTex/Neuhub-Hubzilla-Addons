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
class HomebaseNav {
    /** Comanche: [widget=HomebaseNav] or [widget=HomebaseNav][var=orientation]vertical[/var][/widget] */
    public function widget($args) {
        return \homebase_widget('nav', '', is_array($args) ? $args : []);
    }
}
