<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReminderStatus;
use App\Http\Controllers\Controller;
use App\Models\TrackedItem;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::withCount('trackedItems')
            ->orderByDesc('created_at')
            ->paginate(15);

        $totalUsers = User::count();
        $totalItems = TrackedItem::count();

        $stats = [
            'total' => $totalUsers,
            'new_7d' => User::where('created_at', '>=', now()->subDays(7))->count(),
            'new_30d' => User::where('created_at', '>=', now()->subDays(30))->count(),
            'active_today' => User::where('last_login_at', '>=', now()->today())->count(),
            'active_7d' => User::where('last_login_at', '>=', now()->subDays(7))->count(),
            'active_30d' => User::where('last_login_at', '>=', now()->subDays(30))->count(),
            'telegram_linked' => User::whereNotNull('telegram_user_id')->count(),
            'telegram_missing' => User::whereNull('telegram_user_id')->count(),
            'total_items' => $totalItems,
            'avg_items_per_user' => $totalUsers > 0 ? round($totalItems / $totalUsers, 1) : 0,
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    /**
     * Toggle user active status.
     */
    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        // Prevent self-deactivation
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak bisa menonaktifkan akun sendiri.');
        }

        if ($user->is_active) {
            // Deactivate: cancel pending reminders + invalidate sessions
            $user->update(['is_active' => false]);

            $user->trackedItems()
                ->where('reminder_status', ReminderStatus::Pending)
                ->update(['reminder_status' => ReminderStatus::Failed]);

            DB::table('sessions')
                ->where('user_id', $user->id)
                ->delete();

            return back()->with('success', 'Akun '.$user->name.' dinonaktifkan.');
        }

        // Reactivate
        $user->update(['is_active' => true]);

        return back()->with('success', 'Akun '.$user->name.' diaktifkan kembali.');
    }

    /**
     * Update user role.
     */
    public function updateRole(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak bisa mengubah role sendiri.');
        }

        $validated = $request->validate([
            'role' => ['required', 'string', 'in:admin,user'],
        ]);

        $user->update(['role' => $validated['role']]);

        return back()->with('success', 'Role '.$user->name.' diubah menjadi '.ucfirst($validated['role']).'.');
    }
}
