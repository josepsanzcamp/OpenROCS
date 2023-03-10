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
$pipe[1]->write(__SCHEDULER_START__.__EOL__);
is_stopped(1);
// DEFINE AND PROGRAM SHUTDOWN HANDLER
function shutdown_handler() {
	garbage_collector_empty();
}
register_shutdown_function("shutdown_handler");
// INITIALIZE THE INTERNAL NEEDED STRUCTURE
foreach($scheduler as $node=>$options) {
	if(limpiar_key($node)==__SCHEDULER_NODE__ && $node==$mychildnode) {
		if(!is_array($options[__SCHEDULER_HASH__])) $options[__SCHEDULER_HASH__]=array();
		$options[__SCHEDULER_LASTHASH__]="";
		$options[__SCHEDULER_TRIGGERHASH__]="";
		$scheduler[$node]=$options;
	} else {
		unset($scheduler[$node]);
	}
}
// BEGIN THE LOOP
$stop1=is_stopped();
while(1) {
	$stop2=is_stopped();
	if(!$stop2) {
		if($stop1) {
			foreach($scheduler as $node=>$options) {
				if(limpiar_key($node)==__SCHEDULER_NODE__ && $node==$mychildnode) {
					$options[__SCHEDULER_LASTHASH__]="";
					$options[__SCHEDULER_TRIGGERHASH__]="";
					$scheduler[$node]=$options;
				}
			}
		}
		foreach($scheduler as $node=>$options) {
			if(limpiar_key($node)==__SCHEDULER_NODE__ && $node==$mychildnode) {
				if(!$options[__SCHEDULER_TRIGGERHASH__]) {
					$hash=array();
					foreach(server_array_vars() as $key=>$val) if(in_array($key,$options[__SCHEDULER_HASH__])) $hash[$key]=$val;
					$hash=md5(serialize($hash));
					if($options[__SCHEDULER_LASTHASH__]!=$hash) $options[__SCHEDULER_TRIGGERHASH__]=$hash;
				}
				if($options[__SCHEDULER_TRIGGERHASH__]) {
					if(process($options)) {
						$options[__SCHEDULER_LASTHASH__]=$options[__SCHEDULER_TRIGGERHASH__];
						$options[__SCHEDULER_TRIGGERHASH__]="";
					}
					$scheduler[$node]=$options;
				}
			}
		}
	} else {
		if(!$stop1) {
			foreach($scheduler as $node=>$options) {
				if(limpiar_key($node)==__SCHEDULER_NODE__ && $node==$mychildnode) {
					process_reset($options);
					$scheduler[$node]=$options;
				}
			}
		}
	}
	$stop1=$stop2;
	usleep_protected(getNode(__POLLING_SCHEDULER__));
}
die();
?>