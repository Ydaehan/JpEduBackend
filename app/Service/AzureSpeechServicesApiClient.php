<?php

namespace App\Services\Speech;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Config\Repository;

class AzureSpeechServicesApiClient
{
  private string $key;
  private string $region;
  private string $pronunciationEndpoint;
  private Client $client;

  public function __construct(Repository $config)
  {
    $this->key = $config->get('services.azureSpeech.key');
    $this->region = $config->get('services.azureSpeech.region');
    $this->pronunciationEndpoint =
      "https://$this->region.stt.speech.microsoft.com/speech/recognition/conversation/cognitiveservices/v1?language=:lang";
  }

  public function assessPronunciation(string $text, string $audio): string
  {
    $response = $this->client()->post(
      $this->pronunciationEndpoint(),
      [
        RequestOptions::HEADERS => $this->pronunciationHeaders($text),
        RequestOptions::BODY => $audio
      ]
    );

    return $response->getBody()->getContents();
  }

  public function region(): string
  {
    return $this->region;
  }

  private function client(): Client
  {
    if (!isset($this->client)) {
      $this->client = new Client();
    }

    return $this->client;
  }

  private function pronunciationHeaders(string $text): array
  {
    return [
      'Ocp-Apim-Subscription-Key' => $this->key,
      'Content-Type' => 'audio/wav',
      'Accept' => 'application/json;text/xml',
      'Pronunciation-Assessment' => base64_encode(json_encode([
        'ReferenceText' => $text,
        'GradingSystem' => 'HundredMark',
        'PhonemeAlphabet' => 'IPA',
      ])),

    ];
  }

  private function pronunciationEndpoint(): string
  {
    $language = "ja-jp";

    return str_replace(':lang', $language, $this->pronunciationEndpoint);
  }
}
