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
if(!isset($shell))
echo "Usage: ${argv[0]} command [parameters]\n";
echo "\n";
echo "Available single commands:\n";
if(!isset($shell))
echo "        shell           open an interactive shell (default if not command is present)\n";
echo "        help            display this screen\n";
echo "        start           launch the main server process\n";
echo "        restart         restart the main server process\n";
echo "        reload          same as restart but without losing data\n";
echo "        stop            stop the main server process\n";
echo "        status          check the server state\n";
echo "        dump            dump all stacks and their keys and values\n";
echo "        check           run a quick validation test\n";
echo "        history         display the command history (only exists in the interactive shell)";
echo "\n";
echo "Available commands that need parameters:\n";
echo "        get             [none]          return a list with all stacks\n";
echo "                        STACK           return a list with all data in the STACK\n";
echo "                        STACK KEY       return the value of the KEY in the STACK\n";
echo "                        TIMESTAMP       return a list with the modified stacks from TIMESTAMP\n";
echo "                        STACK TIMESTAMP return a list with the modified data from TIMESTAMP in the STACK\n";
echo "        add/create      STACK           create the STACK if not exists\n";
echo "                        STACK KEY       add the KEY to the STACK\n";
echo "                        STACK KEY=VALUE add the KEY with their VALUE to the STACK\n";
echo "        update/set      STACK KEY=VALUE update in the STACK the KEY with the new VALUE\n";
echo "        remove/delete   STACK           remove the STACK only if exists and is void\n";
echo "                        STACK KEY       remove the KEY in the STACK if exists\n";
echo "        stop            SERVICE         pause the service requested\n";
echo "        status          SERVICE         check the status of the service requested\n";
echo "        start           SERVICE         continue the service requested\n";
echo "\n";
?>