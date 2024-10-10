<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SentenceNote;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class SentenceSeeder extends Seeder
{
	private function getSentenceData()
	{
		$file = getFilesFromS3('sentences');
		foreach ($file as $fileName) {
			// 원본 파일의 확장자 가져오기
			$extension = pathinfo($fileName, PATHINFO_EXTENSION);
			// 임시 파일로 저장
			$tempPath = tempnam(sys_get_temp_dir(), 'txt') . '.' . $extension;
			// S3에서 파일 내용 가져오기
			$fileContent = Storage::disk('s3')->get($fileName);
			// 파일 이름을 추출하여 문장 노트 제목으로 사용
			$sentenceNoteName = pathinfo($fileName, PATHINFO_FILENAME);

			$existingNote = SentenceNote::where('title', $sentenceNoteName)->first();
			// DB에 이미 있는 문장노트의 경우 스킵
			if ($existingNote) {
				continue;
			}
			// 파일 내용을 임시 파일에 저장
			file_put_contents($tempPath, $fileContent);
			$contents = file_get_contents($tempPath); // 파일의 내용을 가져옴
			// 줄바꿈 문자를 기준으로 분리하여 배열로 만듦
			$replace_search = array("\n", "\r");
			$replace_target = array("", "");
			$contents = str_replace($replace_search, $replace_target, $contents);
			$contents = str_replace('。', "\n", $contents);
			$contents = rtrim($contents, "\n");
			$lines = explode("\n", $contents);

			// 배열의 각 원소를 순회하며 처리
			$lines = array_unique($lines);
			$text = implode("\n", $lines);
			// 의미 생성
			$source = "ja";
			$target = "ko";
			$meaning = papagoTranslation($source, $target, $text);
			$translateResult = explode("\n", $meaning);
			$result = [];
			foreach ($lines as $index => $line) {
				$result[] = [
					'문장' => $line,
					'의미' => $translateResult[$index],
				];
			}
			foreach ($result as $index => $text) {
				$gooResult = gooHiragana($text['문장']);
				$text['히라가나'] = $gooResult;
				$result[$index] = $text;
			}

			$encodedResult = json_encode($result);
			// 문장 데이터 생성
			$sentence = new SentenceNote();
			$sentence->user_id = 1;
			$sentence->title = $sentenceNoteName;
			$sentence->sentences = $encodedResult;
			$sentence->situation = $sentenceNoteName;
			$sentence->save();
		}
	}

	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		$this->getSentenceData();
	}
}
