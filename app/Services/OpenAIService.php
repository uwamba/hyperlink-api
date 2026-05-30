<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use App\Models\ChatMessage;

class OpenAIService
{
    public function reply(string $sessionId, string $userMessage): string
    {
        // Load full chat history for context
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
            // Map sender to OpenAI roles
            if ($msg->sender === 'user') {
                $role = 'user';
            } elseif ($msg->sender === 'agent') {
                $role = 'assistant'; // treat agent messages as assistant too
            } else {
                $role = 'assistant'; // bot
            }

            $messages[] = [
                'role'    => $role,
                'content' => $msg->message,
            ];
        }

        // Append the new message
        $messages[] = [
            'role'    => 'user',
            'content' => $userMessage,
        ];

        try {
            $response = OpenAI::chat()->create([
                'model'       => config('openai.model', 'gpt-4o-mini'),
                'messages'    => $messages,
                'max_tokens'  => 500,
                'temperature' => 0.4,
            ]);

            return $response->choices[0]->message->content
                ?? "I couldn't process that. Please try again or click 'Talk to Agent'.";

        } catch (\Exception $e) {
            \Log::error('OpenAI error: ' . $e->getMessage());
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
- Do not repeat the welcome message — just answer the user's question directly
PROMPT;
    }
}