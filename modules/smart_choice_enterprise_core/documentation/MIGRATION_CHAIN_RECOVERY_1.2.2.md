# Migration Chain Recovery 1.2.2

Perfex converts semantic module versions by removing periods. Version `1.2.2` therefore targets migration `122`.

The Perfex `App_module_migration` engine is sequential and rejects gaps larger than one. This package contains every migration from `100` through `122`:

- `100`: Phase 1 baseline
- `101`–`109`: sequential bridge migrations
- `110`: Phase 2 enterprise foundation
- `111`–`119`: sequential bridge migrations
- `120`: Phase 3 Customer 360 and Global Search
- `121`: maintenance bridge
- `122`: current version synchronization option

Do not manually change `tblmodules.installed_version`. Upload the complete replacement folder and use the standard module upgrade action.
