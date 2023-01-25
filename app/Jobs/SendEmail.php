<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $email, $token, $type;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($email, $token, $type)
    {
        $this->email = $email;
        $this->token = $token;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        // send email for user verification
        if ($this->type == 'verification') {
            Mail::send('emails.verify', ['confirmation_code' => $this->token], function (Message $m) {
                $m->to($this->email);
                $m->subject('Konfirmasi alamat email anda');
            });
        } else {
            // send email for password reset
            Mail::send('emails.index', ['token' => $this->token], function (Message $message) {
                $message->to($this->email);
                $message->subject('Password Reset');
            });
        }
    }
}
