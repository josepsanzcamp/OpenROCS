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
function leer_nodos(&$data,$file="") {
	$array=array();
	while($linea=array_pop($data)) {
		$name=$linea["tag"];
		$type=$linea["type"];
		$value="";
		if(isset($linea["value"])) $value=$linea["value"];
		$attr=array();
		if(isset($linea["attributes"])) $attr=$linea["attributes"];
		if($type=="open") {
			// CASE 1 <some>
			$value=leer_nodos($data,$file);
			if(count($attr)) $value=array("value"=>$value,"#attr"=>$attr);
			set_array($array,$name,$value);
		} elseif($type=="close") {
			// CASE 2 </some>
			return $array;
		} elseif($type=="complete" && $value=="") {
			// CASE 3 <some/>
			if(count($attr)) $value=array("value"=>$value,"#attr"=>$attr);
			set_array($array,$name,$value);
		} elseif($type=="complete" && $value!="") {
			// CASE 4 <some>some</some>
			if(count($attr)) $value=array("value"=>$value,"#attr"=>$attr);
			set_array($array,$name,$value);
		} elseif($type=="cdata") {
			// NOTHING TO DO
		} else {
			xml_error("Unknown tag type with name '&lt;/$name&gt;'",$linea,"",$file);
		}
	}
	return $array;
}

function set_array(&$array,$name,$value) {
	$count="";
	$prefix="";
	if(isset($array[$name])) {
		$count=1;
		$prefix="#";
		while(isset($array[$name.$prefix.$count])) $count++;
	}
	$array[$name.$prefix.$count]=$value;
}

function unset_array(&$array,$name) {
	$len=strlen($name);
	foreach($array as $key=>$val) {
		if(substr($key,0,$len)==$name) unset($array[$key]);
	}
}

function limpiar_key($key) {
	$pos=strpos($key,"#");
	if($pos!==false) $key=substr($key,0,$pos);
	return $key;
}

function xml2array($file,$usecache=true) {
	$xml=file_get_contents($file);
	$data=xml2struct($xml,$file);
	$data=array_reverse($data);
	$array=leer_nodos($data,$file);
	return $array["root"];
}

function xml2struct($xml,$file="") {
	$parser=xml_parser_create();
	xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
	xml_parser_set_option($parser,XML_OPTION_TARGET_ENCODING,"UTF-8");
	xml_parse_into_struct($parser,$xml,$array,$index);
	$code=xml_get_error_code($parser);
	if($code) {
		$error=xml_error_string($code);
		$linea=xml_get_current_line_number($parser);
		$fila=xml_get_current_column_number($parser);
		xml_error("Error ".$code.": ".$error,"",$linea.",".$fila,$file);
	}
	xml_parser_free($parser);
	return $array;
}

function eval_bool($arg) {
	if($arg===true) return 1;
	if($arg===false) return 0;
	$bool=strtoupper($arg);
	if($bool=="TRUE") return 1;
	if($bool=="FALSE") return 0;
	if($bool=="ON") return 1;
	if($bool=="OFF") return 0;
	if($bool=="YES") return 1;
	if($bool=="NO") return 0;
	if($bool=="1") return 1;
	if($bool=="0") return 0;
	xml_error("Unknown boolean value '$arg'");
}

function xml_error($error,$source="",$count="",$file="") {
	$array=array();
	$array["xmlerror"]=$error;
	if($count!="" && $file=="") $array["xmlerror"].=" (at line $count)";
	if($count=="" && $file!="") $array["xmlerror"].=" (on file $file)";
	if($count!="" && $file!="") $array["xmlerror"].=" (on file $file at line $count)";
	if(is_array($source)) $array["source"]=htmlentities(sprintr($source),ENT_COMPAT,"UTF-8");
	elseif($source!="") $array["source"]=htmlentities($source,ENT_COMPAT,"UTF-8");
	show_php_error($array);
}
?>