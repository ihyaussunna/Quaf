<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $action = $request->query('action');
        $userId = $request->query('user_id');

        $query = AuditLog::with('user');

        if ($action) {
            $query->where('action', $action);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $logs = $query->latest()->paginate(25)->withQueryString();
        $users = User::orderBy('name')->get();
        $actions = AuditLog::select('action')->distinct()->pluck('action');

        return view('admin.audit-logs.index', compact('logs', 'users', 'actions', 'action', 'userId'));
    }
}
