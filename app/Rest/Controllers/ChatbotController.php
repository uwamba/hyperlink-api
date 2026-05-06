<?php

namespace App\Rest\Controllers;

use Illuminate\Http\Request;
use App\Rest\Controller as RestController;
use Illuminate\Support\Facades\Log;

class ChatbotController extends RestController
{
    private array $knowledge = [
        'services' => [
            'keywords' => ['service', 'offer', 'provide', 'do you', 'what is', 'network', 'internet', 'connectivity'],
            'response' => 'Hyperlink Network offers the following services: Internet Connectivity, Cloud Solutions, Managed Infrastructure, VPN Services, and Technical Support. Would you like to know more about any specific service?'
        ],
        'contact' => [
            'keywords' => ['contact', 'reach', 'phone', 'email', 'address', 'location', 'office', 'call'],
            'response' => 'You can reach us via: Email: info@hyperlinknetwork.com | Phone: our support line is available 24/7. You can also fill out the Contact form on our website and we will get back to you shortly.'
        ],
        'support' => [
            'keywords' => ['support', 'help', 'issue', 'problem', 'ticket', 'complaint', 'not working', 'broken', 'fix'],
            'response' => 'For technical support, you can submit a support ticket directly on our website. Our technical team is available 24/7. You can also email support@hyperlinknetwork.com or call our helpline.'
        ],
        'pricing' => [
            'keywords' => ['price', 'cost', 'pricing', 'plan', 'package', 'how much', 'tariff', 'rate', 'subscription'],
            'response' => 'Our pricing depends on the service and package you choose. We have flexible plans for individuals and businesses. Please contact our sales team at sales@hyperlinknetwork.com or fill the contact form for a custom quote.'
        ],
        'about' => [
            'keywords' => ['about', 'who are', 'company', 'hyperlink', 'history', 'founded', 'team', 'mission'],
            'response' => 'Hyperlink Network is a leading telecommunications and network solutions provider. We are committed to delivering reliable, fast, and secure connectivity solutions for businesses and individuals.'
        ],
        'installation' => [
            'keywords' => ['install', 'setup', 'connect', 'connection', 'technician', 'visit', 'deploy'],
            'response' => 'Our team of certified technicians handles all installations. Once you subscribe to a plan, we schedule a site visit at your convenience. Installation typically takes 1-2 business days.'
        ],
        'greeting' => [
            'keywords' => ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening', 'howdy'],
            'response' => 'Hello! Welcome to Hyperlink Network. How can I help you today? You can ask me about our services, pricing, support, or how to contact us.'
        ],
        'thanks' => [
            'keywords' => ['thank', 'thanks', 'appreciate', 'helpful', 'great', 'awesome', 'perfect'],
            'response' => 'You are welcome! Is there anything else I can help you with? Feel free to ask anytime.'
        ],
        'bye' => [
            'keywords' => ['bye', 'goodbye', 'see you', 'later', 'done', 'nothing else', 'that is all'],
            'response' => 'Thank you for contacting Hyperlink Network! Have a great day. Feel free to reach out anytime.'
        ],
    ];

    public function reply(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $message = strtolower(trim($request->input('message')));

        Log::info('Chatbot message received', ['message' => $message]);

        $reply = $this->findReply($message);

        return response()->json([
            'reply' => $reply,
            'status' => 'success',
        ], 200);
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

        return "I'm not sure I understand your question. You can ask me about our services, pricing, support, or contact details. For direct assistance, please email us at info@hyperlinknetwork.com or call our support line.";
    }
}
