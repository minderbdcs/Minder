#!/usr/bin/env python2
"""
<title>
archivetrans4.py, Version 10.08.05
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
#import mx.DateTime
if sys.version > "2.6":
	kinterbasdb.init(type_conv=200)

#redirect stdout and stderr

if os.name == 'nt':
	logfile = "d:/tmp/archivetrans4."
else:
	logfile = "/tmp/archivetrans4."
havelog = 1;

mydb = "minder"
if len(sys.argv)>1:
	inData = sys.argv[1]
	myparms = inData.split("=")
	if "db"  == myparms[0]:
		mydb = myparms[1]
print "mydb", mydb
logfile = logfile + mydb + ".log"
print time.asctime()
#
#redirect stdout and stderr
if (havelog == 1):
	out = open(logfile,'w')
	sys.stdout = out
	sys.stderr = out

print "mydb", mydb
print time.asctime()
if os.name == 'nt':
	#	dsn="127.0.0.1:d:/asset.rf/database/wh.v39.gdb",
	con = kinterbasdb.connect(
		dsn="127.0.0.1:" + mydb,
		user="sysdba",
		password="masterkey")
else:
	#	dsn="/data/asset.rf/wh.v39.gdb",
	#	dsn="127.0.0.1:minder",
	con = kinterbasdb.connect(
		dsn="127.0.0.1:" + mydb,
		user="sysdba",
		password="masterkey")

print "connected to db"
cur = con.cursor()
cur2 = con.cursor()

wk_date = time.strftime("%d/%m/%y")
wk_line = 0	

wk_stmt_web_requests =  "update web_requests set request_status='E1' where (TRN_DATE IS NOT NULL) AND (DIFFDATE(ZEROTIME(TRN_DATE),'TODAY',4) > 7)"
cur.execute(wk_stmt_web_requests)
cur.callproc("web_request4_archive",())
print "called proc to archive web requests"

wk_stmt_trans4 =  """update transactions4 t1 set t1.complete='T'  where t1.message_id in (
select t2.message_id from transactions4 t2 left outer join web_requests on t2.message_id = web_requests.message_id where web_requests.message_id is null) """
cur.execute(wk_stmt_trans4)
#mytime = mx.DateTime.now()

cur.callproc("tran4_archive",())
print "called proc to archive trans 4"

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
