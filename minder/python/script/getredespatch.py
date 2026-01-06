#!/usr/bin/env python2
"""
<title>
getredespatch.py, Version 02.08.12
</title>
<long>
Re Exports Despatches of the day
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
#import fdb;fdb.init(type_conv=200)
#import mx.DateTime
import datetime

#redirect stdout and stderr

if os.name == 'nt':
	logfile = "d:/tmp/getredespatch.log"
else:
	logfile = "/data/tmp/getredespatch.log"
havelog = 1;
#print "getreruns log ", logfile

####################################################################
#


mydb = "minder"
myHost = "localhost"
myuser = "minder"
mypasswd = "minder"
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
print "mydb", mydb
print "logfile", logfile

print time.asctime()
####################################################################
#
#redirect stdout and stderr
if (havelog == 1):
	out = open(logfile,'w')
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
#read std or 1st input parm
tran_type = "GCPD"
tran_class = "S"
tran_delim = "|"
tran_user = "BDCS"
tran_device = "XX"
tran_source = "SSS"
#print "trans type",tran_type,"class",tran_class
#print "user",tran_user,"dev",tran_device,"source",tran_source

#mytime = mx.DateTime.now()

tran_printer = "PA"
# first must get errored transactions
query0 = """select EXPORT_DESPATCH_PRINTER from control  """
cur2.execute(query0)
## get data record
data_fields = cur2.fetchone()
#print data_fields
if data_fields is None:
	print "no Despatch Printer  "
else:
	while not data_fields is None:
		#print data_fields
		tran_printer = data_fields[0]
		data_fields = cur2.fetchone()

#query1 = """select pick_order from pick_order where pick_status='DX' and (exported_despatch_post_date is null)  """
query1 = """select pick_order from pick_order where pick_status='DX' and (exported_despatch_date is not null) and (exported_despatch_post_date is null)  """
query1 = """select pick_order from pick_order where pick_status='DX' and (exported_despatch_date is not null) and (exported_despatch_post_date is null) and (dateadd(DAY,+5,exported_despatch_date) >= 'TODAY')  """

cur2.execute(query1)

## get data record
data_fields = cur2.fetchone()
#print data_fields
if data_fields is None:
	print "no orders with null despatch post date "
else:
	while not data_fields is None:
		#print data_fields
		tran_type = "DSRE"
		tran_class = "R"
		tran_date = "NOW"
		tran_user = "BDCS"
		tran_device = "XX"
		tran_source = "SSSSSSSSS"
		tran_instance = "MINDER    "
		tran_wh = "XX"
		tran_locn = ""
		tran_object = data_fields[0]
		tran_ref = "rerun despatch export"
		tran_qty = 1
		tran_sublocn = tran_printer
		print "got %s %s %s " % (tran_type, tran_source, tran_object)
		print "trans type",tran_type,"class",tran_class
		print "item",tran_object,"locn",tran_locn
		print "sublocn",tran_sublocn,"ref",tran_ref,"qty",tran_qty
		print "user",tran_user,"dev",tran_device,"source",tran_source

		# then add transactions
		
		query2 = """select response_text from add_tran_response(
			'%s','%s','%s','%s','%s','%s','%s','%s','F','','MASTER    ',0,'%s','%s','%s','%s')
		"""
		
		cur.execute(query2 % (tran_wh, tran_locn, tran_object, tran_type, tran_class, tran_date, tran_ref, tran_qty, tran_sublocn, tran_source, tran_user, tran_device ))
		
		## get data record
		data_fields = cur.fetchone()
		#print data_fields
		if data_fields is None:
			print "no record id "
			record_id = ""
		else:
			#print data_fields
			record_id = data_fields[0]
		
		print "response %s" % record_id
		# if response is "" then must look at transactions table
		# to see whether worked or not	
		# ok so is rerun
		data_fields = cur2.fetchone()

print "end of reading transactions " 

#query4 = """update transactions_archive set complete = 'e' where trn_date > 'YESTERDAY' and complete='E' """

#cur2.execute(query4)

#query5 = """update transactions_archive set complete = 'r' where trn_date > 'YESTERDAY' and complete='R' """

#cur2.execute(query5)

print "end of updates" 

con.commit()

print "end - of "


#revert stdin stdout and stderr
if (havelog == 1):
	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__
	out.close()

###
