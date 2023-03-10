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
	// *************************************************************************
	// START WATCHDOGS AND MORE
	// *************************************************************************
	foreach($childs as $childhash) {
		child_send($childhash,__WATCHDOG_START__,$childs);
		$childstr=child_string($childhash);
		echo __WATCHDOG_STARTED__.__SPACE__.$childstr.__EOL__;
	}
	// START BROADCAST AND MONITORS OR BROADCAST AND SCHEDULERS
	$expected=array(__BROADCAST_NODE__=>__STATUS_RUN__,__MONITOR_NODE__=>__STATUS_INIT__);
	if(!isset($monitor) || !is_array($monitor) || !count($monitor)) $expected=array(__BROADCAST_NODE__=>__STATUS_RUN__,__SCHEDULER_NODE__=>__STATUS_RUN__);
	foreach($childs as $childhash) {
		$childnode=limpiar_key(child_node($childhash));
		if(array_key_exists($childnode,$expected)) {
			child_send($childhash,__PROCESS_START__);
			comm(__UPDATE_CMD__.__SPACE__.__CHILD_ARG__.__SPACE__.$childhash.__EQUAL__.$expected[$childnode]);
			$childstr=child_string($childhash);
			echo __SENDING_START__.__SPACE__.$childstr.__EOL__;
		}
	}
	// WAIT UNTIL SERVER RESPONDS
	$timeout=getNode(__TIMEOUT_WAIT__);
	while(comm(__STATUS_CMD__)!=__SERVER_RUNNING__.__EOL__ && $timeout>0) $timeout-=usleep_protected(getNode(__POLLING_WAIT__));
	if(getNode(__DEBUG_TRACE__)) addlog_trace("start",getNode(__TIMEOUT_WAIT__)-$timeout,getNode(__TIMEOUT_WAIT__),__TRACE_LOG__);
	if($timeout<=0) show_php_error(array("phperror"=>"Unable to start server"));
}
?>