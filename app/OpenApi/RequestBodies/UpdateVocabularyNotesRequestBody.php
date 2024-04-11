<?php

namespace App\OpenApi\RequestBodies;

use App\OpenApi\Schemas\UpdateVocabularyNoteSchema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use Vyuldashev\LaravelOpenApi\Factories\RequestBodyFactory;

class UpdateVocabularyNotesRequestBody extends RequestBodyFactory
{
  public function build(): RequestBody
  {
    return RequestBody::create('UpdateVocabularyNote')
      ->description('단어장 수정')
      ->content(
        MediaType::formData()->schema(UpdateVocabularyNoteSchema::ref())
      );
  }
}
