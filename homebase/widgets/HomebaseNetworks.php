<?php
/**
 * Home Base addon. Copyright (c) 2026 WisTex TechSero Ltd. Co.
 * License: MIT Expat License. See LICENSE.
 */
namespace Zotlabs\Widget;

require_once('addon/homebase/lib.php');

class HomebaseNetworks {
    /** Comanche: [widget=HomebaseNetworks] or [widget=HomebaseNetworks][var=orientation]vertical[/var][/widget] */
    public function widget($args) {
        return \homebase_widget('networks', 'Networks', is_array($args) ? $args : []);
    }
}
