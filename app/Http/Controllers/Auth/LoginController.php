<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    // protected $redirectTo = RouteServiceProvider::HOME;
    protected $redirectTo;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    //  public function authenticated(){


    //    if(Auth::user()->role == "1"){

    //         return redirect('/admin');

    //     }
    //     else{
    //         return redirect('/user');
    //     }

    // }

    // public function __construct()
    // {
    //     $this->middleware('guest')->except('logout');
    // }


    public function showLoginForm()
    {
        if (Auth::user()) {

            return redirect()->intended('/admin');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');


        if (Auth::attempt($credentials)) {
            $request->session()->regenerate(); // protect against session fixation

            return redirect()->intended('/admin'); // or your desired route
        }

        return back()->with('error', 'Invalid credentials.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
