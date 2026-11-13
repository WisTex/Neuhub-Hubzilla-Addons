# Home Base architecture and implementation notes

This document records the **why** behind the working Home Base addon. It is intended for developers and AI agents implementing or maintaining Hubzilla addons. The administrator-facing [README](../README.md) is intentionally short.

## Responsibilities and flow

1. `homebase.php` is the addon entry point. Hubzilla calls `homebase_load()` on enable and `homebase_unload()` on disable.
2. `Zotlabs\\Extend\\Route` registers two **modules**, `/homebase` and `/quickaccess`, backed by `Mod_Homebase.php` and `Mod_Quickaccess.php`.
3. `Zotlabs\\Extend\\Hook` registers `load_pdl`. The handler supplies `pdl/mod_homebase.pdl` or `pdl/mod_quickaccess.pdl` as a **default**, only when Hubzilla has not supplied a layout.
4. PDL defines page template, regions, and widget placement. `Zotlabs\\Extend\\Widget` registration makes each `Zotlabs\\Widget\\Homebase*` class available to Comanche `[widget=...]` directives.
5. Widget classes call shared functions in `lib.php`, which build data and render `view/tpl/homebase_links.tpl` or `homebase_welcome.tpl`.

The controller deliberately returns an empty content string when its PDL is active: widgets are rendered by the PDL, **not** duplicated in controller output.

## Why two modules rather than a subroute?

Hubzilla's module PDL convention uses the **module name**. A `/homebase/quickaccess` subpath would still be part of the `homebase` module; it would not independently select `mod_quickaccess.pdl`. Separate registered routes give the two pages independent default layouts and site overrides.

## Registration lifecycle

- Register and unregister the `load_pdl` hook symmetrically.
- Register and unregister both routes symmetrically.
- Register and unregister each widget symmetrically; a PHP widget file alone does not make it available through the addon's Comanche widget name.
- Keep widget registration names consistent with the PHP classes and the `[widget=...]` references in PDL.
- Do not add an independent PDL renderer or copy layouts into site directories at install time.

## PDL lookup and overrides

| Module | Bundled default | Site override |
| --- | --- | --- |
| Home Base | `addon/homebase/pdl/mod_homebase.pdl` | `view/site/pdl/mod_homebase.pdl` |
| Quick Access | `addon/homebase/pdl/mod_quickaccess.pdl` | `view/site/pdl/mod_quickaccess.pdl` |

Addon-local PDL files are **not automatically discovered** as module PDLs. `homebase_load_pdl(&$arr)` is called through the native `load_pdl` hook, restricts itself to the two module names, and fills `$arr['layout']` only when it is empty. This is why editing a bundled PDL will not necessarily affect a page with an existing site or other resolved layout override.

The bundled PDLs use `[template]default[/template]` and define `aside`, `content`, and `right_aside` regions. They refer to registered widgets; they do not hardcode their HTML. Administrators can rearrange or omit widgets through their PDL without changing PHP.

**Important scope:** The hook preserves any layout already supplied in `$arr['layout']`. The exact ordering of all Hubzilla theme, channel, and editor override sources is controlled by the installed Hubzilla version; do not claim a universal ordering without checking its loader.

## TPL resolution and overrides

| Template | Addon fallback | Site override |
| --- | --- | --- |
| Link lists | `addon/homebase/view/tpl/homebase_links.tpl` | `view/site/tpl/homebase_links.tpl` |
| Welcome | `addon/homebase/view/tpl/homebase_welcome.tpl` | `view/site/tpl/homebase_welcome.tpl` |

`homebase_template($filename)` first calls `get_markup_template($filename)`, allowing Hubzilla's normal active theme/site/core lookup to resolve a matching template. If none is found, it calls `get_markup_template($filename, 'addon/homebase')` for the bundled fallback. Passing the addon root **first** would prevent normal global site overrides from being found in the expected location. This two-step lookup is intentional.

Six navigation widgets share one link-list TPL. This is intentional because they render the same presentation pattern; each widget supplies its own group, title, and optional orientation arguments.

## Navigation and identity

`/homebase` and `/quickaccess` always link to the **current hub**, including for remotely authenticated observers. Other links currently use the context derived from the local channel or remote observer. The remote-observer base calculation and federation destinations are not yet fully validated for all installations.

Both modules require `local_channel()` today. Do not interpret the existence of observer-aware helper code as a claim that the pages currently support remote-only visitors.

## Extending the addon

For a new widget: add a `Zotlabs\\Widget` class in `widgets/`, register/unregister it in `homebase.php`, use `lib.php` for shared data/rendering, and place it in a PDL with `[widget=ClassName]`. Avoid adding widget markup directly to the module controller.

For a new independent module page: add a controller, register/unregister its route, and supply its module-specific default PDL through the existing `load_pdl` hook. Preserve any already selected layout.

For a new TPL: keep the default under `view/tpl/` and use the same site-first, addon-fallback lookup when site/theme overrides are desired.

## Decisions and avoided regressions

- **Do not assume bundled PDL discovery.** Initial layouts were ignored until the addon registered `load_pdl`.
- **Do not treat URL subpaths as separate modules.** Independent module PDLs require independent modules.
- **Do not render the same widget list in both controller and PDL.** PDL is the source of page layout.
- **Do not load addon TPLs first when global site overrides are required.** Use normal lookup before addon fallback.
- **Do not conflate local addon routes with remote home-hub navigation.**
- **Do not couple reusable widgets to a fixed page grid.** Let PDL select regions and placement.
- **Keep MIT Expat License and copyright notices in new source files.**

## Verification checklist

1. Enable addon; verify both module routes and all seven widgets resolve.
2. Confirm bundled PDLs render when no override exists.
3. Add a site PDL override; confirm it takes precedence and persists across addon updates.
4. Add a site TPL override; confirm widgets use it, and remove it to confirm addon fallback.
5. Move a widget between PDL regions; verify no controller duplication.
6. Verify `/homebase` and `/quickaccess` remain local hub URLs.
7. Disable addon; verify routes, widgets, and hook registrations are removed.

## Known follow-ups

- Verify federation addon routes and hide federation entries when their addons are disabled.
- Verify remote observer home-hub navigation rather than inferring server root from a channel URL.
- Add automated regression checks where practical.
