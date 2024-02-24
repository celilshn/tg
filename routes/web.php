<?php

use DefStudio\Telegraph\Models\TelegraphBot;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/', function () {
    $bot = \DefStudio\Telegraph\Models\TelegraphBot::fromId(1);
    $bot->registerWebhook()->send();

    $ts = round(microtime(true) * 1000);

// Fetching API secret and API key from environment variables
    $api_secret = "mx0aBYs33eIilxBWC5";
    $api_key = "45d0b3c26f2644f19bfb98b07741b2f5";

// Getting request parameters
    $parameters = $_GET;

    $paramsObject = [];

// Iterating through parameters
    foreach ($parameters as $key => $value) {
        if ($key != 'signature' &&
            $key != 'timestamp' &&
            !is_empty($value) &&
            !is_disabled($value)) {
            $paramsObject[$key] = $value;
        }
    }

// Adding timestamp to parameters
    $paramsObject['timestamp'] = $ts;

// Generating signature
    if ($api_secret) {
        $queryString = http_build_query($paramsObject);

        $signature = hash_hmac('sha256', $queryString, $api_secret);
    }

// Function to check if string is disabled
    function is_disabled($str)
    {
        return $str == true;
    }

// Function to check if string is empty
    function is_empty($str)
    {
        return empty(trim($str));
    }

    $json =
        Http::get('https://api.mexc.com/api/v3/klines', [
            "api_key" => "mx0aBYs33eIilxBWC5",
            "secret_key" => "45d0b3c26f2644f19bfb98b07741b2f5",
            "timestamp" => $ts,
            "signature" => $signature,
            "symbol" => "COTIUSDT",
            "interval" => "15m",
            "limit" => "6",
        ])->body();
    $array = array_reverse(json_decode($json, true));
    $newArray = array_map(function ($data) {
        $candle = new Candle();
        $candle->open = $data[1];
        $candle->lowest = $data[3];
        $candle->close = $data[4];
        $candle->volume = $data[5];
        return $candle;
    }, $array);
    $candle0 = $newArray[0];
    $candle1 = $newArray[1];
    $candle2 = $newArray[2];
    $candle3 = $newArray[3];
    $candle4 = $newArray[4];
    $candle5 = $newArray[5];
    $result = false;
    if ($candle5->isBuliish() && $candle4->isBuliish() && $candle3->isBuliish())
        if ($candle2->isBearish() && $candle1->isBearish())
            if ($candle1->close > $candle3->open)
                $result = true;

    dd($result);

});

class Candle
{
    public $open;
    public $close;
    public $volume;
    public $lowest;

    public function isBearish()
    {
        return $this->close < $this->open;
    }

    public function isBuliish()
    {
        return $this->close > $this->open;
    }
}
