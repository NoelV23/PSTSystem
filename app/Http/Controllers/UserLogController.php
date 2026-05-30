<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = UserLog::query()->with(['user:id,name,email,role']);

        if ($request->filled('search')) {
            $raw = $request->string('search')->toString();
            $term = '%'.addcslashes($raw, '%_\\').'%';
            $query->where(function ($q) use ($term) {
                $q->where('action', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('ip_address', 'like', $term);
            });
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        $logs = $query->latest()->paginate(50)->withQueryString();

        $users = User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);

        return view('user-logs.index', [
            'logs' => $logs,
            'users' => $users,
        ]);
    }
}
