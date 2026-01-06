#!/usr/bin/env python2
"""
<title>
archivetrans.py, Version 10.08.05
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

import kinterbasdb
import mx.DateTime

#redirect stdout and stderr

if os.name == 'nt':
	logfile = "d:/tmp/archivetrans.log"
else:
	logfile = "/tmp/archivetrans.log"
havelog = 1;

#
#redirect stdout and stderr
if (havelog == 1):
	out = open(logfile,'w')
	sys.stdout = out
	sys.stderr = out

if os.name == 'nt':
	#dsn="127.0.0.1:d:/asset.rf/database/wh.v39.gdb",
	con = kinterbasdb.connect(
		dsn="127.0.0.1:minder",
		user="sysdba",
		password="masterkey")
else:
	#dsn="/data/asset.rf/wh.v39.gdb",
	con = kinterbasdb.connect(
		dsn="minder",
		user="sysdba",
		password="masterkey")

print "connected to db"
cur = con.cursor()
cur2 = con.cursor()

wk_date = time.strftime("%d/%m/%y")
wk_line = 0	

mytime = mx.DateTime.now()

query0 = """update transactions set complete='T' where trn_date > 'YESTERDAY' """

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
