<?php
namespace App\Http\Helpers;

use Illuminate\Http\Request;
use DB;

class mainHelper
{
    //public function mainHelper(){}

    /** var_dump() with <pre> included, and exit; **/
    public static function var_dumpx()
    {
        echo "<pre>";
        if(func_num_args()>0)
        foreach(func_get_args() as $argv)
        {
            var_dump($argv);
        }
        echo "</pre>";
        exit;

    }//end func...


    /** var_dump() with <pre> included **/
    public static function var_dumpp()
    {
        echo "<pre>";
        if(func_num_args()>0)
        foreach(func_get_args() as $argv)
        {
            var_dump($argv);
        }
        echo "</pre>";

    }//end func...


    /** Session Methods **/
    public static function sess()
    {
        $SESS = \Request::session()->all();
        //var_dump($SESS);

        return $SESS;
    }

    public static function flush_sess($var)
    {
        if(empty($var)){return false;}

        \Request::session()->pull($var);
        \Request::session()->save();
    }

    public static function sess_save($key, $val)
    {
        \Request::session()->put($key, $val);
        \Request::session()->save();
    }



    public static function cur_route()
    {
        $cur_route = \Request::route()->getName();
        return $cur_route;
    }

    public static function global_msg($key, $msg, $succ=false)
    {
        if(empty($msg)){return false;}

        $msg_v = '';
        if(is_array($msg)){
        if($succ==false){$msg_v = 'Please clear the following Error(s):<br /><br />- '; }
        $fv_msg_ar=array();
        foreach($msg as $fv_k=>$fv_v){$fv_msg_ar = array_merge($fv_msg_ar, $fv_v);}
        $msg_v.=@implode('<br />- ', $fv_msg_ar);
        } else {
        $msg_v = $msg;
        }

        \Request::session()->put($key, array($succ, $msg_v));
        \Request::session()->save();
    }

    public static function last_query()
    {
        $query = \DB::getQueryLog();
        $lastQuery = end($query);

        self::var_dumpx($lastQuery);
    }

    /**
     * function cb89
     * Set top key of a multi-dimensional array from a kay=>value in its 2nd level
     * USAGE Example: $contact_res_t = cb89($contact_res, 'id');
     */
    public static function cb89($a, $set_key){$ret=array(); foreach($a as $v){$ret[$v[$set_key]]=$v;} return $ret;}
}
