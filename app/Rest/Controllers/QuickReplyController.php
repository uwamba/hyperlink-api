<?php

namespace App\Rest\Controllers;

use Illuminate\Http\Request;
use App\Rest\Controller as RestController;
use Illuminate\Support\Str;

class QuickReplyController extends RestController
{
    private string $filePath;

    public function __construct()
    {
        $this->filePath = storage_path('app/quick_replies.json');
    }

    // ── Read file ──────────────────────────────────────────────────
    private function readAll(): array
    {
        if (!file_exists($this->filePath)) {
            // Copy default file from resources on first run
            $default = resource_path('data/quick_replies.json');
            if (file_exists($default)) {
                copy($default, $this->filePath);
            } else {
                file_put_contents($this->filePath, '[]');
            }
        }
        return json_decode(file_get_contents($this->filePath), true) ?? [];
    }

    // ── Write file ─────────────────────────────────────────────────
    private function writeAll(array $data): void
    {
        file_put_contents(
            $this->filePath,
            json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    // GET /chatbot/quick-replies
    public function listReplies()
    {
        return response()->json(['quick_replies' => $this->readAll()]);
    }

    // POST /chatbot/quick-replies
    public function storeReply(Request $request)
    {
        $data = $request->validate([
            'label'           => 'required|string|max:100',
            'text'            => 'required|string|max:2000',
            'category'        => 'nullable|string|max:50',
            'attachment_url'  => 'nullable|string',
            'attachment_name' => 'nullable|string|max:255',
            'attachment_type' => 'nullable|in:image,pdf',
        ]);

        $all = $this->readAll();

        $newReply = [
            'id'              => (string) Str::uuid(),
            'label'           => $data['label'],
            'category'        => $data['category'] ?? 'General',
            'text'            => $data['text'],
            'attachment_url'  => $data['attachment_url'] ?? null,
            'attachment_name' => $data['attachment_name'] ?? null,
            'attachment_type' => $data['attachment_type'] ?? null,
        ];

        $all[] = $newReply;
        $this->writeAll($all);

        return response()->json(['quick_reply' => $newReply], 201);
    }

    // PUT /chatbot/quick-replies/{id}
    public function updateReply(Request $request, string $id)
    {
        $data = $request->validate([
            'label'           => 'sometimes|string|max:100',
            'text'            => 'sometimes|string|max:2000',
            'category'        => 'nullable|string|max:50',
            'attachment_url'  => 'nullable|string',
            'attachment_name' => 'nullable|string|max:255',
            'attachment_type' => 'nullable|in:image,pdf',
        ]);

        $all = $this->readAll();
        $found = false;

        $all = array_map(function ($reply) use ($id, $data, &$found) {
            if ($reply['id'] === $id) {
                $found = true;
                return array_merge($reply, $data);
            }
            return $reply;
        }, $all);

        if (!$found) {
            return response()->json(['message' => 'Quick reply not found.'], 404);
        }

        $this->writeAll($all);
        $updated = collect($all)->firstWhere('id', $id);

        return response()->json(['quick_reply' => $updated]);
    }

    // DELETE /chatbot/quick-replies/{id}
    public function deleteReply(string $id)
    {
        $all = $this->readAll();
        $filtered = array_filter($all, fn($r) => $r['id'] !== $id);

        if (count($filtered) === count($all)) {
            return response()->json(['message' => 'Quick reply not found.'], 404);
        }

        $this->writeAll($filtered);
        return response()->json(['message' => 'Deleted successfully.']);
    }
    
}