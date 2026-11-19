<?php

namespace App\Http\Controllers;

use App\Models\CashTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class CashTransactionProofController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, CashTransaction $transaction): Response
    {
        Gate::authorize('proof', $transaction);

        if (! $transaction->proof_file) {
            abort(404);
        }

        $disk = null;
        if (Storage::disk('local')->exists($transaction->proof_file)) {
            $disk = Storage::disk('local');
        } elseif (Storage::disk('public')->exists($transaction->proof_file)) {
            $disk = Storage::disk('public');
        } else {
            abort(404);
        }

        $mimeType = $disk->mimeType($transaction->proof_file) ?: 'application/octet-stream';

        $headers = [
            'Content-Type' => $mimeType,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'no-store, private',
        ];

        if ($mimeType === 'application/pdf' || str_ends_with(strtolower($transaction->proof_file), '.pdf')) {
            return $disk->download($transaction->proof_file, basename($transaction->proof_file), $headers);
        }

        return $disk->response($transaction->proof_file, null, $headers);
    }
}
