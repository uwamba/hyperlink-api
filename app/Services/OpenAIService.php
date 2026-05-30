<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\ChatMessage;

class OpenAIService
{
    public function reply(string $sessionId, string $userMessage): string
    {
        $history = ChatMessage::where('session_id', $sessionId)
            ->orderBy('created_at', 'asc')
            ->get();

        $messages = [
            [
                'role'    => 'system',
                'content' => $this->systemPrompt(),
            ]
        ];

        foreach ($history as $msg) {
            $messages[] = [
                'role'    => $msg->sender === 'user' ? 'user' : 'assistant',
                'content' => $msg->message,
            ];
        }

        $messages[] = [
            'role'    => 'user',
            'content' => $userMessage,
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.openai.key'),
                'Content-Type'  => 'application/json',
            ])
            ->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'       => config('services.openai.model', 'gpt-4o-mini'),
                'messages'    => $messages,
                'max_tokens'  => 500,
                'temperature' => 0.4,
            ]);

            if ($response->failed()) {
                Log::error('OpenAI HTTP error: ' . $response->body());
                return "Sorry, I'm having trouble right now. Please try again or click 'Talk to Agent'.";
            }

            return $response->json('choices.0.message.content')
                ?? "I couldn't process that. Please try again or click 'Talk to Agent'.";

        } catch (\Exception $e) {
            Log::error('OpenAI error: ' . $e->getMessage());
            return "Sorry, I'm having trouble right now. Please try again or click 'Talk to Agent'.";
        }
    }

    protected function systemPrompt(): string
    {
        return <<<PROMPT
You are a friendly and professional support assistant for Hyperlink Network, an internet service provider using Starlink satellite technology in Rwanda.

Your job is to help customers with:
- Internet connectivity issues (no connection, slow speeds, outages)
- Billing and payment questions
- New connection and installation requests
- Router and equipment troubleshooting
- Service outage information

Guidelines:
- Keep replies concise and clear (2–4 sentences unless troubleshooting steps are needed)
- Always be polite and empathetic
- Respond in the same language the user writes in (English or Kinyarwanda)
- For billing or account-specific issues, ask the customer to verify their email or phone
- If the problem needs physical intervention or is complex, suggest talking to a human agent
- Never make up information about specific account balances or outage statuses
- If unsure, say so and offer to connect them to an agent
PROMPT;
    }
}