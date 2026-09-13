<?php

namespace App\Http\Controllers;

use App\Models\LoginHelpSetting;
use Illuminate\Http\Request;

class LoginHelpSettingController extends Controller
{
    public function edit()
    {
        return view('settings.login-help', ['setting' => LoginHelpSetting::current()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string|max:1000',
            'message_template' => 'required|string|max:2000',
            'is_active' => 'required|boolean',
            'contact_names' => 'nullable|array|max:10',
            'contact_names.*' => 'nullable|string|max:100',
            'contact_phones' => 'nullable|array|max:10',
            'contact_phones.*' => 'nullable|string|max:30',
        ]);

        $contacts = collect($data['contact_names'] ?? [])
            ->map(fn ($name, $index) => [
                'name' => trim((string) $name),
                'phone' => LoginHelpSetting::normalizeWhatsapp($data['contact_phones'][$index] ?? ''),
            ])
            ->filter(fn ($contact) => $contact['name'] !== '' && $contact['phone'] !== '')
            ->values()->all();

        LoginHelpSetting::current()->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'message_template' => $data['message_template'],
            'is_active' => (bool) $data['is_active'],
            'contacts' => $contacts,
        ]);

        return back()->with('success', 'Pengaturan bantuan login berhasil disimpan.');
    }
}
