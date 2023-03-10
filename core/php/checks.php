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
function check_system() {
	// TEST NORMAL EXECUTION
	$usleep=rand(300000,600000);
	$process=new child_helper("usleep $usleep; echo test");
	$process->wait_while_exists();
	$used=$process->elapsed_time()*1000000;
	$percent=($used-$usleep)*100/$used;
	$stdout=trim($process->get_stdout());
	$stderr=trim($process->get_stderr());
	$result=(abs($percent)<=25 && $stdout=="test" && $stderr=="");
	echo "Test 1: ".($result?"\033[32mOK\033[0m":"\033[31mERROR\033[0m")." ".round($percent,2)."%\n";
	// TEST CONTROLED EXECUTION
	$usleep=rand(300000,600000);
	$process=new child_helper("usleep 1000000; echo test");
	usleep($usleep);
	$process->kill(__SIGNAL_KILL__,true);
	$process->wait_while_exists();
	$used=$process->elapsed_time()*1000000;
	$percent=($used-$usleep)*100/$used;
	$stdout=trim($process->get_stdout());
	$stderr=trim($process->get_stderr());
	$result=(abs($percent)<=25 && $stdout=="" && $stderr=="");
	echo "Test 2: ".($result?"\033[32mOK\033[0m":"\033[31mERROR\033[0m")." ".round($percent,2)."%\n";
	// TEST SEMAPHORE ACQUIRE AND RELEASE
	$sem=get_temp_file();
	$result="";
	if(!semaphore_acquire($sem)) {
		$result="Could not acquire semaphore";
	} elseif(semaphore_acquire($sem)) {
		$result="Could acquire an acquired semaphore";
	} elseif(!semaphore_release($sem)) {
		$result="Could not release semaphore";
	} elseif(semaphore_release($sem)) {
		$result="Could release a released semaphore";
	}
	echo "Test 3: ".(!$result?"\033[32mOK\033[0m":"\033[31mERROR\033[0m ($result)")."\n";
}

function check_config($config,$file) {
	$logs=array(__ERROR_LOG__,__TRACE_LOG__,__DEBUG_LOG__,__USER_LOG__);
	foreach($logs as $log) {
		$dir=dirname($log);
		if(!is_dir($dir)) show_php_error(array("phperror"=>"Log directory '$dir' not found"));
	}
	$keys=array(__SERVER_HOST__,__SERVER_PORT__,__SERVER_STACKS__,
		__BROADCAST_ENABLED__,__BROADCAST_PORT__,__BROADCAST_DISCOVERY__,__BROADCAST_SYNCHRONIZE__,
		__DEBUG_COMM__,__DEBUG_SIGNAL__,__DEBUG_PROCESS__,__DEBUG_TRACE__,__DEBUG_MAXLINES__,__DEBUG_PERCENT__,
		__SHELL_HISTORY__,__SHELL_MAXLINES__,
		__TIMEOUT_COMM__,__TIMEOUT_CHILDS__,__TIMEOUT_PIPES__,__TIMEOUT_WAIT__,__TIMEOUT_SERVER__,__TIMEOUT_SEMAPHORE__,
		__POLLING_COMM__,__POLLING_CHILDS__,__POLLING_PIPES__,__POLLING_WAIT__,__POLLING_SERVER__,__POLLING_MONITOR__,__POLLING_SCHEDULER__,__POLLING_BROADCAST__,
		__RETRIES_COMM__,__RETRIES_CHILDS__,__RETRIES_PIPES__);
	foreach($keys as $key) if(is_null(getNode($key,$config))) xml_error(__CUOTE__.$key.__CUOTE__.__SPACE__.__NOT_FOUND__,"","",$file);
	$keys=array(__BROADCAST_ENABLED__,__DEBUG_COMM__,__DEBUG_SIGNAL__,__DEBUG_PROCESS__,__DEBUG_TRACE__);
	foreach($keys as $key) {
		$key2=explode("/",$key);
		$count=count($key2);
		$val=eval_bool(getNode($key,$config));
		if($count==1) $config[$key2[0]]=$val;
		elseif($count==2) $config[$key2[0]][$key2[1]]=$val;
		elseif($count==3) $config[$key2[0]][$key2[1]][$key2[2]]=$val;
		elseif($count==4) $config[$key2[0]][$key2[1]][$key2[2]][$key2[3]]=$val;
		elseif($count==5) $config[$key2[0]][$key2[1]][$key2[2]][$key2[3]][$key2[4]]=$val;
		else show_php_error(array("phperror"=>"Long path '$key' unsuported"));
	}
	return $config;
}

function check_monitor($monitor,$file) {
	//~ if(!is_array($monitor)) xml_error(__MONITOR_NOT_FOUND__,"","",$file);
	if(is_array($monitor)) {
		foreach($monitor as $node=>$tasks) {
			if(limpiar_key($node)==__MONITOR_NODE__) {
				if(!is_array($tasks)) xml_error(__MONITOR_NOT_FOUND__,"","",$file);
				foreach($tasks as $task=>$options) {
					if(limpiar_key($task)==__MONITOR_TASK__) {
						if(!is_array($options)) xml_error(__TASK_NOT_FOUND__,"","",$file);
						$interval=getNode(__MONITOR_INTERVAL__,$options);
						$frequency=getNode(__MONITOR_FREQUENCY__,$options);
						if(!$interval && !$frequency) xml_error(__CUOTE__.__MONITOR_INTERVAL__.__CUOTE__.__SPACE__.__OR__.__SPACE__.
							__CUOTE__.__MONITOR_FREQUENCY__.__CUOTE__.__SPACE__.__NOT_FOUND__,"","",$file);
						if($interval!==null) {
							list($interval,$error)=str_replace_with_vars($interval,config_array_vars());
							if($error) xml_error(__CUOTE__.__MONITOR_INTERVAL__.__CUOTE__.__SPACE__.__VALUE_ERROR__,"","",$file);
							if(!is_numeric($interval)) xml_error(__CUOTE__.__MONITOR_INTERVAL__.__CUOTE__.__SPACE__.__NOT_NUMERIC__,"","",$file);
							if($interval<=0) xml_error(__CUOTE__.__MONITOR_INTERVAL__.__CUOTE__.__SPACE__.__GREATER_ZERO__,"","",$file);
						}
						if($frequency!==null) {
							list($frequency,$error)=str_replace_with_vars($frequency,config_array_vars());
							if($error) xml_error(__CUOTE__.__MONITOR_INTERVAL__.__CUOTE__.__SPACE__.__VALUE_ERROR__,"","",$file);
							if(!is_numeric($frequency)) xml_error(__CUOTE__.__MONITOR_FREQUENCY__.__CUOTE__.__SPACE__.__NOT_NUMERIC__,"","",$file);
							if($frequency<=0) xml_error(__CUOTE__.__MONITOR_FREQUENCY__.__CUOTE__.__SPACE__.__GREATER_ZERO__,"","",$file);
						}
						__process_select_check($options,$file);
					}
				}
			}
		}
	}
}

function check_scheduler($scheduler,$file) {
	//~ if(!is_array($scheduler)) xml_error(__SCHEDULER_NOT_FOUND__,"","",xml_real_file(__XML_SCHEDULER__));
	if(is_array($scheduler)) {
		foreach($scheduler as $node=>$options) {
			if(limpiar_key($node)==__SCHEDULER_NODE__) {
				if(!is_array($options)) xml_error(__SCHEDULER_NOT_FOUND__,"","",$file);
				if(!getNode(__SCHEDULER_HASH__,$options)) xml_error(__CUOTE__.__SCHEDULER_NODE__.__CUOTE__.__SPACE__.__REQUIRES__.__SPACE__.
					__CUOTE__.__SCHEDULER_HASH__.__CUOTE__,"","",$file);
				__process_select_check($options,$file);
			}
		}
	}
}
?>