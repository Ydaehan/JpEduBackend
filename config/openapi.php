<?php

return [

	'collections' => [

		'default' => [

			'info' => [
				'title' => config('app.name'),
				'description' => null,
				'version' => '1.0.0',
				'contact' => [],
			],

			'servers' => [
				[
					'url' => env('APP_URL'),
					'description' => null,
					'variables' => [],
				],
			],

			'tags' => [

				[
					'name' => 'User',
					'description' => 'User 관련 API 입니다',
				],
				[
					'name' => 'SocialAuth',
					'description' => 'SocialAuth 관련 API 입니다',
				],
				[
					'name' => 'Game',
					'description' => 'Game 관련 API 입니다',
				],
				[
					'name' => 'Grammar',
					'description' => 'Grammar 관련 API 입니다',
				],
				[
					'name' => 'Mail',
					'description' => 'Mail 관련 API 입니다',
				],
				[
					'name' => 'Manager',
					'description' => 'Manager 관련 API 입니다',
				],
				[
					'name' => 'Speech',
					'description' => 'Speech 관련 API 입니다',
				],
				[
					'name' => 'AdminSentenceNote',
					'description' => 'AdminSentenceNote 관련 API 입니다',
				],
				[
					'name' => 'VocabularyNote',
					'description' => 'VocabularyNote 관련 API 입니다',
				],
				[
					'name' => 'Ranking',
					'description' => 'Ranking 관련 API 입니다',
				],
				[
					'name' => 'S3',
					'description' => 'S3 관련 API 입니다',
				],
				[
					'name' => 'SentenceNote',
					'description' => 'SentenceNote 관련 API 입니다',
				]
			],

			'security' => [
				// GoldSpecDigital\ObjectOrientedOAS\Objects\SecurityRequirement::create()->securityScheme('BearerToken'),
			],

			// Non standard attributes used by code/doc generation tools can be added here
			'extensions' => [
				// 'x-tagGroups' => [
				//     [
				//         'name' => 'General',
				//         'tags' => [
				//             'user',
				//         ],
				//     ],
				// ],
			],

			// Route for exposing specification.
			// Leave uri null to disable.
			'route' => [
				'uri' => '/openapi',
				'middleware' => [],
			],

			// Register custom middlewares for different objects.
			'middlewares' => [
				'paths' => [
					//
				],
				'components' => [
					//
				],
			],

		],

	],

	// Directories to use for locating OpenAPI object definitions.
	'locations' => [
		'callbacks' => [
			app_path('OpenApi/Callbacks'),
		],

		'request_bodies' => [
			app_path('OpenApi/RequestBodies'),
		],

		'responses' => [
			app_path('OpenApi/Responses'),
		],

		'schemas' => [
			app_path('OpenApi/Schemas'),
		],

		'security_schemes' => [
			app_path('OpenApi/SecuritySchemes'),
		],
	],

];
