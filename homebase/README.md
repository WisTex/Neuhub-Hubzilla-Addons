# Home Base (0.1.0)

Hubzilla addon with a local-channel dashboard, Quick Access page and seven reusable widgets.

## Installation

Place this directory at `addon/homebase` in your Hubzilla installation, enable **Home Base** through the administrator's addon management, and visit `/homebase` or `/homebase/quickaccess`.

The addon registers its own route and widgets. Default PDL layout files must be installed under `view/site/pdl/` if not already provided by a theme; see `pdl/` for the templates. Existing site/theme/channel PDL overrides take precedence. Do not overwrite existing site PDL files.

## Widgets

- `HomebaseNav` (horizontal by default; set `orientation=vertical` in Comanche widget arguments)
- `HomebaseWelcome`
- `HomebaseCommunications`
- `HomebaseContacts`
- `HomebaseChannel`
- `HomebaseConfiguration`
- `HomebaseNetworks`

Example: `[widget=HomebaseNav][var=orientation]vertical[/var][/widget]`.

Widgets derive destinations from the local channel or authenticated remote observer. Remote links assume a compatible Hubzilla-style home server in this initial version.

Resources, labels, settings, and platform-specific route detection are deferred.

License: MIT Expat License. Copyright (c) 2026 WisTex TechSero Ltd. Co.
