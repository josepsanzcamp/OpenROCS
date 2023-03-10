<?php
/*
  ___                   ____   ___   ____ ____          ____    ___
 / _ \ _ __   ___ _ __ |  _ \ / _ \ / ___/ ___|  __   _|___ \  / _ \
| | | | '_ \ / _ \ '_ \| |_) | | | | |   \___ \  \ \ / / __) || | | |
| |_| | |_) |  __/ | | |  _ <| |_| | |___ ___) |  \ V / / __/ | |_| |
 \___/| .__/ \___|_| |_|_| \_\\___/ \____|____/    \_/ |_____(_)___/
      |_|

OpenROCS: Open Robotic Observatory Control System
Copyright (C) 2011-2014 by Institut d'Estudis Espacials de Catalunya (IEEC)
More information in http://www.ieec.cat or ieec@ieec.cat

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
function file_get_contents_protected($file) {
	capture_next_error();
	$buffer=file_get_contents($file);
	get_clear_error();
	return $buffer;
}

function unlink_protected($file) {
	capture_next_error();
	unlink($file);
	get_clear_error();
}

function eval_with_vars($eval,$vars="") {
	if(!is_array($vars)) $vars=array();
	extract($vars);
	$eval="return ".$eval.";";
	capture_next_error();
	ob_start();
	$eval=eval($eval);
	$error1=ob_get_clean();
	$error2=get_clear_error();
	return array($eval,$error1.$error2);
}

function str_replace_with_vars($cad,$vars="") {
	$cad=str_replace("\"","\\\"",$cad);
	$cad=str_replace("\$\$","\\\$",$cad);
	return eval_with_vars('"'.$cad.'"',$vars);
}

function make_array_vars($array) {
	foreach($array as $key=>$val) {
		$val=str_replace(__EOL__,__SPACE__,$val);
		$part=strtok($val,__SPACE__);
		$count=1;
		while($part!="") {
			$array[$key.$count]=$part;
			$part=strtok(__SPACE__);
			$count++;
		}
	}
	return $array;
}

function __config_array_vars_helper($array,$prefix="") {
	$count=0;
	foreach($array as $key=>$val) {
		unset($array[$key]);
		$key=limpiar_key($key);
		if(is_array($val)) {
			list($array2,$count2)=__config_array_vars_helper($val,$prefix.$key.__UNDERSTAND__);
			$array=array_merge($array,$array2);
			$count=$count+$count2;
		} else {
			$array[$prefix.$key]=$val;
			$count++;
		}
	}
	if($prefix!="") return array($array,$count);
	if(count($array)!=$count) xml_error(__COLLISION_ERROR__,"","",xml_real_file(__XML_CONFIG__));
	return $array;
}

function config_array_vars() {
	static $vars=null;
	if(is_null($vars)) {
		$vars=array();
		$file=xml_real_file(__XML_VARIABLES__);
		if(file_exists($file)) {
			$array=xml2array($file);
			if(is_array($array)) {
				foreach($array as $key=>$val) {
					if(limpiar_key($key)==__VARIABLE_NODE__) {
						$vars=array_merge($vars,__config_array_vars_helper($val));
					}
				}
			}
		}
		if(!isset($vars[__HOME__])) $vars[__HOME__]=getenv(__HOME__);
		if(!isset($vars[__HOSTNAME__])) $vars[__HOSTNAME__]=php_uname("n");
		if(!isset($vars[__PATH__])) $vars[__PATH__]=getenv(__PATH__);
	}
	return $vars;
}

function server_array_vars() {
	static $vars=array();
	static $oldtimestamp=0;
	$host=getNode(__SERVER_HOST__);
	$port=getNode(__SERVER_PORT__);
	$name=getNode(__SERVER_NAME__);
	$name=__ARROBA__.($name?$name:$host.__TWO_POINTS__.$port);
	$newtimestamp=microtime(true);
	$stacks=comm_get_array(__GET_CMD__.__SPACE__.$oldtimestamp);
	$update=count($stacks);
	foreach($stacks as $stack) {
		$stack2=limpiar_stack($stack);
		if($stack2!=__CHILD_ARG__) {
			$datas=comm_get_array(__GET_CMD__.__SPACE__.$stack.__SPACE__.$oldtimestamp);
			$update*=count($datas);
			foreach($datas as $data) {
				$key=encode_bad_chars($stack).__UNDERSTAND__.strtok($data,__EQUAL__);
				$val=strtok(__NONE__);
				$is_remove=(substr($key,-1,1)==__NEGATION__);
				if($is_remove) $key=substr($key,0,-1);
				if(!$is_remove) $vars[$key]=$val;
				if($is_remove) unset($vars[$key]);
				if($stack==$stack2.$name) {
					$key=$stack2.__UNDERSTAND__.strtok($data,__EQUAL__);
					$val=strtok(__NONE__);
					$is_remove=(substr($key,-1,1)==__NEGATION__);
					if($is_remove) $key=substr($key,0,-1);
					if(!$is_remove) $vars[$key]=$val;
					if($is_remove) unset($vars[$key]);
				}
			}
		}
	}
	if($update) $oldtimestamp=$newtimestamp;
	return $vars;
}

function xml_real_file($file) {
	$hostname=php_uname("n");
	if(!$hostname) {
		$process=new child_helper("hostname");
		$process->wait_while_exists();
		$hostname=trim($process->get_stdout());
		$process->__destruct();
	}
	$dirname=dirname($file);
	$basename=basename($file);
	$file=$dirname."/".$hostname."/".$basename;
	if(!file_exists($file)) $file=$dirname."/".$basename;
	return $file;
}

function socket_close_protected(&$socket) {
	capture_next_error();
	socket_shutdown($socket);
	get_clear_error();
	capture_next_error();
	socket_close($socket);
	get_clear_error();
	$socket=null;
}

function limpiar_stack($stack) {
	$pos=strpos($stack,__ARROBA__);
	if($pos!==false) $stack=substr($stack,0,$pos);
	return $stack;
}

function file_exists_protected($file) {
	clearstatcache(true,$file);
	return file_exists($file);
}

function addlog_trace($label,$used,$total,$file) {
	global $mychildhash;
	static $dict=array(array("***** "," *****\n"),array("","\n"),"\n");
	$percent=$used*100/$total;
	if(isset($mychildhash) && $percent>=getNode(__DEBUG_PERCENT__)) {
		$msg=array();
		$trace=sprintf("%s total=%d used=%d percent=%d%%","comm",$total,$used,$percent);
		$msg[]=array("Trace",$trace);
		$debug=debug_backtrace();
		array_walk($debug,"__debug_backtrace_helper");
		$msg[]=array("Backtrace",implode($dict[2],$debug));
		array_walk($msg,"__do_message_error_helper",$dict);
		$msg=implode($msg);
		addlog($msg,$file);
	}
}
?>