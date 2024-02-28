<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegisterEmail;

class MailController extends Controller
{
    public static function sendRegisterEmail($name, $email, $verificationCode)
    {
        $data = [
            'name' => $name,
            'verificationCode' => $verificationCode
        ];
        Mail::to($email)->send(new RegisterEmail($data));
    }
}
