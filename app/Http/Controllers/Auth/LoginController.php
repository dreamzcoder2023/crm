<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Models\User;
use App\Mail\forgetpassword;
use Illuminate\Support\Facades\Mail;

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
     *
     */
    // public function __construct()
    // {
    //     $this->middleware('guest')->except('logout');
    // }
    // public function login(Request $request)
    // {

    //     $credentials = $request->only('phone', 'password');
    //     if (Auth::attempt($credentials)) {
    //         return redirect()->intended('dashboard')
    //                     ->with('msg','You have Successfully loggedin');
    //     }

    //     return redirect("login")->with('msg','Oppes! You have entered invalid credentials');
    // }
    public function username()
    {
        return 'phone';
    }
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->flush();

        $request->session()->regenerate();

        return redirect('/welcome');
    }
    public function forget_password(Request $request){
      return view('auth.passwords.forget');
    }
    public function checkmail(Request $request){

      $user = User::where(['email' => $request->email,'active_status' => 1, 'delete_status' => 0])->first();
      if(empty($user)){
        $result = true;
      }
      else{
        $result = false;
      }
      return response()->json(['result' => $result,'user' =>$user]);
    }
    public function send_mail(Request $request){
      $user = User::where(['email'=> $request->email,'active_status' => 1, 'delete_status' => 0])->first();
      $encid = encrypt($user->id);
        $resetLink = env('APP_URL') . '/password-reset/'.$encid;
       //  dd($resetLink);
        // Send email using Laravel's mailing system
        $data = [
            'user' => $user,
            'resetLink' => $resetLink,
        ];
        // dd(config('mail.username'));
        Mail::to($user->email)->send(new forgetpassword($data));

        return redirect()->route('login')->with('success', 'Check your mail for password reset instructions.');
    }
    public function password_reset($id){
   $id = decrypt($id);
   
      return view('auth.passwords.confirm',['id' => $id]);
    }

}
