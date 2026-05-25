<?php

return [
    'groq_api_key' => env('GROQ_API_KEY'),
    'groq_model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
    'mail_enabled' => filter_var(env('MAIL_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
];
