<?php
namespace App\Http\Helpers;

class formatStrHelper {

/**
 * Function format_str
 * Formats string and arrays (Please upgrade as per your need)
 * version 6.5
 * Author: Raheel Hasan


 * $in = input string/array
 * $max_length (def: 0) = set it to an int value and it will cut-out all chars after this length. Set to "{INTVAL}:dot" and it will also insert dots after int value
 * $add_space (def: false) = set it to a value and it will Add Space after this length
 * $escape_mysql (def:false) = set to TRUE if escaping/sanitizing for mysql query. This will apply mysql_real_escape
 * $utf (default: false) = set as TRUE in-order to FORCE and convert result into UTF-8 (from ISO-8859-1).
 * OR set to 'utf-ignore' to ignore between UTF-8 to UTF8 only (or iso-ignore) - use this when the source is in utf but require fixes
 * $rem_inline (default: true) = set as TRUE in-order to remove all inline xss.
 * $all (default: true) = set it to FALSE and it will NOT format Tags and ignore < and > from formating


 *
 * NOTE:
 * If the meta charset is not on UTF, you will have to convert strings manually everywhere (between db save and retrive).
 * For this, either Save (in db) as normal and Retrive as utf, -or- Save as utf and Retrive as normal.
 * But DONOT do utf conversion on both sides.
 */

public static function format_str($in, $max_length=0, $add_space=false, $escape_mysql=false, $utf=false, $rem_inline=true, $all=true)
{
	$out = $in;

	if(is_array($out))
    {
        $out_x = array();
        foreach($out as $k=>$v)
        {
            $k = strip_tags($k);
            $k = self::remove_x($k);
            $k = self::format_str($k);

            $v = self::format_str($v, $max_length, $add_space, $escape_mysql, $utf, $rem_inline, $all);
            $out_x[$k] = $v;
        }
        $out = $out_x;
    }
    else
    {
        if($rem_inline){
        //$out = preg_replace('/<(.*?)(on[a-z]{1,}[\s]{0,}=[\s]{0,})(.*?)>/ims', '<$1 x$2 $3>', $out);
        $out = preg_replace('/([\s\'"]{1,})(on[a-z]{1,}[\s]{0,}=[\s]{0,}[\'"]{1})/ims', '$1x$2', $out);
        }

        if(($utf!=false) && (stristr($utf, 'ignore')==false))
        {
            $out = @iconv("ISO-8859-1", "UTF-8//TRANSLIT//IGNORE", $out);

            //if(is_string($utf)){
            //$out = @iconv("{$utf}", "UTF-8//IGNORE", $out);
            //}
        }

        if($add_space!=false){
        $out = preg_replace("/([^\s]{{$add_space}})/ims", '$1 ', $out);
        }

        $max_length_i = @explode(':', $max_length);
        $max_length_ic = @$max_length_i[0];
        if($max_length_ic>0)
        {
            $cur_len = strlen($out);
            $out = substr($out, 0, $max_length_ic);
            $out.= ($cur_len>$max_length_ic && isset($max_length_i[1]) && $max_length_i[1]=='dot') ? ' ...':'';
        }


        if($all){
        $out = str_replace('<', '&lt;', $out);
    	$out = str_replace('>', '&gt;', $out);
        }

        $out = str_replace("'", '&#39;', $out);
    	$out = str_replace('"', '&quot;', $out);

        $out = str_replace("(", '&#x28;', $out);
        $out = str_replace(")", '&#x29;', $out);

        $out = str_replace("\\", '&#92;', $out); //most important

        //$out = trim($out);

        if($utf=='utf-ignore'){
        $out = @iconv("UTF-8", "UTF-8//IGNORE", $out);
        } else if($utf=='iso-ignore'){
        $out = @iconv("ISO-8859-1", "UTF-8//IGNORE", $out);
        }

        if($escape_mysql!=false){
        $out = mysql_real_escape_string($out);
        }

        $out = trim($out);

    }

	return $out;
}//end func.....



/**
 * Function reverse_format
 * reverses '<', '>', '"', "'", '(', ')', '`' to regular
 **/
public static function reverse_format($in)
{
    $out = $in;

    $array_s = array('<', '>', '"', "'", '(', ')', '\\');
    $array_r = array('&lt;', '&gt;', '&#39;', '&quot;', '&#x28;', '&#x29;', '&#92;');
    $out = str_replace($array_r, $array_s, $out);

    $out = trim($out);
    return $out;
}//end func.....


/**
 * Function reverse_format
 * Reverses the format done by format_str
 * version 1.0
 * Author: Raheel Hasan
 *
 * $in = input string/array
 */
public static function reverse_format_ar($in, $all=false)
{
    $out = $in;

	if(is_array($out))
    {
        $out_x = array();
        foreach($out as $k=>$v)
        {
            $v = self::reverse_format_ar($v, $all);
            $out_x[$k] = $v;
        }
        $out = $out_x;
    }
    else
    {
        if($all){
        $out = str_replace('&lt;', '<', $out);
    	$out = str_replace('&gt;', '>', $out);
        }

        $out = str_replace("&#39;", "'", $out);
    	$out = str_replace('&quot;', '"', $out);

        $out = str_replace('&#x28;', "(", $out);
        $out = str_replace('&#x29;', ")", $out);
    }

	return $out;

}//end func.....


/**
 * Function remove_x
 * Remove roots of every danger all together
 * Please add/remove items as needed
 * $in = input string
 **/
public static function remove_x($in)
{
    $out = $in;

    if(is_array($out))
    {
        $out_x = array();
        foreach($out as $k=>$v)
        {
            $v = self::remove_x($v);
            $out_x[$k] = $v;
        }
        $out = $out_x;
    }
    else
    {

        $array_s = array('<', '>', '"', "'", '(', ')', '`');
        $out = str_replace($array_s, '', $out);
        $out = trim($out);
    }

    return $out;
}//end func.....


/**
 * Function format_filename
 * Remove all special chars except for allowed chars for filename
 * $in = input string
**/
public static function format_filename($in)
{
    $out = $in;

    $out = self::remove_x($in); //remove xss
    $out = str_replace(array(' '), '_', $out);
    $out = preg_replace('/[^a-zA-Z0-9_\-\.]/i', '', $out);

    $out = trim($out);
    return $out;
}//end func.....



/**
 * Function allowed_format
 * reconvert a few items into visible html formatss
 * $in = input string
**/
public static function allowed_format($in)
{
    $out = $in;

    if(empty($out)){return $out;}

    ##/ Convert Bolds
    if(strstr($out, '**')!=false){
    $out = preg_replace('/\*\*(.*?)\*\*/ims', '<b>$1</b>', $out);
    //var_dump_p($out); die();
    }

    #/ Convert Anchors
    if(stristr($out, 'http://')!=false || stristr($out, 'https://')!=false){
    $out = preg_replace('/([^\s])\</ims', '$1 <', $out); //add space for bold tag
    $out = preg_replace('/(http[s]{0,1}\:\/\/[\p{L}\p{N}\p{P}\p{S}]{4,})(\s{0,})/ims', '<a href="$1" target="_blank">$1</a>$2', $out);
    $out = preg_replace('/\.{1,}\" target\=\"/ims', '" target="', $out);
    $out = str_ireplace(' </b>', '</b>', $out); //remove space befor </b>
    //var_dump_p($out); die();
    }


    return $out;
}//end func.....


/**
 * Function rem_risky_tags
 * Use for sql SAVE/UPDATE only
**/
public static function rem_risky_tags($in, $is_admin=true)
{
    $out = $in;

    $srch = array(
    '/<style.*?>.*?(<\/style>){1,}/ims',
    '/<script.*?>.*?(<\/script>){1,}/ims',
    '/<iframe.*?>.*?(<\/iframe>){1,}/ims',
    '/<applet.*?>.*?(<\/applet>){1,}/ims',
    '/<frame.*?>.*?(<\/frame>){1,}/ims',
    '/<frameset.*?>.*?(<\/frameset>){1,}/ims',
    '/<ilayer.*?>.*?(<\/ilayer>){1,}/ims',
    '/<layer.*?>.*?(<\/layer>){1,}/ims',
    '/<layer.*?>.*?(<\/layer>){1,}/ims',
    '/<base.*?>.*?(<\/base>){1,}/ims',
    '/<code.*?>.*?(<\/code>){1,}/ims',
    '/<xml.*?>.*?(<\/xml>){1,}/ims',
    '/<applet.*?>.*?(<\/applet>){1,}/ims',
    '/<html.*?>.*?(<\/html>){1,}/ims',
    '/<head.*?>.*?(<\/head>){1,}/ims',
    '/<meta.*?>.*?(<\/meta>){1,}/ims',
    '/<body.*?>.*?(<\/body>){1,}/ims'
    );

    if($is_admin==false)
    {
        $srch2 = array(
        '/<embed.*?>.*?(<\/embed>){1,}/ims',
        '/<object.*?>.*?(<\/object>){1,}/ims',
        );

        $srch = array_merge($srch, $srch2);
    }

    $out = preg_replace($srch, '', $out);
    $out = str_replace("'", '&#39;', $out);
    $out = trim($out);

    return $out;

}//end func.....
}
?>