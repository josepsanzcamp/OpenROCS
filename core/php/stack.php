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
class stack {
    var $stack;
    var $timestamp;
    var $action;

	function __construct($array="") {
		$this->stack=array();
		$this->timestamp=array();
		$this->action=array();
	}

    function add($key,$val) {
		if($this->exists($key)) return false;
		$this->stack[$key]=$val;
		$this->timestamp[$key]=microtime(true);
		$this->action[$key]="add";
		return true;
    }

	function update($key,$val) {
		if(!$this->exists($key)) return false;
		if($this->stack[$key]==$val) return false;
		$this->stack[$key]=$val;
		$this->timestamp[$key]=microtime(true);
		$this->action[$key]="update";
		return true;
	}

    function remove($key) {
        if(!$this->exists($key)) return false;
        unset($this->stack[$key]);
		$this->timestamp[$key]=microtime(true);
		$this->action[$key]="remove";
        return true;
    }

	function import($array) {
		$this->stack=$array;
		$this->timestamp=array_fill_keys(array_keys($array),microtime(true));
		$this->action=array_fill_keys(array_keys($array),"add");
	}

    function export() {
		return $this->stack;
	}

	function exists($key) {
		return isset($this->stack[$key]);
	}

	function count() {
		return count($this->stack);
	}

	function modified($timestamp) {
		foreach($this->timestamp as $key=>$val) if($val>=$timestamp) return true;
		return false;
	}

	function __modified_by_action($timestamp,$action) {
		$array=array();
		foreach($this->timestamp as $key=>$val) if($val>=$timestamp && $this->action[$key]==$action) $array[]=$key;
		return $array;
	}

	function added($timestamp) {
		return $this->__modified_by_action($timestamp,"add");
	}

	function updated($timestamp) {
		return $this->__modified_by_action($timestamp,"update");
	}

	function removed($timestamp) {
		return $this->__modified_by_action($timestamp,"remove");
	}
}
?>