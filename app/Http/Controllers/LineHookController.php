<?php

namespace App\Http\Controllers;

use LINE\LINEBot;
use App\Models\LineMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;

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

                    $calc_text = [
                        '加法', '減法', '乘法', '除法'
                    ];
                    $lineMessage = new LineMessage();
                    $previousMsg = $lineMessage->getMessage($event->getUserId())->message;
                    $calc = $lineMessage->getCalcType($event->getUserId())->message;
                    $lineMessage->addMessage($event);

                    if (in_array($text, $calc_text)) {
                        $bot->replyText($reply_token, '請輸入第一個數字');
                    } elseif (preg_match("/^[0-9]*$/", $text) && in_array($previousMsg, $calc_text)) {
                        $bot->replyText($reply_token, '請輸入下一個數字');
                    } else if (preg_match("/^[0-9]*$/", $text) && preg_match("/^[0-9]*$/", $previousMsg)) {

                        switch($calc){
                            case '加法':
                                $bot->replyText($reply_token, "$previousMsg + $text = " . intval($previousMsg) + intval($text));
                                break;
                            case '減法':
                                $bot->replyText($reply_token, "$previousMsg - $text = " . intval($previousMsg) - intval($text));
                                break;
                            case '乘法':
                                $bot->replyText($reply_token, "$previousMsg * $text = " . intval($previousMsg) * intval($text));
                                break;
                            case '除法':
                                $bot->replyText($reply_token, "$previousMsg / $text = " . intval($previousMsg) / intval($text));
                                break;
                        }
                    }

                }
            }
        } catch (\Exception $e) {
            Log::debug($e);
        }
    }
}
