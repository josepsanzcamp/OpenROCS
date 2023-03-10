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
class pipe {
    var $file;
    var $hash;
    var $sem0;
	var $sem1;

	function __construct() {
		$this->file=get_temp_file();
		$this->hash=md5($this->file);
		$this->sem0=get_temp_file();
		$this->sem1=get_temp_file();
		garbage_collector_add_file($this->file);
		garbage_collector_add_file($this->sem0);
		garbage_collector_add_file($this->sem1);
	}

    function write($data,$sync=true) {
		$result=false;
		$retries=getNode(__RETRIES_PIPES__);
		while($retries>0) {
			if(semaphore_acquire($this->sem0,getNode(__TIMEOUT_SEMAPHORE__))) {
				$timeout=getNode(__TIMEOUT_PIPES__);
				while(file_exists_protected($this->file) && $timeout>0) $timeout-=usleep_protected(getNode(__POLLING_PIPES__));
				if(getNode(__DEBUG_TRACE__)) addlog_trace("pipe->write",getNode(__TIMEOUT_PIPES__)-$timeout,getNode(__TIMEOUT_PIPES__),__TRACE_LOG__);
				if($timeout>0 && !file_exists_protected($this->file)) {
					file_put_contents($this->file,$data.__EOL__.$this->hash.__EOL__);
					$result=true;
					if($sync) {
						while(file_exists_protected($this->file) && $timeout>0) $timeout-=usleep_protected(getNode(__POLLING_PIPES__));
						if(getNode(__DEBUG_TRACE__)) addlog_trace("pipe->write",getNode(__TIMEOUT_PIPES__)-$timeout,getNode(__TIMEOUT_PIPES__),__TRACE_LOG__);
						if(file_exists_protected($this->file)) $result=false;
					}
				}
				semaphore_release($this->sem0);
				if($result!==false) break;
			}
			$retries--;
		}
		if(getNode(__DEBUG_TRACE__)) addlog_trace("pipe->write",getNode(__RETRIES_PIPES__)-$retries,getNode(__TIMEOUT_PIPES__),__TRACE_LOG__);
		return $result;
	}

	function read() {
		$data=false;
		$retries=getNode(__RETRIES_PIPES__);
		while($retries>0) {
			if(semaphore_acquire($this->sem1,getNode(__TIMEOUT_SEMAPHORE__))) {
				$timeout=getNode(__TIMEOUT_PIPES__);
				while(!file_exists_protected($this->file) && $timeout>0) $timeout-=usleep_protected(getNode(__POLLING_PIPES__));
				if(getNode(__DEBUG_TRACE__)) addlog_trace("pipe->read",getNode(__TIMEOUT_PIPES__)-$timeout,getNode(__TIMEOUT_PIPES__),__TRACE_LOG__);
				if($timeout>0 && file_exists_protected($this->file)) {
					while($timeout>0) {
						$data=file_get_contents($this->file);
						$pos=strpos($data,__EOL__.$this->hash.__EOL__);
						if($pos!==false) break;
						$timeout-=usleep_protected(getNode(__POLLING_PIPES__));
					}
					if(getNode(__DEBUG_TRACE__)) addlog_trace("pipe->read",getNode(__TIMEOUT_PIPES__)-$timeout,getNode(__TIMEOUT_PIPES__),__TRACE_LOG__);
					if($pos!==false) {
						unlink_protected($this->file);
						$data=substr($data,0,$pos);
					} else {
						$data=false;
					}
				}
				semaphore_release($this->sem1);
				if($data!==false) break;
			}
			$retries--;
		}
		if(getNode(__DEBUG_TRACE__)) addlog_trace("pipe->read",getNode(__RETRIES_PIPES__)-$retries,getNode(__RETRIES_PIPES__),__TRACE_LOG__);
		return $data;
	}
}
?>