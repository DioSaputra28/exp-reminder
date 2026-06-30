<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Show the profile edit form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile (Telegram User ID).
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'telegram_user_id' => ['nullable', 'string', 'max:20', 'regex:/^\d*$/'],
        ]);

        $user = $request->user();

        $user->update([
            'telegram_user_id' => $validated['telegram_user_id'] ?: null,
        ]);

        return redirect()->route('profile.edit')
            ->with('success', 'Profil berhasil diperbarui.');
    }
}
