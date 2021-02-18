<?php

namespace App\Http\Controllers;

use App\SlotGameAuth;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class SessionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'destroy']);
    }

    public function create()
    {
        return view('sessions.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            //'email' => 'required|email',
            'username'=> 'required|alpha_num',
            'password' => 'required|min:6',
            //'g-recaptcha-response' => 'required|captcha',
        ]);

        //db 에 등록된 암호 조회
        $user = User::where(['username'=> $request->username, 'admin_yn' => 'N'])->first();


        if (is_null($user)){
            flash()->error(
                trans('auth.sessions.error_incorrect_credentials')
            );
            return redirect('auth/login');
        }

        if($user->bank == null || $user->withdrawal_password == null){
            $stepRequest = array(
                'id' => $user->username,
                'key' => md5(uniqid(rand(), true)),
                'stepKey' => Crypt::encryptString($request->input('password'))
            );

            if($user->bank == null){

                //Step2로 request 이동
                return redirect()->route('users.bank')->with('stepParams',$stepRequest);
            }
            if($user->withdrawal_password == null){

                return redirect()->route('users.pincode')->with('stepParams',$stepRequest);
            }
            exit;
        }





        if (!auth()->attempt($request->only('username', 'password'), $request->has('remember'))) {

            return $this->respondError(trans('auth.sessions.error_incorrect_credentials'));
        }

        //게임 계정이 없다면 생성 해 준다.
        //게임DB에 해당 회원 계정이 없으면 게임DB 에 등록
        if ($user->account_id === null){
            $session_id = Str::random(80) . '' .  $user->id;
            //임시 해당 테이블 마지막 아이디 조회
            $result_id = SlotGameAuth::insertGetId([
                'CertificationKey' => $session_id,
                'UpdateDate' => Carbon::now(),
            ]);
            $user->account_id = $result_id;
            $user->save();
        }

        //멀티 로그인 방지
        $password = $request->password;
        Auth::logoutOtherDevices($password);



        return redirect()->intended('/');
    }

    public function destroy()
    {
        auth()->logout();
        return redirect('/');
    }

    /**
     * Make an error response.
     *
     * @param string $message
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function respondError($message)
    {
        flash()->error($message);
        return back()->withInput();
    }
}
