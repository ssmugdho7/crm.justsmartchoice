<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_294 extends App_module_migration
{
    public function up()
    {
        // Perform database upgrade here
    }
    public function down()
    {
        // Perform database downgrade here
    }
    public function logChanged()
    {
        /*
        -------- Version 2.9.4 (January 26, 2025) --------  
        P/S: In case you encounter any conflicts during usage, please leave feedback or contact me at polyxgo@gmail.com. I will support you right away! Thanks.

        NEW
        - Added alignment attributes for Widgets Language. You need to remove all previously added Widgets Language and add them again.
        - Context Menu: Add an icon when a menu item contains submenu items.

        FIXED
        - Resolved customer-reported issues: Widgets Language did not display language flags in content display positions of widgets.
        - Context Menu: Adjust the click event so that the link only works when clicking directly on the anchor text.
    
        TASKS in PROGRESS
        - Handling storage and operations on systems with a large number of modules and menu items.
        - Support for managing, categorizing, and creating a list of task templates for new project creation. This eliminates the need to recreate generic tasks for most projects, such as gathering client requirements, feature lists, design, feature integration, handover, etc.
        - Fixed bottom menu on mobile devices.
        - Support for searching and displaying custom fields in the PerfexCRM search bar.
        - Feature to select and confirm bulk deletion of invoices.
        */
    }
}