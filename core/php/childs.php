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
class child_helper {
	var $pid;
    var $stdout;
    var $stderr;
    var $timestamp;

	function __construct($cmd) {
		$process=child_exec($cmd);
		$this->pid=$process[__CHILD_PID__];
		$this->stdout=$process[__CHILD_STDOUT__];
		$this->stderr=$process[__CHILD_STDERR__];
		$this->timestamp=$process[__CHILD_TIME__];
	}

	function __destruct() {
		unlink_protected($this->stdout);
		garbage_collector_remove_file($this->stdout);
		unlink_protected($this->stderr);
		garbage_collector_remove_file($this->stderr);
	}

	function get_pid() {
		return $this->pid;
	}

	function get_stdout() {
		$buffer=file_get_contents_protected($this->stdout);
		unlink_protected($this->stdout);
		garbage_collector_remove_file($this->stdout);
		return $buffer;
	}

	function get_stderr() {
		$buffer=file_get_contents_protected($this->stderr);
		unlink_protected($this->stderr);
		garbage_collector_remove_file($this->stderr);
		return $buffer;
	}

	function get_timestamp() {
		return $this->timestamp;
	}

	function set_timestamp($timestamp) {
		$this->timestamp=$timestamp;
	}

	function exists() {
		return child_exists($this->pid);
	}

	function elapsed_time($timestamp="") {
		if(!$timestamp) $timestamp=microtime(true);
		return $timestamp-$this->timestamp;
	}

	function wait_while_exists() {
		child_wait($this->pid);
	}

	function kill($signal,$recursive=false) {
		return child_kill($this->pid,$signal,$recursive);
	}

	function sigterm_sigkill() {
		child_sigterm_sigkill($this->pid);
		$this->__destruct();
	}
}

function child_kill($pid,$signal,$recursive=false) {
	if(!child_exists($pid)) return false;
	if($recursive) {
		$pids=__child_kill_recursive($pid);
		foreach($pids as $pid) child_kill($pid,$signal);
	} else {
		$process=new child_helper("kill -$signal $pid");
		$process->wait_while_exists();
		$process->__destruct();
	}
	return true;
}

function __child_kill_recursive($pid) {
	$pids=array($pid);
	$cmd="ps --ppid $pid -o pid=";
	$process=new child_helper($cmd);
	$process->wait_while_exists();
	$stdout=$process->get_stdout();
	$stderr=$process->get_stderr();
	if($stderr!="") show_php_error(array("phperror"=>"Command '$cmd' generates stderr","details"=>$stderr));
	if($stdout!="") {
		$stdout=explode(__EOL__,trim($stdout));
		foreach($stdout as $pid) $pids=array_merge($pids,__child_kill_recursive($pid));
	}
	return $pids;
}

function child_wait($pid) {
	while(child_exists($pid)) usleep_protected(getNode(__POLLING_CHILDS__));
}

function child_sigterm_sigkill($pid) {
	child_kill($pid,__SIGNAL_TERM__,true);
	$timeout=getNode(__TIMEOUT_CHILDS__);
	while(child_exists($pid) && $timeout>0) $timeout-=usleep_protected(getNode(__POLLING_CHILDS__));
	if(getNode(__DEBUG_TRACE__)) addlog_trace("child_sigterm_sigkill",getNode(__TIMEOUT_CHILDS__)-$timeout,getNode(__TIMEOUT_CHILDS__),__TRACE_LOG__);
	if(child_exists($pid)) child_kill($pid,__SIGNAL_KILL__,true);
}

function child_exec($cmd) {
	static $disableds_string=null;
	static $disableds_array=null;
	if($disableds_string===null && $disableds_array===null) {
		$disableds_string=ini_get("disable_functions");
		$disableds_array=$disableds_string?explode(",",$disableds_string):array();
		foreach($disableds_array as $key=>$val) $disableds_array[$key]=strtolower(trim($val));
	}
	$stdout=get_temp_file();
	$stderr=get_temp_file();
	$timestamp=microtime(true);
	if(trim($cmd)=="") $cmd="echo ''";
	$cmd="($cmd) 1>$stdout 2>$stderr & echo \$!";
	if(!in_array("passthru",$disableds_array)) {
		ob_start();
		passthru($cmd);
		$pid=ob_get_clean();
	} elseif(!in_array("system",$disableds_array)) {
		ob_start();
		system($cmd);
		$pid=ob_get_clean();
	} elseif(!in_array("exec",$disableds_array)) {
		$pid=array();
		exec($cmd,$pid);
		$pid=implode("\n",$pid)."\n";
	} elseif(!in_array("shell_exec",$disableds_array)) {
		ob_start();
		$pid=shell_exec($cmd);
		ob_get_clean();
	} else {
		show_php_error(array("phperror"=>"Unknown command shell in child_exec","details"=>"ini_get(disable_functions)=${disableds_string}"));
	}
	$pid=trim($pid);
	if(!$pid) $pid="0";
	garbage_collector_add_proc($pid);
	garbage_collector_add_file($stdout);
	garbage_collector_add_file($stderr);
	return array(__CHILD_PID__=>$pid,__CHILD_STDOUT__=>$stdout,__CHILD_STDERR__=>$stderr,__CHILD_TIME__=>$timestamp);
}

function child_exists($pid) {
	$exists=file_exists_protected(__PROC_DIR__.$pid);
	if($exists) {
		capture_next_error();
		$stat=file_get_contents(__PROC_DIR__.$pid.__PROC_STAT__);
		$error=get_clear_error();
		if(!$error) {
			$stat=explode(" ",$stat);
			if(isset($stat[2]) && $stat[2]=="Z") $exists=false;
		}
	}
	if(!$exists) garbage_collector_remove_proc($pid);
	return $exists;
}

function child2hash($host,$port,$name,$pid,$node,$alias,$pipe) {
	$hash=base64_encode(serialize(array(__HASH_HOST__=>$host,__HASH_PORT__=>$port,__HASH_NAME__=>$name,__HASH_PID__=>$pid,__HASH_NODE__=>$node,__HASH_ALIAS__=>$alias,__HASH_PIPE__=>$pipe)));
	$hash=str_replace("=","",$hash);
	return $hash;
}

function hash2array($hash) {
	while(strlen($hash)%4!=0) $hash.="=";
	$array=unserialize(base64_decode($hash));
	return $array;
}

function child_host($hash) {
	$array=hash2array($hash);
	return $array[__HASH_HOST__];
}

function child_port($hash) {
	$array=hash2array($hash);
	return $array[__HASH_PORT__];
}

function child_name($hash) {
	$array=hash2array($hash);
	return $array[__HASH_NAME__];
}

function child_pid($hash) {
	$array=hash2array($hash);
	return $array[__HASH_PID__];
}

function child_node($hash) {
	$array=hash2array($hash);
	return $array[__HASH_NODE__];
}

function child_alias($hash) {
	$array=hash2array($hash);
	return $array[__HASH_ALIAS__];
}

function child_pipe($hash) {
	$array=hash2array($hash);
	return $array[__HASH_PIPE__];
}

function child_string($hash) {
	$array=hash2array($hash);
	return $array[__HASH_ALIAS__]."[".$array[__HASH_PID__].__ARROBA__.($array[__HASH_NAME__]?$array[__HASH_NAME__]:$array[__HASH_HOST__].__TWO_POINTS__.$array[__HASH_PORT__])."]";
}

function child_key_val($hash) {
	$key=strtok($hash,__EQUAL__);
	$val=strtok(__NONE__);
	return array($key,$val);
}

function child_send($hash,$cmd,$arg="") {
	$sem=get_temp_file(md5($hash));
	$pipe=child_pipe($hash);
	$pid=child_pid($hash);
	if(getNode(__DEBUG_SIGNAL__)) addlog("child_send pid=$pid, cmd=$cmd",__DEBUG_LOG__);
	$data=false;
	$retries=getNode(__RETRIES_CHILDS__);
	while($retries>0) {
		if(semaphore_acquire($sem,getNode(__TIMEOUT_SEMAPHORE__))) {
			if($pipe[0]->write(serialize(array(__PIPE_CMD__=>$cmd,__PIPE_ARG__=>$arg)),false)) {
				if(child_kill($pid,__SIGNAL_USR1__)) {
					$data=$pipe[1]->read();
					if($data!==false) {
						$data=unserialize($data);
						if(getNode(__DEBUG_SIGNAL__)) addlog("child_send data=$data",__DEBUG_LOG__);
					}
				}
			}
			semaphore_release($sem);
			if($data!==false) break;
		}
		$retries--;
	}
	if(getNode(__DEBUG_TRACE__)) addlog_trace("child_send",getNode(__RETRIES_CHILDS__)-$retries,getNode(__RETRIES_CHILDS__),__TRACE_LOG__);
	return $data;
}

function child_read($hash) {
	$pipe=child_pipe($hash);
	$data=$pipe[0]->read();
	if($data===false) return false;
	$data=unserialize($data);
	return array($data[__PIPE_CMD__],$data[__PIPE_ARG__]);
}

function child_response($hash,$data="") {
	$pipe=child_pipe($hash);
	return $pipe[1]->write(serialize($data),false);
}
?>