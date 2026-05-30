<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PaymentProofService
{
    private string $disk   = 'public';
    private string $folder = 'payment-proofs';
    private string $index  = 'payment_proofs.json';

    // ── Store proof file and index it by payment_id ───────────────────
    public function store(UploadedFile $file, string $paymentId): array
    {
        $this->ensureStorageLinked();

        $ext      = $file->getClientOriginalExtension();
        $safeName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $filename = date('Ymd_His') . '_proof_' . $paymentId . '_' . $safeName . '.' . $ext;

        $path = $file->storeAs($this->folder, $filename, $this->disk);
        $url  = Storage::disk($this->disk)->url($path);
        $type = str_starts_with($file->getMimeType() ?? '', 'image/') ? 'image' : 'pdf';

        $entry = [
            'payment_id' => $paymentId,
            'filename'   => $filename,
            'path'       => $path,
            'url'        => $url,
            'type'       => $type,
            'name'       => $file->getClientOriginalName(),
            'size'       => $file->getSize(),
            'created_at' => now()->toISOString(),
        ];

        $this->appendToIndex($entry);

        return $entry;
    }

    // ── Get proof entry by payment_id ─────────────────────────────────
    public function getByPaymentId(string $paymentId): ?array
    {
        $index = $this->readIndex();
        foreach (array_reverse($index) as $entry) {
            if ($entry['payment_id'] === $paymentId) {
                return $entry;
            }
        }
        return null;
    }

    // ── Get proofs for multiple payment IDs at once ───────────────────
    public function getByPaymentIds(array $paymentIds): array
    {
        $index  = $this->readIndex();
        $result = [];
        foreach ($index as $entry) {
            if (in_array($entry['payment_id'], $paymentIds)) {
                // Keep most recent per payment_id
                $result[$entry['payment_id']] = $entry;
            }
        }
        return $result;
    }

    // ── Read index ────────────────────────────────────────────────────
    private function readIndex(): array
    {
        $path = storage_path('app/' . $this->index);
        if (!file_exists($path)) return [];
        return json_decode(file_get_contents($path), true) ?? [];
    }

    // ── Append entry to index ─────────────────────────────────────────
    private function appendToIndex(array $entry): void
    {
        $path  = storage_path('app/' . $this->index);
        $index = $this->readIndex();
        $index[] = $entry;
        file_put_contents($path, json_encode($index, JSON_PRETTY_PRINT));
    }

    // ── Ensure storage symlink exists ─────────────────────────────────
    private function ensureStorageLinked(): void
    {
        if (!file_exists(public_path('storage'))) {
            \Artisan::call('storage:link');
        }
    }
}