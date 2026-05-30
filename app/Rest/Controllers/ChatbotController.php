<?php

namespace App\Rest\Controllers;

use Illuminate\Http\Request;
use App\Rest\Controller as RestController;
use Illuminate\Support\Facades\Log;
use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Models\Client;
use App\Services\OpenAIService;
use App\Services\AttachmentService; 

class ChatbotController extends RestController
{
    private array $knowledge = [
        'no_internet' => [
            'keywords' => ['no internet', 'connection down', 'not connecting', 'no connection', 'disconnected', 'offline'],
            'response' => "Sorry to hear you're having connection issues! Here are some quick steps:\n1. Restart your router (unplug for 30 seconds)\n2. Check if the lights on your router are normal\n3. Try connecting another device\n\nIf still not working, our technicians are available 24/7. Would you like to talk to an agent?"
        ],
        'slow_internet' => [
            'keywords' => ['slow', 'speed', 'buffering', 'lag', 'slow internet', 'loading slowly'],
            'response' => "Slow internet can be caused by several factors:\n1. Too many devices connected\n2. Peak usage hours\n3. Router placement\n4. Plan speed limit\n\nRun a speed test at fast.com and share your results with our team. Would you like to talk to an agent?"
        ],
        'billing' => [
            'keywords' => ['bill', 'billing', 'payment', 'invoice', 'charge', 'pay', 'cost', 'subscription'],
            'response' => "For billing inquiries:\n- Check your invoice in the client portal\n- Payment is due on the 5th of each month\n- We accept Mobile Money, Bank Transfer, and Card\n\nFor disputes, email billing@hyperlinknetwork.com. Would you like to talk to an agent?"
        ],
        'new_connection' => [
            'keywords' => ['new connection', 'new install', 'subscribe', 'sign up', 'get internet', 'new client'],
            'response' => "Welcome! Getting connected is easy:\n1. Choose a plan that fits your needs\n2. Our team surveys your location\n3. Installation within 1-2 business days\n\nContact sales@hyperlinknetwork.com or fill the contact form. Would you like to talk to an agent?"
        ],
        'router' => [
            'keywords' => ['router', 'equipment', 'device', 'modem', 'light', 'blinking', 'hardware'],
            'response' => "Router troubleshooting steps:\n1. Check all cable connections\n2. Restart the router\n3. Check indicator lights (red = issue, green = normal)\n4. Factory reset if needed (hold reset button 10s)\n\nIf equipment is faulty, we'll replace it. Would you like to talk to an agent?"
        ],
        'outage' => [
            'keywords' => ['outage', 'down for everyone', 'area', 'neighborhood', 'maintenance', 'everyone offline'],
            'response' => "We apologize for any service disruption in your area. Our team monitors outages 24/7. Check our status page or follow us on social media for live updates. Would you like to talk to an agent to report your location?"
        ],
        'greeting' => [
            'keywords' => ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening'],
            'response' => "Hello! Welcome to Hyperlink Network support. How can I help you today? You can select one of the common issues below or type your question."
        ],
        'thanks' => [
            'keywords' => ['thank', 'thanks', 'appreciate', 'helpful', 'great', 'awesome', 'perfect'],
            'response' => "You are welcome! Is there anything else I can help you with?"
        ],
        'agent' => [
            'keywords' => ['agent', 'human', 'person', 'talk to someone', 'real person', 'employee', 'staff'],
            'response' => "I'll connect you with a support agent right away. Please hold on while we find the next available agent."
        ],
    ];

    // Step 1: Start session — verify identity
    public function startSession(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
        ]);

        // Check if existing client
        $client = Client::where('email', $data['email'])->first();

        $session = ChatSession::create([
            'name'                => $data['name'],
            'email'               => $data['email'],
            'phone'               => $data['phone'],
            'client_id'           => $client?->id,
            'is_verified_client'  => $client !== null,
            'status'              => 'active',
        ]);

        // Save welcome message
        ChatMessage::create([
            'session_id' => $session->id,
            'sender'     => 'bot',
            'message'    => $client
                ? "Welcome back, {$data['name']}! 👋 I can see you're an existing Hyperlink client. How can I help you today?"
                : "Hello {$data['name']}! 👋 Welcome to Hyperlink Network support. How can I help you today?",
        ]);

        return response()->json([
            'session_id'          => $session->id,
            'is_verified_client'  => $session->is_verified_client,
            'welcome_message'     => ChatMessage::where('session_id', $session->id)->first(),
        ], 201);
    }

    // Step 2: Send message in a session
   public function reply(Request $request)
{
    $data = $request->validate([
        'session_id' => 'required|uuid|exists:chat_sessions,id',
        'message'    => 'required|string|max:1000',
    ]);

    $session = ChatSession::findOrFail($data['session_id']);

    // Save user message
    ChatMessage::create([
        'session_id' => $session->id,
        'sender'     => 'user',
        'message'    => $data['message'],
    ]);

    // If agent has joined, do NOT respond with bot
    if (in_array($session->status, ['with_agent', 'waiting_agent'])) {
        return response()->json([
            'reply'  => null,
            'status' => 'agent_handling',
        ]);
    }

    // ── OpenAI reply (replaces findReply) ──
    $ai        = new OpenAIService();
    $replyText = $ai->reply($session->id, $data['message']);

    $botMessage = ChatMessage::create([
        'session_id' => $session->id,
        'sender'     => 'bot',
        'message'    => $replyText,
    ]);

    return response()->json([
        'reply'      => $replyText,
        'message_id' => $botMessage->id,
        'status'     => 'success',
    ]);
}

    // Step 3: User selects a quick issue
    public function selectIssue(Request $request)
{
    $data = $request->validate([
        'session_id' => 'required|uuid|exists:chat_sessions,id',
        'issue'      => 'required|string|max:255',
    ]);

    $session = ChatSession::findOrFail($data['session_id']);
    $session->update(['issue_category' => $data['issue']]);

    ChatMessage::create([
        'session_id' => $session->id,
        'sender'     => 'user',
        'message'    => $data['issue'],
    ]);

    // ── OpenAI reply ──
    $ai        = new OpenAIService();
    $replyText = $ai->reply($session->id, $data['issue']);

    ChatMessage::create([
        'session_id' => $session->id,
        'sender'     => 'bot',
        'message'    => $replyText,
    ]);

    return response()->json([
        'reply'  => $replyText,
        'status' => 'success',
    ]);
}

    // Step 4: Request agent
    public function requestAgent(Request $request)
    {
        $data = $request->validate([
            'session_id' => 'required|uuid|exists:chat_sessions,id',
        ]);

        $session = ChatSession::findOrFail($data['session_id']);
        $session->update(['status' => 'waiting_agent']);

        ChatMessage::create([
            'session_id' => $session->id,
            'sender'     => 'bot',
            'message'    => "You've been added to the agent queue. An agent will join shortly. Your session ID is: {$session->id}",
        ]);

        // TODO: notify agents via broadcast/email/push
        Log::info('Agent requested', ['session_id' => $session->id, 'email' => $session->email]);

        return response()->json([
            'message' => 'Agent request submitted. Please wait.',
            'status'  => 'waiting_agent',
        ]);
    }

    // Step 5: Get session history (for agent dashboard later)
    public function getHistory(Request $request, string $sessionId)
    {
        $session = ChatSession::with('messages')->findOrFail($sessionId);

        return response()->json([
            'session'  => $session,
            'messages' => $session->messages()->orderBy('created_at')->get(),
        ]);
    }

    private function findReply(string $message): string
    {
        foreach ($this->knowledge as $topic) {
            foreach ($topic['keywords'] as $keyword) {
                if (str_contains($message, $keyword)) {
                    return $topic['response'];
                }
            }
        }

        return "I'm not sure I understand. Please select one of the common issues below, or type your question differently. You can also click 'Talk to Agent' for direct help.";
    }


    // Get all sessions (for dashboard list)
public function getSessions(Request $request)
{
    $sessions = ChatSession::with(['messages' => function($q) {
            $q->latest()->limit(1);
        }])
        ->orderByRaw("FIELD(status, 'waiting_agent', 'with_agent', 'active', 'closed')")
        ->orderBy('updated_at', 'desc')
        ->get()
        ->map(function($session) {
            return [
                'id'                 => $session->id,
                'name'               => $session->name,
                'email'              => $session->email,
                'phone'              => $session->phone,
                'status'             => $session->status,
                'issue_category'     => $session->issue_category,
                'is_verified_client' => $session->is_verified_client,
                'last_message'       => $session->messages->first()?->message,
                'last_message_at'    => $session->messages->first()?->created_at,
                'created_at'         => $session->created_at,
            ];
        });

    return response()->json(['sessions' => $sessions]);
}

// Agent sends a message to a session
public function agentReply(Request $request)
{
    $data = $request->validate([
        'session_id' => 'required|uuid|exists:chat_sessions,id',
        'message'    => 'required|string|max:2000',
        'agent_id'   => 'nullable|uuid',
    ]);

    $session = ChatSession::findOrFail($data['session_id']);

    // Mark session as with_agent
    if ($session->status === 'waiting_agent') {
        $session->update([
            'status'           => 'with_agent',
            'agent_joined_at'  => now(),
        ]);
    }

    $message = ChatMessage::create([
        'session_id' => $data['session_id'],
        'sender'     => 'agent',
        'message'    => $data['message'],
        'agent_id'   => $data['agent_id'] ?? null,
    ]);

    return response()->json([
        'message' => $message,
        'status'  => 'sent',
    ]);
}

// Close a session
public function closeSession(Request $request, string $sessionId)
{
    $session = ChatSession::findOrFail($sessionId);
    $session->update(['status' => 'closed']);

    ChatMessage::create([
        'session_id' => $sessionId,
        'sender'     => 'bot',
        'message'    => 'This support session has been closed by an agent. Thank you for contacting Hyperlink Network!',
    ]);

    return response()->json(['message' => 'Session closed.']);
}
// Agent AI suggest — generates a reply draft based on context provided by agent
public function agentAiSuggest(Request $request)
{
    $data = $request->validate([
        'session_id' => 'required|uuid|exists:chat_sessions,id',
        'context'    => 'required|string|max:1000',
    ]);

    // Load recent chat history for this session (last 10 messages)
    $history = ChatMessage::where('session_id', $data['session_id'])
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get()
        ->reverse();

    $messages = [
        [
            'role'    => 'system',
            'content' => "You are an expert support agent assistant for Hyperlink Network, an internet provider using Starlink in Rwanda.
Your job is to help support agents draft professional, friendly, and concise replies to customers.
The agent will describe the situation. Generate a ready-to-send reply the agent can send directly to the customer.
Keep it short (2-4 sentences), empathetic, and action-oriented.
Do NOT include any preamble like 'Here is a reply:' — just write the reply text directly.",
        ],
    ];

    foreach ($history as $msg) {
        $messages[] = [
            'role'    => $msg->sender === 'user' ? 'user' : 'assistant',
            'content' => $msg->message,
        ];
    }

    $messages[] = [
        'role'    => 'user',
        'content' => "Agent context: {$data['context']}\n\nDraft a reply I can send to the customer.",
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
            'max_tokens'  => 300,
            'temperature' => 0.5,
        ]);

        if ($response->failed()) {
            return response()->json(['suggestion' => null, 'error' => 'AI unavailable'], 200);
        }

        $suggestion = $response->json('choices.0.message.content');

        return response()->json(['suggestion' => $suggestion]);

    } catch (\Exception $e) {
        Log::error('Agent AI suggest error: ' . $e->getMessage());
        return response()->json(['suggestion' => null, 'error' => 'AI unavailable'], 200);
    }
}

public function uploadAttachment(Request $request)
{
    $request->validate([
        'session_id' => 'required|uuid|exists:chat_sessions,id',
        'sender'     => 'required|in:user,agent',
        'file'       => 'required|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:5120',
    ]);

    $session = ChatSession::findOrFail($request->session_id);

    // Store file — all metadata goes to JSON index, nothing to DB columns
    $service    = new AttachmentService();
    $attachment = $service->store($request->file('file'), $session->id);

    // Save ONE plain-text message so it appears in chat history
    // Format: [IMAGE: filename.jpg | url] or [PDF: filename.pdf | url]
    $tag     = strtoupper($attachment['type']); // IMAGE or PDF
    $msgText = "[{$tag}: {$attachment['name']} | {$attachment['url']}]";

    ChatMessage::create([
        'session_id' => $session->id,
        'sender'     => $request->sender,
        'message'    => $msgText,
    ]);

    return response()->json([
        'url'  => $attachment['url'],
        'type' => $attachment['type'],
        'name' => $attachment['name'],
    ], 201);
}

// POST /chatbot/cleanup-attachments
public function cleanupAttachments(Request $request)
{
    $service = new AttachmentService();
    $result  = $service->cleanup();

    return response()->json([
        'message' => "Cleanup complete. {$result['deleted']} file(s) deleted, {$result['kept']} kept.",
        'deleted' => $result['deleted'],
        'kept'    => $result['kept'],
    ]);
}
}