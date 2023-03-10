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
function comm($cmd,$host_extra="",$port_extra="") {
	if(getNode(__DEBUG_COMM__)) addlog("comm cmd=$cmd",__DEBUG_LOG__);
	static $sockets=array();
	$polling_sec=intval(getNode(__POLLING_COMM__)/1000000);
	$polling_usec=intval(getNode(__POLLING_COMM__)%1000000);
	$timeout_sec=floatval(getNode(__TIMEOUT_COMM__)/1000000);
	$hash=md5(serialize(array($host_extra,$port_extra)));
	if(!isset($sockets[$hash])) $sockets[$hash]=null;
	$retries=getNode(__RETRIES_COMM__);
	while($retries>0) {
		if(!$sockets[$hash]) {
			$host=getNode(__SERVER_HOST__);
			$port=getNode(__SERVER_PORT__);
			if(getNode(__DEBUG_COMM__)) addlog("comm connecting to host=".($host_extra?$host_extra:$host)." port=".($port_extra?$port_extra:$port),__DEBUG_LOG__);
			$sockets[$hash]=socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
			capture_next_error();
			socket_connect($sockets[$hash],$host_extra?$host_extra:$host,$port_extra?$port_extra:$port);
			$error=get_clear_error();
			if($error) {
				if(getNode(__DEBUG_COMM__)) addlog("comm connect error=$error",__DEBUG_LOG__);
				socket_close_protected($sockets[$hash]);
			}
		} else {
			if(getNode(__DEBUG_COMM__)) addlog("comm connection recycled ",__DEBUG_LOG__);
		}
		if($sockets[$hash]) {
			if(getNode(__DEBUG_COMM__)) addlog("comm write cmd=$cmd",__DEBUG_LOG__);
			capture_next_error();
			socket_write($sockets[$hash],$cmd.__EOL__.__CRC32_CMD__.__EOL__);
			$error=get_clear_error();
			if($error) {
				if(getNode(__DEBUG_COMM__)) addlog("comm write error=$error",__DEBUG_LOG__);
				socket_close_protected($sockets[$hash]);
			}
		}
		$output=__NONE__;
		if($sockets[$hash]) {
			if(getNode(__DEBUG_COMM__)) addlog("comm read",__DEBUG_LOG__);
			$timestamp=microtime(true);
			while($sockets[$hash]) {
				$read=array($sockets[$hash]);
				$write=null;
				$except=null;
				capture_next_error();
				$ready=socket_select($read,$write,$except,$polling_sec,$polling_usec);
				$error=get_clear_error();
				if(!$error && $ready) {
					capture_next_error();
					$temp=socket_read($sockets[$hash],__MAXSIZE__);
					$error=get_clear_error();
					if($error || !$temp) {
						if(getNode(__DEBUG_COMM__)) addlog("comm read error=$error",__DEBUG_LOG__);
						socket_close_protected($sockets[$hash]);
					} else {
						$output.=$temp;
						$pos=strpos($output,__CRC32_CMD__.__EQUAL__);
						if($pos!==false) {
							$crc32=trim(substr($output,$pos+strlen(__CRC32_CMD__.__EQUAL__)));
							$output=substr($output,0,$pos);
							if($crc32==crc32($output)) {
								if(getNode(__DEBUG_COMM__)) addlog("comm output=$output",__DEBUG_LOG__);
								break;
							} else {
								if(getNode(__DEBUG_COMM__)) addlog("comm crc32 error for cmd=$cmd",__DEBUG_LOG__);
								socket_close_protected($sockets[$hash]);
							}
						}
					}
				}
				if($timestamp+$timeout_sec<=microtime(true)) {
					if(getNode(__DEBUG_COMM__)) addlog("comm timeout reading cmd=$cmd",__DEBUG_LOG__);
					socket_close_protected($sockets[$hash]);
				}
			}
			if(getNode(__DEBUG_TRACE__)) addlog_trace("comm",(microtime(true)-$timestamp)*1000000,getNode(__TIMEOUT_COMM__),__TRACE_LOG__);
		}
		if($sockets[$hash]) break;
		$retries--;
	}
	if(getNode(__DEBUG_TRACE__)) addlog_trace("comm",getNode(__RETRIES_COMM__)-$retries,getNode(__RETRIES_COMM__),__TRACE_LOG__);
	return $sockets[$hash]?$output:false;
}

function comm_get_array($cmd,$host="",$port="") {
	$result=array();
	$retries=getNode(__RETRIES_COMM__);
	while($retries>0) {
		$buffer=comm($cmd,$host,$port);
		if($buffer!==false) {
			$buffer=explode(__EOL__,$buffer);
			$number=intval($buffer[0]);
			$count=count($buffer);
			if($number+2==$count) {
				for($i=1;$i<=$number;$i++) $result[]=$buffer[$i];
				break;
			}
		}
		$retries--;
	}
	if(getNode(__DEBUG_TRACE__)) addlog_trace("comm_get_array",getNode(__RETRIES_COMM__)-$retries,getNode(__RETRIES_COMM__),__TRACE_LOG__);
	return $result;
}

function comm_and_wait($cmd,$host="",$port="") {
	if(getNode(__DEBUG_COMM__)) addlog("comm_and_wait cmd=$cmd",__DEBUG_LOG__);
	$input0=strtok($cmd,__SPACE__);
	$input1=strtok(__NONE__);
	$wait=in_array($input0,array(__START_CMD__,__STOP_CMD__));
	if($wait) {
		$childs=comm_get_array(__GET_CMD__.__SPACE__.__CHILD_ARG__,$host,$port);
		foreach($childs as $key=>$val) {
			$val=child_key_val($val);
			$childs[$key]=$val[0];
		}
		foreach($childs as $key=>$childhash) {
			$childalias=child_alias($childhash);
			$childpid=child_pid($childhash);
			if($childalias==$input1 || limpiar_key($childalias)==$input1 || $childpid==$input1) {
				// NOTHING TO DO
			} else {
				unset($childs[$key]);
			}
		}
		$result=false;
		if(count($childs)) {
			$expected=array(__START_CMD__=>__STATUS_RUN__,__STOP_CMD__=>__STATUS_STOP__);
			$retries=getNode(__RETRIES_COMM__);
			while($retries>0) {
				if(semaphore_acquire(__SEMAPHORE_COMM__,getNode(__TIMEOUT_SEMAPHORE__))) {
					if(getNode(__DEBUG_COMM__)) addlog("comm_and_wait acquire semaphore for $cmd",__DEBUG_LOG__);
					$result=comm($cmd,$host,$port);
					$timeout=getNode(__TIMEOUT_WAIT__);
					while($timeout>0) {
						foreach($childs as $key=>$childhash) if(comm(__GET_CMD__.__SPACE__.__CHILD_ARG__.__SPACE__.$childhash,$host,$port)==$expected[$input0].__EOL__) unset($childs[$key]);
						if(!count($childs)) break;
						$timeout-=usleep_protected(getNode(__POLLING_WAIT__));
					}
					if(getNode(__DEBUG_TRACE__)) addlog_trace("comm_and_wait",getNode(__TIMEOUT_WAIT__)-$timeout,getNode(__TIMEOUT_WAIT__),__TRACE_LOG__);
					if(getNode(__DEBUG_COMM__)) addlog("comm_and_wait release semaphore for $cmd",__DEBUG_LOG__);
					semaphore_release(__SEMAPHORE_COMM__);
					if(!count($childs)) break;
				}
				$retries--;
			}
			if(getNode(__DEBUG_TRACE__)) addlog_trace("comm_and_wait",getNode(__RETRIES_COMM__)-$retries,getNode(__RETRIES_COMM__),__TRACE_LOG__);
			$result=comm(__STATUS_CMD__.__SPACE__.$input1,$host,$port);
		}
	} else {
		$result=comm($cmd,$host,$port);
	}
	return $result;
}

function comm_human($cmd,$host="",$port="") {
	$result=comm($cmd,$host,$port);
	if($result===false) $result=__SERVER_NOT_RUNNING__.__EOL__;
	return $result;
}
?>