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

#print "importfile filename logfilename "
#redirect stdout and stderr

if len(sys.argv)>1:
	print "savetodays ", sys.argv[1]
	mydir = sys.argv[1]
else:
	print "savetodays stdin"
        mydir = os.getcwd()

os.chdir(mydir)
#
todays = time.strftime("%Y-%m-%d")
print todays

try:
	os.mkdir(todays)
	print "directory created"
except OSError:
	print "directory already exists"

myfiles = glob.glob("*.log")
#print myfiles

#for i in range(0,mylen)
for i in myfiles:
	print i
	os.rename(i, todays + "/" + i)

myfiles = glob.glob("*_log")
#print myfiles

#for i in range(0,mylen)
for i in myfiles:
	print i
	os.rename(i, todays + "/" + i)


print "end - "
#revert stdin stdout and stderr
#sys.stdout = sys.__stdout__
#sys.stderr = sys.__stderr__

#out.close()
###
