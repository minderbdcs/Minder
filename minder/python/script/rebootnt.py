#!/usr/bin/env python2
"""
<title>
rebootnt.py, Version 17.03.04
</title>
<long>
Reboots a windows machine
<br>
Parameters: <tt> </tt>
<br>
</long>
"""
import win32api
import win32security
import sys
import time
from ntsecuritycon import *

def AdjustPriviledge(priv, enable = 1):
	#Get the process token
	flags = TOKEN_ADJUST_PRIVILEGES | TOKEN_QUERY
	htoken = win32security.OpenProcessToken(win32api.GetCurrentProcess(), flags)
	#Get the ID for the system shutdown priviledge
	id = win32security.LookupPrivilegeValue(None, priv)
	#Now obtain the priviledge for this process
	#create a list of the priviledges to be added
	if enable:
		newPriviledges = [(id, SE_PRIVILEGE_ENABLED)]
	else:
		newPriviledges = [(id, 0)]
	#make the adjustment
	win32security.AdjustTokenPrivileges(htoken, 0, newPriviledges)


def RebootServer(message="Server Rebooting", timeout=30, bForce=0, bReboot=1):
	AdjustPriviledge(SE_SHUTDOWN_NAME)
	try:
		win32api.InitiateSystemShutdown(None, message, timeout, bForce, bReboot)
	finally:
		#now remove the priviledge we just added
		AdjustPriviledge(SE_SHUTDOWN_NAME, 0)

if __name__=='__main__':
	message = "This machine is being rebooted. "
	RebootServer(message)


###
