<?php
/**
 * Home Base addon. Copyright (c) 2026 WisTex TechSero Ltd. Co.
 * License: MIT Expat License. See LICENSE.
 */
require_once('addon/homebase/lib.php');

class HomebaseNav {
    /** Comanche: [widget=HomebaseNav] or [widget=HomebaseNav][var=orientation]vertical[/var][/widget] */
    public function widget($args) {
        return homebase_widget('nav', '', is_array($args) ? $args : []);
    }
}
