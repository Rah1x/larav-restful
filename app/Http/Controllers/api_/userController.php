<?php

namespace App\Http\Controllers\api_;

#/ Core
use Illuminate\Http\Request;
use Illuminate\Session;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Validator;

#/ Helpers
use r, f, checkAttempts, encHelper;

#/ Models
use App\User;

class userController extends Controller
{
    function __construct(Request $request)
    {
        parent::__construct();

        $this->req = $request;

        //$this->SESS = \Request::session()->all(); //cant do it from 5.3 onwards
        //die('x');
    }


    private function errors()
    {
        $_SESS = r::sess();
        //@r::var_dumpx($_SESS);

        $msg = '';
        if (@array_key_exists($this->S_PREFIX."ADMIN_MSG_GLOBAL", $_SESS))
        {
            if ($_SESS[$this->S_PREFIX."ADMIN_MSG_GLOBAL"][0]==false){ $msg = ' - '.$_SESS[$this->S_PREFIX.'ADMIN_MSG_GLOBAL'][1]; }
            if ($_SESS[$this->S_PREFIX."ADMIN_MSG_GLOBAL"][0]==true) { $msg = ' - '.$_SESS[$this->S_PREFIX.'ADMIN_MSG_GLOBAL'][1]; }
            r::flush_sess($this->S_PREFIX."ADMIN_MSG_GLOBAL");
        }

        return @f::format_str(strip_tags($msg));
    }


    private function save_user($POST)
    {
        #/ Check Attempts
        #/*
        if(checkAttempts::check_attempts(3, $this->S_PREFIX.'ADMIN_MSG_GLOBAL')==false){
        checkAttempts::update_attempt_counts();
        return 1;
        }
        #*/


        #/ Validate Fields
        $validator = Validator::make($this->req->all(), [
            'email_add' => 'required|email|max:100',
            'pass_w' => 'required|min:8|max:20',
        ]
        )->setAttributeNames([
        'email_add'=>'Email Address',
        'pass_w'=>'Password'
        ]);


        if($validator->errors()->count()>0)
        {
            r::global_msg($this->S_PREFIX."ADMIN_MSG_GLOBAL", $validator->errors()->messages());
            checkAttempts::update_attempt_counts();
            return $validator->errors()->count();
        }
        else
        {
            ##/ Check if User Exists
            $email_add = $POST['email_add'];
            $pass_w = encHelper::enc($email_add, $POST['pass_w']);
            //r::var_dumpx($email_add, $pass_w);

            $chk_email_add = User::where('email_add', $email_add) //."1 AND 1=1 -- "
            ->where('pass_w', $pass_w)
            //->toSql();
            ->first();

            //r::last_query();
            //r::var_dumpx($chk_email_add);

            if(!is_null($chk_email_add))
            {
                r::global_msg($this->S_PREFIX."ADMIN_MSG_GLOBAL", 'This user is already registered!');
                checkAttempts::update_attempt_counts();
                return 1;
            }//end if..
            #-


            #/ Save User
            $new_user = User::create([
            'email_add'=>$email_add,
            'pass_w'=>$pass_w,
            ]);

            #/ Return
            if(!empty($new_user)){
            checkAttempts::reset_attempt_counts();
            return 0;
            }

            //r::var_dumpx($new_user);

        }//end else..

        return 1;

    }//end func....



    public function signin_user($POST)
    {
        #/ Check Attempts
        #/*
        if(checkAttempts::check_attempts(3, $this->S_PREFIX.'ADMIN_MSG_GLOBAL')==false){
        checkAttempts::update_attempt_counts();
        return 1;
        }
        #*/


        #/ Validate Fields
        $validator = Validator::make($this->req->all(), [
            'email_add' => 'required|email|max:100',
            'pass_w' => 'required|min:8|max:20',
        ]
        )->setAttributeNames([
        'email_add'=>'Email Address',
        'pass_w'=>'Password'
        ]);


        if($validator->errors()->count()>0)
        {
            r::global_msg($this->S_PREFIX."ADMIN_MSG_GLOBAL", $validator->errors()->messages());
            checkAttempts::update_attempt_counts();
            return $validator->errors()->count();
        }
        else
        {
            ##/ Check if User Exists
            $email_add = $POST['email_add'];
            $pass_w = encHelper::enc($email_add, $POST['pass_w']);
            //r::var_dumpx($email_add, $pass_w);

            $chk_email_add = User::where('email_add', $email_add)
            ->where('pass_w', $pass_w)
            ->first();

            //r::last_query();
            //r::var_dumpx($chk_email_add);

            if(is_null($chk_email_add))
            {
                r::global_msg($this->S_PREFIX."ADMIN_MSG_GLOBAL", 'Unable to authenticate the given credentials!');
                checkAttempts::update_attempt_counts();
                return 1;
            }//end if..
            #-
        }


        return 1;
    }//end func....

    ///////////////////////////////////////////////////////////////// PUBLIC Methods below

    public function create(Request $req)
    {
        $POST = $this->POST;
        //r::var_dumpx($req->input('email_add'), $POST);

        if(isset($POST['email_add']))
        {
            $tot_errors = (int)@$this->save_user($POST);
            if($tot_errors<=0)
            {
                return response()->json([
                'msg'=>'User successfully created'
                ], 200);
            }
        }

        return response()->json([
        'msg'=>'Error 400'.$this->errors()
        ], 400);

    }//end func...


    public function signin(Request $req)
    {
        $POST = $this->POST;
        //r::var_dumpx($req->input('email_add'), $POST);

        if(isset($POST['email_add']))
        {
            $tot_errors = (int)@$this->signin_user($POST);
            if($tot_errors<=0)
            {
            }
        }

        return response()->json([
        'msg'=>'Error 400'.$this->errors()
        ], 400);
    }//end func...


    public function get(Request $req, int $user_id)
    {
        $GET = $this->GET;
        r::var_dumpx($GET, $user_id);


        return response()->json([
        'msg'=>'Error 400'.$this->errors()
        ], 400);
    }//end func...
}