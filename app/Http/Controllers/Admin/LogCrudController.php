<?php

namespace App\Http\Controllers\Admin;

use App\Models\Log;
use Backpack\CRUD\app\Http\Controllers\CrudController;

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

        // NOTE: filters (date_range + select2_multiple on type/level/client_ip) were
        // removed here during the Backpack 4 -> 5 upgrade: Backpack 5 moved list
        // filters to the paid "Pro" package. The log list still works (ordering,
        // pagination); restore filters by adding backpack/pro or reimplementing them.

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

}
