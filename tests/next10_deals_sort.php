<?php
namespace app\services { abstract class AbstractKanban {} }
namespace {
 define('BASEPATH',__DIR__);$options=[];function get_option($key){return $GLOBALS['options'][$key]??'';}
 require __DIR__.'/../modules/deals/libraries/DealsKanban.php';
 $kanban=new \modules\deals\libraries\DealsKanban;
 if($kanban->defaultSortColumn()!=='dealorder'||$kanban->defaultSortDirection()!=='asc')throw new \RuntimeException('Native column/direction reversed');
 $options=['default_deals_kanban_sort_by'=>'deal_value','default_deals_kanban_sort_type'=>'DESC'];
 if($kanban->defaultSortColumn()!=='deal_value'||$kanban->defaultSortDirection()!=='desc')throw new \RuntimeException('Configuredsortlost');
 $options=['default_deals_kanban_sort_by'=>'drop table x','default_deals_kanban_sort_type'=>'dealorder'];
 if($kanban->defaultSortColumn()!=='dealorder'||$kanban->defaultSortDirection()!=='asc')throw new \RuntimeException('Unsafe sort escapedallowlist');
 echo "PASS actual DealsKanban nativecolumn/direction/configuredsort allowlists\n";
}
