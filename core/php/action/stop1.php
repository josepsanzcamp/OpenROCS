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
	$numwaits=isset($childs)?count($childs):0;
	// GET ALL CHILDS AND FILTER ONLY THE KEYS
	$childs=comm_get_array(__GET_CMD__.__SPACE__.__CHILD_ARG__);
	foreach($childs as $key=>$val) {
		$val=child_key_val($val);
		$childs[$key]=$val[0];
	}
	// STOP BROADCAST, MONITORS AND SCHEDULERS
	$expected=array(__BROADCAST_NODE__,__MONITOR_NODE__,__SCHEDULER_NODE__);
	foreach($childs as $childhash) {
		$childnode=limpiar_key(child_node($childhash));
		if(in_array($childnode,$expected)) {
			child_send($childhash,__PROCESS_STOP__);
			comm(__UPDATE_CMD__.__SPACE__.__CHILD_ARG__.__SPACE__.$childhash.__EQUAL__.__STATUS_STOP__);
			$childstr=child_string($childhash);
			echo __SENDING_STOP__.__SPACE__.$childstr.__EOL__;
		}
	}
	// STOP WATCHDOGS
	foreach($childs as $childhash) {
		while(!child_send($childhash,__WATCHDOG_STOP__));
		$childstr=child_string($childhash);
		echo __WATCHDOG_STOPPED__.__SPACE__.$childstr.__EOL__;
	}
}
?>