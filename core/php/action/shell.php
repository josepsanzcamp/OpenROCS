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
if(isset($shell)) {
	echo __SHELL_RUNNING__.__EOL__;
} else {
	if(!function_exists("__shell_completion")) {
		function __shell_completion($line,$pos,$cursor) {
			$current=readline_info("line_buffer");
			$stacks=comm_get_array(__GET_CMD__);
			if(count($stacks)) {
				$stacks=array_combine($stacks,$stacks);
				foreach($stacks as $key=>$val) {
					$val=comm_get_array(__GET_CMD__.__SPACE__.$key);
					foreach($val as $key2=>$val2) $val[$key2]=strtok($val2,__EQUAL__);
					$stacks[$key]=$val;
				}
				$name=getNode(__SERVER_NAME__);
				$name=__ARROBA__.($name?$name:$host.__TWO_POINTS__.$port);
				foreach($stacks as $key=>$val) {
					$key2=limpiar_stack($key);
					if($key2!=__CHILD_ARG__ && count($val)) {
						foreach($val as $val2) $stacks[]=encode_bad_chars($key).__UNDERSTAND__.$val2;
						if($key==$key2.$name) {
							$stacks[$key2]=$val;
							foreach($val as $val2) $stacks[]=$key2.__UNDERSTAND__.$val2;
						}
					} else {
						unset($stacks[$key]);
						$stacks[]=$key;
						if($key==$key2.$name) $stacks[]=$key2;
					}
				}
			}
			$suggest=array(
				__HELP_CMD__,
				__START_CMD__=>array(__MONITOR_NODE__,__SCHEDULER_NODE__),
				__RESTART_CMD__,
				__RELOAD_CMD__,
				__STOP_CMD__=>array(__MONITOR_NODE__,__SCHEDULER_NODE__),
				__STATUS_CMD__=>array(__MONITOR_NODE__,__SCHEDULER_NODE__),
				__DUMP_CMD__,
				__CHECK_CMD__,
				__LOG_CMD__,
				__HISTORY_CMD__,
				__EXIT_CMD__,
				__QUIT_CMD__,
				__BYE_CMD__
			);
			$suggest2=array(
				__GET_CMD__,
				__ADD_CMD__,
				__CREATE_CMD__,
				__UPDATE_CMD__,
				__SET_CMD__,
				__REMOVE_CMD__,
				__DELETE_CMD__
			);
			if(count($stacks)) $suggest2=array_fill_keys($suggest2,$stacks);
			$suggest=array_merge($suggest,$suggest2);
			$pos=intval(strrpos($current,__SPACE__));
			$current=substr($current,0,$pos);
			$current=trim($current);
			if($current) {
				$current=explode(__SPACE__,$current);
				while($command=array_shift($current)) {
					if(isset($suggest[$command])) {
						$suggest=$suggest[$command];
					} elseif(in_array($command,$suggest)) {
						$suggest=array(__NONE__);
						break;
					} else {
						break;
					}
				}
			}
			foreach($suggest as $key=>$val) {
				if(is_array($val)) {
					unset($suggest[$key]);
					$suggest[]=$key;
				}
			}
			sort($suggest);
			array_unshift($suggest,__NONE__);
			return $suggest;
		}
	}
	list($histfile,$error)=str_replace_with_vars(getNode(__SHELL_HISTORY__),config_array_vars());
	readline_completion_function("__shell_completion");
	if(readline_read_history($histfile)) {
		$history=explode(__EOL__,trim(file_get_contents($histfile)));
		$history=array_pop($history);
	}
	echo __SHELL_WELCOME__.__EOL__;
	while(1) {
		$shell=readline(__SHELL_PROMPT__.__SPACE__);
		if($shell===false) break;
		$shell=trim($shell);
		if($shell) {
			$shell0=strtok($shell,__SPACE__);
			if($shell0==__HELPER_CMD__) {
				echo __HELPER_MSG__.__EOL__;
			} elseif(!isset($history) || ($shell!=$history)) {
				readline_add_history($shell);
				$history=$shell;
			}
			switch($shell0) {
				case __HELP_CMD__:
					include(__HELP_PHP__);
					break;
				case __START_CMD__:
					$shell1=strtok(__NONE__);
					if($shell1==__NONE__) include(__START_PHP__);
					if($shell1!=__NONE__) echo comm_human(__START_CMD__.__SPACE__.$shell1);
					break;
				case __RESTART_CMD__:
					$shell1=strtok(__NONE__);
					if($shell1==__NONE__) include(__RESTART_PHP__);
					if($shell1!=__NONE__) echo __UNKNOWN_PARAMETER__.__SPACE__.__CUOTE__.$shell1.__CUOTE__.__EOL__;
					break;
				case __RELOAD_CMD__:
					$shell1=strtok(__NONE__);
					if($shell1==__NONE__) include(__RELOAD_PHP__);
					if($shell1!=__NONE__) echo __UNKNOWN_PARAMETER__.__SPACE__.__CUOTE__.$shell1.__CUOTE__.__EOL__;
					break;
				case __STOP_CMD__:
					$shell1=strtok(__NONE__);
					if($shell1==__NONE__) include(__STOP_PHP__);
					if($shell1!=__NONE__) echo comm_human(__STOP_CMD__.__SPACE__.$shell1);
					break;
				case __STATUS_CMD__:
					$shell1=strtok(__NONE__);
					echo comm_human(__STATUS_CMD__.__SPACE__.$shell1);
					break;
				case __GET_CMD__:
					echo comm_human(__GET_CMD__.__SPACE__.strtok(__SPACE__).__SPACE__.strtok(__NONE__));
					break;
				case __ADD_CMD__:
				case __CREATE_CMD__:
					echo comm_human(__ADD_CMD__.__SPACE__.strtok(__SPACE__).__SPACE__.strtok(__NONE__));
					break;
				case __UPDATE_CMD__:
				case __SET_CMD__:
					echo comm_human(__UPDATE_CMD__.__SPACE__.strtok(__SPACE__).__SPACE__.strtok(__NONE__));
					break;
				case __REMOVE_CMD__:
				case __DELETE_CMD__:
					echo comm_human(__REMOVE_CMD__.__SPACE__.strtok(__SPACE__).__SPACE__.strtok(__NONE__));
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
				case __CHECK_CMD__:
					check_system();
					break;
				case __LOG_CMD__:
					addlog(strtok(__NONE__),__USER_LOG__);
					break;
				case __HISTORY_CMD__:
					readline_write_history($histfile);
					foreach(explode(__EOL__,trim(file_get_contents($histfile))) as $key=>$val) if($key>0) echo $key.__SPACE__.$val.__EOL__;
					break;
				case __HELPER_CMD__:
					break;
				case __EXIT_CMD__:
				case __QUIT_CMD__:
				case __BYE_CMD__:
					break;
				default:
					echo __UNKNOWN_COMMAND__.__SPACE__.__CUOTE__.$shell0.__CUOTE__.__EOL__;
					break;
			}
			if(in_array($shell,array(__EXIT_CMD__,__QUIT_CMD__,__BYE_CMD__))) break;
		}
	}
	readline_write_history($histfile);
	$history=explode(__EOL__,trim(file_get_contents($histfile)));
	$length=count($history)-getNode(__SHELL_MAXLINES__)-1;
	if($length>0) {
		array_splice($history,1,$length);
		file_put_contents($histfile,implode(__EOL__,$history).__EOL__);
	}
}
?>