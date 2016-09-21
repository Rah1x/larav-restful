<?php

namespace App\Http\Controllers\api_;

#/ Core
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Validator;

#/ Helpers
use r, f, checkAttempts, encHelper;

#/ Models
use App\User;

# 3rd party
use Tymon\JWTAuth\Exceptions\JWTException;
use JWTAuth;

class userController extends Controller
{
    private $error_array = '', $user_data = array(), $jwt_token = '';

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

        $msg = array('');
        if (@array_key_exists($this->S_PREFIX."ADMIN_MSG_GLOBAL", $_SESS))
        {
            if ($_SESS[$this->S_PREFIX."ADMIN_MSG_GLOBAL"][0]==false){ $msg = $_SESS[$this->S_PREFIX.'ADMIN_MSG_GLOBAL'][1]; }
            if ($_SESS[$this->S_PREFIX."ADMIN_MSG_GLOBAL"][0]==true) { $msg = $_SESS[$this->S_PREFIX.'ADMIN_MSG_GLOBAL'][1]; }
            r::flush_sess($this->S_PREFIX."ADMIN_MSG_GLOBAL");
            $msg = ['Error'=>strip_tags($msg)];
        }
        else
        {
            $msg = ($this->error_array)?? array('');
        }

        //r::var_dumpx($msg);
        return @f::format_str($msg);
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
            'st_address' => 'max:1000',
        ]
        )->setAttributeNames([
        'email_add'=>'Email Address',
        'pass_w'=>'Password',
        'st_address'=>'Street Address',
        ]);


        if($validator->errors()->count()>0)
        {
            //r::global_msg($this->S_PREFIX."ADMIN_MSG_GLOBAL", $validator->errors()->messages());
            $this->error_array = ['fields'=>$validator->errors()->messages()];
            //r::var_dumpx($this->json_error);

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
            if(!empty($new_user))
            {
                #/ Add address
                $new_user->address()->create([
                'st_address'=>(@$POST['st_address']??''),
                ]);

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
        /*
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
            //r::global_msg($this->S_PREFIX."ADMIN_MSG_GLOBAL", $validator->errors()->messages());
            $this->error_array = ['fields'=>$validator->errors()->messages()];
            checkAttempts::update_attempt_counts();
            return $validator->errors()->count();
        }
        else
        {
            ##/ Check if User Exists
            $email_add = $POST['email_add'];
            $pass_w = encHelper::enc($email_add, $POST['pass_w']);
            //r::var_dumpx($email_add, $pass_w);

            /*$chk_email_add = User::where('email_add', $email_add)
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
            */
            #-


            #/ Check & get JWT Tokens
            try
            {
                r::var_dumpx($email_add, $pass_w);
                //if(!$this->jwt_token='xx')
                if(! $this->jwt_token = JWTAuth::attempt(['email_add'=>$email_add, 'pass_w'=>$pass_w]))
                {
                    r::global_msg($this->S_PREFIX."ADMIN_MSG_GLOBAL", 'Unable to authenticate the given credentials!');
                    checkAttempts::update_attempt_counts();
                    return 1;
                }
                else
                {
                    checkAttempts::reset_attempt_counts();
                    return 0;
                }
            }
            catch (JWTException $e)
            {
                r::global_msg($this->S_PREFIX."ADMIN_MSG_GLOBAL", 'Unable to authenticate the given credentials!');
                checkAttempts::update_attempt_counts();
                return 1;
            }
        }


        return 1;
    }//end func....



    public function get_user($user_id)
    {
        #/ Check Attempts
        #/*
        if(checkAttempts::check_attempts(3, $this->S_PREFIX.'ADMIN_MSG_GLOBAL')==false){
        checkAttempts::update_attempt_counts();
        return 1;
        }
        #*/

        if($user_id<=0)
        {
            r::global_msg($this->S_PREFIX."ADMIN_MSG_GLOBAL", 'Invalid User Id provided!');
            checkAttempts::update_attempt_counts();
            return $validator->errors()->count();
        }
        else
        {
            ##/ Get user data
            $get_user = User::where('id', $user_id)
            ->first();

            //r::last_query();
            //r::var_dumpx($get_user);

            if(is_null($get_user))
            {
                r::global_msg($this->S_PREFIX."ADMIN_MSG_GLOBAL", 'Unable to locate the given user!');
                checkAttempts::update_attempt_counts();
                return 1;
            }

            #/ Return user data
            $this->user_data = [
            'email_add' => f::format_str($get_user->email_add),
            'address' => f::format_str($get_user->address->st_address??''),
            ];

            return 0;
        }


        return 1;
    }//end func....

    ///////////////////////////////////////////////////////////////// PUBLIC Methods below

    public function create(Request $req)
    {
        //$POST = $req->json()->all(); //if coming via JSON
        //$t = json_decode(file_get_contents('php://input'), true); //direct test
        //r::var_dumpx($t, json_last_error_msg(), $this->POST, $_POST);

        $POST = $this->POST; //if coming via POST
        //r::var_dumpx($POST);

        if(isset($POST['email_add']))
        {
            $tot_errors = (int)@$this->save_user($POST);
            if($tot_errors<=0)
            {
                return response()->json([
                'msg'=>'User successfully created',
                'token_href'=>route('user.signin'),
                ], 200);
            }
        }

        return response()->json(array_merge(
        ['msg'=>'Error 400'],
        $this->errors()
        ), 400);

    }//end func...



    public function signin()
    {
        $POST = $this->POST;
        //r::var_dumpx($req->input('email_add'), $POST);

        if(isset($POST['email_add']))
        {
            $tot_errors = (int)@$this->signin_user($POST);
            if($tot_errors<=0)
            {
                return response()->json([
                'msg'=>'success',
                'token'=>$this->jwt_token,
                ], 200);
            }
        }

        return response()->json(array_merge(
        ['msg'=>'Error 400'],
        $this->errors()
        ), 400);

    }//end func...



    public function get($user_id)
    {
        $GET = $this->GET;
        //r::var_dumpx($GET, $user_id);

        $user_id = (int)@$user_id;
        if($user_id)
        {
            $tot_errors = (int)@$this->get_user($user_id);
            if($tot_errors<=0)
            {
                return response()->json([
                $this->user_data
                ], 200);
            }
        }

        return response()->json(array_merge(
        ['msg'=>'Error 400'],
        $this->errors()
        ), 400);

    }//end func...
}