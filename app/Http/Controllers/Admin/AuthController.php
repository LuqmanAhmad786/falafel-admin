<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminPassResetToken;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Redirect;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $fieldType = filter_var($request->input('email'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $credentials = [$fieldType => $request->input('email'), 'password' => $request->input('password')];

        if(Auth::guard('admin')->attempt(array($fieldType => $request->input('email'), 'password' => $request->input('password')))){

        //if (Auth::guard('admin')->attempt($credentials)) {

            $restaurant = Restaurant::get();
            if (Auth::guard('admin')->user()->type == 1) {
                Session::put('my_restaurant', $restaurant[0]->id);
            } else if (Auth::guard('admin')->user()->type == 2) {
                Session::put('my_restaurant', Auth::guard('admin')->user()->assigned_location);
            }

            return redirect()->intended(route('dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();

        return redirect('login');
    }

    public function appRedirect(){
        return Redirect::to('http://onelink.to/farmersfresh');
    }

    public function forgetPassword(Request $request){
        $token = uniqid();
        $valid_till = strtotime('+1 day', time());
        $data = [
            'token' => $token,
            'valid_till' => $valid_till
        ];
        AdminPassResetToken::create($data);
        $template = view('email-templates.admin-forgot-password', [
            'token' => $token,
            'valid_till' => $valid_till
        ])->render();

        sendEmail($template, 'yashdeep.qualwebs@gmail.com', 'Falafel Reset Password');
        $request->session()->flash('message', 'Password reset link has been sent successfully.');
        Session::flash('alert-class', 'alert-success');
        return redirect()->back();
    }

    public function resetPassword(Request $request,$token){
        $dbToken = AdminPassResetToken::where('token',$token)->first();
        if($dbToken){
            if($dbToken->valid_till > time()){
                return view('auth.reset-password',compact('dbToken'));
            } else{
                $request->session()->flash('message', 'Password reset token is expired.');
                Session::flash('alert-class', 'alert-danger');
                return redirect()->to('/login');
            }
        } else{
            $request->session()->flash('message', 'Password reset token is invalid.');
            Session::flash('alert-class', 'alert-danger');
            return redirect()->to('/login');
        }
    }

    public function resetAdminPassword(Request $request){
        $validator = Validator::make($request->all(), [
            'token' => 'required|exists:admin_pass_reset_tokens,token',
            'password' => 'min:6|required_with:confirm_password|same:confirm_password',
            'confirm_password' => 'min:6'
        ]);

        if ($validator->fails()) {
            $request->session()->flash('message', $validator->errors()->first());
            Session::flash('alert-class', 'alert-danger');
            return redirect()->back();
        }

        Admin::where('type',1)->update([
            'password' => Hash::make($request->input('password'))
        ]);
        AdminPassResetToken::where('token',$request->input('token'))->delete();
        $request->session()->flash('message', 'Password update successfully.');
        Session::flash('alert-class', 'alert-success');
        return redirect()->to('/login');
    }
}
