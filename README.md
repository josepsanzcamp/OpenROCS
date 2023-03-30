# OpenROCS
Software platform developed for the robotic control of telescopes.

![](https://raw.githubusercontent.com/josepsanzcamp/josepsanz/master/pdf/images/tjo.jpg)
![](https://raw.githubusercontent.com/josepsanzcamp/josepsanz/master/pdf/images/sqt.jpg)
![](https://raw.githubusercontent.com/josepsanzcamp/josepsanz/master/pdf/images/icetel.jpg)

# What is OpenROCS?
OpenROCS conforms an independent control layer for robotic observatories. It is a master scheduler of the processes that the system has to execute and a monitor of the overall state of the observatory. It controls the end-to-end data flow and the housekeeping processes by handling a set of predefined events. The former are responsible for ensuring that the system executes the routine operation by maximizing the scientific return of the facility. While the latter consider all those anomalous situations that could put the observatory at risk and activate corrective or mitigation actions. All the applications running at the observatory and the hardware components interact with it. It is released under GNU/GPL public license.

# License
This work is property of the [IEEC](https://www.ieec.cat/) and it was released under GNU/GPL public license. It was developed by me when I worked there and it was presented as a final work of my degree studies, you can see the publication at the [UPC](https://upcommons.upc.edu/handle/2099.1/26215) and the source code of the project at [SourceForge](https://sourceforge.net/projects/openrocs/). This code was published in SourceForge between 2012 and 2014, and for this reason it only works with PHP 5.6, I made improvements to run it in PHP 7 but the code of the new releases was closed and never published as free software.
