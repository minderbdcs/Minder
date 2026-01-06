#!/usr/bin/env python2
"""
<title>
ftporders.py, Version 18.07.06
</title>
<long>
Creates/Updates files for orders
<br>
Parameters: <tt>log file</tt>
<br>
<br>
</long>
"""
# import order files by ftp
import sys
import string
import time , os ,glob
import fileinput

from ftplib import FTP

print "ftporders "

if len(sys.argv)>1:
	print "log ", sys.argv[1]
	logfile = sys.argv[1]
	havelog = 1;
else:
	print "log stdin"
	havelog = 0;

host = "10.27.3.14"
username = "root"
password = "beer"
mydir1 = "work/minder"
mydir2 = "d:/sysdata/iis/default/ftproot/import"
#mydir2 = "/sysdata/iis/default/ftproot/log"

#
#redirect stdout and stderr
if (havelog == 1):
	out = open(logfile,'a')
	sys.stdout = out
	sys.stderr = out

ftp1 = FTP(host)
print "connected"
ftp1.login(username, password)
print "logged in"
ftp1.cwd(mydir1)
print "changed directory"
#ftp1.retrlines('LIST')
#print "done ls"
mydir = ftp1.nlst()
print "done nlst"
#print(mydir)
for myfile in mydir:
	#print myfile
	if myfile[:3] == "dsp" and myfile[-4:] == ".txt":
		print "starts dsp"
		print myfile
		# get this one
		# open a file
		myfile3 = os.path.join(mydir2, myfile)
		print myfile3
		mydata = open(myfile3, "wb")
		mycmd = "RETR " + myfile
		ftp1.retrbinary(mycmd, mydata.write)
		# close it
		mydata.close()
		# then rename it 
		rest, ext = os.path.splitext(myfile)
		path, base = os.path.split(rest)
		myformat = "%s%s" % (base, ".dxt")
		myfile2 = os.path.join(path, myformat)
		ftp1.rename(myfile,myfile2)
		print "renamed"
		# then delete
		#ftp1.delete(myfile)

ftp1.quit
print "logged out"

#revert stdin stdout and stderr
if (havelog == 1):
	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__
	out.close()

###
