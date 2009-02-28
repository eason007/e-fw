<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty truncate modifier plugin
 *
 * Type:     modifier<br>
 * Name:     truncate<br>
 * Purpose:  Truncate a string to a certain length if necessary,
 *           optionally splitting in the middle of a word, and
 *           appending the $etc string or inserting $etc into the middle.
 * @link http://smarty.php.net/manual/en/language.modifier.truncate.php
 *          truncate (Smarty online manual)
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @param string
 * @param integer
 * @param string
 * @param boolean
 * @param boolean
 * @return string
 */
function smarty_modifier_truncate_cn($string, $length = 80, $etc = '...', $code = 'UTF-8')
{
    if ($length == 0)
        return '';
    if ($code == 'UTF-8') {
        $pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
    }
    else {
    $pa = "/[\x01-\x7f]|[\xa1-\xff][\xa1-\xff]/";
    }
    preg_match_all($pa, $string, $t_string);
	
	$curentLength=0;		//用于计算真正的字符长度
	
	$arrayPoint=0;			//如过长时，以该坐标为截取长度

	$arrayLength = count($t_string[0]);

	for ($arrayPoint=0; ($arrayPoint<$arrayLength) && ($curentLength<$length); $arrayPoint++ )
	{
		if ( strlen ($t_string[0][$arrayPoint])>1 )
		{
			$curentLength+=2;
		}
		else
		{
			$curentLength++;
		}
	}

	if ($arrayLength > $arrayPoint)
	{
        return join('', array_slice($t_string[0], 0, ($arrayPoint-1) ) ) . $etc;
	}

    return join('', array_slice($t_string[0], 0, $arrayLength));
}


/* vim: set expandtab: */

?>
