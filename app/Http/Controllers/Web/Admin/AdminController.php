<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\CourseActivationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'students_count' => User::where('role', 'student')->count(),
            'instructors_count' => User::where('role', 'instructor')->count(),
            'courses_count' => Course::count(),
            'codes_generated' => CourseActivationCode::count(),
            'codes_used' => CourseActivationCode::where('is_used', true)->count(),
        ];

        $instructors = User::where('role', 'instructor')->latest()->get();
        $courses = Course::with('instructor')->latest()->get();

        return view('admin.dashboard', compact('stats', 'instructors', 'courses'));
    }

    public function teachersList()
    {
        $instructors = User::where('role', 'instructor')->latest()->get();
        return view('admin.teachers', compact('instructors'));
    }

    public function createInstructor(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => 'instructor',
            'status' => 'active',
        ]);

        return redirect()->back()->with('success', 'Instructor account created successfully.');
    }

    public function generateCodes(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'quantity' => 'required|integer|min:1|max:100',
        ]);

        $course = Course::findOrFail($validated['course_id']);
        $cleanTitle = substr(preg_replace('/[^A-Za-z0-9]/', '', $course->title), 0, 4);

        for ($i = 0; $i < $validated['quantity']; $i++) {
            $code = strtoupper($cleanTitle . '-' . Str::random(4) . '-' . Str::random(4));
            
            CourseActivationCode::create([
                'course_id' => $course->id,
                'code' => $code,
                'is_used' => false,
                'created_by_user_id' => auth()->id(),
            ]);
        }

        return redirect()->back()->with('success', "Successfully generated {$validated['quantity']} activation codes.");
    }
}
