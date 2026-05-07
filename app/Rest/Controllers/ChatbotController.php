<?php

namespace App\Rest\Controllers;

use Illuminate\Http\Request;
use App\Rest\Controller as RestController;
use Illuminate\Support\Facades\Log;
use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Models\Client;

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

        // Get bot reply
        $replyText = $this->findReply(strtolower(trim($data['message'])));

        // Save bot reply
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

        // Save as user message
        ChatMessage::create([
            'session_id' => $session->id,
            'sender'     => 'user',
            'message'    => $data['issue'],
        ]);

        $replyText = $this->findReply(strtolower($data['issue']));

        $botMessage = ChatMessage::create([
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
}