<?php


namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    /**
     * Store a log line sent by piGarden's log_send().
     *
     * Responses are deliberately explicit: the caller is a shell curl that
     * throws its output away, so when someone debugs by hand the body has to
     * say what went wrong (wrong token, missing permission, bad field).
     */
    public function postLog(Request $request)
    {
        if (! $request->user()->hasPermissionTo('api log', backpack_guard_name())) {
            return response()->json([
                'message' => "The user '{$request->user()->email}' lacks the 'api log' permission.",
            ], 403);
        }

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
            'message' => $validated['message'] ?? '',
            'username' => $request->user()->email,
            'client_ip' => $request->getClientIp(),
        ]);

        // Keep the table bounded: drop everything older than the newest
        // PIGARDEN_MAX_RECORD_LOG rows (0 disables the trimming).
        $max_record_log = (int) config('pigarden.max_record_log');
        if ($max_record_log > 0) {
            $max_id = $log->id - $max_record_log;
            if ($max_id > 0) {
                Log::where('id', '<=', $max_id)->delete();
            }
        }

        return response()->json(['message' => 'ok', 'id' => $log->id], 201);
    }
}
