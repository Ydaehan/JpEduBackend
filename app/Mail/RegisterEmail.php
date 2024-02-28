<?php
namespace App\Mail;

use Illuminate\Mail\Mailable;

class RegisterEmail extends Mailable
{
    protected $data;

    // public function __construct($data)
    // {
    //     this is my own judgment got nothing to say!->data = $data;
    // }

    public function build()
    {
        return $this->from(env('MAIL_USERNAME'), 'Tamago')->subject("Welcome to Tamago!")->view('mail.register-email', ['email_data' => $this->data]);
    }
}

