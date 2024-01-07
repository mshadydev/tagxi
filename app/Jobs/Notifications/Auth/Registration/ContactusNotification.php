<?php

namespace App\Jobs\Notifications\Auth\Registration;

use App\Jobs\Notifications\BaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use App\Mail\ContactUsMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

class ContactusNotification extends BaseNotification
{


    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * The registered user.
     *
     * @var User
     */
    protected $data;

    /**
     * Create a new job instance.
     *
     * @param User $user
     */
    public function __construct($data)
    {
        $this->data=$data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to(env('MAIL_FROM_ADDRESS'))->send(new ContactUsMail($this->data));
    }
    /**
     * Send the Contactus (welcome) email.
     */
    // protected function sendContactusEMail()
    // {
              
    //     $this->mailer()
    //         ->send('email.auth.contactus.contactusmail', $this->data, function (Message $message) {
    //             $message->to(env('MAIL_FROM_ADDRESS'));
    //             $message->subject('Contact us Mail');
    //         });

    // }
}
