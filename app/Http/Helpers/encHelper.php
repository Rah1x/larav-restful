<?php
namespace App\Http\Helpers;
use r;

class encHelper
{
    //I will always use a new salt, and a new way to encrypt
    public static function enc($key, $in, $salt_key='P-(87mb:¼???Ø')
    {
        if(empty($in)){return false;}

        #/ Making Salt with EmailAdd
        $kx = md5($key);
        $kx_1 = substr($kx, 0, 4);
        $kx_2 = substr($kx, -4);
        #-

        #/ Making Password
        $inx = @str_split($in);
        $pass_= array();
        $i=0;
        foreach($inx as $v)
        {
            $i++;
            if($i%2==0){
            $pass_[]= md5($salt_key.$v.$kx_1);
            } else {
            $pass_[]= md5($v.$salt_key.$kx_2);
            }
        }
        $pass = hash('sha256', implode('', $pass_));
        //r::var_dumpx($inx, $pass_, $pass);

        return $pass;
    }


    public static function createRandomPassword($length=7)
    {
        $chars = "wxyIgJ#NB0$2745abcUdeIKfhBt@uCD!EGm%Hn*opqr6389svFijkAz";
        srand((double)microtime()*1000000);
        $i = 0;
        $pass = '' ;
        while ($i <= $length)
    	{
            $num = rand() % 33;
            $tmp = substr($chars, $num, 1);
            $pass = $pass . $tmp;
            $i++;
        }
        return $pass;
    }//end func....
}