<?php
/**
 * Home Base addon. Copyright (c) 2026 WisTex TechSero Ltd. Co.
 * License: MIT Expat License. See LICENSE.
 */

/**
 * Shared widget data and rendering; no WisTex-specific runtime dependencies.
 * Keep widget classes thin so that PDL can place them independently.
 */
/**
 * Resolve the current local channel or remotely authenticated observer.
 * A remote observer's channel URL is not necessarily the server root;
 * remote navigation remains provisional until home-hub URLs are verified.
 */
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
    return ['base' => $base, 'local_base' => rtrim(z_root(), '/'), 'address' => $address, 'name' => $local && $channel ? $channel['channel_name'] : ($observer['xchan_name'] ?? ''), 'authenticated' => (bool)($local || $observer)];
}

/**
 * Link-list data only. Keep the groups independent of page layout: each group
 * is rendered by its own registered widget and can move between PDL regions.
 * Federation destinations are provisional; check addon enablement and routes
 * before making those entries part of a launch-ready navigation experience.
 */
function homebase_items($group) {
    $groups = [
        'nav' => [
            ['Home Base','homebase','Personal dashboard'],
            ['Quick Access','quickaccess','Hubzilla features and tools'],
            ['Headquarters (HQ)','hq','Notifications and incoming posts'],
            ['Direct Messages','network/?f=&dm=1','Private conversations'],
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

/**
 * Home Base and Quick Access are routes on THIS hub, even when the visitor
 * authenticated remotely. Other paths currently use the context's base URL.
 * Do not inadvertently redirect the addon's own navigation to another hub.
 */
function homebase_url($path, $ctx) {
    // Addon routes are always local, even for authenticated remote observers.
    $local_route = in_array($path, ['homebase', 'quickaccess'], true);
    $base = $local_route ? $ctx['local_base'] : $ctx['base'];
    if (!$base) return '';
    $path = str_replace('{channel}', rawurlencode($ctx['address']), $path);
    return $base . '/' . ltrim($path, '/');
}

/**
 * Bootstrap 5 common navigation. The page heading is supplied by Hubzilla,
 * not inferred from the active menu item. The avatar identifies the active
 * local channel and links to its channel page; Hubzilla's account dropdown
 * remains responsible for switching identities.
 *
 * Keep this separate from homebase_widget(): that renderer intentionally
 * remains the shared presentation for Quick Access link-list widgets.
 */
function homebase_navbar($args = []) {
    $channel = local_channel() ? \App::get_channel() : null;
    if (!$channel) return '';

    $address = $channel['channel_address'] ?? '';
    $name = $channel['channel_name'] ?? '';
    $observer = \App::get_observer();
    $avatar = $observer['xchan_photo_m'] ?? '';
    // Hubzilla's redbasic navbar displays the translated active app name.
    // page_title is initialized by Router to the lowercase module slug.
    $page_title = \App::$nav_sel['name'] ?? '';
    if (!$page_title) $page_title = \App::$page['title'] ?? '';
    if (!$page_title) $page_title = \App::$page['page_title'] ?? '';
    if (!$page_title) $page_title = t('Home Base');

    $items = [];
    foreach (homebase_items('nav') as $item) {
        [$label, $path] = $item;
        $url = homebase_url($path, homebase_context());
        $items[] = [
            'label' => htmlspecialchars(t($label), ENT_QUOTES, 'UTF-8'),
            'url' => htmlspecialchars($url, ENT_QUOTES, 'UTF-8'),
            'active' => trim(parse_url($url, PHP_URL_PATH) ?? '', '/') === trim(\App::$module ?? '', '/')
        ];
    }
    return replace_macros(homebase_template('homebase_navbar.tpl'), [
        '$title' => htmlspecialchars((string)$page_title, ENT_QUOTES, 'UTF-8'),
        '$channel_name' => htmlspecialchars((string)$name, ENT_QUOTES, 'UTF-8'),
        '$channel_url' => htmlspecialchars(z_root() . '/channel/' . rawurlencode($address), ENT_QUOTES, 'UTF-8'),
        '$avatar' => htmlspecialchars((string)$avatar, ENT_QUOTES, 'UTF-8'),
        '$items' => $items,
        '$collapse_id' => 'homebase-common-nav'
    ]);
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
 * native get_markup_template() resolution. One shared link-list TPL is
 * intentional; different widgets supply different data and arguments.
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
