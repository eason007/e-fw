<?

/**

 * XML生成类

 * 

 * @package baseClass

 * @version 1.0

 */



class helper_XML {

	/***************************************************************************

	* 函数名:			makeInfoXML($arrayList=null, $encoding='UTF-8')

	* 功能:				生成XML

	* 版本:				1.0

	* 日期:				2006-03-03

	* 输入:				$arrayList : 输入的内容数组

	                    $total: 总数

	                    $checkBox： checkBox所要对应的值

	                    $arrayElements： element数组

	                    $encoding: 编码

	* 输出:				

	***************************************************************************/

	function makeInfoXML($arrayList=null, $arrayElements=null, $encoding='UTF-8') {

		$xml = '<?xml version="1.0" encoding="'.$encoding.'"?>';

		$xml .= "<jobui>";

		if(!empty($arrayElements)) {

			$xml.="<elements>";

			while (list($key, $value) = each($arrayElements)) {

				 if(is_numeric($value)) {

			        $xml.="<element elementID=\"".$key."\">".$value."</element>";

			     } else {

			        $xml.="<element elementID=\"".$key."\"><![CDATA[".$value."]]></element>";

			     }

			}

			$xml.="</elements>";

		}

		if(!empty($arrayList)){

			$xml .= "<info>";		

			while (list($key, $value) = @each($arrayList)) {

				if(is_numeric($value)) {

					$xml .="<".$key.">".$value."</".$key.">";

				} else {

					 $xml .="<".$key."><![CDATA[".$value."]]></".$key.">";

				}

			}		

			$xml .= "</info>";

		}else{

			$xml .= "<info />";

		}

		$xml .= "</jobui>";

		return $xml;

	}

	

	

	/***************************************************************************

	* 函数名:			makeXML($arrayList=null, $total=0, $checkBox="", $arrayElements=null, $encoding='UTF-8')

	* 功能:				生成XML

	* 版本:				1.0

	* 日期:				2006-03-03

	* 输入:				$arrayList : 输入的内容数组

	                    $total: 总数

	                    $checkBox： checkBox所要对应的值

	                    $arrayElements： element数组

	                    $encoding: 编码

	* 输出:				

	***************************************************************************/

	function makeXML($arrayList=null, $total=0, $checkBox="ID", $arrayElements=null, $encoding='UTF-8') {

		$xml = '<?xml version="1.0" encoding="'.$encoding.'"?>';

		$innerContent=helper_XML::_arrayInfo2xml($arrayList, $total, $checkBox , $arrayElements);

		if(!empty($innerContent)){

			$xml .= "<jobui>";

			$xml .= $innerContent;

			$xml .= "</jobui>";

		}else{

			$xml .= "<jobui />";

		}

		return $xml;

	}

	

	/***************************************************************************

	* 函数名:			_arrayInfo2xml($arrayList, $total, $checkBox, $arrayElements)

	* 功能:				生成XML

	* 版本:				1.0

	* 日期:				2006-03-03

	***************************************************************************/

	/**

	 * 

	 * @access private

	 */

	function _arrayInfo2xml($arrayList, $total, $checkBox , $arrayElements) {

		$xml = "";

		$checkboxB = false;

		if(!empty($arrayElements)) {

			$xml.="<elements>";

			while (list($key, $value) = each($arrayElements)) {

				 if(is_int($value)) {

			        $xml.="<element elementID=\"".$key."\">".$value."</element>";

			     } else {

			        $xml.="<element elementID=\"".$key."\"><![CDATA[".$value."]]></element>";

			     }

			}

			$xml.="</elements>";

		}

		

		if(!empty($arrayList)) {

			$total=empty($total)?0:$total;



			$checkBoxList = "";



			$xml.="<list total=\"".$total."\">";

			if(!empty($arrayList)) {			

				while (list($key, $value) = @each($arrayList)) {		

					$xmlList = "";		

					while (list($key2, $value2) = @each($value)) {

						 if($checkBox == $key2) {						     

							 $checkBoxValue = $value2;

						 }

						if($key2 == "ID") {						

							$ID = $value2;

						} else {						

							if($value2 == "" and $value2 != "0") {

								$value2 = "&nbsp;";

							} 						

							if(strtolower($key2) != "checkbox") {

								 if(is_numeric($value)) {

									 $xmlList .="<".$key2.">".$value2."</".$key2.">";

								 } else {

									 $xmlList .= "<".$key2."><![CDATA[".$value2."]]></".$key2.">";

								 }

							} else {

								if(strtolower($key2) == "checkbox") {

									$checkboxB = true;

								}

							}

						}

					}

					$xml.="<item ID=\"".$ID."\">";

					if($checkboxB) {

						$checkBoxList ="<checkbox><![CDATA[".$checkBoxValue."]]></checkbox>";

					}

					$xml.= $checkBoxList.$xmlList;

					$xml.="</item>";

				}			

			}

			$xml.="</list>";

		}else{

			$total=empty($total)?0:$total;

			$xml.='<list total="'.$total.'" />';

		}



		return $xml;

	}

	

	/***************************************************************************

	* 函数名:			xmlFileToArray($fileName)

	* 功能:				文件转成数组

	* 版本:				1.0

	* 日期:				2006-03-03

	***************************************************************************/

	function xmlFileToArray($fileName) {

		return helper_XML::object2array(simplexml_load_file($fileName));

	}

	

	/***************************************************************************

	* 函数名:			xmlStringToArray($string)

	* 功能:				内容转成数组

	* 版本:				1.0

	* 日期:				2006-03-03

	***************************************************************************/

	function xmlStringToArray($string) {

		return helper_XML::object2array(simplexml_load_string($string));

	}

	

	/***************************************************************************

	* 函数名:			object2array($object)

	* 功能:				对象转成数组

	* 版本:				1.0

	* 日期:				2006-03-03

	***************************************************************************/

	function object2array($object){

	   $return = NULL;	       

	   if(is_array($object)){

	       foreach($object as $key => $value)

	           $return[$key] = helper_XML::object2array($value);

	   } else {

	       $var = get_object_vars($object);

	           

	       if($var) {

	           foreach($var as $key => $value)

	               $return[$key] = helper_XML::object2array($value);

	       } else {

	           return trim((string) $object);

	       }

	   }	

	   return $return;

	}



}