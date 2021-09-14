<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MakeMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
      $data = $this->data;
      $from = env('MAIL_FROM_ADDRESS');
      //お問い合わせフォームからの送信の場合、fromを問合元のメールアドレスにしたいが、sendgridAPIの仕様上、不可能
      // if(!empty($data['from'])) {
      //    $from = $data['from'];
      // }
      return $this->view($this->data['template'], compact('data'))
        ->subject($this->data['subject'])
        ->from($from);
    }
}
