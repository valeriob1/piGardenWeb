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
        $this->crud->setEntityNameStrings('log', 'logs');
    }

    protected function setupListOperation()
    {
        $this->crud->addClause('orderBy', 'datetime_log', 'desc');

        $this->crud->enableBulkActions();
        $this->crud->addBulkDeleteButton();

        $this->crud->addButtonFromView('top', 'table_refresh', 'table_refresh', 'beginning');
        $this->crud->removeButton('delete');

        $this->crud->disableResponsiveTable();

        // NOTE: filters (date_range + select2_multiple on type/level/client_ip) were
        // removed here during the Backpack 4 -> 5 upgrade: Backpack 5 moved list
        // filters to the paid "Pro" package. The log list still works (ordering,
        // pagination); restore filters by adding backpack/pro or reimplementing them.

        $this->crud->addColumns([
            [
                'name' => 'message',
                'label' => "Log",
                'type' => 'textarea',
            ],
            [
                'name' => 'type',
                'label' => __("Type"),
                'type' => 'text',
            ],
            [
                'name' => 'level',
                'label' => __("Level"),
                'type' => 'text',
            ],
            [
                'name' => 'datetime_log',
                'label' => __("Log date time"),
                'type' => 'text',
            ],
            [
                'name' => 'client_ip',
                'label' => __('Client ip'),
                'type' => 'text',
            ],

        ]);

    }

}
