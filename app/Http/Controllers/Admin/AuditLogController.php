<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->latest('id');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('event', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($qu) use ($search) {
                        $qu->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($event = $request->input('event')) {
            $query->where('event', $event);
        }

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($startDate = $request->input('start_date')) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate = $request->input('end_date')) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $logs = $query->paginate(20)->withQueryString();

        $distinctEvents = AuditLog::distinct()->pluck('event')->sort()->values();
        $users = User::whereIn('role', ['admin', 'bendahara_kelas'])->orderBy('name')->get();

        return view('admin.audit-logs.index', compact('logs', 'distinctEvents', 'users'));
    }
}
