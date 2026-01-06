#!/usr/bin/env python2
"""
<title>
importfile.py, Version 20.02.06
</title>
<long>
Creates/Updates tables in the database
<br>
Parameters: <tt>input file</tt></tt>log file</tt>
the input file holds <tt>wh_id</tt><tt>location</tt>
<br>
So check if the unique record exists
If not then insert it
else update it
<br>
</long>
"""
import sys
import string
import time , os ,glob
import fileinput

import kinterbasdb
import mx.DateTime

def addtran(tran_type,tran_class,tran_locn,tran_item,tran_ref,tran_qty,tran_sublocn,tran_user,tran_device,tran_source):
	mytime = mx.DateTime.now()
	cur.callproc("add_tran",(
		tran_locn[:2],
		tran_locn[2:], 
		tran_item, 
		tran_type,
		tran_class,
		mytime,
		tran_ref,
		int(tran_qty),
		'F',
		'',
		'MASTER    ',
		0,
		tran_sublocn,
		tran_source,
		tran_user,
		tran_device))
	print "called proc to add record"
	cur.execute("""select record_id from transactions 
		where wh_id = ? and 
		locn_id = ? and 
		object = ? and 
		trn_date = ? and 
		trn_type = ? and 
		trn_code = ? and 
		device_id = ? and 
		complete = 'F' """, (
		tran_locn[:2],
		tran_locn[2:], 
		tran_item, 
		mytime,
		tran_type,
		tran_class,
		tran_device))   
	tran_record = cur.fetchonemap()
	if tran_record is None:
		myrecord = None
	else:
		myrecord = tran_record['record_id']
			
	## process it
	if myrecord is None:
		print "No Transaction Found - Processed OK"
		print "trans type",tran_type,"class",tran_class
		print "item",tran_item,"locn",tran_locn
		print "sublocn",tran_sublocn,"ref",tran_ref,"qty",tran_qty
		print "user",tran_user,"dev",tran_device,"source",tran_source
	else:
		print "record_id is",myrecord
              
		cur.execute("""select error_text, complete from transactions 
			where record_id = %d """ % myrecord)   
		tran_record = cur.fetchonemap()
		if tran_record['complete'] == 'F':
	     		print "Failed to process ",tran_record['error_text']
                        print "record_id is ",str(myrecord)
                        #print "trans type",tran_type,"class",tran_class,"date",tran_date
                        #print "time",tran_time,"item",tran_item,"locn",tran_locn
                        #print "sublocn",tran_sublocn,"ref",tran_ref,"qty",tran_qty
                        #print "user",tran_user,"dev",tran_device,"source",tran_source


#redirect stdout and stderr

if len(sys.argv)>0:
	print "allocateSO ", sys.argv[1]
	logfile = sys.argv[1]
	print "log ",logfile
	havelog = 1;
else:
	print "log stdin"
	havelog = 0;

#
#redirect stdout and stderr
if (havelog == 1):
	out = open(logfile,'a')
	sys.stdout = out
	sys.stderr = out

if os.name == 'nt':
	con = kinterbasdb.connect(
		dsn="127.0.0.1:d:/asset.rf/database/wh.v39.gdb",
		user="sysdba",
		password="masterkey")
else:
	con = kinterbasdb.connect(
		dsn="/data/asset.rf/wh.v39.gdb",
		user="sysdba",
		password="masterkey")

print "connected to db"
cur = con.cursor()
cur2 = con.cursor()

#need status, locn
wk_date = time.strftime("%d/%m/%y %H:%M:%S")
print wk_date
# add tran
addtran("PKBS","P","          ","","BDCS",0,"","BDCS","XX","SSSSSSSSS")

print "end - of recalseq"
wk_date = time.strftime("%d/%m/%y %H:%M:%S")
print wk_date

con.commit()

wk_date = time.strftime("%d/%m/%y %H:%M:%S")
print wk_date

#revert stdin stdout and stderr
if (havelog == 1):
	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__
	out.close()

###
