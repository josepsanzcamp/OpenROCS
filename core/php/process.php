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
function process(&$select) {
	if(is_stopped()) return false;
	if(getNode(__DEBUG_PROCESS__)) addlog("executing process",__DEBUG_LOG__);
	$result=isset($select[__PROCESS_TRACE__])?$select[__PROCESS_TRACE__]:null;
	if(is_null($result)) {
		$void=array();
		$result=__process_select($select,"","",$void);
	} else {
		$process=$result[__PROCESS_PROCESS__];
		$timeout=$result[__PROCESS_TIMEOUT__];
		$ontimeout=$result[__PROCESS_ONTIMEOUT__];
		$context=$result[__PROCESS_CONTEXT__];
		if(!$process->exists()) {
			$stdout=trim($process->get_stdout());
			if(getNode(__DEBUG_PROCESS__)) addlog("- stdout=$stdout",__DEBUG_LOG__);
			$stderr=trim($process->get_stderr());
			if(getNode(__DEBUG_PROCESS__)) addlog("- stderr=$stderr",__DEBUG_LOG__);
			$context[0][__PROCESS_STDOUT__]=$stdout;
			$context[0][__PROCESS_STDERR__]=$stderr;
			$result=__process_select($select,"","",$context);
		} elseif($timeout && $process->elapsed_time()>=$timeout) {
			$process->sigterm_sigkill();
			if($ontimeout) {
				$void=array();
				__process_select($ontimeout,"","",$void);
			}
			$context[0][__PROCESS_STDOUT__]="";
			$context[0][__PROCESS_STDERR__]="";
			$result=__process_select($select,"","",$context);
		}
	}
	$select[__PROCESS_TRACE__]=$result;
	return is_null($result);
}

function __process_select(&$select,$stdout,$stderr,&$context) {
	if(is_stopped()) return null;
	if(getNode(__DEBUG_PROCESS__)) addlog("executing __process_select",__DEBUG_LOG__);
	$result=null;
	$frompath=null;
	$islast=0;
	if(count($context)) {
		$temp=array_pop($context);
		$frompath=$temp[__PROCESS_PATH__];
		if(getNode(__DEBUG_PROCESS__)) addlog("- jumping=$frompath",__DEBUG_LOG__);
		$stdout=$temp[__PROCESS_STDOUT__];
		$stderr=$temp[__PROCESS_STDERR__];
		$islast=count($context)?0:1;
	}
	foreach($select as $node=>$options) {
		if(is_stopped()) return null;
		if($node==$frompath) $frompath=null;
		if(is_null($frompath)) {
			$node2=limpiar_key($node);
			switch($node2) {
				case __PROCESS_ACTION__:
					if(getNode(__DEBUG_PROCESS__)) addlog("case action",__DEBUG_LOG__);
					if(!is_array($options)) $options=__process_getaction($options);
					if(getNode(__DEBUG_PROCESS__)) addlog("- action=".$options["name"],__DEBUG_LOG__);
					$result=__process_select($options,$stdout,$stderr,$context);
					$select[$node]=$options;
					break;
				case __PROCESS_SHELL__:
					if($islast) {
						if(getNode(__DEBUG_PROCESS__)) addlog("case shell",__DEBUG_LOG__);
						if(getNode(__DEBUG_PROCESS__)) addlog("- islast=$islast",__DEBUG_LOG__);
						if(getNode(__DEBUG_PROCESS__)) addlog("- shell=$options",__DEBUG_LOG__);
						$islast=0;
					} else {
						if(getNode(__DEBUG_PROCESS__)) addlog("case shell",__DEBUG_LOG__);
						if(getNode(__DEBUG_PROCESS__)) addlog("- shell=$options",__DEBUG_LOG__);
						list($stdout2,$stderr2)=str_replace_with_vars($options,array_merge(config_array_vars(),server_array_vars(),make_array_vars(array(__VARIABLE_STDOUT__=>$stdout,__VARIABLE_STDERR__=>$stderr))));
						if($stdout2!="" && $stderr2=="") {
							if(is_stopped()) return null;
							$process=new child_helper($stdout2);
							list($timeout,$ontimeout)=__process_timeout($select,$node);
							if(getNode(__DEBUG_PROCESS__)) addlog("- timeout=$timeout",__DEBUG_LOG__);
							if(getNode(__DEBUG_PROCESS__)) addlog("- ontimeout=$ontimeout",__DEBUG_LOG__);
							$result=array(__PROCESS_PROCESS__=>$process,__PROCESS_TIMEOUT__=>$timeout,__PROCESS_ONTIMEOUT__=>$ontimeout,__PROCESS_CONTEXT__=>array());
						} else {
							$stdout=$stdout2;
							$stderr=$stderr2;
						}
					}
					break;
				case __PROCESS_PHP__:
					if(getNode(__DEBUG_PROCESS__)) addlog("case php",__DEBUG_LOG__);
					if(getNode(__DEBUG_PROCESS__)) addlog("- php=$options",__DEBUG_LOG__);
					list($stdout,$stderr)=eval_with_vars($options,array_merge(config_array_vars(),server_array_vars(),make_array_vars(array(__VARIABLE_STDOUT__=>$stdout,__VARIABLE_STDERR__=>$stderr))));
					break;
				case __PROCESS_CHOOSE__:
					if(getNode(__DEBUG_PROCESS__)) addlog("case choose",__DEBUG_LOG__);
					$result=__process_choose($options,$stdout,$stderr,$context);
					$select[$node]=$options;
					break;
				case __PROCESS_SEND__:
					if(getNode(__DEBUG_PROCESS__)) addlog("case send",__DEBUG_LOG__);
					if(getNode(__DEBUG_PROCESS__)) addlog("- send=$options",__DEBUG_LOG__);
					list($stdout2,$stderr2)=str_replace_with_vars($options,array_merge(config_array_vars(),server_array_vars(),make_array_vars(array(__VARIABLE_STDOUT__=>$stdout,__VARIABLE_STDERR__=>$stderr))));
					if($stdout2!="" && $stderr2=="") {
						if(is_stopped()) return null;
						comm_and_wait($stdout2);
					} else {
						$stdout=$stdout2;
						$stderr=$stderr2;
					}
					break;
				case __PROCESS_LOGNODE__:
					if(getNode(__DEBUG_PROCESS__)) addlog("case log",__DEBUG_LOG__);
					if(getNode(__DEBUG_PROCESS__)) addlog("- log=$options",__DEBUG_LOG__);
					list($stdout2,$stderr2)=str_replace_with_vars($options,array_merge(config_array_vars(),server_array_vars(),make_array_vars(array(__VARIABLE_STDOUT__=>$stdout,__VARIABLE_STDERR__=>$stderr))));
					if($stdout2!="" && $stderr2=="") {
						if(is_stopped()) return null;
						addlog($stdout2,__USER_LOG__);
					} else {
						$stdout=$stdout2;
						$stderr=$stderr2;
					}
					break;
			}
		}
		if(!is_null($result)) break;
	}
	if(!is_null($result)) array_push($result[__PROCESS_CONTEXT__],array(__PROCESS_PATH__=>$node,__PROCESS_STDOUT__=>$stdout,__PROCESS_STDERR__=>$stderr));
	return $result;
}

function __process_choose(&$choose,$stdout,$stderr,&$context) {
	if(is_stopped()) return null;
	if(getNode(__DEBUG_PROCESS__)) addlog("executing __process_choose",__DEBUG_LOG__);
	$eval=false;
	$result=null;
	$frompath=null;
	$isrestore=0;
	if(count($context)) {
		$temp=array_pop($context);
		$frompath=$temp[__PROCESS_PATH__];
		if(getNode(__DEBUG_PROCESS__)) addlog("- jumping=$frompath",__DEBUG_LOG__);
		$isrestore=1;
	}
	$trace=isset($choose[__PROCESS_TRACE__])?$choose[__PROCESS_TRACE__]:array(__PROCESS_CASE__=>"",__PROCESS_ITERATIONS__=>"",__PROCESS_TIMESTAMP1__=>"",__PROCESS_TIMESTAMP2__=>"");
	foreach($choose as $case=>$action) {
		if(is_stopped()) return null;
		if($case==$frompath) $frompath=null;
		if(is_null($frompath)) {
			$case2=limpiar_key($case);
			switch($case2) {
				case __PROCESS_WHEN__:
					if(getNode(__DEBUG_PROCESS__)) addlog("case eval",__DEBUG_LOG__);
					if(getNode(__DEBUG_PROCESS__)) addlog("- eval=${action[__PROCESS_EVAL__]}",__DEBUG_LOG__);
					if(getNode(__DEBUG_PROCESS__)) addlog("- stdout=$stdout",__DEBUG_LOG__);
					if($isrestore) {
						if(getNode(__DEBUG_PROCESS__)) addlog("- isrestore=$isrestore",__DEBUG_LOG__);
						$eval=true;
					} else {
						list($eval,$error)=eval_with_vars($action[__PROCESS_EVAL__],array_merge(config_array_vars(),server_array_vars(),make_array_vars(array(__VARIABLE_STDOUT__=>$stdout,__VARIABLE_STDERR__=>$stderr))));
						if($error!="") $eval="";
					}
					if(getNode(__DEBUG_PROCESS__)) addlog("- eval=$eval",__DEBUG_LOG__);
					break;
				case __PROCESS_OTHERWISE__:
					if(getNode(__DEBUG_PROCESS__)) addlog("case otherwise",__DEBUG_LOG__);
					$eval=true;
					if(getNode(__DEBUG_PROCESS__)) addlog("- eval=$eval",__DEBUG_LOG__);
					break;
			}
			if($eval) break;
		}
	}
	if($eval) {
		if($isrestore) {
			$trigger=true;
		} else {
			if($trace[__PROCESS_CASE__]!=$case) {
				$trace[__PROCESS_CASE__]=$case;
				$trace[__PROCESS_ITERATIONS__]=1;
				$trace[__PROCESS_TIMESTAMP1__]=microtime(true);
				$trace[__PROCESS_TIMESTAMP2__]=microtime(true);
				__process_select_reset($action);
			} else {
				$trace[__PROCESS_ITERATIONS__]++;
			}
			$iterations=$trace[__PROCESS_ITERATIONS__];
			list($fromiter,$error)=isset($action[__PROCESS_FROMITER__])?eval_with_vars($action[__PROCESS_FROMITER__],config_array_vars()):array(1,"");
			list($everyiter,$error)=isset($action[__PROCESS_EVERYITER__])?eval_with_vars($action[__PROCESS_EVERYITER__],config_array_vars()):array(1,"");
			list($untiliter,$error)=isset($action[__PROCESS_UNTILITER__])?eval_with_vars($action[__PROCESS_UNTILITER__],config_array_vars()):array($iterations,"");
			$seconds1=(microtime(true)-$trace[__PROCESS_TIMESTAMP1__]);
			$seconds2=(microtime(true)-$trace[__PROCESS_TIMESTAMP2__]);
			list($fromsec,$error)=isset($action[__PROCESS_FROMSEC__])?eval_with_vars($action[__PROCESS_FROMSEC__],config_array_vars()):array(0,"");
			list($everysec,$error)=isset($action[__PROCESS_EVERYSEC__])?eval_with_vars($action[__PROCESS_EVERYSEC__],config_array_vars()):array(0,"");
			list($untilsec,$error)=isset($action[__PROCESS_UNTILSEC__])?eval_with_vars($action[__PROCESS_UNTILSEC__],config_array_vars()):array($seconds1,"");
			$trigger=true;
			$trigger&=($iterations>=$fromiter);
			$trigger&=(($iterations-$fromiter)%$everyiter==0);
			$trigger&=($iterations<=$untiliter);
			$trigger&=(ceil($seconds1)>=$fromsec);
			$trigger2=(round($seconds2)>=$everysec);
			$trigger&=$trigger2;
			$trigger&=(floor($seconds1)<=$untilsec);
			if($trigger && $trigger2) $trace[__PROCESS_TIMESTAMP2__]=microtime(true);
		}
		if($trigger) {
			$result=__process_select($action,$stdout,$stderr,$context);
			$choose[$case]=$action;
			if(!is_null($result)) array_push($result[__PROCESS_CONTEXT__],array(__PROCESS_PATH__=>$case));
		}
	} else {
		$trace[__PROCESS_CASE__]="";
		$trace[__PROCESS_ITERATIONS__]="";
		$trace[__PROCESS_TIMESTAMP1__]="";
		$trace[__PROCESS_TIMESTAMP2__]="";
	}
	$choose[__PROCESS_TRACE__]=$trace;
	return $result;
}

function __process_getaction($name) {
	$file=strtok($name,"[");
	$temp=strtok("]");
	// CHECK THAT XML EXISTS
	$file=xml_real_file(__XML_ACTIONS__.$file);
	if(!file_exists($file)) show_php_error(array("phperror"=>"Unknown action '$name'"));
	$xml=xml2array($file);
	// SEARCH AND RETURN THE ACTION
	if(!$temp) return $xml;
	if(is_array($xml)) {
		foreach($xml as $node=>$actions) {
			if(limpiar_key($node)==__PROCESS_ACTIONS__) {
				if(is_array($actions)) {
					foreach($actions as $action=>$options) {
						if(limpiar_key($action)==__PROCESS_ACTION__) {
							if(isset($options[__PROCESS_NAME__]) && $options[__PROCESS_NAME__]==$temp) return $options;
						}
					}
				}
			}
		}
	}
	return "";
}

function __process_timeout($select,$from) {
	$timeout="";
	$ontimeout="";
	$valid=0;
	foreach($select as $node=>$options) {
		$node2=limpiar_key($node);
		switch($node2) {
			case __PROCESS_SHELL__:
				$valid=($node==$from);
				break;
			case __PROCESS_TIMEOUT__:
				if($valid) list($timeout,$error)=str_replace_with_vars($options,config_array_vars());
				break;
			case __PROCESS_ONTIMEOUT__:
				if($valid) list($ontimeout,$error)=str_replace_with_vars($options,config_array_vars());
				break;
		}
	}
	return array($timeout,$ontimeout);
}

function process_reset(&$select) {
	$result=isset($select[__PROCESS_TRACE__])?$select[__PROCESS_TRACE__]:null;
	if(!is_null($result)) {
		$process=$result[__PROCESS_PROCESS__];
		$process->sigterm_sigkill();
	}
	__process_select_reset($select);
	unset($select[__PROCESS_TRACE__]);
}

function __process_select_reset(&$select) {
	foreach($select as $node=>$options) {
		$node2=limpiar_key($node);
		switch($node2) {
			case __PROCESS_ACTION__:
				if(!is_array($options)) $options=__process_getaction($options);
				__process_select_reset($options);
				$select[$node]=$options;
				break;
			case __PROCESS_SHELL__:
				// NOTHING TO DO
				break;
			case __PROCESS_PHP__:
				// NOTHING TO DO
				break;
			case __PROCESS_CHOOSE__:
				__process_choose_reset($options);
				$select[$node]=$options;
				break;
			case __PROCESS_SEND__:
				// NOTHING TO DO
				break;
			case __PROCESS_LOGNODE__:
				// NOTHING TO DO
				break;
		}
	}
}

function __process_choose_reset(&$choose) {
	foreach($choose as $case=>$action) {
		$case2=limpiar_key($case);
		switch($case2) {
			case __PROCESS_WHEN__:
				__process_select_reset($action);
				$choose[$case]=$action;
				break;
			case __PROCESS_OTHERWISE__:
				__process_select_reset($action);
				$choose[$case]=$action;
				break;
		}
	}
	unset($choose[__PROCESS_TRACE__]);
}

function __process_select_check($select,$file) {
	foreach($select as $node=>$options) {
		$node2=limpiar_key($node);
		switch($node2) {
			case __PROCESS_ACTION__:
				if(!is_array($options)) {
					$options2=__process_getaction($options);
					if(!is_array($options2)) xml_error(__CUOTE__.$options.__CUOTE__.__SPACE__.__NOT_FOUND__,"","",$file);
					$options=$options2;
				}
				__process_select_check($options,$file);
				break;
			case __PROCESS_SHELL__:
				list($timeout,$ontimeout)=__process_timeout($select,$node);
				if($timeout) {
					if(!is_numeric($timeout)) xml_error(__CUOTE__.__PROCESS_TIMEOUT__.__CUOTE__.__SPACE__.__NOT_NUMERIC__,"","",$file);
					if($timeout<=0) xml_error(__CUOTE__.__PROCESS_TIMEOUT__.__CUOTE__.__SPACE__.__GREATER_ZERO__,"","",$file);
				}
				if($ontimeout && !$timeout) xml_error(__CUOTE__.__PROCESS_ONTIMEOUT__.__CUOTE__.__SPACE__.__REQUIRES__.__SPACE__.
					__CUOTE__.__PROCESS_TIMEOUT__.__CUOTE__,"","",$file);
				break;
			case __PROCESS_PHP__:
				// NOTHING TO DO
				break;
			case __PROCESS_CHOOSE__:
				__process_choose_check($options,$file);
				break;
			case __PROCESS_SEND__:
				// NOTHING TO DO
				break;
			case __PROCESS_LOGNODE__:
				// NOTHING TO DO
				break;
		}
	}
}

function __process_choose_check(&$choose,$file) {
	foreach($choose as $case=>$action) {
		$case2=limpiar_key($case);
		switch($case2) {
			case __PROCESS_WHEN__:
				if(!isset($action[__PROCESS_EVAL__])) xml_error(__CUOTE__.__PROCESS_WHEN__.__CUOTE__.__SPACE__.__REQUIRES__.__SPACE__.
					__CUOTE__.__PROCESS_EVAL__.__CUOTE__,"","",$file);
				$keys=array(__PROCESS_FROMITER__,__PROCESS_EVERYITER__,__PROCESS_UNTILITER__,__PROCESS_FROMSEC__,__PROCESS_EVERYSEC__,__PROCESS_UNTILSEC__);
				foreach($keys as $key) {
					if(isset($action[$key])) {
						list($value,$error)=str_replace_with_vars($action[$key],config_array_vars());
						if($error) xml_error(__CUOTE__.$key.__CUOTE__.__SPACE__.__VALUE_ERROR__,"","",$file);
						if(!is_numeric($value)) xml_error(__CUOTE__.$key.__CUOTE__.__SPACE__.__NOT_NUMERIC__,"","",$file);
						if($value<=0) xml_error(__CUOTE__.$key.__CUOTE__.__SPACE__.__GREATER_ZERO__,"","",$file);
					}
				}
				__process_select_check($action,$file);
				break;
			case __PROCESS_OTHERWISE__:
				__process_select_check($action,$file);
				break;
			default:
				xml_error(__UNKNOWN_PARAMETER__.__SPACE__.__CUOTE__.$case.__CUOTE__,"","",$file);
				break;
		}
	}
}
?>