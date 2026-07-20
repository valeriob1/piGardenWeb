<?php

namespace App\Http\Controllers\Admin;

use Backpack\PermissionManager\app\Http\Controllers\UserCrudController as BackpackUserCrudController;

class UserCrudController extends BackpackUserCrudController
{
    public function setup()
    {
        parent::setup();

        if (
            !config('backpack.permissionmanager.allow_manage_user') &&
            !backpack_user()->hasPermissionTo('manage users', backpack_guard_name())
        ) {
            $this->crud->denyAccess(['list', 'create', 'update', 'delete', 'show']);
        }
    }

    public function setupCreateOperation()
    {
        parent::setupCreateOperation();
        $this->addApiTokenFields();
    }

    public function setupUpdateOperation()
    {
        parent::setupUpdateOperation();
        $this->addApiTokenFields();
    }

    /**
     * Custom api-token fields, appended after the permission-manager password fields.
     */
    protected function addApiTokenFields()
    {
        $this->crud->addField([
            'name' => 'api_token',
            'type' => 'text',
            'attributes' => [
                'readonly' => 'readonly'
            ],
            'wrapperAttributes' => [
                'class' => 'form-group col-md-6'
            ]
        ])->afterField('password_confirmation');

        $this->crud->addField([
            'name' => 'action_api_token',
            'type' => 'select_from_array',
            'options' => [
                '' => '',
                'remove_token' => __('Remove token'),
                'regenerate_token' => __('Generate new token'),
            ],
            'wrapperAttributes' => [
                'class' => 'form-group col-md-6'
            ]
        ])->afterField('api_token');
    }
}
