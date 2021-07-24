<?php

namespace App\Http\Controllers;

use App\Jobs\Calculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\Constant\HTTPHeader;

class LineHookController extends Controller
{
    public function hooks(Request $request)
    {
        $ChannelAccessToken = env('LINE_CHANNEL_ACCESS_TOKEN');
        $ChannelSecret = env('LINE_CHANNEL_SECRET');

        try {
            $httpClient = new CurlHTTPClient($ChannelAccessToken);
            $bot = new LINEBot($httpClient, ['channelSecret' => $ChannelSecret]);
            $signature = $request->header(HTTPHeader::LINE_SIGNATURE);
            $body = $request->getContent();
            $events = $bot->parseEventRequest($body, $signature);
            foreach ($events as $event) {

                if ($event instanceof \LINE\LINEBot\Event\MessageEvent\TextMessage) {
                    $reply_token = $event->getReplyToken();
                    $text = $event->getText();

                    switch ($text) {
                        case '加法':
                        case '減法':
                        case '乘法':
                        case '除法':
                            // Calculator::dispatch($bot, $reply_token);
                            $bot->replyText($reply_token, '請輸入第一個數字');
                            break;
                        case preg_match("/^[0-9]*$/", $text) ? true : false:
                            $bot->replyText($reply_token, '請輸入下一個數字');
                            $calcJob = (new Calculator($bot, $reply_token, $text))->onQueue('calc');
                            dispatch($calcJob);
                            break;
                    }

                }
            }
        } catch (\Exception $e) {
            Log::debug($e);
        }
    }
}
