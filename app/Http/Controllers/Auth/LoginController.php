<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Socialite;
use App\User;
use Auth;
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

    public function credentials(Request $request){
        // Cho phép cả admin và sub_admin đăng nhập
        return ['email'=>$request->email,'password'=>$request->password,'status'=>'active'];
    }

    /**
     * Get the post-login redirect path.
     */
    public function redirectTo()
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            // Chỉ cho phép admin và sub_admin đăng nhập qua route này
            if ($user->role === 'admin') {
                return route('admin');
            } elseif ($user->role === 'sub_admin') {
                return route('sub-admin.dashboard');
            } else {
                // User thường không được đăng nhập qua /login, logout và redirect
                Auth::logout();
                request()->session()->flash('error', 'User thường vui lòng đăng nhập qua trang user');
                return redirect()->route('login.form');
            }
        }
        
        return $this->redirectTo;
    }

    /**
     * The user has been authenticated.
     */
    protected function authenticated(Request $request, $user)
    {
        // Kiểm tra role trước khi cho đăng nhập
        if (!in_array($user->role, ['admin', 'sub_admin'])) {
            Auth::logout();
            $request->session()->flash('error', 'Trang này chỉ dành cho Admin và Sub Admin');
            return redirect()->route('login.form');
        }

        $request->session()->flash('success', 'Đăng nhập thành công');
        
        return redirect()->intended($this->redirectTo());
    }
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function redirect($provider)
    {
        // dd($provider);
     return Socialite::driver($provider)->redirect();
    }
 
    public function Callback($provider)
    {
        $userSocial =   Socialite::driver($provider)->stateless()->user();
        $users      =   User::where(['email' => $userSocial->getEmail()])->first();
        // dd($users);
        if($users){
            Auth::login($users);
            return redirect('/')->with('success','You are login from '.$provider);
        }else{
            $user = User::create([
                'name'          => $userSocial->getName(),
                'email'         => $userSocial->getEmail(),
                'image'         => $userSocial->getAvatar(),
                'provider_id'   => $userSocial->getId(),
                'provider'      => $provider,
            ]);
         return redirect()->route('home');
        }
    }
}
