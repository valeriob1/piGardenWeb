<?php
/**
 * Get list of zones
 * User: lejubila
 * Date: 04/09/16
 * Time: 8.50
 */

namespace app;

use App\PiGardenSocketClient;
use Illuminate\Support\Facades\Log;

class Zones {

    protected static $zones = null;

    public static function get()
    {
        try
        {
            self::$zones = array();
            $c = new PiGardenSocketClient();
            $status = $c->getStatus(null, true);
            if (!is_null($status)) {
                if (property_exists($status, 'zones') && count((array)$status->zones)>0) {
                    self::$zones = array();
                    foreach ($status->zones as $zone) {
                        $zone->name_stripped = str_replace('_', ' ', $zone->name);
                        self::$zones[] = $zone;
                    }
                }
            }
        }
        catch (\Exception $e)
        {
            // NB: don't use \Debugbar here: it's a dev-only dependency and this
            // catch runs in production exactly when the socket server is unreachable
            self::$zones = array();
            Log::warning('Zones::get failed: '.$e->getMessage());
        }

        return self::$zones;
    }


}
