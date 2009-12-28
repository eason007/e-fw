<?php
/**
 * @package Helper
 */

/**
 * XML生成类
 * 
 * @package Helper
 * @author eason007<eason007@163.com>
 * @copyright Copyright (c) 2007-2008 eason007<eason007@163.com>
 */
class helper_XML {
	/**
	 * 生成XML
	 *
	 * @access public
	 * @param array $arrayList 输入的内容数组
	 * @param array $arrayElements element数组
	 * @param array $arrayInfos
	 * @param string $headMark
	 * @param string $encoding 编码
	 * @return string
	 */
	public function makeXML($arrayList = null, $arrayElements = null, $arrayInfos = null, $headMark = "E-FW", $encoding="UTF-8") {
		$xml = "<?xml version=\"1.0\" encoding=\"".$encoding."\"?>";
		$xml.= "<".$headMark.">";
		
		if(is_array($arrayElements)) {
			$xml.= self::getElements($arrayElements);
		}

		if(is_array($arrayInfos)){
			$xml.= self::getInfos($arrayInfos);
		}
		
		if(is_array($arrayList)){
			$xml.= self::getList($arrayList);
		}

		$xml.= "</".$headMark.">";

		return $xml;
	}
	
	
	/**
	 * Enter description here...
	 *
	 * @access private
	 * @param array $dataArray
	 * @return str
	 */
	private function getElements ($dataArray = null) {
		if (is_array($dataArray)) {
			$xml.= "<elements>";
			foreach($dataArray as $key => $value){
				 if(is_numeric($value)) {
			        $xml.= "<element elementID=\"".$key."\">".$value."</element>";
			     } 
			     else {
			        $xml.= "<element elementID=\"".$key."\"><![CDATA[".$value."]]></element>";
			     }
			}
			$xml.= "</elements>";
		}
		
		return $xml;
	}
	
	
	/**
	 * Enter description here...
	 *
	 * @access private
	 * @param array $dataArray
	 * @return str
	 */
	private function getInfos ($dataArray = null) {
		if (is_array($dataArray)) {
			$xml.= "<info>";
			foreach($dataArray as $key => $value){
				if(is_numeric($value)) {
					$xml.= "<".$key.">".$value."</".$key.">";
				} 
				else {
					 $xml.= "<".$key."><![CDATA[".$value."]]></".$key.">";
				}
			}		
			$xml.= "</info>";
		}
		
		return $xml;
	}


	private function getList ($dataArray = null) {
		$arrayList 	= $dataArray["list"];
		$total 		= $dataArray["total"];

		if(!empty($arrayList)) {
			$total 	= empty($total) ? 0 : $total;
			$xml 	= "<list total=\"".$total."\">";

			foreach($arrayList as $value){
				$xmlList 	= "";
				foreach($value as $key2 => $value2){
					switch (strtolower($key2)) {
						case "id":
							$ID = $value2;
						
							break;
						
						default:
							if(is_numeric($value2)) {
								$xmlList .="<".$key2.">".$value2."</".$key2.">";
							} 
							else {
								$xmlList .= "<".$key2."><![CDATA[".$value2."]]></".$key2.">";
							}
					}
				}

				$xml.= "<item ID=\"".$ID."\">";
				$xml.= $xmlList;
				$xml.= "</item>";
			}
			$xml.= "</list>";
		}
		else{
			$xml = "<list total=\"0\" />";
		}

		return $xml;
	}


	public function fileToArray($fileName) {
		return self::objectToArray(simplexml_load_file($fileName));
	}

	
	public function stringToArray($string) {
		return self::objectToArray(simplexml_load_string($string));
	}

	
	private function objectToArray($object){
	   $return = NULL;	       

	   if(is_array($object)){
	       foreach($object as $key => $value){
	           $return[$key] = self::objectToArray($value);
	       }
	   } 
	   else {
	       $var = get_object_vars($object);

	       if($var) {
	           foreach($var as $key => $value){
	               $return[$key] = self::objectToArray($value);
	           }
	       } 
	       else {
	           return trim((string) $object);
	       }
	   }

	   return $return;
	}
}