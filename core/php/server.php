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
include("php/signals.php");
$mychildhash=$pipe[0]->read();
garbage_collector_add_file(get_temp_file(md5($mychildhash)));
$signal_handler_pcntl_wait=0;
// DEFINE AND PROGRAM SHUTDOWN HANDLER
function shutdown_handler() {
	if(defined("__CANCEL_SHUTDOWN_HANDLER__")) return;
	global $clients,$socket;
	if(is_array($clients)) foreach($clients as $client) if($client) socket_close_protected($client);
	if($socket) socket_close_protected($socket);
	garbage_collector_empty();
}
register_shutdown_function("shutdown_handler");
// DEFINE A COMPAT FUNCTION TO ALLOW "STACK VARIABLE" AND "STACK_VARIABLE"
function __server_compat_helper($input,$__data,$name) {
	$input1=strtok($input,__SPACE__);
	$input2=strtok(__EQUAL__);
	$input3=strtok(__NONE__);
	if(isset($__data[$input1.$name])) $input1.=$name;
	return array($input1,$input2,$input3);
}
function __server_compat($input,$__data,$name) {
	list($input1,$input2,$input3)=__server_compat_helper($input,$__data,$name);
	if(!isset($__data[$input1])) {
		$input2=strtok($input,__EQUAL__);
		$input3=strtok(__NONE__);
		$input1=array();
		$input2=explode(__UNDERSTAND__,$input2);
		while(count($input2)) {
			$input1[]=array_shift($input2);
			$temp=implode(__UNDERSTAND__,$input1);
			if(count($input2)) {
				$temp2=array_shift($input2);
				$name2=__ARROBA__.$temp2;
				if(isset($__data[$temp.$name2])) $name=$name2;
				else array_unshift($input2,$temp2);
			}
			if(isset($__data[$temp.$name])) break;
		}
		$input1=implode(__UNDERSTAND__,$input1).$name;
		$input2=implode(__UNDERSTAND__,$input2);
	}
	if(!isset($__data[$input1])) {
		list($input1,$input2,$input3)=__server_compat_helper($input,$__data,$name);
	}
	return array($input1,$input2,$input3);
}
// NORMAL OPERATION
$host=getNode(__SERVER_HOST__);
$port=getNode(__SERVER_PORT__);
$name=getNode(__SERVER_NAME__);
$name=__ARROBA__.($name?$name:$host.__TWO_POINTS__.$port);
$socket=socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
socket_set_option($socket,SOL_SOCKET,SO_REUSEADDR,1);
capture_next_error();
socket_bind($socket,$host,$port);
$error=get_clear_error();
if($error) {
	$pipe[1]->write(__SERVER_ERROR__.__EOL__);
	show_php_error();
} else {
	$pipe[1]->write(__SERVER_START__.__SPACE__.$port.__EOL__);
	socket_listen($socket);
	$__data=array(__CHILD_ARG__.$name=>new stack());
	$stacks=getNode(__SERVER_STACKS__);
	if(is_array($stacks)) foreach($stacks as $stack) $__data[$stack.$name]=new stack();
	$clients=array();
	$timestamps=array();
	$outputs=array();
	$polling_sec=intval(getNode(__POLLING_SERVER__)/1000000);
	$polling_usec=intval(getNode(__POLLING_SERVER__)%1000000);
	$timeout_sec=floatval(getNode(__TIMEOUT_SERVER__)/1000000);
	$signals=array();
	while($socket) {
		$read=array_merge(array($socket),$clients);
		$write=null;
		$except=null;
		capture_next_error();
		$ready=socket_select($read,$write,$except,$polling_sec,$polling_usec);
		$error=get_clear_error();
		if(!$error && $ready) {
			foreach($read as $client) {
				if($client==$socket) {
					capture_next_error();
					$client=socket_accept($socket);
					$error=get_clear_error();
					if(!$error) {
						array_push($clients,$client);
						$key=array_search($client,$clients);
						$timestamps[$key]=microtime(true);
						$outputs[$key]=__NONE__;
					}
				} else {
					capture_next_error();
					$input=socket_read($client,__MAXSIZE__);
					$error=get_clear_error();
					if($error || !$input) {
						$key=array_search($client,$clients);
						unset($clients[$key]);
						unset($timestamps[$key]);
						unset($outputs[$key]);
						socket_close_protected($client);
					} else {
						$input=trim($input);
						$input_array=explode(__EOL__,$input);
						while($client && $input=array_shift($input_array)) {
							$input=trim($input);
							if($input) {
								if(getNode(__DEBUG_COMM__)) addlog("server input=$input",__DEBUG_LOG__);
								// CONTINUE WITH NORMAL CODE
								$output=__NONE__;
								$input0=strtok($input,__SPACE__);
								switch($input0) {
									case __START_CMD__:
										$input1=strtok(__NONE__);
										if($input1==__SERVER_NODE__) {
											$output=__PERMISSION_DENIED__.__SPACE__.__CUOTE__.$input1.__CUOTE__.__EOL__;
										} elseif($input1!=__NONE__) {
											$childs=$__data[__CHILD_ARG__.$name]->export();
											$mychildhost=child_host($mychildhash);
											foreach($childs as $childhash=>$value) {
												if(child_host($childhash)==$mychildhost) {
													$childalias=child_alias($childhash);
													$childpid=child_pid($childhash);
													if($childalias==$input1 || limpiar_key($childalias)==$input1 || $childpid==$input1) {
														array_push($signals,array(__SERVER_CHILDHASH__=>$childhash,__SERVER_CMD__=>__PROCESS_START__,__SERVER_STATUS__=>__STATUS_RUN__));
														$childstr=child_string($childhash);
														if($value==__STATUS_RUN__) {
															$output.=__SERVICE__.__SPACE__.__CUOTE__.$childstr.__CUOTE__.__SPACE__.__IS__.__SPACE__.__CUOTE__.__STATUS_RUN__.__CUOTE__.__EOL__;
														} else {
															$output.=__SERVICE__.__SPACE__.__CUOTE__.$childstr.__CUOTE__.__SPACE__.__IS_GOING_TO__.__SPACE__.__CUOTE__.__STATUS_RUN__.__CUOTE__.__EOL__;
														}
													}
												}
											}
										}
										if($output==__NONE__) $output=__UNKNOWN_PARAMETER__.__SPACE__.__CUOTE__.$input1.__CUOTE__.__EOL__;
										break;
									case __STOP_CMD__:
										$input1=strtok(__NONE__);
										if($input1==__SERVER_NODE__) {
											$output=__PERMISSION_DENIED__.__SPACE__.__CUOTE__.$input1.__CUOTE__.__EOL__;
										} elseif($input1!=__NONE__) {
											$childs=$__data[__CHILD_ARG__.$name]->export();
											$mychildhost=child_host($mychildhash);
											foreach($childs as $childhash=>$value) {
												if(child_host($childhash)==$mychildhost) {
													$childalias=child_alias($childhash);
													$childpid=child_pid($childhash);
													if($childalias==$input1 || limpiar_key($childalias)==$input1 || $childpid==$input1) {
														array_push($signals,array(__SERVER_CHILDHASH__=>$childhash,__SERVER_CMD__=>__PROCESS_STOP__,__SERVER_STATUS__=>__STATUS_STOP__));
														$childstr=child_string($childhash);
														if($value==__STATUS_STOP__) {
															$output.=__SERVICE__.__SPACE__.__CUOTE__.$childstr.__CUOTE__.__SPACE__.__IS__.__SPACE__.__CUOTE__.__STATUS_STOP__.__CUOTE__.__EOL__;
														} else {
															$output.=__SERVICE__.__SPACE__.__CUOTE__.$childstr.__CUOTE__.__SPACE__.__IS_GOING_TO__.__SPACE__.__CUOTE__.__STATUS_STOP__.__CUOTE__.__EOL__;
														}
													}
												}
											}
										}
										if($output==__NONE__) $output=__UNKNOWN_PARAMETER__.__SPACE__.__CUOTE__.$input1.__CUOTE__.__EOL__;
										break;
									case __STATUS_CMD__:
										$input1=strtok(__NONE__);
										if($input1!=__NONE__) {
											$childs=$__data[__CHILD_ARG__.$name]->export();
											$mychildhost=getNode(__SERVER_HOST__);
											$exists=false;
											foreach($childs as $childhash=>$value) {
												if(child_host($childhash)==$mychildhost) {
													$childalias=child_alias($childhash);
													$childpid=child_pid($childhash);
													if($childalias==$input1 || limpiar_key($childalias)==$input1 || $childpid==$input1) {
														$childstr=child_string($childhash);
														$output.=__SERVICE__.__SPACE__.__CUOTE__.$childstr.__CUOTE__.__SPACE__.__IS__.__SPACE__.__CUOTE__.$value.__CUOTE__.__EOL__;
														$exists=true;
													}
												}
											}
											if(!$exists) $output=__UNKNOWN_SERVICE__.__SPACE__.__CUOTE__.$input1.__CUOTE__.__EOL__;
										} else {
											$output=__SERVER_RUNNING__.__EOL__;
										}
										break;
									case __GET_CMD__:
										list($input1,$input2,$input3)=__server_compat(strtok(__NONE__),$__data,$name);
										if($input1==__NONE__) {
											$output=count($__data).__SPACE__.__STACK_FOUND__.__EOL__;
											foreach($__data as $key=>$val) $output.=$key.__EOL__;
										} elseif(is_numeric($input1)) {
											// REPORT ALL STACKS WITH MODIFICATIONS
											$temp=array();
											foreach($__data as $key=>$val) if($val->modified($input1)) $temp[$key]=$val;
											$output=count($temp).__SPACE__.__STACK_FOUND__.__EOL__;
											foreach($temp as $key=>$val) $output.=$key.__EOL__;
										} elseif(isset($__data[$input1])) {
											$array=$__data[$input1]->export();
											if($input2==__NONE__) {
												$count=count($array);
												$output=$count.__SPACE__.__DATA_FOUND__.__SPACE__.$input1.__EOL__;
												foreach($array as $key=>$val) $array[$key]=($val!="")?$key."=".$val:$key;
												$output.=implode(__EOL__,$array).($count?__EOL__:__NONE__);
											} elseif(is_numeric($input2)) {
												// REPORT ALL VARIABLES WITH MODIFICATIONS
												$temp=array();
												foreach($__data[$input1]->added($input2) as $key) $temp[$key]=$array[$key];
												foreach($__data[$input1]->updated($input2) as $key) $temp[$key]=$array[$key];
												foreach($__data[$input1]->removed($input2) as $key) $temp[$key.__NEGATION__]="";
												$count=count($temp);
												$output=$count.__SPACE__.__DATA_FOUND__.__SPACE__.$input1.__EOL__;
												foreach($temp as $key=>$val) $temp[$key]=($val!="")?$key."=".$val:$key;
												$output.=implode(__EOL__,$temp).($count?__EOL__:__NONE__);
											} else {
												$array=$__data[$input1]->export();
												$output.=(isset($array[$input2])?$array[$input2]:__DATA_NOT_FOUND__).__EOL__;
											}
										} else {
											$output=__STACK_NOT_FOUND__.__EOL__;
										}
										break;
									case __ADD_CMD__:
									case __CREATE_CMD__:
										list($input1,$input2,$input3)=__server_compat(strtok(__NONE__),$__data,$name);
										if($input1==__NONE__) {
											$output=__UNKNOWN_STACK__.__EOL__;
										} elseif($input2==__NONE__) {
											if(!isset($__data[$input1])) {
												$__data[$input1]=new stack();
												$bool=true;
											} else {
												$bool=false;
											}
											$output=($bool?1:0).__SPACE__.__STACK_ADDED__.__EOL__;
										} elseif(isset($__data[$input1])) {
											$bool=$__data[$input1]->add($input2,$input3);
											$output=($bool?1:0).__SPACE__.__DATA_ADDED__.__SPACE__.$input1.__EOL__;
										} else {
											$output=__STACK_NOT_FOUND__.__EOL__;
										}
										break;
									case __UPDATE_CMD__:
									case __SET_CMD__:
										list($input1,$input2,$input3)=__server_compat(strtok(__NONE__),$__data,$name);
										if($input1==__NONE__) {
											$output=__UNKNOWN_STACK__.__EOL__;
										} elseif($input2==__NONE__) {
											$output=__UNKNOWN_DATA__.__EOL__;
										} elseif(isset($__data[$input1])) {
											$bool1=$__data[$input1]->update($input2,$input3);
											$bool2=$bool1?false:$__data[$input1]->add($input2,$input3);
											if($bool1) $output="1".__SPACE__.__DATA_UPDATED__.__SPACE__.$input1.__EOL__;
											elseif($bool2) $output="1".__SPACE__.__DATA_ADDED__.__SPACE__.$input1.__EOL__;
											else $output="0".__SPACE__.__DATA_UPDATED__.__SPACE__.$input1.__EOL__;
										} else {
											$output=__STACK_NOT_FOUND__.__EOL__;
										}
										break;
									case __REMOVE_CMD__:
									case __DELETE_CMD__:
										list($input1,$input2,$input3)=__server_compat(strtok(__NONE__),$__data,$name);
										if($input1==__NONE__) {
											$output=__UNKNOWN_STACK__.__EOL__;
										} elseif($input2==__NONE__) {
											if(isset($__data[$input1]) && !$__data[$input1]->count()) {
												unset($__data[$input1]);
												$bool=true;
											} else {
												$bool=false;
											}
											$output=($bool?1:0).__SPACE__.__STACK_REMOVED__.__EOL__;
										} elseif(isset($__data[$input1])) {
											$bool=$__data[$input1]->remove($input2);
											$output=($bool?1:0).__SPACE__.__DATA_REMOVED__.__SPACE__.$input1.__EOL__;
										} else {
											$output=__STACK_NOT_FOUND__.__EOL__;
										}
										break;
									case __LOG_CMD__:
										$input1=strtok(__NONE__);
										addlog($input1,__USER_LOG__);
										break;
									case __HELPER_CMD__:
										$output=__HELPER_MSG__.__EOL__;
										break;
									case __EXIT_CMD__:
									case __QUIT_CMD__:
									case __BYE_CMD__:
										break;
									case __HTTP_HEADER_1__:
									case __HTTP_HEADER_2__:
									case __HTTP_HEADER_3__:
									case __HTTP_HEADER_4__:
										// THIS CALLS ARE REFUSED BY US
										$input=__QUIT_CMD__;
										break;
									case __CRC32_CMD__:
										$key=array_search($client,$clients);
										$output=__CRC32_CMD__.__EQUAL__.crc32($outputs[$key]).__EOL__;
										break;
									default:
										$output=__UNKNOWN_COMMAND__.__SPACE__.__CUOTE__.$input.__CUOTE__.__EOL__;
										break;
								}
								if($output) {
									if(getNode(__DEBUG_COMM__)) addlog("server output=$output",__DEBUG_LOG__);
									capture_next_error();
									socket_write($client,$output);
									get_clear_error();
									$key=array_search($client,$clients);
									if($input!=__CRC32_CMD__) $outputs[$key]=$output;
								}
								if(in_array($input,array(__EXIT_CMD__,__QUIT_CMD__,__BYE_CMD__))) {
									$key=array_search($client,$clients);
									unset($clients[$key]);
									unset($timestamps[$key]);
									unset($outputs[$key]);
									socket_close_protected($client);
								}
							}
						}
						if($client) {
							$key=array_search($client,$clients);
							$timestamps[$key]=microtime(true);
						}
					}
				}
			}
		}
		foreach($clients as $client) {
			$key=array_search($client,$clients);
			if($timestamps[$key]+$timeout_sec<=microtime(true)) {
				unset($clients[$key]);
				unset($timestamps[$key]);
				unset($outputs[$key]);
				socket_close_protected($client);
			}
		}
		if(count($signals)>0 && !$signal_handler_pcntl_wait) {
			$array=array_shift($signals);
			$childpid2=@pcntl_fork();
			if($childpid2==-1) show_php_error(array("phperror"=>"Unable to fork a new process"));
			if($childpid2==0) {
				define("__CANCEL_SHUTDOWN_HANDLER__",1);
				child_send($array[__SERVER_CHILDHASH__],$array[__SERVER_CMD__]);
				comm(__UPDATE_CMD__.__SPACE__.__CHILD_ARG__.__SPACE__.$array[__SERVER_CHILDHASH__].__EQUAL__.$array[__SERVER_STATUS__]);
				die();
			}
			$signal_handler_pcntl_wait=1;
		}
	}
}
die();
?>