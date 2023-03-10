<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2014 by Josep Sanz Campderrós
More information in http://www.saltos.net or info@saltos.net

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
function __escribir_nodos_check_node_name($name) {
	try {
		new DOMElement($name);
		return 1;
	} catch(DOMException $e) {
		return 0;
	}
}

function escribir_nodos(&$array,$level=null) {
	static $bad_chars=0;
	if(!$bad_chars) {
		$bad_chars=array(0,1,2,3,4,5,6,7,8,11,12,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31);
		foreach($bad_chars as $key=>$val) $bad_chars[$key]=chr($val);
		$bad_chars=array_merge($bad_chars,array("<![CDATA[","]]>"));
	}
	if(is_null($level)) {
		$prefix="";
		$postfix="";
	} else {
		$prefix=str_repeat("\t",$level);
		$postfix="\n";
		$level++;
	}
	$buffer="";
	foreach($array as $key=>$val) {
		$key=limpiar_key($key);
		$attr="";
		if(is_array($val) && isset($val["value"]) && isset($val["#attr"])) {
			$attr=array();
			foreach($val["#attr"] as $key2=>$val2) $attr[]=$key2."=".'"'.$val2.'"';
			$attr=" ".implode(" ",$attr);
			$val=$val["value"];
		}
		if(is_array($val)) {
			$buffer.="$prefix<$key$attr>$postfix";
			$buffer.=escribir_nodos($val,$level);
			$buffer.="$prefix</$key>$postfix";
		} else {
			if(!__escribir_nodos_check_node_name($key)) show_php_error(array("phperror"=>"Invalid XML tag name '$key' for the value '$val'"));
			$val=str_replace($bad_chars,"",$val);
			if(strpos($val,"<")!==false || strpos($val,"&")!==false) $val="<![CDATA[$val]]>";
			$buffer.=($val!="")?"$prefix<$key$attr>$val</$key>$postfix":"$prefix<$key$attr/>$postfix";
		}
	}
	return $buffer;
}

function array2xml($array) {
	$array=array("root"=>$array);
	$buffer=escribir_nodos($array,0);
	return $buffer;
}
?>