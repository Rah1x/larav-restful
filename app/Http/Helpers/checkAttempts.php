<?php
namespace App\Http\Helpers;

use Illuminate\Http\Request;
use r;

class checkAttempts
{

    /**
     * Function check_attempts
     * Description: Check Script usage attempts and returns false if attempts reach max allowed
     * Requirements: Sessions
     *
     * @author Raheel Hasan
     * @version 1.2
     *
     * @example
     * #/ Check Attempts
     * include_once('includes/check_attempts.php');
     * if(check_attempts(10)==false)
     * {
     *    return_back('resend-activation', true); exit;
     * }
     *
     * // return_back() function must call update_attempt_counts();
     *
    **/


    public static function check_attempts($allowed=10, $sess_msg_key)
    {
        $SESS = \Request::session()->all();

        ###/ Check Attempts
        if(isset($SESS["au_wrongtry"]) && ($SESS["au_wrongtry"]>=$allowed))
        {
            $last_time = (int)$SESS['au_last_attempt'];
            $now = time();
            $stop_tm = rand(120, 320);

            if(($now-$last_time)>$stop_tm) // Reset after random 80 to 120 seconds
            {
                self::reset_attempt_counts();
            }
            else
            {
                \Request::session()->put($sess_msg_key, array(false, '<strong class="red-txt">Too Many Attempts!</strong> &nbsp;&nbsp;Please try again after a few minutes.'));
                \Request::session()->put('au_last_attempt', time());
                \Request::session()->save();

                return false;
            }
        }//end if attempt check....

        return true;

    }//end func...


    /**
     * Function update_attempt_counts
     * Description: Update the Attempt Counts. These counts are used by check_attempts function.
    */
    public static function update_attempt_counts()
    {
        $SESS = \Request::session()->all();

        #/ Update attempts count
        $au_wrongtry = (int)@$SESS["au_wrongtry"];
        if(isset($SESS["au_wrongtry"])) \Request::session()->put('au_wrongtry', ($au_wrongtry+1));
        else \Request::session()->put('au_wrongtry', 1);

        \Request::session()->put('au_last_attempt', time());
        \Request::session()->save();

    }//end func...


    public static function reset_attempt_counts()
    {
        \Request::session()->put('au_wrongtry', 0);
        \Request::session()->put('au_last_attempt', 0);
        \Request::session()->save();

    }//end func...

}
?>