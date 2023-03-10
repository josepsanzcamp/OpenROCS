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
if(comm(__STATUS_CMD__)==__SERVER_RUNNING__.__EOL__) {
	// SEND THE TERM SIGNAL
	foreach($childs as $childhash) {
		$childpid=child_pid($childhash);
		if(child_kill($childpid,__SIGNAL_TERM__,true)) {
			$childstr=child_string($childhash);
			echo __SENDING_TERM__.__SPACE__.$childstr.__EOL__;
		}
	}
	// WAIT THE FINISH OF ALL PROCESS
	$count=count($childs);
	$total=$count;
	$timeout=getNode(__TIMEOUT_WAIT__);
	while($total>0 && $timeout>0) {
		$total=$count;
		foreach($childs as $childhash) {
			$childpid=child_pid($childhash);
			if(!child_exists($childpid)) $total--;
		}
		$timeout-=usleep_protected(getNode(__POLLING_WAIT__));
	}
	if(getNode(__DEBUG_TRACE__)) addlog_trace("stop",getNode(__TIMEOUT_WAIT__)-$timeout,getNode(__TIMEOUT_WAIT__),__TRACE_LOG__);
	// SEND KILL SIGNAL IF EXISTS PROCESSES
	if($total>0) {
		foreach($childs as $childhash) {
			$childpid=child_pid($childhash);
			if(child_kill($childpid,__SIGNAL_KILL__,true)) {
				$childstr=child_string($childhash);
				echo __SENDING_KILL__.__SPACE__.$childstr.__EOL__;
			}
		}
	}
	// WAIT UNTIL SERVER RESPONDS
	$timeout=getNode(__TIMEOUT_WAIT__);
	while(comm(__STATUS_CMD__)!==false && $timeout>0) $timeout-=usleep_protected(getNode(__POLLING_WAIT__));
	if(getNode(__DEBUG_TRACE__)) addlog_trace("stop",getNode(__TIMEOUT_WAIT__)-$timeout,getNode(__TIMEOUT_WAIT__),__TRACE_LOG__);
	if($timeout<=0) show_php_error(array("phperror"=>"Unable to stop server"));
	// WAIT THE CHILDS (IF IS NEEDED)
	$status=0;
	for($i=0;$i<$numwaits;$i++) pcntl_wait($status);
}
?>