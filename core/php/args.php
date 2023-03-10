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
if(!isset($argv[0])) $argv[0]=__NONE__;
if(!isset($argv[1])) $argv[1]=__SHELL_CMD__;
if(!isset($argv[2])) $argv[2]=__NONE__;
if(!isset($argv[3])) $argv[3]=__NONE__;
switch($argv[1]) {
	case __SHELL_CMD__:
		include(__SHELL_PHP__);
		break;
	case __HELP_CMD__:
		include(__HELP_PHP__);
		break;
	case __START_CMD__:
		if($argv[2]==__NONE__) include(__START_PHP__);
		if($argv[2]!=__NONE__) echo comm_human(__START_CMD__.__SPACE__.$argv[2]);
		break;
	case __RESTART_CMD__:
		if($argv[2]==__NONE__) include(__RESTART_PHP__);
		if($argv[2]!=__NONE__) echo __UNKNOWN_PARAMETER__.__SPACE__.__CUOTE__.$argv[2].__CUOTE__.__EOL__;
		break;
	case __RELOAD_CMD__:
		if($argv[2]==__NONE__) include(__RELOAD_PHP__);
		if($argv[2]!=__NONE__) echo __UNKNOWN_PARAMETER__.__SPACE__.__CUOTE__.$argv[2].__CUOTE__.__EOL__;
		break;
	case __STOP_CMD__:
		if($argv[2]==__NONE__) include(__STOP_PHP__);
		if($argv[2]!=__NONE__) echo comm_human(__STOP_CMD__.__SPACE__.$argv[2]);
		break;
	case __STATUS_CMD__:
		echo comm_human(__STATUS_CMD__.__SPACE__.$argv[2]);
		break;
	case __GET_CMD__:
		echo comm_human(__GET_CMD__.__SPACE__.$argv[2].__SPACE__.$argv[3]);
		break;
	case __ADD_CMD__:
	case __CREATE_CMD__:
		echo comm_human(__ADD_CMD__.__SPACE__.$argv[2].__SPACE__.$argv[3]);
		break;
	case __UPDATE_CMD__:
	case __SET_CMD__:
		echo comm_human(__UPDATE_CMD__.__SPACE__.$argv[2].__SPACE__.$argv[3]);
		break;
	case __REMOVE_CMD__:
	case __DELETE_CMD__:
		echo comm_human(__REMOVE_CMD__.__SPACE__.$argv[2].__SPACE__.$argv[3]);
		break;
	case __DUMP_CMD__:
		echo comm_human(__GET_CMD__);
		$stacks=comm_get_array(__GET_CMD__);
		foreach($stacks as $stack) {
			$output=comm_human(__GET_CMD__.__SPACE__.$stack);
			if(limpiar_stack($stack)==__CHILD_ARG__) {
				$datas=comm_get_array(__GET_CMD__.__SPACE__.$stack);
				foreach($datas as $data) {
					$data=child_key_val($data);
					$output=str_replace($data[0],child_string($data[0]),$output);
				}
			}
			echo $output;
		}
		break;
	case __LOG_CMD__:
		if($argv[2]==__NONE__) unset($argv[2]);
		if($argv[3]==__NONE__) unset($argv[3]);
		addlog(implode(__SPACE__,array_splice($argv,2)),__USER_LOG__);
		break;
	case __CHECK_CMD__:
		check_system();
		break;
	case __CRONTAB_CMD__:
	case __CRON_CMD__:
		include(__CRONTAB_PHP__);
		break;
	case __HELPER_CMD__:
		echo __HELPER_MSG__.__EOL__;
		break;
	default:
		echo __UNKNOWN_PARAMETER__.__SPACE__.__CUOTE__.$argv[1].__CUOTE__.__EOL__;
		include(__HELP_PHP__);
		break;
}
?>