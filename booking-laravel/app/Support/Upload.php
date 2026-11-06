<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Simpan file unggahan pengguna. Driver diatur lewat UPLOAD_DRIVER:
 *
 * - local    : ke public/uploads/{folder}; kolom database berisi nama file.
 * - database : isi file disimpan di tabel uploads; kolom berisi "db:{id}/{nama}".
 *              Dipakai di Vercel, karena disk servernya hanya-baca dan tidak permanen.
 * - blob     : ke Vercel Blob (butuh BLOB_READ_WRITE_TOKEN); kolom berisi URL lengkap.
 *
 * Nilai lama tetap terbaca walau driver diganti.
 */
class Upload
{
    private const BLOB_API = 'https://vercel.com/api/blob';

    /** Simpan file, kembalikan nilai untuk kolom database. */
    public static function store(UploadedFile $file, string $folder, string $prefix): string
    {
        $name = $prefix.'_'.time().'_'.Str::random(5).'.'.$file->extension();
        $mime = $file->getMimeType() ?: 'application/octet-stream';

        return match (self::driver()) {
            'blob' => self::storeBlob($file, self::path($folder, $name), $mime),
            'database' => self::storeDatabase($file, $folder, $name, $mime),
            default => self::storeLocal($file, $folder, $name),
        };
    }

    /** URL publik file, atau null bila tidak ada. */
    public static function url(?string $value, string $folder): ?string
    {
        if (! $value) {
            return null;
        }
        if (self::isRemote($value)) {
            return $value;
        }
        if ($ref = self::databaseRef($value)) {
            return route('upload.show', $ref);
        }

        $relative = self::path(self::path('uploads', $folder), $value);

        return is_file(public_path($relative)) ? asset($relative) : null;
    }

    /** Hapus file lama (diam saja bila gagal; file yatim tidak merusak apa pun). */
    public static function delete(?string $value, string $folder): void
    {
        if (! $value) {
            return;
        }

        if ($ref = self::databaseRef($value)) {
            DB::table('uploads')->where('id', $ref['upload'])->delete();

            return;
        }

        if (self::isRemote($value)) {
            if ($token = self::token()) {
                try {
                    Http::withToken($token)->withHeaders(['x-api-version' => '11'])
                        ->post(self::BLOB_API.'/delete', ['urls' => [$value]]);
                } catch (\Throwable) {
                    // Abaikan: penghapusan file lama tidak boleh menggagalkan permintaan.
                }
            }

            return;
        }

        $path = public_path(self::path(self::path('uploads', $folder), $value));
        if (is_file($path)) {
            @unlink($path);
        }
    }

    public static function isRemote(?string $value): bool
    {
        return $value !== null && Str::startsWith($value, ['https://', 'http://']);
    }

    /** "db:12/foto.png" → ['upload' => 12, 'name' => 'foto.png'] */
    public static function databaseRef(?string $value): ?array
    {
        if ($value && preg_match('#^db:(\d+)/(.+)$#', $value, $m)) {
            return ['upload' => (int) $m[1], 'name' => $m[2]];
        }

        return null;
    }

    private static function driver(): string
    {
        return config('filesystems.upload_driver') ?: (self::token() ? 'blob' : 'local');
    }

    private static function storeLocal(UploadedFile $file, string $folder, string $name): string
    {
        $file->move(public_path(self::path('uploads', $folder)), $name);

        return $name;
    }

    private static function storeDatabase(UploadedFile $file, string $folder, string $name, string $mime): string
    {
        $id = DB::table('uploads')->insertGetId([
            'folder' => $folder,
            'name' => $name,
            'mime' => $mime,
            'size' => $file->getSize(),
            'data' => base64_encode(file_get_contents($file->getRealPath())),
            'created_at' => now(),
        ]);

        return "db:{$id}/{$name}";
    }

    private static function storeBlob(UploadedFile $file, string $pathname, string $mime): string
    {
        $response = Http::withToken((string) self::token())
            ->withHeaders([
                'x-api-version' => '11',
                'x-content-type' => $mime,
                'x-add-random-suffix' => '1',
            ])
            ->withBody(file_get_contents($file->getRealPath()), $mime)
            ->put(self::BLOB_API.'/?'.http_build_query(['pathname' => $pathname]));

        if (! $response->successful() || ! $response->json('url')) {
            throw new RuntimeException('Gagal mengunggah file ke Vercel Blob: '.$response->body());
        }

        return $response->json('url');
    }

    private static function token(): ?string
    {
        return config('filesystems.blob_token') ?: null;
    }

    private static function path(string $a, string $b): string
    {
        return trim($a.'/'.$b, '/');
    }
}
