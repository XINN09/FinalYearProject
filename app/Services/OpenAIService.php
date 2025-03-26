<?php

namespace App\Services;

use GuzzleHttp\Client;

class OpenAIService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = env('OPENAI_API_KEY');
    }

    public function verifyReceiptAmount($text, $amount)
    {
        $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'gpt-4',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are an AI assistant verifying receipt amounts.'],
                    ['role' => 'user', 'content' => "Verify if the amount {$amount} matches the total amount in the following receipt text: {$text}"],
                ],
                'max_tokens' => 50,
            ],
        ]);

        return json_decode($response->getBody(), true);
    }
}