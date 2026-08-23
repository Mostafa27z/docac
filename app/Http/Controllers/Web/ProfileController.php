<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\BunnyStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    protected BunnyStorageService $bunnyStorage;

    public function __construct(BunnyStorageService $bunnyStorage)
    {
        $this->bunnyStorage = $bunnyStorage;
    }

    public function edit()
    {
        $user = auth()->user();
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20|unique:users,phone,' . $user->id,
            'avatar_file' => 'nullable|image|max:10240',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ];

        $file = $request->file('avatar_file') ?? $request->file('avatar');
        if ($file) {
            $avatarPath = $this->bunnyStorage->uploadFile($file, 'avatars');
            if ($avatarPath) {
                $updateData['avatar'] = $avatarPath;
            }
        }

        $user->update($updateData);

        return redirect()->back()->with('success', 'تم تحديث البيانات الشخصية بنجاح.');
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return redirect()->back()->withErrors(['current_password' => 'كلمة المرور الحالية غير صحيحة.'])->with('error', 'كلمة المرور الحالية غير صحيحة.');
        }

        $user->update([
            'password' => Hash::make($validated['password'])
        ]);

        return redirect()->back()->with('success', 'تم تغيير كلمة المرور بنجاح.');
    }
}
