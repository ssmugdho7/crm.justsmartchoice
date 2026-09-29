# Cabinet Maker 1.0.0

Production-oriented Perfex CRM 3.4.1 module by Smart Choice Development.

## Installation
1. Upload `cabinet_maker.zip` from **Setup > Modules**.
2. Activate **Cabinet Maker**.
3. Assign role permissions under **Setup > Staff > Roles**.
4. Review defaults under **Setup > Cabinet Maker**.
5. Open **Cabinet Maker > Designs** and create a design.

## Included
- Native role permissions, menus, settings, English and Spanish language files.
- Project-level Cabinet Maker tab.
- Cabinet and appliance gallery, measured room, plan/front/side/3D canvas views.
- Finish colors and texture styles.
- Cut-list generation, basic rectangular shelf nesting, costing, PDF material list.
- Friendly customer share links and CRM email-template registration.
- Browser video recording of the design canvas.
- Material catalog and reusable offcut inventory schema.
- AI assistant endpoint prepared for CRM AI integration.

## Important manufacturing notice
All dimensions, joinery, clearances, grain direction, edge-banding, appliance specifications, structural requirements, and nesting results must be reviewed by a qualified cabinet professional before purchasing or machining material. Version 1.0 uses a deterministic rectangular nesting heuristic; CNC post-processors and photorealistic cloud rendering are extension points, not falsely represented as proprietary V-Ray or Cabinet Vision engines.

## Upgrade path
Sequential migrations begin with `100_version_100.php`. Never edit Perfex core migration configuration.


Version 1.1.2 safety change: the module bootstrap performs no database writes. Schema creation occurs only during activation or migration 112. Vendor catalog import is deferred until explicitly requested from the vendor area.
