<?php

return [
    /*
    | Supported drivers: fake, ai (aliases: openai, laravel-ai)
    */
    'driver' => env('CONTENT_ASSISTANT_DRIVER', 'fake'),

    'provider' => env('CONTENT_ASSISTANT_PROVIDER', env('AI_DEFAULT_PROVIDER', 'openai')),

    'model' => env('CONTENT_ASSISTANT_MODEL', 'gpt-4.1-mini'),

    'timeout' => (int) env('CONTENT_ASSISTANT_TIMEOUT', 60),

    'limits' => [
        'max_tokens_per_request' => (int) env('CONTENT_ASSISTANT_MAX_TOKENS', 4096),
        'daily_cost_limit_cents' => (int) env('CONTENT_ASSISTANT_DAILY_COST_LIMIT_CENTS', 500),
        'requests_per_minute' => (int) env('CONTENT_ASSISTANT_REQUESTS_PER_MINUTE', 10),
    ],

    'publish_confirmation_ttl_minutes' => 15,

    'internal_placeholders' => [
        '[RESULT METRIC NEEDED]',
        '[CLIENT NAME NEEDED]',
        '[TESTIMONIAL NEEDED]',
    ],

    'attachments' => [
        'disk' => env('CONTENT_ASSISTANT_ATTACHMENT_DISK', 'public'),
        'directory' => env('CONTENT_ASSISTANT_ATTACHMENT_DIRECTORY', 'content-assistant'),
        'max_files_per_message' => (int) env('CONTENT_ASSISTANT_MAX_ATTACHMENTS', 5),
        'max_file_size_kb' => (int) env('CONTENT_ASSISTANT_MAX_ATTACHMENT_SIZE_KB', 5120),
    ],
];
