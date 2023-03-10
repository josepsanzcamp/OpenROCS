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
include(__START0_PHP__);
echo __SERVER_STOPPING__.__EOL__;
include(__STOP1_PHP__);
echo __SAVING_DATA__.__SPACE__;
// SAVE ALL STACKS AND VARIABLES
$stacks=comm_get_array(__GET_CMD__);
$cmds=array();
foreach($stacks as $stack) {
	if(limpiar_stack($stack)!=__CHILD_ARG__) {
		$cmds[]=__ADD_CMD__.__SPACE__.$stack;
		$datas=comm_get_array(__GET_CMD__.__SPACE__.$stack);
		foreach($datas as $data) $cmds[]=__ADD_CMD__.__SPACE__.$stack.__SPACE__.$data;
	}
}
$total2=count($cmds);
$cmds=implode(__EOL__,$cmds);
// CONTINUE
echo $total2.__SPACE__.__ENTRIES_SAVED__.__EOL__;
include(__STOP2_PHP__);
echo __SERVER_STARTING__.__EOL__;
include(__START1_PHP__);
echo __RESTORING_DATA__.__SPACE__;
// RESTORE USING THE CHUNK ALGORITHM
$maxsize=__MAXSIZE__-strlen(__EOL__.__CRC32_CMD__.__EOL__);
while($cmds!=__NONE__) {
	$pos=strlen($cmds);
	if($pos>=$maxsize) {
		$new=strpos($cmds,__EOL__);
		while($new<$maxsize && $new!==false) {
			$pos=$new;
			$new=strpos($cmds,__EOL__,$new+1);
		}
	}
	$cmd=substr($cmds,0,$pos);
	$cmds=substr($cmds,$pos+1);
	comm($cmd);
}
// CONTINUE
echo $total2.__SPACE__.__ENTRIES_RESTORED__.__EOL__;
include(__START2_PHP__);
?>