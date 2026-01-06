#!/usr/bin/env python2
"""
<title>
savetodays.py, Version 21.01.04
</title>
<long>
Creates todays directory and moves files to it
<br>
Parameters: <tt> startdirectory </tt>
<br>
This scans the directory <tt>startdirectory</tt> for data 
and puts the files into the saved directory
<br>
</long>
"""
import sys
import string
import time , os ,glob

#redirect stdout and stderr

print (sys.argv)

print sys.argv[0]

if len(sys.argv)>1:
	print "overdueloan ", sys.argv[1]
	inData = sys.argv[1]
	myparms = inData.split("=")
	if "db"  == myparms[0]:
		mydb = myparms[1]
else:
	print "overdueloan stdin"
	mydb = "minder"
	havelog = 0;

print "mydb", mydb

