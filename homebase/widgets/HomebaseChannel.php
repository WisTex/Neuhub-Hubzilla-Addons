<?php
/**
 * Home Base addon. Copyright (c) 2026 WisTex TechSero Ltd. Co.
 * License: MIT Expat License. See LICENSE.
 */
require_once('addon/homebase/lib.php');

class HomebaseChannel {
    /** Comanche: [widget=HomebaseChannel] or [widget=HomebaseChannel][var=orientation]vertical[/var][/widget] */
    public function widget($args) {
        return homebase_widget('channel', 'Channel Features', is_array($args) ? $args : []);
    }
}
