# OpenROCS
Software platform developed for the robotic control of telescopes.

![](https://raw.githubusercontent.com/josepsanzcamp/josepsanz/master/pdf/images/tjo.jpg)
![](https://raw.githubusercontent.com/josepsanzcamp/josepsanz/master/pdf/images/sqt.jpg)
![](https://raw.githubusercontent.com/josepsanzcamp/josepsanz/master/pdf/images/icetel.jpg)

# What is OpenROCS?
OpenROCS conforms an independent control layer for robotic observatories. It is a master scheduler of the processes that the system has to execute and a monitor of the overall state of the observatory. It controls the end-to-end data flow and the housekeeping processes by handling a set of predefined events. The former are responsible for ensuring that the system executes the routine operation by maximizing the scientific return of the facility. While the latter consider all those anomalous situations that could put the observatory at risk and activate corrective or mitigation actions. All the applications running at the observatory and the hardware components interact with it. It is released under GNU/GPL public license.

# Quick start
This code was published in SourceForge between 2012 and 2014, and for this reason it only works with PHP 5.6, I made improvements to run it in PHP 7 but the code of the new releases was closed and never published as free software. The main code is found in the code directory, to use it, simply enter to the code directory and type ./orocs help:

```
Usage: ./orocs command [parameters]

Available single commands:
        shell           open an interactive shell (default if not command is present)
        help            display this screen
        start           launch the main server process
        restart         restart the main server process
        reload          same as restart but without losing data
        stop            stop the main server process
        status          check the server state
        dump            dump all stacks and their keys and values
        check           run a quick validation test
        history         display the command history (only exists in the interactive shell)
Available commands that need parameters:
        get             [none]          return a list with all stacks
                        STACK           return a list with all data in the STACK
                        STACK KEY       return the value of the KEY in the STACK
                        TIMESTAMP       return a list with the modified stacks from TIMESTAMP
                        STACK TIMESTAMP return a list with the modified data from TIMESTAMP in the STACK
        add/create      STACK           create the STACK if not exists
                        STACK KEY       add the KEY to the STACK
                        STACK KEY=VALUE add the KEY with their VALUE to the STACK
        update/set      STACK KEY=VALUE update in the STACK the KEY with the new VALUE
        remove/delete   STACK           remove the STACK only if exists and is void
                        STACK KEY       remove the KEY in the STACK if exists
        stop            SERVICE         pause the service requested
        status          SERVICE         check the status of the service requested
        start           SERVICE         continue the service requested
```

The documentation is found in the docs directory:
- ![OpenROCS-Documentation.pdf](docs/OpenROCS-Documentation.pdf) :link: This document contains the original documentation published in SourceForge at year 2012.
- ![pfc_josep_sanz.pdf](docs/pfc_josep_sanz.pdf) :link: This document contains the final work used in the defense of my degree studies published at year 2015
- ![presentacion.pdf](docs/presentacion.pdf) :link: This document contains the presentation used in the defense of my degree studies published at year 2015


# License
This work is property of the [IEEC](https://www.ieec.cat/) and it was released under GNU/GPL public license. It was developed by me when I worked there and it was presented as a final work of my degree studies, you can see the publication at the [UPC](https://upcommons.upc.edu/handle/2099.1/26215) and the source code of the project at [SourceForge](https://sourceforge.net/projects/openrocs/).
