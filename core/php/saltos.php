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
function capture_next_error() {
	global $_ERROR_HANDLER;
	if(!isset($_ERROR_HANDLER["level"])) show_php_error(array("phperror"=>"error_handler without levels availables"));
	$_ERROR_HANDLER["level"]++;
	array_push($_ERROR_HANDLER["msg"],"");
}

function get_clear_error() {
	global $_ERROR_HANDLER;
	if($_ERROR_HANDLER["level"]<=0) show_php_error(array("phperror"=>"error_handler without levels availables"));
	$_ERROR_HANDLER["level"]--;
	// TRICK TO PREVENT THAT SHUTDOWN_HANDLER CAPTURES THE ERROR
	$error=error_get_last();
	if(is_array($error) && isset($error["type"]) && $error["type"]!=E_USER_NOTICE) {
		set_error_handler("var_dump",0);
		trigger_error("");
		restore_error_handler();
	}
	// CONTINUE
	return array_pop($_ERROR_HANDLER["msg"]);
}

function do_message_error($array,$format) {
	static $dict=array(
		"html"=>array(array("<h1>","</h1>"),array("<p>","</p>"),"<br/>"),
		"text"=>array(array("***** "," *****\n"),array("","\n"),"\n")
	);
	if(!isset($dict[$format])) die("Unknown format $format");
	$msg=array();
	if(isset($array["phperror"])) $msg[]=array("PHP Error",$array["phperror"]);
	if(isset($array["xmlerror"])) $msg[]=array("XML Error",$array["xmlerror"]);
	if(isset($array["dberror"])) $msg[]=array("DB Error",$array["dberror"]);
	if(isset($array["emailerror"])) $msg[]=array("EMAIL Error",$array["emailerror"]);
	if(isset($array["fileerror"])) $msg[]=array("FILE Error",$array["fileerror"]);
	if(isset($array["source"])) $msg[]=array("XML Source",$array["source"]);
	if(isset($array["exception"])) $msg[]=array("Exception",$array["exception"]);
	if(isset($array["details"])) $msg[]=array("Details",$array["details"]);
	if(isset($array["query"])) $msg[]=array("Query",$array["query"]);
	if(isset($array["backtrace"])) {
		$backtrace=$array["backtrace"];
		array_walk($backtrace,"__debug_backtrace_helper");
		$msg[]=array("Backtrace",implode($dict[$format][2],$backtrace));
	}
	array_walk($msg,"__do_message_error_helper",$dict[$format]);
	$msg=implode($msg);
	return $msg;
}

function __debug_backtrace_helper(&$item,$key) {
	$item="${key} => ".$item["function"].(isset($item["class"])?" (in class ".$item["class"].")":"").((isset($item["file"]) && isset($item["line"]))?" (in file ".$item["file"]." at line ".$item["line"].")":"");
}

function __do_message_error_helper(&$item,$key,$dict) {
	$item=$dict[0][0].$item[0].$dict[0][1].$dict[1][0].$item[1].$dict[1][1];
}

function show_php_error($array=null) {
	global $_ERROR_HANDLER;
	static $backup=null;
	if(is_null($array)) $array=$backup;
	if(is_null($array)) return;
	// REFUSE THE DEPRECATED WARNINGS
	if(isset($array["phperror"])) {
		$pos1=stripos($array["phperror"],"function");
		$pos2=stripos($array["phperror"],"deprecated");
		if($pos1!==false && $pos2!==false) return;
	}
	// ADD BACKTRACE IF NOT FOUND
	if(!isset($array["backtrace"])) $array["backtrace"]=debug_backtrace();
	// CREATE THE MESSAGE ERROR USING PLAIN TEXT
	$msg=do_message_error($array,"text");
	// CHECK IF CAPTURE ERROR WAS ACTIVE
	if($_ERROR_HANDLER["level"]>0) {
		$old=array_pop($_ERROR_HANDLER["msg"]);
		array_push($_ERROR_HANDLER["msg"],$old.$msg);
		$backup=$array;
		return;
	}
	// ADD THE MESSAGE TO THE ERROR LOG FILE
	addlog($msg,__ERROR_LOG__);
	// DUMP TO STDOUT
	while(ob_get_level()) ob_end_clean(); // TRICK TO CLEAR SCREEN
	echo $msg;
	die();
}

function __error_handler($type,$message,$file,$line) {
	$backtrace=debug_backtrace();
	show_php_error(array("phperror"=>"${message} (code ${type})","details"=>"Error on file '${file}' at line ${line}","backtrace"=>$backtrace));
}

function __exception_handler($e) {
	$backtrace=$e->getTrace();
	show_php_error(array("exception"=>$e->getMessage()." (code ".$e->getCode().")","details"=>"Error on file '".$e->getFile()."' at line ".$e->getLine(),"backtrace"=>$backtrace));
}

function __shutdown_handler() {
	semaphore_shutdown();
	$error=error_get_last();
	$types=array(E_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR,E_USER_ERROR,E_RECOVERABLE_ERROR);
	if(is_array($error) && isset($error["type"]) && in_array($error["type"],$types)) {
		global $_ERROR_HANDLER;
		$_ERROR_HANDLER=array("level"=>0,"msg"=>array());
		$backtrace=debug_backtrace();
		show_php_error(array("phperror"=>"${error["message"]}","details"=>"Error on file '${error["file"]}' at line ${error["line"]}","backtrace"=>$backtrace));
	}
}

function program_error_handler() {
	global $_ERROR_HANDLER;
	$_ERROR_HANDLER=array("level"=>0,"msg"=>array());
	error_reporting(0);
	set_error_handler("__error_handler");
	set_exception_handler("__exception_handler");
	register_shutdown_function("__shutdown_handler");
}

function init_random() {
	static $init=false;
	if($init) return;
	srand((float)microtime(true)*1000000);
	$init=true;
}

function get_unique_id_md5() {
	init_random();
	return md5(uniqid(rand(),true));
}

function get_temp_file($hash="") {
	$dir="/tmp/";
	if($hash) {
		$file=$dir.$hash;
	} else {
		while(1) {
			$uniqid=get_unique_id_md5();
			$file=$dir.$uniqid;
			if(!file_exists_protected($file)) break;
		}
	}
	return $file;
}

function current_datetime($offset=0) {
	return current_date($offset)." ".current_time($offset);
}

function current_date($offset=0) {
	return date("Y-m-d",time()+$offset);
}

function current_time($offset=0) {
	return date("H:i:s",time()+$offset);
}

function current_datetime_decimals($offset=0) {
	return current_datetime($offset).".".current_decimals($offset);
}

function current_decimals($offset=0) {
	$decimals=explode(".",microtime(true)+$offset);
	return substr((isset($decimals[1])?$decimals[1]:"")."0000",0,4);
}

function __addlog_helper($a) {
	return current_datetime_decimals().": ".$a;
}

function addlog($msg,$file) {
	if(!$file) $file=__DEFAULT_LOG__;
	$maxlines=getNode(__DEBUG_MAXLINES__);
	if(is_null($maxlines)) $maxlines=__MAXLINES__;
	if($maxlines>0 && file_exists_protected($file) && memory_get_free()>filesize($file)) {
		capture_next_error();
		$numlines=count(file($file));
		$error=get_clear_error();
		if(!$error && $numlines>$maxlines) {
			$next=1;
			while(file_exists_protected($file.".".$next)) $next++;
			capture_next_error();
			rename($file,$file.".".$next);
			get_clear_error();
		}
	}
	$msg=trim($msg);
	$msg=explode("\n",$msg);
	if(count($msg)==0) $msg=array("");
	$msg=array_map("__addlog_helper",$msg);
	$msg=implode("\n",$msg)."\n";
	file_put_contents($file,$msg,FILE_APPEND);
}

function getNode($path,$array=null) {
	global $config;
	static $cache=array();
	if(is_null($array)) $array=$config;
	$hash=md5(serialize(array($path,$array)));
	if(!isset($cache[$hash])) $cache[$hash]=__getNode_helper($path,$array);
	return $cache[$hash];

}

function __getNode_helper($path,$array) {
	if(!is_array($path)) $path=explode("/",$path);
	$elem=array_shift($path);
	if(!is_array($array) || !isset($array[$elem])) return null;
	if(count($path)==0) return $array[$elem];
	return __getNode_helper($path,__getValue_helper($array[$elem]));
}

function __getValue_helper($array) {
	return (is_array($array) && isset($array["value"]))?$array["value"]:$array;
}

function eval_iniset($array) {
	if(is_array($array)) {
		foreach($array as $key=>$val) {
			$current=ini_get($key);
			$diff=0;
			if(strtolower($val)=="on" || strtolower($val)=="off") {
				$current=$current?"On":"Off";
				if(strtolower($val)!=strtolower($current)) $diff=1;
			} else {
				if($val!=$current) $diff=1;
			}
			if($diff) {
				$result=ini_set($key,$val);
				if($result===false) {
					show_php_error(array("phperror"=>"ini_set fails to set '$key' from '$current' to '$val'"));
				}
			}
		}
	}
}

function eval_putenv($array) {
	if(is_array($array)) {
		foreach($array as $key=>$val) {
			$current=getenv($key);
			$diff=0;
			if($val!=$current) $diff=1;
			if($diff) {
				$result=putenv($key."=".$val);
				if($result===false) {
					show_php_error(array("phperror"=>"putenv fails to set '$key' from '$current' to '$val'"));
				}
			}
		}
	}
}

function encode_bad_chars($cad) {
	static $orig=array("á","à","ä","é","è","ë","í","ì","ï","ó","ò","ö","ú","ù","ü","Á","À","Ä","É","È","Ë","Í","Ì","Ï","Ó","Ò","Ö","Ú","Ù","Ü","ñ","Ñ","ç","Ç");
	static $dest=array("a","a","a","e","e","e","i","i","i","o","o","o","u","u","u","A","A","A","E","E","E","I","I","I","O","O","O","U","U","U","n","N","c","C");
	$cad=str_replace($orig,$dest,$cad);
	$len=strlen($cad);
	for($i=0;$i<$len;$i++) {
		$letter=$cad[$i];
		$replace=1;
		if($letter>="a" && $letter<="z") $replace=0;
		if($letter>="A" && $letter<="Z") $replace=0;
		if($letter>="0" && $letter<="9") $replace=0;
		if($replace) $cad[$i]=" ";
	}
	$cad=trim($cad);
	$cad=str_replace(" ","_",$cad);
	$count=1;
	while($count) $cad=str_replace("__","_",$cad,$count);
	return $cad;
}

function sprintr($array) {
	ob_start();
	print_r($array);
	$buffer=ob_get_clean();
	return $buffer;
}

function semaphore_acquire($file,$timeout=100000) {
	return __semaphore_helper(__FUNCTION__,$file,$timeout);
}

function semaphore_release($file) {
	return __semaphore_helper(__FUNCTION__,$file,null);
}

function semaphore_shutdown() {
	return __semaphore_helper(__FUNCTION__,null,null);
}

function __semaphore_helper($fn,$file,$timeout) {
	static $stack=array();
	if(stripos($fn,"acquire")!==false) {
		$hash=md5($file);
		if(!isset($stack[$hash])) $stack[$hash]=null;
		if($stack[$hash]) return false;
		init_random();
		while($timeout>=0) {
			capture_next_error();
			$stack[$hash]=fopen($file,"a");
			get_clear_error();
			if($stack[$hash]) break;
			$timeout-=usleep_protected(rand(0,1000));
		}
		if($timeout<0) {
			return false;
		}
		chmod_protected($file,0666);
		touch_protected($file);
		while($timeout>=0) {
			capture_next_error();
			$result=flock($stack[$hash],LOCK_EX|LOCK_NB);
			get_clear_error();
			if($result) break;
			$timeout-=usleep_protected(rand(0,1000));
		}
		if($timeout<0) {
			if($stack[$hash]) {
				capture_next_error();
				fclose($stack[$hash]);
				get_clear_error();
				$stack[$hash]=null;
			}
			return false;
		}
		ftruncate($stack[$hash],0);
		fwrite($stack[$hash],getmypid());
		return true;
	} elseif(stripos($fn,"release")!==false) {
		$hash=md5($file);
		if(!isset($stack[$hash])) $stack[$hash]=null;
		if(!$stack[$hash]) return false;
		capture_next_error();
		flock($stack[$hash],LOCK_UN);
		get_clear_error();
		capture_next_error();
		fclose($stack[$hash]);
		get_clear_error();
		$stack[$hash]=null;
		return true;
	} elseif(stripos($fn,"shutdown")!==false) {
		foreach($stack as $hash=>$val) {
			if($stack[$hash]) {
				capture_next_error();
				flock($stack[$hash],LOCK_UN);
				get_clear_error();
				capture_next_error();
				fclose($stack[$hash]);
				get_clear_error();
				$stack[$hash]=null;
			}
		}
		return true;
	}
	return false;
}

function memory_get_free() {
	static $memory_limit=0;
	if(!$memory_limit) {
		$memory_limit=ini_get("memory_limit");
		if(strtoupper(substr($memory_limit,-1,1))=="K") $memory_limit=intval(substr($memory_limit,0,-1))*1024;
		if(strtoupper(substr($memory_limit,-1,1))=="M") $memory_limit=intval(substr($memory_limit,0,-1))*1024*1024;
	}
	$memory_usage=memory_get_usage(true);
	return $memory_limit-$memory_usage;
}

function usleep_protected($usec) {
	$socket=socket_create(AF_UNIX,SOCK_STREAM,0);
	$read=null;
	$write=null;
	$except=array($socket);
	capture_next_error();
	$time1=microtime(true);
	socket_select($read,$write,$except,intval($usec/1000000),intval($usec%1000000));
	$time2=microtime(true);
	get_clear_error();
	return ($time2-$time1)*1000000;
}

function chmod_protected($file,$mode) {
	capture_next_error();
	ob_start();
	chmod($file,$mode);
	$error1=ob_get_clean();
	$error2=get_clear_error();
	return $error1.$error2;
}

function touch_protected($file) {
	capture_next_error();
	ob_start();
	touch($file);
	$error1=ob_get_clean();
	$error2=get_clear_error();
	return $error1.$error2;
}
?>