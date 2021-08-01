<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LineMessage extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function addMessage($data)
    {
        LineMessage::create([
            'reply_token' => $data->getReplyToken(),
            'type' => $data->getType(),
            'userId' => $data->getUserId(),
            'message_type' => $data->getMessageType(),
            'message' => $data->getText()
        ]);
    }

    public function getMessage($userId)
    {
        return LineMessage::where('userId', $userId)->orderBy('created_at', 'DESC')->first();
    }

    public function getCalcType($userId)
    {
        return LineMessage::where('userId', $userId)
            ->whereIn('message', ['加法', '減法', '乘法', '除法'])
            ->orderBy('created_at', 'DESC')->first();
    }
}
