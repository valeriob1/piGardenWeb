<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\IconRequest;
use App\PiGardenSocketClient;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Prologue\Alerts\Facades\Alert;

/**
 * Class IconCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class IconCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->setModel('App\Models\Icon');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/icon');
        $this->crud->setEntityNameStrings('icon', 'icons');

        if (!backpack_user()->hasPermissionTo('manage setup', backpack_guard_name())) {
            $this->crud->denyAccess(['list', 'create', 'update', 'delete', 'show']);
        }
    }

    protected function setupListOperation()
    {
        $this->crud->addColumns([
            [
                'name' => 'zone',
                'label' => trans('pigarden.zone'),
                'type' => 'text',
            ],
            [
                'name' => 'enabled',
                'label' => trans('pigarden.enabled'),
                'type' => 'check',
            ],
            [
                'name' => 'icon_close',
                'label' => trans('pigarden.icon.close'),
                'type' => 'image',
                'prefix' => asset('/'),
                'width' => '100px',
                'height' => 'auto',
            ],
            [
                'name' => 'icon_open',
                'label' => trans('pigarden.icon.open'),
                'type' => 'image',
                'prefix' => asset('/'),
                'width' => '100px',
                'height' => 'auto',
            ],
        ]);
    }

    protected function setupCreateOperation()
    {
        $this->crud->setValidation(IconRequest::class);

        $aliases = [];
        try {
            $client = new PiGardenSocketClient();
            $status = $client->getStatus();
            foreach ($status->zones as $z) {
                $aliases[$z->name] = $z->name;
            }
        } catch (\Exception $e) {
            Alert::error($e->getMessage())->flash();
        }

        $this->crud->addFields([
            [
                'name' => 'zone',
                'label' => trans('pigarden.zone'),
                'type' => 'select_from_array',
                'allow_null' => true,
                'options' => $aliases,
                'default' => '',
            ],
            [
                'name' => 'enabled',
                'label' => trans('pigarden.enabled'),
                'type' => 'checkbox',
            ],
            [
                'name' => 'icon_close',
                'label' => trans('pigarden.icon.close'),
                'type' => 'browse',
            ],
            [
                'name' => 'icon_open',
                'label' => trans('pigarden.icon.open'),
                'type' => 'browse',
            ],
        ]);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
