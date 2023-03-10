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
// DEFINE TICK HANDLER (WATCHDOG)
function tick_handler($update=null) {
	// UPDATE CONTROL
	static $childs=null;
	if(!is_null($update)) {
		$childs=$update;
		return;
	}
	// TIME CONTROL
	static $time1=0;
	$time2=microtime(true);
	if($time2>=$time1+1) {
		// NORMAL OPERATION
		global $mychildhash;
		$mychildhost=child_host($mychildhash);
		$mychildport=child_port($mychildhash);
		$mychildpid=child_pid($mychildhash);
		foreach($childs as $childhash) {
			if(child_host($childhash)==$mychildhost && child_port($childhash)==$mychildport) {
				$childpid=child_pid($childhash);
				if($childpid!=$mychildpid) {
					if(!child_exists($childpid)) {
						$childstr=child_string($childhash);
						$mychildstr=child_string($mychildhash);
						addlog($mychildstr.__SPACE__.__NOT_DETECT__.__SPACE__.$childstr,__ERROR_LOG__);
						die();
					}
				}
			}
		}
		// TIME CONTROL
		$time1=$time2;
	}
}
// DEFINE AND PROGRAM THE SIGNAL HANDLER
function signal_handler($signo) {
	switch($signo) {
		case __SIGNAL_TERM__:
		case __SIGNAL_INT__:
			die();
			break;
		case __SIGNAL_CHLD__:
			$status=0;
			pcntl_wait($status);
			global $signal_handler_pcntl_wait;
			$signal_handler_pcntl_wait=0;
			break;
		case __SIGNAL_USR1__:
			global $mychildhash;
			list($cmd,$arg)=child_read($mychildhash);
			if(getNode(__DEBUG_SIGNAL__)) addlog("signal_handler cmd=$cmd",__DEBUG_LOG__);
			switch($cmd) {
				case __WATCHDOG_START__:
					tick_handler($arg);
					register_tick_function("tick_handler");
					child_response($mychildhash);
					break;
				case __WATCHDOG_STOP__:
					capture_next_error();
					unregister_tick_function("tick_handler");
					$error=get_clear_error();
					child_response($mychildhash,$error?0:1);
					break;
				case __PROCESS_START__:
					is_stopped(0);
					child_response($mychildhash);
					break;
				case __PROCESS_STOP__:
					is_stopped(1);
					child_response($mychildhash);
					break;
			}
			break;
	}
}
pcntl_signal(__SIGNAL_TERM__,"signal_handler");
pcntl_signal(__SIGNAL_INT__,"signal_handler");
pcntl_signal(__SIGNAL_CHLD__,"signal_handler");
pcntl_signal(__SIGNAL_USR1__,"signal_handler");
// DEFINE IS_STOPPED HELPER
function is_stopped($update=null) {
	static $stopped=0;
	if(!is_null($update)) $stopped=$update;
	return $stopped;
}
?>