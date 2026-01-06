#!/usr/bin/env python2
"""
<title>
getsoaperror.py, Version 10.04.05
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

import fdb
#import fdb;fdb.init(type_conv=200)
#import mx.DateTime
import datetime

#redirect stdout and stderr

if os.name == 'nt':
	logfile = "d:/tmp/getreruns.log"
else:
	logfile = "/data/tmp/getreruns.log"
havelog = 1;
#print "getreruns log ", logfile

####################################################################

#
mydb  = "minder"
#print  sys.argv
#print  len(sys.argv)

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
logfile = logfile + mydb + ".log"
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

# first must get errored transactions
#query0 = """select trn_version, trn_type, trn_delimiter, person_id, device_id, trn_data, input_source from transactions4_archive where trn_date > 'YESTERDAY' and trn_class in ('E', 'R') """
#query0 = """select trn_type, trn_code, trn_date, person_id, device_id, input_source, instance_id, wh_id, locn_id, object, reference, qty, sub_locn_id  from transactions_archive where trn_date > 'YESTERDAY' and complete in ('R') """
query0 = """select trn_type, trn_code, trn_date, person_id, device_id, input_source, instance_id, wh_id, locn_id, object, reference, qty, sub_locn_id  from transactions_archive where  complete in ('R') """

cur2.execute(query0)

## get data record
data_fields = cur2.fetchone()
#print data_fields
if data_fields is None:
	print "no transactions with R complete "
else:
	while not data_fields is None:
		#print data_fields
		tran_type = data_fields[0]
		tran_class = data_fields[1]
		tran_date = data_fields[2]
		tran_user = data_fields[3]
		tran_device = data_fields[4]
		tran_source = data_fields[5]
		tran_instance = data_fields[6]
		tran_wh = data_fields[7]
		tran_locn = data_fields[8]
		tran_object = data_fields[9]
		tran_ref = data_fields[10]
		tran_qty = data_fields[11]
		tran_sublocn = data_fields[12]
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

query4 = """update transactions_archive set complete = 'e' where trn_date > 'YESTERDAY' and complete='E' """

cur2.execute(query4)

query5 = """update transactions_archive set complete = 'r' where trn_date > 'YESTERDAY' and complete='R' """

cur2.execute(query5)

print "end of updates" 

con.commit()

print "end - of "


#revert stdin stdout and stderr
if (havelog == 1):
	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__
	out.close()

###
