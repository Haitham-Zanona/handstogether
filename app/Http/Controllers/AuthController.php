<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function adminLogin()
    {
        return view('auth.login', ['role' => 'admin', 'title' => 'بوابة الإداريين']);
    }

    public function teacherLogin()
    {
        return view('auth.teacher-login');
    }

    public function authenticateTeacher(Request $request)
    {
        $data = $request->validate([
            'national_id' => 'required|string',
            'password'    => 'required|string',
        ]);

        $user = \App\Models\User::where('national_id', $data['national_id'])
            ->where('role', 'teacher')
            ->where('is_active', true)
            ->first();

        if ($user && \Illuminate\Support\Facades\Hash::check($data['password'], $user->password)) {
            Auth::login($user);
            $request->session()->regenerate();
            return redirect()->route('teacher.dashboard');
        }

        return back()->withErrors([
            'national_id' => 'رقم الهوية أو كلمة المرور غير صحيحة',
        ])->onlyInput('national_id');
    }

    public function parentLogin()
    {
        return view('auth.login', ['role' => 'parent', 'title' => 'بوابة أولياء الأمور']);
    }

    public function studentLogin()
    {
        return view('auth.login', ['role' => 'student', 'title' => 'بوابة الطلبة']);
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
            'role'     => 'required|in:admin,teacher,parent,student',
        ]);

        if (Auth::attempt([
            'email'    => $credentials['email'],
            'password' => $credentials['password'],
            'role'     => $credentials['role'],
        ])) {
            $request->session()->regenerate();
            return redirect()->intended(auth()->user()->getDashboardRoute());
        }

        return back()->withErrors([
            'email' => 'البيانات المدخلة غير صحيحة.',
        ])->onlyInput('email');
    }
}
