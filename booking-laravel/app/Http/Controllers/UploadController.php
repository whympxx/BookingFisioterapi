<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/** Tampilkan file unggahan yang disimpan di database (UPLOAD_DRIVER=database). */
class UploadController extends Controller
{
    public function show(Request $request, int $upload, string $name): Response
    {
        $file = DB::table('uploads')->where('id', $upload)->where('name', $name)->first();
        abort_unless($file, 404);

        // Bukti transfer hanya untuk admin dan pemilik booking.
        $private = $file->folder === 'bukti';
        if ($private) {
            $user = $request->user();
            abort_unless($user && ($user->role === 'admin' || Payment::where('proof', "db:{$file->id}/{$file->name}")
                ->whereHas('booking', fn ($q) => $q->where('user_id', $user->id))->exists()), 403);
        }

        return response(base64_decode($file->data), 200, [
            'Content-Type' => $file->mime,
            'Content-Disposition' => 'inline; filename="'.$file->name.'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => $private ? 'private, no-store' : 'public, max-age=31536000, immutable',
        ]);
    }
}
