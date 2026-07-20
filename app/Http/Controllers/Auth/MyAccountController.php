<?php


namespace App\Http\Controllers\Auth;


use App\Http\Requests\Auth\AccountApiTokenRerquest;
use Prologue\Alerts\Facades\Alert;

class MyAccountController extends \Backpack\CRUD\app\Http\Controllers\MyAccountController
{

    /**
     * Show the user a form to change his personal information.
     */
    public function getApiTokenForm()
    {
        $this->data['title'] = trans('backpack::base.my_account');
        $this->data['user'] = $this->guard()->user();

        return view('auth.account.api_token', $this->data);
    }

    /**
     * Save the modified personal information for a user.
     */
    public function postApiTokenForm(AccountApiTokenRerquest $request)
    {
        // Only the api-token action may pass: with except('_token') any fillable
        // field (email, password, ...) could be mass-assigned from this form
        $result = $this->guard()->user()->update($request->only('action_api_token'));

        if ($result) {
            Alert::success(trans('backpack::base.account_updated'))->flash();
        } else {
            Alert::error(trans('backpack::base.error_saving'))->flash();
        }

        return redirect()->back();
    }


}
