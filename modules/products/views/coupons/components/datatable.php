<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$id                   = $id ?? 'datatable-' . uniqid();
$ajaxUrl              = $ajaxUrl ?? '';
$columns              = $columns ?? [];
$tableClasses         = $tableClasses ?? 'table dt-table';
$defaultHeaderClass   = $defaultHeaderClass ?? 'px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider';
$defaultCellClass     = $defaultCellClass ?? 'px-6 py-4 whitespace-nowrap text-sm text-gray-900';
$toolbar              = $toolbar ?? '';
$filters              = $filters ?? null;
$emptyState           = $emptyState ?? '';
$tableAttributes      = $tableAttributes ?? [];

$tableAttributes['class'] = trim(($tableAttributes['class'] ?? '') . ' ' . $tableClasses);

if (!empty($ajaxUrl)) {
    $tableAttributes['data-ajax-url'] = $ajaxUrl;
}

$normaliseBoolean = static function ($value) {
    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }

    if (is_numeric($value)) {
        return ((int) $value) === 1 ? 'true' : 'false';
    }

    return $value;
};
?>

<div class="space-y-6">
    <?php if (!empty($toolbar)) : ?>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex-1">
                <?= $toolbar; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($filters)) : ?>
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100"><?= $filters['title'] ?? 'Filters'; ?></h3>
                    <?php if (!empty($filters['description'])) : ?>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400"><?= $filters['description']; ?></p>
                    <?php endif; ?>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <?php if (!empty($filters['reset'])) : ?>
                        <button type="button"
                            class="px-4 py-2 bg-white border border-gray-300 text-sm font-medium rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                            data-action="datatable:reset-filters"
                            data-target="#<?= html_escape($id); ?>">
                            <?= $filters['reset']; ?>
                        </button>
                    <?php endif; ?>

                    <button type="button"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors duration-200"
                        data-action="datatable:apply-filters"
                        data-target="#<?= html_escape($id); ?>">
                        <?= $filters['submit'] ?? 'Apply Filters'; ?>
                    </button>
                </div>
            </div>

            <?php if (!empty($filters['content'])) : ?>
                <div class="p-6">
                    <?= $filters['content']; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="rounded-2xl overflow-visible shadow-sm ring-1 ring-black/5 dark:ring-white/10">
        <div class="overflow-x-auto overflow-y-visible scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600 scrollbar-track-transparent">
            <table id="<?= html_escape($id); ?>"
                <?php foreach ($tableAttributes as $attribute => $value) :
                    if ($value === null || $value === '') {
                        continue;
                    }
                    echo ' ' . $attribute . '="' . html_escape($value) . '"';
                endforeach; ?>>
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700 transition-colors duration-300">
                    <tr>
                        <?php foreach ($columns as $column) :
                            $headerClass = $column['header_class'] ?? $defaultHeaderClass;
                            $headerAttributes = $column['header_attributes'] ?? [];

                            if (!is_array($headerAttributes)) {
                                $headerAttributes = [];
                            }

                            $headerAttributes = array_merge([
                                'data-data'       => $column['data'] ?? null,
                                'data-name'       => $column['name'] ?? ($column['data'] ?? null),
                                'data-title'      => $column['title'] ?? null,
                                'data-orderable'  => array_key_exists('orderable', $column) ? $normaliseBoolean($column['orderable']) : null,
                                'data-searchable' => array_key_exists('searchable', $column) ? $normaliseBoolean($column['searchable']) : null,
                                'data-visible'    => array_key_exists('visible', $column) ? $normaliseBoolean($column['visible']) : null,
                                'data-class-name' => $column['className'] ?? null,
                                'data-cell-class' => $column['cell_class'] ?? $defaultCellClass,
                            ], $headerAttributes);

                            $attributeString = '';

                            foreach ($headerAttributes as $attribute => $value) {
                                if ($value === null || $value === '') {
                                    continue;
                                }

                                $attributeString .= ' ' . $attribute . '="' . html_escape($value) . '"';
                            }
                            ?>
                            <th<?= $attributeString; ?> class="<?= html_escape($headerClass); ?>">
                                <?= html_escape($column['label'] ?? $column['title'] ?? ucfirst($column['data'] ?? '')); ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>

                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    <?= isset($slot) ? $slot : ''; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($emptyState)) : ?>
            <div class="p-6 text-center text-sm text-gray-500 dark:text-gray-400">
                <?= $emptyState; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
