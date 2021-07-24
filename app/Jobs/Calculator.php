<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use LINE\LINEBot;

class Calculator implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $bot;
    protected $reply_token;
    protected $num;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(LINEBot $bot, $reply_token, $num)
    {
        $this->bot = $bot;
        $this->$reply_token = $reply_token;
        $this->num = $num;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle($job, $next)
    {
        Redis::throttle('calc')
            ->then(function () use ($job, $next) {
                $next($job);
            }, function () use ($job) {
                $job->release();
            });

        // $this->bot->replyText($this->reply_token, 'hii');
    }
}
