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
include("php/signals.php");
$mychildhash=$pipe[0]->read();
garbage_collector_add_file(get_temp_file(md5($mychildhash)));
$mychildnode=child_node($mychildhash);
$pipe[1]->write(__MONITOR_START__.__EOL__);
is_stopped(1);
// DEFINE AND PROGRAM SHUTDOWN HANDLER
function shutdown_handler() {
	garbage_collector_empty();
}
register_shutdown_function("shutdown_handler");
// INITIALIZE THE INTERNAL NEEDED STRUCTURE
foreach($monitor as $node=>$tasks) {
	if(limpiar_key($node)==__MONITOR_NODE__ && $node==$mychildnode) {
		$delay=1;
		foreach($tasks as $task=>$options) {
			if(limpiar_key($task)==__MONITOR_TASK__) {
				foreach(array(__MONITOR_INTERVAL__,__MONITOR_FREQUENCY__) as $index) {
					if(isset($options[$index])) list($options[$index],$error)=str_replace_with_vars($options[$index],config_array_vars());
				}
				$isset_frequency=isset($options[__MONITOR_FREQUENCY__]);
				if($isset_frequency) $options[__MONITOR_INTERVAL__]=$options[__MONITOR_FREQUENCY__];
				$options[__MONITOR_TIMESTAMP__]=microtime(true)-$options[__MONITOR_INTERVAL__]+$delay;
				$options[__MONITOR_INIT__]=0;
				$monitor[$node][$task]=$options;
				$delay++;
			}
		}
	} else {
		unset($monitor[$node]);
	}
}
// BEGIN THE LOOP
$stop1=is_stopped();
$init1=0;
while(1) {
	$stop2=is_stopped();
	if(!$stop2) {
		if($stop1) {
			foreach($monitor as $node=>$tasks) {
				if(limpiar_key($node)==__MONITOR_NODE__ && $node==$mychildnode) {
					$delay=1;
					foreach($tasks as $task=>$options) {
						if(limpiar_key($task)==__MONITOR_TASK__) {
							$options[__MONITOR_TIMESTAMP__]=microtime(true)-$options[__MONITOR_INTERVAL__]+$delay;
							$monitor[$node][$task]=$options;
							$delay++;
						}
					}
				}
			}
		}
		$init2=1;
		foreach($monitor as $node=>$tasks) {
			if(limpiar_key($node)==__MONITOR_NODE__ && $node==$mychildnode) {
				foreach($tasks as $task=>$options) {
					if(limpiar_key($task)==__MONITOR_TASK__) {
						if($options[__MONITOR_TIMESTAMP__]+$options[__MONITOR_INTERVAL__]<=microtime(true)) {
							if(process($options)) {
								$isset_frequency=isset($options[__MONITOR_FREQUENCY__]);
								if($isset_frequency) $options[__MONITOR_TIMESTAMP__]+=$options[__MONITOR_INTERVAL__];
								if(!$isset_frequency) $options[__MONITOR_TIMESTAMP__]=microtime(true);
								$options[__MONITOR_INIT__]=1;
							}
							$monitor[$node][$task]=$options;
						}
						$init2&=$options[__MONITOR_INIT__];
					}
				}
			}
		}
		if($init1==0 && $init2==1) {
			if(comm(__UPDATE_CMD__.__SPACE__.__CHILD_ARG__.__SPACE__.$mychildhash.__EQUAL__.__STATUS_RUN__)!==false) {
				$childs=comm_get_array(__GET_CMD__.__SPACE__.__CHILD_ARG__);
				$init=count($childs);
				foreach($childs as $childhash) {
					$childhash=child_key_val($childhash);
					if(limpiar_key(child_node($childhash[0]))==__MONITOR_NODE__) $init*=($childhash[1]==__STATUS_RUN__)?1:0;
				}
				if($init) if(comm(__START_CMD__.__SPACE__.__SCHEDULER_NODE__)===false) $init2=0;
			} else {
				$init2=0;
			}
		}
		$init1=$init2;
	} else {
		if(!$stop1) {
			foreach($monitor as $node=>$tasks) {
				if(limpiar_key($node)==__MONITOR_NODE__ && $node==$mychildnode) {
					foreach($tasks as $task=>$options) {
						if(limpiar_key($task)==__MONITOR_TASK__) {
							process_reset($options);
							$monitor[$node][$task]=$options;
						}
					}
				}
			}
		}
	}
	$stop1=$stop2;
	usleep_protected(getNode(__POLLING_MONITOR__));
}
die();
?>