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
is_stopped(1);
// DEFINE AND PROGRAM SHUTDOWN HANDLER
function shutdown_handler() {
	global $socket;
	if($socket) socket_close_protected($socket);
	garbage_collector_empty();
}
register_shutdown_function("shutdown_handler");
// NORMAL OPERATION
$port_broadcast=getNode(__BROADCAST_PORT__);
$discovery=getNode(__BROADCAST_DISCOVERY__);
$synchronize=getNode(__BROADCAST_SYNCHRONIZE__);
$host_server=getNode(__SERVER_HOST__);
$port_server=getNode(__SERVER_PORT__);
$name_server=getNode(__SERVER_NAME__);
// START BROADCAST SERVER
$socket=socket_create(AF_INET,SOCK_DGRAM,SOL_UDP);
socket_set_option($socket,SOL_SOCKET,SO_REUSEADDR,1);
capture_next_error();
socket_bind($socket,__BROADCAST_BIND__,$port_broadcast);
$error=get_clear_error();
if($error) {
	$pipe[1]->write(__BROADCAST_ERROR__.__EOL__);
	show_php_error();
} else {
	$pipe[1]->write(__BROADCAST_START__.__SPACE__.$port_broadcast.__EOL__);
	$polling_sec=intval(getNode(__POLLING_BROADCAST__)/1000000);
	$polling_usec=intval(getNode(__POLLING_BROADCAST__)%1000000);
	socket_set_option($socket,SOL_SOCKET,SO_RCVTIMEO,array("sec"=>$polling_sec,"usec"=>$polling_usec));
	$server=0;
	$clients=array();
	$stop1=is_stopped();
	while($socket) {
		$stop2=is_stopped();
		if(!$stop2) {
			if($stop1) {
				$server=0;
				foreach($clients as $client=>$timestamp) $clients[$client]=0;
			}
			capture_next_error();
			$ready=socket_recvfrom($socket,$input,__MAXSIZE__,0,$host_recv,$port_recv);
			$error=get_clear_error();
			if(!$error && $ready) {
				$input0=strtok($input,__SPACE__);
				if($input0==__BROADCAST_HELO__) {
					$myserver=($name_server?$name_server.__ARROBA__:__NONE__).$host_server.__TWO_POINTS__.$port_server;
					$output=__BROADCAST_ACK__.__SPACE__.$myserver;
					socket_sendto($socket,$output,strlen($output),0,$host_recv,$port_broadcast);
				} elseif($input0==__BROADCAST_ACK__) {
					$myserver=($name_server?$name_server.__ARROBA__:__NONE__).$host_server.__TWO_POINTS__.$port_server;
					$input1=strtok(__NONE__);
					if(!isset($clients[$input1]) && $input1!=$myserver) $clients[$input1]=0;
				}
			}
			if($server+$discovery<=microtime(true)) {
				// SEND BROADCAST PACKET
				$socket2=socket_create(AF_INET,SOCK_DGRAM,SOL_UDP);
				socket_set_option($socket2,SOL_SOCKET,SO_BROADCAST,1);
				capture_next_error();
				socket_sendto($socket2,__BROADCAST_HELO__,strlen(__BROADCAST_HELO__),0,__BROADCAST_SEND__,$port_broadcast);
				$error=get_clear_error();
				socket_close_protected($socket2);
				$server=microtime(true);
			}
			foreach($clients as $client=>$timestamp) {
				if($timestamp+$synchronize<=microtime(true)) {
					$clients[$client]=microtime(true);
					$name_client=strtok($client,__ARROBA__);
					$host_client=strtok(__TWO_POINTS__);
					$port_client=strtok(__NONE__);
					if(!$name_client || !$host_client || !$port_client) {
						$name_client="";
						$host_client=strtok($client,__TWO_POINTS__);
						$port_client=strtok(__NONE__);
					}
					$theserver=__ARROBA__.($name_client?$name_client:$host_client.__TWO_POINTS__.$port_client);
					$stacks=comm_get_array(__GET_CMD__.__SPACE__.$timestamp,$host_client,$port_client);
					$stacks2=comm_get_array(__GET_CMD__);
					foreach($stacks as $stack) {
						$stack2=limpiar_stack($stack);
						if($stack==$stack2.$theserver && $stack2!=__CHILD_ARG__) {
							if(!in_array($stack,$stacks2)) comm(__ADD_CMD__.__SPACE__.$stack);
							$datas=comm_get_array(__GET_CMD__.__SPACE__.$stack.__SPACE__.$timestamp,$host_client,$port_client);
							foreach($datas as $data) {
								$key=strtok($data,__EQUAL__);
								$val=strtok(__NONE__);
								$is_remove=(substr($key,-1,1)==__NEGATION__);
								if($is_remove) $key=substr($key,0,-1);
								if(!$is_remove) comm(__UPDATE_CMD__.__SPACE__.$stack.__SPACE__.$key.__EQUAL__.$val);
								if($is_remove) comm(__REMOVE_CMD__.__SPACE__.$stack.__SPACE__.$key);
							}
						}
					}
				}
			}
		} else {
			usleep_protected(getNode(__POLLING_BROADCAST__));
		}
		$stop1=$stop2;
	}
}
die();
?>