# Home Base

A Hubzilla addon providing a personal Home Base dashboard, a Quick Access page, and reusable navigation widgets.

## Installation

Install the addon in `addon/homebase` and enable **Home Base** in Hubzilla's administrator addon management. No additional configuration is required.

Visit `/homebase` for the dashboard or `/quickaccess` for feature links. A local channel login is required.

## Customization

The addon supplies default layouts. Administrators can customize them using Hubzilla's PDL editor or site PDL overrides (`view/site/pdl/mod_homebase.pdl` and `mod_quickaccess.pdl`). Site and theme TPL overrides can customize widget appearance without modifying the addon.

Developer documentation: [Architecture and implementation notes](docs/ARCHITECTURE.md).

License: MIT Expat License. Copyright (c) 2026 WisTex TechSero Ltd. Co.
