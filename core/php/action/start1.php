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
if(!function_exists("__start_die")) {
	function __start_die() {
		include(__STOP_PHP__);
		die();
	}
}
if(comm(__STATUS_CMD__)===false) {
	$host=getNode(__SERVER_HOST__);
	$port=getNode(__SERVER_PORT__);
	$name=getNode(__SERVER_NAME__);
	$childs=array();
	// *************************************************************************
	// LAUNCH SERVER
	// *************************************************************************
	$pipe=array(new pipe(),new pipe());
	$childpid=@pcntl_fork();
	if($childpid==-1) show_php_error(array("phperror"=>"Unable to fork a new process"));
	if($childpid==0) { include(__SERVER_PHP__); die(); }
	$childhash=child2hash($host,$port,$name,$childpid,__SERVER_NODE__,__SERVER_NODE__,$pipe);
	$childstr=child_string($childhash);
	echo __CHILD_CREATED__.__SPACE__.$childstr.__SPACE__;
	$pipe[0]->write($childhash);
	echo ($error=$pipe[1]->read());
	if(stripos($error,"error")!==false) __start_die();
	// WAIT TO SERVER
	$timeout=getNode(__TIMEOUT_WAIT__);
	while(comm(__STATUS_CMD__)!=__SERVER_RUNNING__.__EOL__ && $timeout>0) $timeout-=usleep_protected(getNode(__POLLING_WAIT__));
	if(getNode(__DEBUG_TRACE__)) addlog_trace("start",getNode(__TIMEOUT_WAIT__)-$timeout,getNode(__TIMEOUT_WAIT__),__TRACE_LOG__);
	if($timeout<=0) show_php_error(array("phperror"=>"Unable to start server"));
	// ADD SERVER CHLD
	comm(__ADD_CMD__.__SPACE__.__CHILD_ARG__.__SPACE__.$childhash.__EQUAL__.__STATUS_RUN__);
	array_push($childs,$childhash);
	// *************************************************************************
	// LAUNCH BROADCAST
	// *************************************************************************
	if(eval_bool(getNode(__BROADCAST_ENABLED__))) {
		$pipe=array(new pipe(),new pipe());
		$childpid=@pcntl_fork();
		if($childpid==-1) show_php_error(array("phperror"=>"Unable to fork a new process"));
		if($childpid==0) { include(__BROADCAST_PHP__); die(); }
		$childhash=child2hash($host,$port,$name,$childpid,__BROADCAST_NODE__,__BROADCAST_NODE__,$pipe);
		$childstr=child_string($childhash);
		echo __CHILD_CREATED__.__SPACE__.$childstr.__SPACE__;
		$pipe[0]->write($childhash);
		echo ($error=$pipe[1]->read());
		if(stripos($error,"error")!==false) __start_die();
		// ADD BROADCAST CHLD
		comm(__ADD_CMD__.__SPACE__.__CHILD_ARG__.__SPACE__.$childhash.__EQUAL__.__STATUS_STOP__);
		array_push($childs,$childhash);
	}
	// *************************************************************************
	// LAUNCH MONITOR
	// *************************************************************************
	if(isset($monitor) && is_array($monitor)) {
		foreach($monitor as $node=>$tasks) {
			if(limpiar_key($node)==__MONITOR_NODE__) {
				$pipe=array(new pipe(),new pipe());
				$childpid=@pcntl_fork();
				if($childpid==-1) show_php_error(array("phperror"=>"Unable to fork a new process"));
				if($childpid==0) { include(__MONITOR_PHP__); die(); }
				$alias=isset($tasks[__PROCESS_NAME__])?__MONITOR_NODE__."#".$tasks[__PROCESS_NAME__]:$node.(limpiar_key($node)==$node?"#0":"");
				$childhash=child2hash($host,$port,$name,$childpid,$node,$alias,$pipe);
				$childstr=child_string($childhash);
				echo __CHILD_CREATED__.__SPACE__.$childstr.__SPACE__;
				$pipe[0]->write($childhash);
				echo ($error=$pipe[1]->read());
				if(stripos($error,"error")!==false) __start_die();
				// ADD MONITOR CHLD
				comm(__ADD_CMD__.__SPACE__.__CHILD_ARG__.__SPACE__.$childhash.__EQUAL__.__STATUS_STOP__);
				array_push($childs,$childhash);
			}
		}
	}
	// *************************************************************************
	// LAUNCH SCHEDULER
	// *************************************************************************
	if(isset($scheduler) && is_array($scheduler)) {
		foreach($scheduler as $node=>$options) {
			if(limpiar_key($node)==__SCHEDULER_NODE__) {
				$pipe=array(new pipe(),new pipe());
				$childpid=@pcntl_fork();
				if($childpid==-1) show_php_error(array("phperror"=>"Unable to fork a new process"));
				if($childpid==0) { include(__SCHEDULER_PHP__); die(); }
				$alias=isset($options[__PROCESS_NAME__])?__SCHEDULER_NODE__."#".$options[__PROCESS_NAME__]:$node.(limpiar_key($node)==$node?"#0":"");
				$childhash=child2hash($host,$port,$name,$childpid,$node,$alias,$pipe);
				$childstr=child_string($childhash);
				echo __CHILD_CREATED__.__SPACE__.$childstr.__SPACE__;
				$pipe[0]->write($childhash);
				echo ($error=$pipe[1]->read());
				if(stripos($error,"error")!==false) __start_die();
				// ADD SCHEDULER CHLD
				comm(__ADD_CMD__.__SPACE__.__CHILD_ARG__.__SPACE__.$childhash.__EQUAL__.__STATUS_STOP__);
				array_push($childs,$childhash);
			}
		}
	}
}
?>