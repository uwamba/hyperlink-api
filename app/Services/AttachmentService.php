<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentService
{
    private string $disk = 'public';
    private string $folder = 'chat-attachments';
    private int $maxAgeDays = 30;

    // ── Store a file and return its public URL ────────────────────────
    // $source: 'session' for chat messages, 'quick-reply' for quick reply definitions
    public function store(UploadedFile $file, string $sessionId, string $source = 'session'): array
    {
        $this->ensureStorageLinked();

        $ext      = $file->getClientOriginalExtension();
        $safeName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $filename = date('Ymd_His') . '_' . $sessionId . '_' . $safeName . '.' . $ext;

        $path = $file->storeAs($this->folder, $filename, $this->disk);
        $url  = Storage::disk($this->disk)->url($path);
        $type = $this->resolveType($file->getMimeType());

        // Save metadata — mark quick-reply attachments so cleanup skips them
        $this->appendToIndex([
            'filename'   => $filename,
            'path'       => $path,
            'url'        => $url,
            'type'       => $type,
            'name'       => $file->getClientOriginalName(),
            'size'       => $file->getSize(),
            'session_id' => $sessionId,
            'source'     => $source, // 'session' or 'quick-reply'
            'created_at' => now()->toISOString(),
        ]);

        return [
            'url'  => $url,
            'type' => $type,
            'name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'path' => $path,
        ];
    }

    // ── Resolve file type ─────────────────────────────────────────────
    private function resolveType(?string $mime): string
    {
        if (!$mime) return 'file';
        if (str_starts_with($mime, 'image/')) return 'image';
        if ($mime === 'application/pdf') return 'pdf';
        return 'file';
    }

    // ── Append entry to JSON index ────────────────────────────────────
    private function appendToIndex(array $entry): void
    {
        $indexPath = storage_path('app/chat_attachments_index.json');
        $index = file_exists($indexPath)
            ? (json_decode(file_get_contents($indexPath), true) ?? [])
            : [];
        $index[] = $entry;
        file_put_contents($indexPath, json_encode($index, JSON_PRETTY_PRINT));
    }

    // ── Auto cleanup — skips quick-reply attachments ──────────────────
    public function cleanup(): array
    {
        $indexPath = storage_path('app/chat_attachments_index.json');
        if (!file_exists($indexPath)) return ['deleted' => 0, 'kept' => 0, 'protected' => 0];

        $index     = json_decode(file_get_contents($indexPath), true) ?? [];
        $cutoff    = now()->subDays($this->maxAgeDays);
        $kept      = [];
        $deleted   = 0;
        $protected = 0;

        foreach ($index as $entry) {
            // Never delete quick-reply attachments — they are permanent
            if (($entry['source'] ?? 'session') === 'quick-reply') {
                $kept[] = $entry;
                $protected++;
                continue;
            }

            $createdAt = \Carbon\Carbon::parse($entry['created_at'] ?? now());
            if ($createdAt->lt($cutoff)) {
                if (Storage::disk($this->disk)->exists($entry['path'] ?? '')) {
                    Storage::disk($this->disk)->delete($entry['path']);
                }
                $deleted++;
            } else {
                $kept[] = $entry;
            }
        }

        file_put_contents($indexPath, json_encode(array_values($kept), JSON_PRETTY_PRINT));

        return ['deleted' => $deleted, 'kept' => count($kept), 'protected' => $protected];
    }

    // ── Ensure storage is linked ──────────────────────────────────────
    public function ensureStorageLinked(): void
    {
        $publicPath = public_path('storage');

        if (!file_exists($publicPath)) {
            \Artisan::call('storage:link');
            \Log::info('Storage link created automatically by AttachmentService.');
        }
    }

    // ── List all attachments (for admin) ──────────────────────────────
    public function listAll(): array
    {
        $indexPath = storage_path('app/chat_attachments_index.json');
        if (!file_exists($indexPath)) return [];
        return json_decode(file_get_contents($indexPath), true) ?? [];
    }
}