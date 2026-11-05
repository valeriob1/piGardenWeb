<?php

namespace App\Http\Controllers\Admin;

use App\Models\Log;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\Widget;
use Carbon\Carbon;

/**
 * Class LogCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class LogCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup()
    {
        $this->crud->setModel('App\Models\Log');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/log');
        $this->crud->setEntityNameStrings(trans('pigarden.log.singular'), trans('pigarden.log.plural'));
    }

    protected function setupListOperation()
    {
        $this->crud->addClause('orderBy', 'datetime_log', 'desc');

        // NOTE: bulk actions (multi-select + bulk delete) were removed during the
        // Backpack 4 -> 6 upgrade: the bulk_delete button view lives in the paid
        // "Pro" package, so enabling it aborts with "Button view and fallbacks do
        // not exist". Restore by adding backpack/pro.

        $this->crud->addButtonFromView('top', 'table_refresh', 'table_refresh', 'beginning');
        $this->crud->removeButton('delete');

        // Responsive table: on a phone the columns that don't fit collapse into
        // an expandable row (tap the row) instead of overflowing the screen.
        // 'priority' decides what stays visible: date and message first.
        $this->crud->enableResponsiveTable();

        // Filters: Backpack's list filters are part of the paid "Pro" package, so
        // they are reimplemented here as a plain GET form (see log/filters view).
        // The datatable copies the page query string into its /search ajax URL,
        // so the same request parameters reach this method on every reload.
        // Backpack 6 enables "persistent table" by default: opening /admin/log
        // redirected back to the last filtered URL, so "Clear filters" did
        // nothing and an old filter could silently hide new log lines.
        $this->crud->disablePersistentTable();
        $this->applyFilters();
        Widget::add([
            'type' => 'view',
            'view' => 'log.filters',
            'section' => 'before_content',
            'filters' => $this->currentFilters(),
            'types' => $this->distinctValues('type'),
            'levels' => $this->distinctValues('level'),
        ]);

        // Backpack always keeps the first column visible (it hosts the "+" that
        // opens the hidden fields), so the message goes first. The "Follow Log"
        // button (table_refresh) re-sorts on column 1 = datetime_log.
        $this->crud->addColumns([
            [
                'name' => 'message',
                'label' => "Log",
                'type' => 'textarea',
                'priority' => 1,
                // the list table is "nowrap": let long messages wrap, otherwise
                // this column alone is wider than a phone screen
                'wrapper' => ['element' => 'span', 'class' => 'd-block text-wrap', 'style' => 'min-width: 12rem;'],
            ],
            [
                'name' => 'datetime_log',
                'label' => __("Log date time"),
                'type' => 'text',
                'priority' => 2,
            ],
            [
                'name' => 'type',
                'label' => __("Type"),
                'type' => 'text',
                'priority' => 3,
            ],
            [
                'name' => 'level',
                'label' => __("Level"),
                'type' => 'text',
                'priority' => 3,
            ],
            [
                'name' => 'client_ip',
                'label' => __('Client ip'),
                'type' => 'text',
                'priority' => 4,
            ],
        ]);

    }

    /**
     * Filter values from the query string, normalised: unknown dates are dropped.
     *
     * @return array{type: string, level: string, from: string, to: string}
     */
    protected function currentFilters()
    {
        $request = request();
        $date = function ($key) use ($request) {
            $value = (string) $request->query($key, '');
            if ($value === '') {
                return '';
            }
            try {
                return Carbon::createFromFormat('Y-m-d', $value)->format('Y-m-d');
            } catch (\Exception $e) {
                return '';
            }
        };

        return [
            'type' => (string) $request->query('filter_type', ''),
            'level' => (string) $request->query('filter_level', ''),
            'from' => $date('filter_from'),
            'to' => $date('filter_to'),
        ];
    }

    protected function applyFilters()
    {
        $f = $this->currentFilters();

        if ($f['type'] !== '') {
            $this->crud->addClause('where', 'type', $f['type']);
        }
        if ($f['level'] !== '') {
            $this->crud->addClause('where', 'level', $f['level']);
        }
        if ($f['from'] !== '') {
            $this->crud->addClause('where', 'datetime_log', '>=', $f['from'].' 00:00:00');
        }
        if ($f['to'] !== '') {
            $this->crud->addClause('where', 'datetime_log', '<=', $f['to'].' 23:59:59');
        }
    }

    /**
     * Values present in the table for a column, for the filter dropdowns.
     *
     * @param string $column
     * @return array
     */
    protected function distinctValues($column)
    {
        return Log::query()
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->all();
    }

}
