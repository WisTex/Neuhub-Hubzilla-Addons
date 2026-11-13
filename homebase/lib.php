<?php
/**
 * Home Base addon. Copyright (c) 2026 WisTex TechSero Ltd. Co.
 * License: MIT Expat License. See LICENSE.
 */

/** Shared widget data and HTML output; no WisTex-specific runtime dependencies. */
function homebase_context() {
    $observer = \App::get_observer();
    $local = local_channel();
    $channel = $local ? \App::get_channel() : null;
    $url = $local ? z_root() : ($observer ? ($observer['xchan_url'] ?? '') : '');
    $parts = $url ? parse_url($url) : [];
    $base = isset($parts['scheme'], $parts['host']) ? $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '') : '';
    $address = $local && $channel ? $channel['channel_address'] : '';
    if (!$address && $observer) {
        $address = explode('@', $observer['xchan_addr'] ?? '')[0];
    }
    return ['base' => $base, 'address' => $address, 'name' => $local && $channel ? $channel['channel_name'] : ($observer['xchan_name'] ?? ''), 'authenticated' => (bool)($local || $observer)];
}

function homebase_items($group) {
    $groups = [
        'nav' => [
            ['Home Base','homebase','Personal dashboard'],
            ['Quick Access','quickaccess','Hubzilla features and tools'],
            ['Headquarters (HQ)','hq','Notifications and incoming posts'],
            ['Channel','channel/{channel}','Your channel'],
            ['Connections','connections','Manage connections']
        ],
        'communications' => [
            ['Headquarters (HQ)','hq','View notifications and incoming posts.'],
            ['Create a New Post','rpost','Create a post or direct message.'],
            ['Channel','channel/{channel}','View your channel and posts.'],
            ['Stream / Inbox','network','Incoming posts and comments.'],
            ['Starred Posts','network/?f=&star=1','Posts saved for later.'],
            ['Comment History','network/?f=&conv=1','Your posts and comments.'],
            ['Direct Messages','network/?f=&dm=1','Private messages sent and received.']
        ],
        'contacts' => [
            ['Connections','connections','Channels you follow.'],
            ['Connection Requests','connections/pending','Review incoming connection requests.'],
            ['Contact Roles','permcats','Manage contact roles and permissions.']
        ],
        'channel' => [
            ['Channel','channel/{channel}','View your channel and posts.'],
            ['Profile','profile/{channel}','View your profile.'],
            ['Photos','photos/{channel}','Browse your photos.'],
            ['Calendar','cdav/calendar','View calendars and events.'],
            ['Cloud Storage','cloud/{channel}','Access files and uploads.'],
            ['Apps','apps','Manage installed applications.']
        ],
        'configuration' => [
            ['Edit Profile','profiles','Change profile details and avatar.'],
            ['Settings','settings','Manage account and channel settings.'],
            ['Features','settings/features','Enable or disable features.'],
            ['Federation','apps/available/?cat=Federation','Manage federation apps.'],
            ['Theme','settings/display','Customize your channel appearance.'],
            ['Manage Channels','manage','Manage identities on this account.']
        ],
        'networks' => [
            ['Nomad Network','','Connect with Hubzilla and compatible Zot networks.'],
            ['ActivityPub','pubcrawl','Connect to ActivityPub-compatible platforms.'],
            ['Diaspora','diaspora','Connect to Diaspora-compatible platforms.']
        ]
    ];
    return $groups[$group] ?? [];
}

function homebase_url($path, $ctx) {
    if (!$ctx['base']) return '';
    $path = str_replace('{channel}', rawurlencode($ctx['address']), $path);
    return $ctx['base'] . '/' . ltrim($path, '/');
}

/**
 * Prefer Hubzilla's normal theme/site/core lookup. If no override exists,
 * use the addon-bundled template as the fallback.
 */
function homebase_template($filename) {
    $template = get_markup_template($filename);
    return $template ?: get_markup_template($filename, 'addon/homebase');
}

/**
 * Render a Home Base widget with Hubzilla's standard template lookup.
 * The addon supplies the fallback; theme and site overrides use Hubzilla's
 * native get_markup_template() resolution.
 */
function homebase_widget($group, $title, $args = []) {
    $ctx = homebase_context();
    if (!$ctx['authenticated']) return '';

    $orientation = strtolower((string)($args['orientation'] ?? ($group === 'nav' ? 'horizontal' : 'vertical')));
    if (!in_array($orientation, ['horizontal', 'vertical'], true)) $orientation = 'vertical';
    $horizontal = $orientation === 'horizontal';
    $items = [];
    foreach (homebase_items($group) as $item) {
        [$label, $path, $description] = $item;
        $items[] = [
            'label' => htmlspecialchars(t($label), ENT_QUOTES, 'UTF-8'),
            'description' => htmlspecialchars(t($description), ENT_QUOTES, 'UTF-8'),
            'url' => $path ? htmlspecialchars(homebase_url($path, $ctx), ENT_QUOTES, 'UTF-8') : ''
        ];
    }

    return replace_macros(homebase_template('homebase_links.tpl'), [
        '$title' => $title && empty($args['hide_title']) ? htmlspecialchars(t($title), ENT_QUOTES, 'UTF-8') : '',
        '$horizontal' => $horizontal,
        '$descriptions' => empty($args['hide_descriptions']),
        '$items' => $items
    ]);
}
