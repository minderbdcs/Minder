#!/usr/bin/env python2
"""
<title>
archiveprints.py, Version 23.10.09
</title>
<long>
Creates/Updates tables in the database
<br>
Parameters: <tt>log file</tt>
<br>
<br>
</long>
"""
import sys
import string
import time , os ,glob
import fileinput

#import kinterbasdb
#import mx.DateTime
import kinterbasdb;kinterbasdb.init(type_conv=200)

#redirect stdout and stderr

if os.name == 'nt':
	logfile = "d:/tmp/archiveprints.log"
else:
	logfile = "/tmp/archiveprints.log"
havelog = 1;

#
mydb = "minder"
if len(sys.argv)>1:
	inData = sys.argv[1]
	myparms = inData.split("=")
	if "db"  == myparms[0]:
		mydb = myparms[1]
print "mydb", mydb
logfile = logfile + mydb + ".log"

#
#redirect stdout and stderr
if (havelog == 1):
	out = open(logfile,'w')
	sys.stdout = out
	sys.stderr = out

if os.name == 'nt':
	#dsn="127.0.0.1:minder",
	con = kinterbasdb.connect(
		dsn="127.0.0.1:" + mydb,
		user="sysdba",
		password="masterkey")
else:
	#dsn="127.0.0.1:minder",
	con = kinterbasdb.connect(
		dsn="127.0.0.1:" + mydb,
		user="sysdba",
		password="masterkey")

print "connected to db"
cur = con.cursor()
cur2 = con.cursor()

wk_date = time.strftime("%d/%m/%y")
wk_line = 0	

#mytime = mx.DateTime.now()

query0 = """execute procedure prn_request_archive """

cur2.execute(query0)

## get data record

print "end of updates" 

con.commit()

print "end - of "


#revert stdin stdout and stderr
if (havelog == 1):
	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__
	out.close()

###
