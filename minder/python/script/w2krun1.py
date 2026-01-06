#!/usr/bin/env python2
"""
<title>
w2krun1.py, Version 15.11.05
</title>
<long>
Checks whether a program is running and if not starts the program
<br>
Parameters: <tt> program name to look for </tt>
 <tt> path and program name to run </tt>
 <tt> Log file name for output </tt>
<br>
<br>
</long>
"""
import sys
import string
import time , os ,glob
from win32com.client import GetObject
import win32api

if len(sys.argv)>0:
	print "w2krun1 program is ", sys.argv[1]
        progname = sys.argv[1]
else:
	print "w2krun1 program is  noprogram"
        progname = "date"

if len(sys.argv)>1:
        print " execute ", sys.argv[2]
        exefile = sys.argv[2]
else:
        print " execute nothing"
        exefile = "date/t"
#
if len(sys.argv)>2:
        print "log file ", sys.argv[3]
        logfile = sys.argv[3]
	havelog = 1
else:
        print "log file stdout"
        logfile = sys.__stdout__
	havelog = 0
#
#redirect stdout and stderr
if (havelog == 1):
	out = open(logfile,'a')
	sys.stdout = out
	sys.stderr = out
	print "w2krun1 program is  ", progname
        print " execute ", exefile

print time.strftime("%d/%m/%y %h:%M:%S")
WMI = GetObject('winmgmts:')
#processes = WMI.InstancesOf('Win32_Process')
#process_names = [process.Properties_('Name').Value for process in processes]
#print process_names
#p = WMI.ExecQuery('select * from Win32_Process where Name="SoapMinder.exe"')
#print 'select * from Win32_Process where Name="%s"' %(progname)
p = WMI.ExecQuery('select * from Win32_Process where Name="%s"' %(progname))
try:
	prop_names = [prop.Name for prop in p[0].Properties_]
	print "Running"
except IndexError:
	print "Not Running starting now"
	#win32api.WinExec('d:\Program Files\IMS\SoapMinder\SoapMinder.exe')
	win32api.WinExec(exefile)


#revert stdin stdout and stderr
if (havelog == 1):
	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__
	out.close()
###
