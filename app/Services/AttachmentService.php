<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentService
{
    private string $disk = 'public';
    private string $folder = 'chat-attachments';
    private int $maxAgeDays = 30; // auto-clean files older than 30 days

    // ── Store a file and return its public URL ────────────────────────
    public function store(UploadedFile $file, string $sessionId): array
    {
        $this->ensureStorageLinked();

        $ext      = $file->getClientOriginalExtension();
        $safeName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $filename = date('Ymd_His') . '_' . $sessionId . '_' . $safeName . '.' . $ext;

        $path = $file->storeAs($this->folder, $filename, $this->disk);
        $url  = Storage::disk($this->disk)->url($path);
        $type = $this->resolveType($file->getMimeType());

        // Save metadata to index file
        $this->appendToIndex([
            'filename'   => $filename,
            'path'       => $path,
            'url'        => $url,
            'type'       => $type,
            'name'       => $file->getClientOriginalName(),
            'size'       => $file->getSize(),
            'session_id' => $sessionId,
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

    // ── Auto cleanup files older than $maxAgeDays ─────────────────────
    public function cleanup(): array
    {
        $indexPath = storage_path('app/chat_attachments_index.json');
        if (!file_exists($indexPath)) return ['deleted' => 0, 'kept' => 0];

        $index     = json_decode(file_get_contents($indexPath), true) ?? [];
        $cutoff    = now()->subDays($this->maxAgeDays);
        $kept      = [];
        $deleted   = 0;

        foreach ($index as $entry) {
            $createdAt = \Carbon\Carbon::parse($entry['created_at'] ?? now());
            if ($createdAt->lt($cutoff)) {
                // Delete the actual file
                if (Storage::disk($this->disk)->exists($entry['path'] ?? '')) {
                    Storage::disk($this->disk)->delete($entry['path']);
                }
                $deleted++;
            } else {
                $kept[] = $entry;
            }
        }

        file_put_contents($indexPath, json_encode(array_values($kept), JSON_PRETTY_PRINT));

        return ['deleted' => $deleted, 'kept' => count($kept)];
    }

    // ── Ensure storage is linked ──────────────────────────────────────
    public function ensureStorageLinked(): void
    {
        $publicPath = public_path('storage');
        $storagePath = storage_path('app/public');

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