# Home Base (0.1.0)

Hubzilla addon with a local-channel dashboard, Quick Access page and seven reusable widgets.

## Installation

Place this directory at `addon/homebase` in your Hubzilla installation, enable **Home Base** through the administrator's addon management, and visit `/homebase` or `/quickaccess`.

The addon registers its own route and widgets. The two modules use separate PDL overrides: `view/site/pdl/mod_homebase.pdl` and `view/site/pdl/mod_quickaccess.pdl`. The addon registers its bundled PDLs as defaults through Hubzilla's `load_pdl` hook; copying them is not required. Copy and customize a PDL in `view/site/pdl/` only to override the default. The addon widgets are independently movable through the PDL editor. Site TPL overrides belong in `view/site/tpl/homebase_links.tpl` and `view/site/tpl/homebase_welcome.tpl`; normal Hubzilla theme/site template lookup takes priority over addon defaults. Existing site/theme/channel PDL overrides take precedence. Do not overwrite existing site PDL files.

## Widgets

- `HomebaseNav` (horizontal by default; set `orientation=vertical` in Comanche widget arguments)
- `HomebaseWelcome`
- `HomebaseCommunications`
- `HomebaseContacts`
- `HomebaseChannel`
- `HomebaseConfiguration`
- `HomebaseNetworks`

Example: `[widget=HomebaseNav][var=orientation]vertical[/var][/widget]`.

Home Base and Quick Access always link to the current hub. Other destinations derive from the local channel or authenticated remote observer. Remote links assume a compatible Hubzilla-style home server in this initial version.

Federation links currently assume the corresponding addons are active; addon availability checks and verified destinations are planned before launch. Resources, labels, settings, and platform-specific route detection are deferred.

License: MIT Expat License. Copyright (c) 2026 WisTex TechSero Ltd. Co.
