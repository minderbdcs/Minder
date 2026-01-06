#!/usr/bin/env python2
"""
<title>
archiveissn.py, Version 29.02.08
</title>
<long>
Updates ISSN table to Move 0 qty ISSNs to the XX repository
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

import fdb
#import mx.DateTime
#import fdb;fdb.init(type_conv=200)

#redirect stdout and stderr

if os.name == 'nt':
	logfile = "d:/tmp/archiveissn.log"
else:
	logfile = "/tmp/archiveissn.log"
havelog = 1;

###############################################################################
#connect to db


mydb = "minder"
myHost = "localhost"
myuser = "minder"
mypasswd = "minder"
wkConduitWait = None
wkConduitLimit = None
for i in range( len(sys.argv)):
	inData = sys.argv[i]
	myparms = inData.split("=")
	if "db"  == myparms[0]:
		mydb = myparms[1]
	if "host"  == myparms[0]:
		myHost = myparms[1]
	if "user"  == myparms[0]:
		myuser = myparms[1]
	if "passwd"  == myparms[0]:
		mypasswd = myparms[1]
	if "tmp"  == myparms[0]:
		logfile  = myparms[1] + "/archiveissn."
	if "condwait"  == myparms[0]:
		wkConduitWait = int(myparms[1] )
	if "condlimit"  == myparms[0]:
		wkConduitLimit = int( myparms[1] )
print "mydb", mydb
logfile = logfile + mydb + ".log"
print "logfile", logfile
print time.asctime()

#
#redirect stdout and stderr
if (havelog == 1):
	#out = open(logfile,'w')
	out = open(logfile,'a')
	sys.stdout = out
	sys.stderr = out

print "mydb", mydb
print "myhost", myHost
print "myuser", myuser
print "mypassword", mypasswd
con = fdb.connect(
	dsn=myHost+":"+ mydb,
	user=myuser,
	password=mypasswd)

print "connected to db"
cur = con.cursor()
cur2 = con.cursor()

wk_date = time.strftime("%d/%m/%y")
wk_line = 0	

#mytime = mx.DateTime.now()

#query0 = """update issn set prev_prev_wh_id=prev_wh_id,prev_wh_id=wh_id, wh_id='XD'  where current_qty = 0 and (wh_id < 'X' or wh_id > 'X~') and issn_status = 'DX' """
#query1 = """update issn set prev_prev_wh_id=prev_wh_id,prev_wh_id=wh_id, wh_id='XX'  where current_qty = 0 and (wh_id < 'X' or wh_id > 'X~') """
query0 = """update issn set prev_prev_wh_id=prev_wh_id,prev_wh_id=wh_id, wh_id='XD', locn_id='00000000'  where current_qty = 0 and (wh_id < 'X' or wh_id > 'X~') and issn_status = 'DX' """
query1 = """update issn set prev_prev_wh_id=prev_wh_id,prev_wh_id=wh_id, wh_id='XX', locn_id='00000000'  where current_qty = 0 and (wh_id < 'X' or wh_id > 'X~') """

cur2.execute(query0)
cur2.execute(query1)

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
