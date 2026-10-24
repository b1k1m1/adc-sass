<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AIService
{
    public function ask($message)
    {
        $response = Http::timeout(60) // Increase timeout
             ->connectTimeout(15) // connection timeout
             ->withHeaders([
            'Authorization' => 'Bearer ' . config('services.openrouter.key'),
            'HTTP-Referer' => 'http://localhost',
            'X-Title' => 'ADC Chatbot',
        ])->post(config('services.openrouter.url'), [
            'model' => 'deepseek/deepseek-chat',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an AI assistant for an Adult Day Care system.

                    Return ONLY valid JSON. No explanation.

                    Allowed tables:
                    - members
                    - partattendence
                    - partbp
                    - partadl

                    Rules:
                    - Do NOT generate SQL
                    - Only return table + filters
                    - Use only these columns:

                    members: ParticipantId, FirstName, LastName, DateOfBirth
                    partattendence: ParticipantId, CalenderDate, AttendanceYN
                    partbp: ParticipantId, CalenderDate, LowBP, HighBP
                    partadl: ParticipantId, CalenderDate

                    Format:
                    {
                    "table": "table_name",
                    "filters": {
                        "column": "value"
                    }
                    }

                    Examples:

                    User: show attendance for participant 005 last month
                    Output:
                    {
                    "table": "partattendence",
                    "filters": {
                        "ParticipantId": "005",
                        "year": 2026,
                        "month": 2
                    }
                    }

                    User: list all members
                    Output:
                    {
                    "table": "members",
                    "filters": {}
                    }'
                ],
                [
                    'role' => 'user',
                    'content' => $message
                ]
            ],
            'max_tokens' => 200,
        ]);

        return $response->json();

    } // End Method

    public function askWithSystemPrompt($systemPrompt, $userMessage)
    {
        // If your current ask() supports only plain text
        // combine system + user into one message

        $finalPrompt = $systemPrompt . "\n\nUser Question: " . $userMessage;

        return $this->ask($finalPrompt);

    } // End Method

}
