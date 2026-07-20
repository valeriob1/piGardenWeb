<?php


namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;

class ApiController extends Controller
{

    public function postLog(Request $request) {

        $max_record_log = config('pigarden.max_record_log');

        if($request->user()->hasPermissionTo('api log', backpack_guard_name())) {

            $validated = $request->validate([
                'type' => 'required|string|max:100',
                'level' => 'required|string|max:50',
                'datetime_log' => 'required|date',
                'message' => 'nullable|string|max:5000',
            ]);

            $log = Log::create([
                'type' => $validated['type'],
                'level' => $validated['level'],
                'datetime_log' => $validated['datetime_log'],
                'message' => isset($validated['message']) ? $validated['message'] : '',
                'username' => $request->user()->email,
                'client_ip' => $request->getClientIp(),
            ]);

            if($max_record_log > 0){
                $max_id = $log->id - $max_record_log;
                if($max_id > 0) {
                    $deleted = Log::where('id', '<=', $max_id)->delete();
                    //\Log::debug("max_id=$max_id");
                    //\Log::debug("deleted=$deleted");
                }
            }
        }
       else
            return abort(401, "Permission denied");

    }

}
