<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Image Generation Provider
    |--------------------------------------------------------------------------
    |
    | Options: 'none' (placeholder only), 'huggingface', 'dalle'
    |
    */
    'ai_image_provider' => env('AI_IMAGE_PROVIDER', 'none'),

    /*
    |--------------------------------------------------------------------------
    | Hugging Face Configuration
    |--------------------------------------------------------------------------
    | Get your free token at: https://huggingface.co/settings/tokens
    | Free tier has rate limits but works for testing
    |
    */
    'huggingface_token' => env('HUGGINGFACE_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | OpenAI DALL-E Configuration
    |--------------------------------------------------------------------------
    | Get your API key at: https://platform.openai.com/api-keys
    | Requires paid API usage
    |
    */
    'openai_api_key' => env('OPENAI_API_KEY', ''),
];