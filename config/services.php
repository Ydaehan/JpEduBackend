<?php

return [

  /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

  'mailgun' => [
    'domain' => env('MAILGUN_DOMAIN'),
    'secret' => env('MAILGUN_SECRET'),
    'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    'scheme' => 'https',
  ],

  'postmark' => [
    'token' => env('POSTMARK_TOKEN'),
  ],

  'ses' => [
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
  ],

  'kakao' => [
    'client_id' => env('KAKAO_CLIENT_ID'),
    'client_secret' => env('KAKAO_CLIENT_SECRET'),
    'redirect' => env('KAKAO_REDIRECT_URI')
  ],

  'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URI')
  ],

  'naver' => [
    'client_id' => env('NAVER_CLIENT_ID'),
    'client_secret' => env('NAVER_CLIENT_SECRET'),
    'redirect' => env('NAVER_REDIRECT_URI')
  ],

  'github' => [
    'client_id' => env('GITHUB_CLIENT_ID'),
    'client_secret' => env('GITHUB_CLIENT_SECRET'),
    'redirect' => env('GITHUB_REDIRECT_URI')
  ],

  'papago' => [
    'client_id' => env('APP_NAVER_PAPAGO_TEXT_TRANSLATION_CLIENT_ID'),
    'client_secret' => env('APP_NAVER_PAPAGO_TEXT_TRANSLATION_CLIENT_SECRET_KEY')
  ],

  'naver_ocr' => [
    'client_secret' => env('APP_NAVER_CLOVA_OCR_SECRET_KEY'),
    'url' => env('APP_NAVER_APIGW_INVOKE_URL')
  ],
];
