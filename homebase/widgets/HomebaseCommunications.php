<?php
/**
 * Home Base addon. Copyright (c) 2026 WisTex TechSero Ltd. Co.
 * License: MIT Expat License. See LICENSE.
 */
namespace Zotlabs\Widget;

require_once('addon/homebase/lib.php');

class HomebaseCommunications {
    /** Comanche: [widget=HomebaseCommunications] or [widget=HomebaseCommunications][var=orientation]vertical[/var][/widget] */
    public function widget($args) {
        return \homebase_widget('communications', 'Communications', is_array($args) ? $args : []);
    }
}
