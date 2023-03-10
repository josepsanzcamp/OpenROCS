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
class garbage_collector_helper {
	var $procs;
	var $files;

	function __construct() {
		$this->procs=array();
		$this->files=array();
	}

	function __desctuct() {
		while($pid=array_pop($this->procs)) child_kill($pid,__SIGNAL_KILL__,true);
		while($file=array_pop($this->files)) unlink_protected($file);
	}

	function add_proc($pid) {
		if(!in_array($pid,$this->procs)) array_push($this->procs,$pid);
	}

	function add_file($file) {
		if(!in_array($file,$this->files)) array_push($this->files,$file);
	}

	function remove_proc($pid) {
		if(in_array($pid,$this->procs)) $this->procs=array_diff($this->procs,array($pid));
	}

	function remove_file($file) {
		if(in_array($file,$this->files)) $this->files=array_diff($this->files,array($file));
	}
}

function garbage_collector_init() {
	global $garbage_collector;
	$garbage_collector=new garbage_collector_helper();
}

function garbage_collector_add_proc($pid) {
	global $garbage_collector;
	if(method_exists($garbage_collector,"add_proc")) $garbage_collector->add_proc($pid);
}

function garbage_collector_add_file($file) {
	global $garbage_collector;
	if(method_exists($garbage_collector,"add_file")) $garbage_collector->add_file($file);
}

function garbage_collector_remove_proc($pid) {
	global $garbage_collector;
	if(method_exists($garbage_collector,"remove_proc")) $garbage_collector->remove_proc($pid);
}

function garbage_collector_remove_file($file) {
	global $garbage_collector;
	if(method_exists($garbage_collector,"remove_file")) $garbage_collector->remove_file($file);
}

function garbage_collector_empty() {
	global $garbage_collector;
	if(method_exists($garbage_collector,"__desctuct")) $garbage_collector->__desctuct();
}
?>