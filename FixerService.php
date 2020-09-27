<?php

use Curl\Curl;
use Illuminate\Support\Facades\Cache;


class FixerService
{
    const FIXER_API_BASE_URL = "http://data.fixer.io/api/";

    public static function refresh()
    {
        /** @var Curl $curl */
        $curl = new Curl();
        $curl->get(self::FIXER_API_BASE_URL.'latest', array(
            'access_key' => env('FIXER_API_KEY')
        ));

        if ($curl->error) {
            echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
        } else {
//            1.16 USD = 4.27 AED
//            AED = 1.16/4.27
            $currencies = json_decode($curl->getRawResponse(), true);
            $usdPrice = $currencies['rates']['USD'];
            foreach ($currencies['rates'] as $index => $value) {
                if (!Cache::has($index)) {
                    Cache::put($index,round($usdPrice / $value, 2), 60000);
                }
            }
        }
    }
}
