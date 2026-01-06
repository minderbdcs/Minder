#!/usr/bin/env python2
"""
<title>
importfile.py, Version 26.03.03
</title>
<long>
Inserts records into transactions table
<br>
Paramters: <tt> filename </tt>
<br>
This scans the file <tt>filename</tt> for data 
and puts the text into the database
<br>
</long>
"""
import sys
import string
import fileinput
import getpass, sys, time , os

import datetime
#import fdb
#import mx
#import fdb;fdb.init(type_conv=200)
import fdb


#print "importfile filename logfilename "
if len(sys.argv)>1:
	print "import file ", sys.argv[1]
	infile  =  sys.argv[1] 
else:
	print "import file stdin"
	infile  =  sys.stdin 

if len(sys.argv)>2:
        print "log file ", sys.argv[2]
        logfile = sys.argv[2]
else:
        print "log file stdout"
        logfile = sys.__stdout__
#
#if os.name == 'nt':
#	logfile = "d:/tmp/importprod."
#else:
#	logfile = "/tmp/importprod."
havelog = 1;

mydb = "minder"
myHost = "localhost"
wkSystemId = 0
if len(sys.argv)== 1:
	print  sys.argv[0]
	print "commands parameters:"
	print " host=dbHost - defaults to localhost"
	print " db=dbAlias - defaults to minder "
	print " tmp=folder for the log file - defaults to /tmp "
	print " log=log file for output "
	print " in=import file for input "
	sys.exit()
for i in range( len(sys.argv)):
	inData = sys.argv[i]
	myparms = inData.split("=")
	if "db"  == myparms[0]:
		mydb = myparms[1]
	if "tmp"  == myparms[0]:
		logfile  = myparms[1] + "/importprod."
	if "host"  == myparms[0]:
		myHost = myparms[1] 
	if "log"  == myparms[0]:
		logfile  =  myparms[1] 
	if "in"  == myparms[0]:
		infile  =  myparms[1] 

logfile = logfile + "-" + mydb + ".log"

#redirect stdout and stderr
out = open(logfile,'a')
sys.stdout = out
sys.stderr = out


con = fdb.connect(
	dsn=myHost+":" + mydb,
	user="sysdba",
	password="masterkey")

print "connected to db"
cur = con.cursor()
cur2 = con.cursor()
cur3 = con.cursor()
#read std or 1st input parm
for line in fileinput.input(infile):
	print len(line)
	print line
	#sys.exit()
	# expect either length  140  for reference length 40 or xxx for reference length 1024 
	# 4 + 1 + 8 + 6 + 30 + 10 + 10 + 40 + 10 + 8 + 2 + 9 = 138 + cr or crlf
	# 4 + 1 + 8 + 6 + 30 + 10 + 10 + 1024 + 10 + 8 + 2 + 9 = 1122 + cr or cflf
	tran_type = line[:4]
	tran_class = line[4:5] #1
	tran_date = line[5:13] #8
	tran_time = line[13:19] #6
	tran_item = line[19:49] #30
	tran_item = tran_item.strip()
	tran_locn = line[49:59] #10
	tran_locn = tran_locn.strip()
	tran_sublocn = line[59:69] #10
	tran_sublocn = tran_sublocn.strip()
	if len(line) > 1120:
 		tran_ref = line[69:1093] #1024
	 	tran_ref = tran_ref.strip()
		tran_qty = line[1093:1103] #10
		tran_user = line[1103:1111] #8
		tran_user = tran_user.strip()
		tran_device = line[1111:1113] #2
		tran_source = line[1113:1122] #9
	else:
 		tran_ref = line[69:109] #40
	 	tran_ref = tran_ref.strip()
		tran_qty = line[109:119] #10
		tran_user = line[119:127] #8
		tran_user = tran_user.strip()
		tran_device = line[127:129] #2
		tran_source = line[129:138] #9
	print "trans type",tran_type,"class",tran_class,"date",tran_date
	print "time",tran_time,"item",tran_item,"locn",tran_locn
	print "sublocn",tran_sublocn,"ref",tran_ref,"qty",tran_qty
	print "user",tran_user,"dev",tran_device,"source",tran_source

	# Insert into Transactions
	#mytime = mx.DateTime.now()
	#mytime = mx.DateTime.Timestamp(int(tran_date[:4]),
	#	int(tran_date[4:6]),
	#	int(tran_date[6:8]),
	#	int(tran_time[:2]),
	#	int(tran_time[2:4]),
	#	int(tran_time[4:6]))
	mytime = datetime.datetime(int(tran_date[:4]),
		int(tran_date[4:6]),
		int(tran_date[6:8]),
		int(tran_time[:2]),
		int(tran_time[2:4]),
		int(tran_time[4:6]))


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
	cur2.execute("""select record_id from transactions 
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
	tran_record = cur2.fetchonemap()
	if tran_record is None:
		myrecord = None
	else:
		myrecord = tran_record['record_id']
			
	## process it
	if myrecord is None:
		print "No Transaction Found - Processed OK"
		print "trans type",tran_type,"class",tran_class,"date",tran_date
		print "time",tran_time,"item",tran_item,"locn",tran_locn
		print "sublocn",tran_sublocn,"ref",tran_ref,"qty",tran_qty
		print "user",tran_user,"dev",tran_device,"source",tran_source
	else:
		print "record_id is",myrecord
              
		cur3.execute("""select error_text, complete from transactions 
			where record_id = %d """ % myrecord)   
		tran_record = cur3.fetchonemap()
		if tran_record['complete'] == 'F':
	     		print "Failed to process ",tran_record['error_text']
                        print "record_id is ",str(myrecord)
                        #print "trans type",tran_type,"class",tran_class,"date",tran_date
                        #print "time",tran_time,"item",tran_item,"locn",tran_locn
                        #print "sublocn",tran_sublocn,"ref",tran_ref,"qty",tran_qty
                        #print "user",tran_user,"dev",tran_device,"source",tran_source

        con.commit()


con.close()

print "end - closed database"
#revert stdin stdout and stderr
sys.stdout = sys.__stdout__
sys.stderr = sys.__stderr__

out.close()
###
