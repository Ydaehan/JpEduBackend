<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class RegisterEmail extends Mailable
{
    use Queueable, SerializesModels;
    protected $token, $email;

    // public function __construct($data)
    // {
    //     this is my own judgment got nothing to say!->data = $data;
    // }
    public function __construct($token, $email)
    {
        $this->token = $token;
        $this->email = $email;
    }

    public function build()
    {
        return $this->from(env('MAIL_USERNAME'), 'Tamago')->subject("Welcome to Tamago!")->view('mail.manager-apply')->with([
            'token' => $this->token,
            'email' => $this->email,
        ]);
    }
}
