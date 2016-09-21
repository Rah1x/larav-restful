<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;

use r, f;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $S_PREFIX, $PROJECT_NAME, $tx, $cur_route, $SESS, $GET, $POST, $POST_ori, $req;

    function __construct()
    {
        $this->tx = '1';//for testing
        $this->S_PREFIX = @$_ENV['SESSION_PREFIX'];
        $this->PROJECT_NAME = @$_ENV['PROJECT_NAME'];

        $this->cur_route = \Request::route()->getName();
        //$this->SESS = \Request::session()->all(); //cant do it from 5.3 onwards

        #/ Format incoming requests
        $this->GET = $this->POST_ori = $this->POST = array();
        if(\Request::isMethod('post'))
        {
            $this->POST_ori = $this->POST = \Request::input();
            $this->POST = f::format_str($this->POST);
        }

        if(\Request::isMethod('get'))
        {
            $this->GET = \Request::input();
            $this->GET = f::format_str($this->GET);
        }

        #/ Enable SQL Query log
        \DB::connection()->enableQueryLog();
    }
}
