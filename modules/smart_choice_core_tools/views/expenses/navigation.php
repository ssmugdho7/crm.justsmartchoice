<?php defined('BASEPATH') or exit('No direct script access allowed');
$method = strtolower($this->router->fetch_method());
$items = [];
if (staff_can('view', 'expenses') || staff_can('view_own', 'expenses')) {
    $items[] = ['list_expenses', _l('scct_expenses_directory'), admin_url('expenses/list_expenses'), in_array($method, ['index', 'list_expenses'], true)];
}
if (staff_can('create', 'expenses')) {
    $items[] = ['expense', _l('new_expense'), admin_url('expenses/expense'), $method === 'expense'];
    $items[] = ['import', _l('import_expenses'), admin_url('expenses/import'), $method === 'import'];
}
if (is_admin()) {
    $items[] = ['categories', _l('expense_categories'), admin_url('expenses/categories'), $method === 'categories'];
}
?>
<nav class="scct-expense-nav" aria-label="<?= e(_l('expenses')); ?>">
<?php foreach ($items as $item) { ?>
    <a class="<?= $item[3] ? 'active' : ''; ?>" href="<?= e($item[2]); ?>"<?= $item[3] ? ' aria-current="page"' : ''; ?>><?= e($item[1]); ?></a>
<?php } ?>
</nav>
