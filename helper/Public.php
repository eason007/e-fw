<?php
/**
 * @package helper
 * @author eason007<eason007@163.com>
 * @copyright Copyright (c) 2007-2008 eason007<eason007@163.com>
 */

class helper_Public {
	/**
	 * 字符串转换为Unix时间戳
	 * 
	 * 接受多种时间格式，如：
	 * 2006-01-01T13:20:20Z
	 * 2006-01-01T13:20:20+8000
	 * 2006-01-01 13:20
	 * 2006-01-01 13:20:20
	 * Fri, 06 Jan 2006 10:56:21 +0800
	 * 2006年12月31日 13:30
	 * 2006-01-01
	 *
	 * @param string $strTime
	 * @return int 
	 */
	function getStr2Mktime ($strTime){
		if (strstr($strTime, "T")){
			//$strTime = "2006-01-01T13:20:20Z";
			//$strTime = "2006-01-01T13:20:20+8000";
			preg_match_all("/(\d+)-(\d+)-(\d+)T(\d+):(\d+):(\d+)/is", $strTime, $matchs);
	
			$datetime = mktime($matchs[4][0], $matchs[5][0], $matchs[6][0], $matchs[2][0], $matchs[3][0], $matchs[1][0]);
		}
		else{
			preg_match_all("/(\d+)-(\d+)-(\d+) (\d+):(\d+)/is", $strTime, $matchs);
	
			if (!empty($matchs[0])){
				//$strTime = "2006-01-01 13:20";
				//$strTime = "2006-01-01 13:20:20";
				$datetime = mktime($matchs[4][0], $matchs[5][0], 0, $matchs[2][0], $matchs[3][0], $matchs[1][0]);
			}
			else{
				$strTime = preg_replace("/([a-zA-Z]{3}),(\d+)(.*)/i", "$1, $2$3", $strTime);
				
				$strTime = str_replace("  "," ", $strTime);
				
				$d0 = explode(" ", $strTime);
				$d1 = explode(":", $d0[4]);
				
				if (count($d0) > 1){
					//$strTime = "Fri, 06 Jan 2006 10:56:21 +0800";
					$eValue = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
					$iValue = array("01","02","03","04","05","06","07","08","09","10","11","12");
					
					$d0[2] = str_replace($eValue, $iValue, $d0[2]);
					
					if (!empty($d0[5])){
						$datetime = mktime($d1[0]-8+(substr($d0[5],1,2)), $d1[1], $d1[2], $d0[2], $d0[1], $d0[3]);
					}
					else{
						preg_match_all("/(\d+)年(\d+)月(\d+)日 (\d+):(\d+)/is", $strTime, $matchs);
						
						if (!empty($matchs[0])){
							$datetime = mktime($matchs[4][0], $matchs[5][0], 0, $matchs[2][0], $matchs[3][0], $matchs[1][0]);
						}
					}
				}
				else{
					//$strTime = "2006-01-01";
					preg_match_all("/(\d+)-(\d+)-(\d+)/is", $strTime, $matchs);
					
					$datetime = mktime(date("H", time()), date("i", time()), date("s", time()), $matchs[2][0], $matchs[3][0], $matchs[1][0]);
				}
			}
		}
	
		return $datetime;
	}
	
	/**
	 * 格式化字符串参数
	 * 
	 * 将字符串进行关键词过滤或逆转换，包括：
	 * ENCODE/TEXT/TEXTAREA
	 * 	转换为入库格式
	 * DECODE
	 * 	转换为出库格式
	 * GET/POST
	 * 	
	 * POSTAGAIN
	 *
	 * @param string $string 待过滤的字符串
	 * @param string $type 过滤方式
	 * @return string
	 */
	function formatString($string, $type = "ENCODE") {
		if(!empty($string)) {
			switch(strtoupper($type)) {
				case "ENCODE": 
				case "TEXT": 
				case "TEXTAREA": {
					$chars = array(
						'&' => '&#38;',
						'"' => '&quot;', 
						"'" => '&#039;',
						"<" => '&lt;',
						">" => '&gt;',
						"{" => '&#123;',
						"}" => '&#125;',
						"\\" => '&#92;'
					);
					$string = strtr($string, $chars);
					break;
				}
				case "DECODE": {
					$chars = array(
						'&quot;' => '"', 
						'&#039;' => "'",
						'&lt;' => "<",
						'&gt;' => ">",
						'&#123;' => "{",
						'&#125;' => "}",
						'&#92;' => "\\",
						'&#38;' => '&',
						'&amp;' => '&'
					);
					$string = strtr($string, $chars);
					break;
				}
				case "GET": 
				case "POST": {
					$string = str_replace("\\\\" , "\\" , $string);	
					$string = ereg_replace("[\]'" , "'" , $string);
					$string = ereg_replace("[\]\"" , "\"" , $string);
					break;
				}
				case "POSTAGAIN": {
					$string = str_replace("\\\\" , "\\" , $string);	
					$string = ereg_replace("[\]'" , "'" , $string);
					$string = ereg_replace("[\]\"" , "\"" , $string);
					$string = str_replace("\\\\" , "\\" , $string);	
					$string = ereg_replace("[\]'" , "&#039;" , $string);
					$string = ereg_replace("[\]\"" , "&quot;" , $string);
					$string = ereg_replace('<','&lt;', $string);
					$string = ereg_replace(">","&gt;", $string);
					break;
				}
			}
			return trim($string);
		}
	}
	
	/**
	 * 将GET参数字符串化
	 * 
	 * 对上页传入的GET参数，或保留指定的参数组，或删除指定的参数，返回标准的GET字符串
	 *
	 * @param string $char GET参数名称
	 * @param string $type 操作方式：delete为删除指定参数；hold为保留指定参数
	 * @return string
	 */
	function formatQuery($char, $type = "delete") {
		if(!empty($char)) {
			parse_str(QUERY_STRING, $outPut);
			$characterElements = explode(",", $char);
			
			if($type == "delete") {
				while (list ($key, $val) = @each ($outPut)){
					if(!in_array ($key, $characterElements) and $key != '') {
						$newQueryString .= "&".$key."=".urlencode(formatString($val,1));
					}
				}
			}
			elseif($type == "hold") {
				while (list ($key, $val) = each ($outPut)) {
					if(in_array ($key, $characterElements)) { 
						$newQueryString .= "&".$key."=".urlencode(formatString($val,1));
					}
				}
			}
			return $newQueryString;
		} 
		else {
			return QUERY_STRING;
		}
	}
	
	/**
	 * 将字符串截取指定长度
	 * 
	 * 该函数适用于UTF-8格式，默认截取前50个字符，及默认追加省略符号
	 *
	 * @param string $title 待处理字符串
	 * @param int $length 截取的字符长度
	 * @param bool $isApp 是否在末尾追加省略符号
	 * @return str
	 */
	function formatTitleUTF_8($title, $length = 50, $isApp = true) {
		$returnstr='';
		$i = 0;
		$n = 0;
		$strLength = strlen($title);
		
		while (($n<$length) and ($i<=($strLength+1))) {
			$tempStr = substr($title,$i,1);
			$ascnum   = Ord($tempStr);
			if ($ascnum >= 224) {
				$returnstr = $returnstr.substr($title,$i,3);
				$i = $i+3;
				$n++;
			} 
			elseif ($ascnum >= 192) {
				$returnstr = $returnstr.substr($title,$i,2);
				$i = $i+2;
				$n++;
			} 
			else {
				$returnstr = $returnstr.substr($title,$i,1);
				$i = $i+1;
				$n = $n+0.5;
			}
		}
		
		if( ($strLength > $i) and $isApp) {
			$returnstr .= "..";
		}
		
		return $returnstr;
	}
	
	function pageNav ($iRecordTotal = 0, $iPageTotal = 1, $iPageLength = 4, $iNowPage = 1, $sUrl = ""){
		$iStep = 4;

		if ( ($iPageTotal == 0) and ($iRecordTotal > 0) ) {
			if ( ($iRecordTotal % $iPageLength) > 0){
				$iPageTotal = floor($iRecordTotal / $iPageLength) + 1;
			}
			else{
				$iPageTotal = $iRecordTotal / $iPageLength;
			}
		}
		
		if ( ($iNowPage - $iStep) <= 1){
			$iStart = 1;
		}
		else{
			$iStart = $iNowPage - $iStep;
		}
		
		if ( ($iNowPage + $iStep) >= $iPageTotal){
			$iEnd = $iPageTotal;
		}
		else{
			$iEnd = $iNowPage + $iStep;
		}
		
		$sText = "<div id=\"pageList\"><span class=\"total\">共 ".$iRecordTotal." 记录 / ".$iPageTotal." 页</span>&nbsp;";
		if ($iNowPage > 1) {
			$sText.= "<a href=\"".str_replace("{p}", 1, $sUrl)."\" title=\"首页\" class=\"first\">&laquo;</a>&nbsp;";
			$sText.= "<a href=\"".str_replace("{p}", $iNowPage - 1, $sUrl)."\" title=\"上页\" class=\"list\">&lt;</a>&nbsp;";
		}
		
		for ($i = $iStart;$i <= $iEnd;$i++){
			if ($i == $iNowPage){
				$sText.= "<span class=\"current\">".$i."</span>&nbsp;";
			}
			else{
				$sText.= "<a href=\"".str_replace("{p}", $i, $sUrl)."\" class=\"list\">".$i."</a>&nbsp;";
			}
		}
		
		if ($iNowPage != $iPageTotal){
			$sText.= "<a href=\"".str_replace("{p}", $iNowPage + 1, $sUrl)."\" title=\"下页\" class=\"list\">&gt;</a>&nbsp;";
			$sText.= "<a href=\"".str_replace("{p}", $iPageTotal, $sUrl)."\" title=\"尾页\" class=\"last\">&raquo;</a>&nbsp;";
		}
		
		$sText.= "</div>";
		
		return $sText;
	}
	
	
	function postData($url, $data) {
		$url = parse_url($url);
		
		if (!$url) {
			return "couldn't parse url";
		}
		if (!isset($url['port'])) {
			$url['port'] = "";
		}
		if (!isset($url['query'])) {
			$url['query'] = "";
		}
		
		$encoded = "";
		
		while (list($k,$v) = each($data)) {
			$encoded .= ($encoded ? "&" : "");
			$encoded .= rawurlencode($k)."=".rawurlencode($v);
		}
		
		$fp = fsockopen($url['host'], $url['port'] ? $url['port'] : 80);
		if (!$fp) {
			return "Failed to open socket to $url[host]";
		}
		
		fputs($fp, sprintf("POST %s%s%s HTTP/1.0\n", $url['path'], $url['query'] ? "?" : "", $url['query']));
		fputs($fp, "Host: $url[host]\n");
		fputs($fp, "Content-type: application/x-www-form-urlencoded\n");
		fputs($fp, "Content-length: " . strlen($encoded) . "\n");
		fputs($fp, "Connection: close\n\n");
		
		fputs($fp, "$encoded\n");
		
		$line = fgets($fp,1024);
		if (!eregi("^HTTP/1\.. 200", $line)) {
			return;
		}
		
		$results = ""; $inheader = 1;
		while(!feof($fp)) {
			$line = fgets($fp,1024);
			if ($inheader && ($line == "\n" || $line == "\r\n")) {
				$inheader = 0;
			}
			elseif (!$inheader) {
				$results .= $line;
			}
		}
		fclose($fp);
		
		return $results;
	}
}
?>