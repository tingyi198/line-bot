<?php

namespace App\Http\Controllers;

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
                    $bot->replyText($reply_token, $text);
                }
            }
        } catch (\Exception $e) {
            Log::debug($e);
        }
    }
}
