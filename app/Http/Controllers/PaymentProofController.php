<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class PaymentProofController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Payment $payment): Response
    {
        Gate::authorize('proof', $payment);

        if (! $payment->proof_file) {
            abort(404);
        }

        $disk = null;
        if (Storage::disk('local')->exists($payment->proof_file)) {
            $disk = Storage::disk('local');
        } elseif (Storage::disk('public')->exists($payment->proof_file)) {
            $disk = Storage::disk('public');
        } else {
            abort(404);
        }

        $mimeType = $disk->mimeType($payment->proof_file) ?: 'application/octet-stream';

        $headers = [
            'Content-Type' => $mimeType,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'no-store, private',
        ];

        if ($mimeType === 'application/pdf' || str_ends_with(strtolower($payment->proof_file), '.pdf')) {
            return $disk->download($payment->proof_file, basename($payment->proof_file), $headers);
        }

        return $disk->response($payment->proof_file, null, $headers);
    }
}
