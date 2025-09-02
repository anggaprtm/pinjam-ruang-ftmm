<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
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
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Mengganti field 'email' menjadi 'login' agar bisa menerima email atau nip.
     *
     * @return string
     */
    public function username()
    {
        return 'login';
    }

    /**
     * Menyesuaikan kredensial untuk login.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        $login = $request->input($this->username());

        // Cek apakah input merupakan email yang valid
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'nip';

        // Kembalikan array kredensial sesuai dengan tipe input
        return [
            $field => $login,
            'password' => $request->input('password'),
        ];
    }
}