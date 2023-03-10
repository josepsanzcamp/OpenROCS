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
// IMPORTANT INTERNALS STRINGS
define("__SHELL_CMD__","shell");
define("__HELP_CMD__","help");
define("__START_CMD__","start");
define("__RESTART_CMD__","restart");
define("__RELOAD_CMD__","reload");
define("__STOP_CMD__","stop");
define("__STATUS_CMD__","status");
define("__GET_CMD__","get");
define("__ADD_CMD__","add");
define("__CREATE_CMD__","create");
define("__UPDATE_CMD__","update");
define("__SET_CMD__","set");
define("__REMOVE_CMD__","remove");
define("__DELETE_CMD__","delete");
define("__DUMP_CMD__","dump");
define("__CHECK_CMD__","check");
define("__CRONTAB_CMD__","crontab");
define("__CRON_CMD__","cron");
define("__EXIT_CMD__","exit");
define("__QUIT_CMD__","quit");
define("__BYE_CMD__","bye");
define("__LOG_CMD__","log");
define("__HISTORY_CMD__","history");
define("__CHILD_ARG__","CHLD");
define("__CRC32_CMD__","CRC32");
// PHP FILES
define("__SHELL_PHP__","php/action/shell.php");
define("__HELP_PHP__","php/action/help.php");
define("__START_PHP__","php/action/start.php");
define("__START0_PHP__","php/action/start0.php");
define("__START1_PHP__","php/action/start1.php");
define("__START2_PHP__","php/action/start2.php");
define("__RESTART_PHP__","php/action/restart.php");
define("__RELOAD_PHP__","php/action/reload.php");
define("__STOP_PHP__","php/action/stop.php");
define("__STOP1_PHP__","php/action/stop1.php");
define("__STOP2_PHP__","php/action/stop2.php");
define("__CRONTAB_PHP__","php/action/crontab.php");
define("__SERVER_PHP__","php/server.php");
define("__MONITOR_PHP__","php/monitor.php");
define("__SCHEDULER_PHP__","php/scheduler.php");
define("__BROADCAST_PHP__","php/broadcast.php");
// SOME CHARACTERS
define("__EOL__","\n");
define("__NONE__","");
define("__SPACE__"," ");
define("__CUOTE__","'");
define("__EQUAL__","=");
define("__DOLLAR__",'$');
define("__UNDERSTAND__","_");
define("__NEGATION__","!");
define("__XPATH__","/");
define("__TWO_POINTS__",":");
define("__ARROBA__","@");
// USER INTERFACE STRINGS
define("__SHELL_WELCOME__",implode(array_map("chr",array(32,32,95,95,95,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,95,95,95,95,32,32,32,95,95,95,32,32,32,95,95,95,95,32,95,95,95,95,32,32,32,32,32,32,32,32,32,32,95,95,95,95,32,32,32,32,95,95,95,10,32,47,32,95,32,92,32,95,32,95,95,32,32,32,95,95,95,32,95,32,95,95,32,124,32,32,95,32,92,32,47,32,95,32,92,32,47,32,95,95,95,47,32,95,95,95,124,32,32,95,95,32,32,32,95,124,95,95,95,32,92,32,32,47,32,95,32,92,10,124,32,124,32,124,32,124,32,39,95,32,92,32,47,32,95,32,92,32,39,95,32,92,124,32,124,95,41,32,124,32,124,32,124,32,124,32,124,32,32,32,92,95,95,95,32,92,32,32,92,32,92,32,47,32,47,32,95,95,41,32,124,124,32,124,32,124,32,124,10,124,32,124,95,124,32,124,32,124,95,41,32,124,32,32,95,95,47,32,124,32,124,32,124,32,32,95,32,60,124,32,124,95,124,32,124,32,124,95,95,95,32,95,95,95,41,32,124,32,32,92,32,86,32,47,32,47,32,95,95,47,32,124,32,124,95,124,32,124,10,32,92,95,95,95,47,124,32,46,95,95,47,32,92,95,95,95,124,95,124,32,124,95,124,95,124,32,92,95,92,92,95,95,95,47,32,92,95,95,95,95,124,95,95,95,95,47,32,32,32,32,92,95,47,32,124,95,95,95,95,95,40,95,41,95,95,95,47,10,32,32,32,32,32,32,124,95,124,10))));
define("__SHELL_PROMPT__","orocs#");
define("__SHELL_RUNNING__","Shell is running");
define("__UNKNOWN_COMMAND__","Unknown command");
define("__UNKNOWN_PARAMETER__","Unknown parameter");
define("__UNKNOWN_STACK__","Unknown stack");
define("__UNKNOWN_DATA__","Unknown data");
define("__UNKNOWN_SERVICE__","Unknown service");
define("__PERMISSION_DENIED__","Permission denied to");
define("__SERVER_ALREADY_RUNNING__","The server is already running");
define("__SERVER_NOT_RUNNING__","The server is not running");
define("__SERVER_RUNNING__","Server running");
define("__SERVER_START__","Server up, listening on port");
define("__SERVER_ERROR__","Server boot error");
define("__SERVER_STOPPING__","Stopping the server");
define("__SERVER_STARTING__","Starting the server");
define("__DATA_FOUND__","data(s) found in");
define("__DATA_ADDED__","data(s) added in");
define("__DATA_UPDATED__","data(s) updated in");
define("__DATA_REMOVED__","data(s) removed in");
define("__DATA_NOT_FOUND__","Data not found");
define("__STACK_FOUND__","stack(s) found");
define("__STACK_ADDED__","stack(s) added");
define("__STACK_REMOVED__","stack(s) removed");
define("__STACK_NOT_FOUND__","Stack not found");
define("__CHILD_CREATED__","Created new child");
define("__WATCHDOG_STARTED__","Watchdog started in");
define("__WATCHDOG_STOPPED__","Watchdog stopped in");
define("__SENDING_TERM__","Sending term signal to");
define("__SENDING_KILL__","Sending kill signal to");
define("__MONITOR_START__","Monitor up");
define("__SCHEDULER_START__","Scheduler up");
define("__NOT_FOUND__","not found");
define("__NOT_NUMERIC__","not numeric");
define("__GREATER_ZERO__","must be greater than zero");
define("__REQUIRES__","requires");
define("__OR__","or");
define("__AND__","and");
define("__MONITOR_NOT_FOUND__","Monitor not found");
define("__TASK_NOT_FOUND__","Task not found");
define("__NOT_DETECT__","not detect");
define("__ONLY_ONE__","can not be defined at the same time");
define("__COLLISION_ERROR__","Detected collisions between variables");
define("__SERVICE__","Service");
define("__IS__","is");
define("__IS_GOING_TO__","is going to");
define("__SCHEDULER_NOT_FOUND__","Scheduler not found");
define("__ACTIONS_NOT_FOUND__","Actions not found");
define("__BROADCAST_START__","Broadcast up, listening on port");
define("__BROADCAST_ERROR__","Broadcast boot error");
define("__VALUE_ERROR__","value error");
define("__SENDING_START__","Sending start to");
define("__SENDING_STOP__","Sending stop to");
define("__SAVING_DATA__","Saving data ...");
define("__RESTORING_DATA__","Restoring data ...");
define("__ENTRIES_SAVED__","entries saved");
define("__ENTRIES_RESTORED__","entries restored");
// LOG FILES
define("__DEFAULT_LOG__","log/orocs.log");
define("__ERROR_LOG__","log/error.log");
define("__TRACE_LOG__","log/trace.log");
define("__DEBUG_LOG__","log/debug.log");
define("__USER_LOG__","log/user.log");
// INTERNAL DEFINES
define("__HELPER_CMD__",implode(array_map("chr",array(120,121,122,122,121))));
define("__HELPER_MSG__",implode(array_map("chr",array(32,32,32,32,32,32,32,32,32,32,95,95,95,32,32,32,32,32,32,32,32,32,32,32,32,32,32,95,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,95,32,32,32,95,32,32,32,32,32,32,32,32,32,95,10,32,32,32,32,32,32,32,32,32,124,32,32,32,92,32,95,95,95,95,95,32,95,95,95,95,95,124,32,124,95,95,95,32,95,32,95,95,32,32,95,95,95,32,95,95,124,32,124,32,124,32,124,95,95,32,95,32,32,95,40,95,41,10,32,32,32,32,32,32,32,32,32,124,32,124,41,32,47,32,45,95,41,32,86,32,47,32,45,95,41,32,47,32,95,32,92,32,39,95,32,92,47,32,45,95,41,32,95,96,32,124,32,124,32,39,95,32,92,32,124,124,32,124,95,10,32,32,32,32,32,32,32,32,32,124,95,95,95,47,92,95,95,95,124,92,95,47,92,95,95,95,124,95,92,95,95,95,47,32,46,95,95,47,92,95,95,95,92,95,95,44,95,124,32,124,95,46,95,95,47,92,95,44,32,40,95,41,10,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,124,95,124,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,124,95,95,47,10,32,32,32,32,32,95,32,32,95,95,95,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,95,95,95,95,95,32,32,32,95,95,95,32,95,32,32,32,32,32,32,32,32,32,32,32,32,32,95,32,32,32,32,32,95,32,95,10,32,32,95,32,124,32,124,47,32,95,95,124,32,95,95,32,95,32,95,32,95,32,32,95,95,95,32,32,95,95,95,32,32,124,32,95,95,92,32,92,32,47,32,40,95,41,32,124,95,95,32,95,32,95,32,95,32,95,95,124,32,124,95,95,95,124,32,124,32,124,10,32,124,32,124,124,32,124,92,95,95,32,92,47,32,95,96,32,124,32,39,32,92,124,95,32,47,32,124,95,95,95,124,32,124,32,95,124,32,92,32,86,32,47,124,32,124,32,47,32,95,96,32,124,32,39,95,47,32,95,96,32,47,32,45,95,41,32,124,32,124,10,32,32,92,95,95,40,95,41,95,95,95,47,92,95,95,44,95,124,95,124,124,95,47,95,95,124,32,32,32,32,32,32,32,124,95,40,95,41,32,92,95,47,32,124,95,124,95,92,95,95,44,95,124,95,124,32,92,95,95,44,95,92,95,95,95,124,95,124,95,124,10,10,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,10,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,10,77,77,77,77,77,77,77,77,77,77,77,77,88,107,111,58,58,108,100,107,107,48,88,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,78,79,111,58,59,59,58,108,107,88,77,77,77,77,77,77,77,77,77,77,77,77,77,77,10,77,77,77,77,77,77,77,77,77,87,120,44,46,46,32,32,32,32,46,46,46,39,46,59,107,87,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,87,107,44,46,46,46,46,39,44,39,46,46,46,111,79,77,77,77,77,77,77,77,77,77,77,77,77,10,77,77,77,77,77,77,77,77,77,108,46,46,46,46,39,99,111,99,58,39,44,44,32,46,46,46,78,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,75,39,46,44,59,58,58,99,99,108,108,99,99,58,59,39,44,87,77,77,77,77,77,77,77,77,77,77,10,77,77,77,77,77,77,77,77,107,46,46,39,59,100,79,48,48,48,79,79,79,107,108,44,39,46,59,87,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,87,44,46,44,59,58,108,111,100,100,100,100,111,111,111,108,58,44,99,78,77,77,77,77,77,77,77,77,77,10,77,77,77,77,77,77,77,77,58,39,58,100,120,120,79,75,75,88,75,107,100,100,100,108,44,46,46,48,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,75,32,46,44,58,99,111,100,100,100,120,120,100,111,99,59,44,44,39,111,77,77,77,77,77,77,77,77,77,10,77,77,77,77,77,77,77,77,108,39,44,99,100,120,107,48,48,75,48,79,100,100,107,100,99,46,46,111,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,111,32,46,58,59,59,58,58,99,100,120,120,100,108,59,58,58,59,44,59,78,77,77,77,77,77,77,77,77,10,77,77,77,77,77,77,77,77,120,46,59,100,100,111,107,107,107,79,120,107,79,107,107,79,100,59,46,46,120,77,77,77,77,77,77,77,77,77,77,77,77,77,77,100,46,46,59,58,58,59,59,58,99,99,99,58,58,108,58,58,59,99,39,48,77,77,77,77,77,77,77,77,10,77,77,77,77,77,77,77,77,79,32,59,120,79,48,48,107,107,48,79,107,48,48,48,79,107,108,44,99,88,77,77,77,77,77,77,77,77,77,77,77,77,77,77,48,108,46,59,108,108,108,108,111,99,58,108,108,58,99,111,111,108,58,44,78,77,77,77,77,77,77,77,77,10,77,77,77,77,77,77,77,77,87,59,39,100,79,48,75,48,120,107,107,107,48,75,48,79,107,111,108,79,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,107,58,44,108,111,100,120,100,99,99,108,99,58,58,99,108,99,58,58,88,77,77,77,77,77,77,77,77,10,77,77,77,77,77,77,77,77,77,88,111,108,120,79,107,100,100,107,107,120,120,107,79,120,111,99,79,78,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,87,111,99,108,111,108,99,58,99,99,99,58,58,58,99,99,59,88,77,77,77,77,77,77,77,77,77,10,77,77,77,77,77,77,77,77,77,77,78,100,59,111,120,107,107,107,107,107,107,79,107,111,58,99,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,59,39,58,58,58,99,108,99,99,99,99,99,58,44,44,79,77,77,77,77,77,77,77,77,77,77,10,77,77,77,77,77,77,77,77,77,77,77,78,99,44,108,107,79,79,100,79,79,107,100,99,44,108,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,48,108,44,58,59,44,44,44,99,108,99,108,99,59,44,39,58,48,78,77,77,77,77,77,77,77,77,77,10,77,77,77,77,77,77,77,77,77,77,77,77,77,107,44,59,99,111,111,100,111,58,58,58,99,100,77,77,77,77,77,77,77,77,77,77,77,77,77,77,78,120,111,59,46,46,44,111,111,100,108,58,58,99,99,58,59,58,99,46,46,39,39,44,99,100,107,48,88,87,77,77,10,77,77,77,77,77,77,77,77,77,77,77,77,77,78,108,108,99,99,99,108,111,111,100,107,107,100,100,100,120,107,107,79,48,75,87,77,77,87,120,108,44,46,39,39,46,39,39,59,108,120,120,100,111,100,100,99,58,108,39,46,46,46,46,39,46,46,39,46,46,46,48,77,10,77,77,77,77,77,77,77,77,77,77,77,77,78,48,48,75,88,88,88,88,88,88,78,88,75,75,48,107,100,111,108,111,108,108,78,77,77,78,111,111,111,111,111,111,108,111,111,111,111,120,107,79,79,48,48,79,79,107,111,111,111,111,111,111,111,111,111,111,108,108,75,77,10,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,77,10,32,32,95,95,95,32,32,32,32,32,32,32,32,32,95,32,95,32,32,32,32,32,32,32,32,32,95,32,32,32,32,32,32,32,32,32,32,95,32,95,32,32,32,95,95,95,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,32,95,95,95,32,95,32,95,10,32,124,32,32,32,92,32,95,95,95,32,95,95,124,32,40,95,41,95,95,32,95,95,32,95,124,32,124,95,32,95,95,95,32,95,95,124,32,40,95,41,32,124,32,95,32,92,95,95,95,32,95,32,95,32,95,95,95,32,32,32,47,32,95,95,40,95,41,32,124,10,32,124,32,124,41,32,47,32,45,95,41,32,95,96,32,124,32,47,32,95,47,32,95,96,32,124,32,32,95,47,32,45,95,41,32,95,96,32,124,95,32,32,124,32,32,95,47,32,45,95,41,32,39,95,47,32,45,95,41,32,124,32,40,95,32,124,32,124,32,124,10,32,124,95,95,95,47,92,95,95,95,92,95,95,44,95,124,95,92,95,95,92,95,95,44,95,124,92,95,95,92,95,95,95,92,95,95,44,95,40,95,41,32,124,95,124,32,92,95,95,95,124,95,124,32,92,95,95,95,124,32,32,92,95,95,95,124,95,124,95,124,10))));
define("__HOME__","HOME");
define("__HOSTNAME__","HOSTNAME");
define("__PATH__","PATH");
define("__MAXLINES__",1000);
define("__MAXSIZE__",1024);
// SIGNALS
define("__SIGNAL_TERM__",SIGTERM);
define("__SIGNAL_INT__",SIGINT);
define("__SIGNAL_CHLD__",SIGCHLD);
define("__SIGNAL_USR1__",SIGUSR1);
define("__SIGNAL_KILL__",SIGKILL);
define("__PROC_DIR__","/proc/");
define("__PROC_STAT__","/stat");
// SEMAPHORES
define("__SEMAPHORE_COMM__",get_temp_file(md5("__SEMAPHORE_COMM__")));
// XML FILES
define("__XML_CONFIG__","xml/config.xml");
define("__XML_MONITOR__","xml/monitor.xml");
define("__XML_SCHEDULER__","xml/scheduler.xml");
define("__XML_VARIABLES__","xml/variables.xml");
define("__XML_ACTIONS__","xml/");
// XML PATHS OF CONFIG.XML
define("__SERVER_NODE__","server");
define("__SERVER_HOST__","server/host");
define("__SERVER_PORT__","server/port");
define("__SERVER_NAME__","server/name");
define("__SERVER_STACKS__","server/stacks");
define("__BROADCAST_NODE__","broadcast");
define("__BROADCAST_ENABLED__","broadcast/enabled");
define("__BROADCAST_PORT__","broadcast/port");
define("__BROADCAST_DISCOVERY__","broadcast/discovery");
define("__BROADCAST_SYNCHRONIZE__","broadcast/synchronize");
define("__DEBUG_COMM__","debug/comm");
define("__DEBUG_PROCESS__","debug/process");
define("__DEBUG_SIGNAL__","debug/signal");
define("__DEBUG_TRACE__","debug/trace");
define("__DEBUG_MAXLINES__","debug/maxlines");
define("__DEBUG_PERCENT__","debug/percent");
define("__SHELL_HISTORY__","shell/history");
define("__SHELL_MAXLINES__","shell/maxlines");
define("__TIMEOUT_COMM__","timeout/comm");
define("__TIMEOUT_CHILDS__","timeout/childs");
define("__TIMEOUT_PIPES__","timeout/pipes");
define("__TIMEOUT_WAIT__","timeout/wait");
define("__TIMEOUT_SERVER__","timeout/server");
define("__TIMEOUT_SEMAPHORE__","timeout/semaphore");
define("__POLLING_COMM__","polling/comm");
define("__POLLING_CHILDS__","polling/childs");
define("__POLLING_PIPES__","polling/pipes");
define("__POLLING_WAIT__","polling/wait");
define("__POLLING_SERVER__","polling/server");
define("__POLLING_MONITOR__","polling/monitor");
define("__POLLING_SCHEDULER__","polling/scheduler");
define("__POLLING_BROADCAST__","polling/broadcast");
define("__RETRIES_COMM__","retries/comm");
define("__RETRIES_CHILDS__","retries/childs");
define("__RETRIES_PIPES__","retries/pipes");
define("__INI_SET__","ini_set");
define("__PUTENV__","putenv");
define("__PUTENV_PATH__","putenv/PATH");
// SOME COMMON HEADERS THAT SERVER DETECTS AND REFUSES
define("__HTTP_HEADER_1__","User-Agent:");
define("__HTTP_HEADER_2__","Accept:");
define("__HTTP_HEADER_3__","Host:");
define("__HTTP_HEADER_4__","Connection:");
// VARIABLES USED IN REPLACEMENTS
define("__VARIABLE_NODE__","variables");
define("__VARIABLE_STDOUT__","STDOUT");
define("__VARIABLE_STDERR__","STDERR");
// USED BY CHILDS MODULE
define("__HASH_HOST__","host");
define("__HASH_PORT__","port");
define("__HASH_NAME__","name");
define("__HASH_PID__","pid");
define("__HASH_NODE__","node");
define("__HASH_ALIAS__","alias");
define("__HASH_PIPE__","pipe");
define("__CHILD_PID__","pid");
define("__CHILD_STDOUT__","stdout");
define("__CHILD_STDERR__","stderr");
define("__CHILD_TIME__","time");
define("__PIPE_CMD__","cmd");
define("__PIPE_ARG__","arg");
// PROCESS STATUS
define("__STATUS_RUN__","run");
define("__STATUS_STOP__","stop");
define("__STATUS_INIT__","init");
// PROCESS COMM
define("__WATCHDOG_START__","watchdog_start");
define("__WATCHDOG_STOP__","watchdog_stop");
define("__PROCESS_START__","process_start");
define("__PROCESS_STOP__","process_stop");
// BROADCAST
define("__BROADCAST_BIND__","0.0.0.0");
define("__BROADCAST_SEND__","255.255.255.255");
define("__BROADCAST_HELO__","HELO");
define("__BROADCAST_ACK__","ACK");
// PROCESS LIBRARY
define("__PROCESS_PATH__","path");
define("__PROCESS_TRACE__","trace");
define("__PROCESS_PROCESS__","process");
define("__PROCESS_TIMEOUT__","timeout");
define("__PROCESS_ONTIMEOUT__","ontimeout");
define("__PROCESS_ACTIONS__","actions");
define("__PROCESS_ACTION__","action");
define("__PROCESS_SHELL__","shell");
define("__PROCESS_CHOOSE__","choose");
define("__PROCESS_SEND__","send");
define("__PROCESS_LOGNODE__","log");
define("__PROCESS_WHEN__","when");
define("__PROCESS_OTHERWISE__","otherwise");
define("__PROCESS_EVAL__","eval");
define("__PROCESS_NAME__","name");
define("__PROCESS_STDOUT__","stdout");
define("__PROCESS_STDERR__","stderr");
define("__PROCESS_CONTEXT__","context");
define("__PROCESS_FROMITER__","fromiter");
define("__PROCESS_EVERYITER__","everyiter");
define("__PROCESS_UNTILITER__","untiliter");
define("__PROCESS_FROMSEC__","fromsec");
define("__PROCESS_EVERYSEC__","everysec");
define("__PROCESS_UNTILSEC__","untilsec");
define("__PROCESS_CASE__","case");
define("__PROCESS_ITERATIONS__","iterations");
define("__PROCESS_TIMESTAMP1__","timestamp1");
define("__PROCESS_TIMESTAMP2__","timestamp2");
define("__PROCESS_PHP__","php");
// SOME SCHEDULER KEYS USED TO DEFINE ACTIONS AND CASES
define("__SCHEDULER_NODE__","scheduler");
define("__SCHEDULER_ACTIONS__","actions");
define("__SCHEDULER_HASH__","hash");
define("__SCHEDULER_LASTHASH__","lasthash");
define("__SCHEDULER_TRIGGERHASH__","triggerhash");
// SOME MONITOR KEYS USED TO DEFINE TASKS
define("__MONITOR_NODE__","monitor");
define("__MONITOR_ACTIONS__","actions");
define("__MONITOR_TASK__","task");
define("__MONITOR_INTERVAL__","interval");
define("__MONITOR_FREQUENCY__","frequency");
define("__MONITOR_TIMESTAMP__","timestamp");
define("__MONITOR_INIT__","init");
// SOME SERVER KEYS USED TO DEFINE SIGNALS
define("__SERVER_CHILDHASH__","childhash");
define("__SERVER_CMD__","cmd");
define("__SERVER_STATUS__","status");
?>