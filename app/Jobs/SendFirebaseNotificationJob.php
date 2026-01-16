<?php

namespace App\Jobs;

use App\Models\User;
use App\Traits\HasFcmToken;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;

class SendFirebaseNotificationJob implements ShouldQueue,ShouldBeEncrypted
{
    use Queueable, HasFcmToken;

    protected string $token;
    protected string $body;
    protected string $title;
    protected array $data;
    /**
     * Create a new job instance.
     */
    public function __construct(string $token, string $title, string $body,array $data)
    {
        $this->token = $token ;
        $this->title = $title ;
        $this->body = $body ;
        $this->data = array_map(function ($value) {
            return strval($value);
        }, $data);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->sendFirebaseMessage($this->token , $this->title , $this->body,$this->data) ;
    }
}
