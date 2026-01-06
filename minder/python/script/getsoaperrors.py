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

import kinterbasdb
import mx.DateTime

#redirect stdout and stderr

if os.name == 'nt':
	logfile = "d:/tmp/getsoaperror.log"
else:
	logfile = "/tmp/getsoaperror.log"
havelog = 1;
#print "getsoaperrors log ", logfile

#
#redirect stdout and stderr
if (havelog == 1):
	out = open(logfile,'w')
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

wk_date = time.strftime("%d/%m/%y")
wk_line = 0	
#read std or 1st input parm
tran_type = "GCPD"
tran_class = "S"
tran_delim = "|"
tran_user = "BDCS"
tran_device = "XX"
tran_source = "SSS"
print "trans type",tran_type,"class",tran_class
print "user",tran_user,"dev",tran_device,"source",tran_source

mytime = mx.DateTime.now()

# first must get errored transactions
query0 = """select trn_version, trn_type, trn_delimiter, person_id, device_id, trn_data, input_source from transactions4_archive where trn_date > 'YESTERDAY' and trn_class in ('E', 'R') """
#query0 = """select trn_version, trn_type, trn_delimiter, person_id, device_id, trn_data, input_source from transactions4_archive where trn_class in ('E', 'R') """

cur2.execute(query0)

## get data record
data_fields = cur2.fetchone()
#print data_fields
if data_fields is None:
	print "no transactions with E trn_class "
else:
	while not data_fields is None:
		#print data_fields
		tran_type = data_fields[1]
		tran_delim = data_fields[2]
		tran_user = data_fields[3]
		tran_device = data_fields[4]
		tran_data = data_fields[5]
		tran_source = data_fields[6]
		tran_data_part = tran_data.split(tran_delim )
		print "got %s %s %s " % (tran_type, tran_source, tran_data)

		# must get message id
		query1 = """select message_id from get_next_message """
		
		cur.execute(query1)
		
		## get data record
		data_fields = cur.fetchone()
		#print data_fields
		if data_fields is None:
			print "no message_id "
			message_id = ""
		else:
			#print data_fields
			message_id = data_fields[0]
		
		print "message id %s" % message_id
		
		# then add transactions4
		
		#tran_data2 = tran_user + tran_delim + tran_device + tran_delim + message_id + tran_delim
		tran_data_part[2] = message_id
		tran_data3 = string.join(tran_data_part, tran_delim)
		print "tran data", tran_data3
		
		query2 = """select rec_id from add_tran_v4('V4',
			'%s','%s','%s','%s','%s','%s','%s','%s','F','','MASTER',0,'%s')
		"""
		
		cur.execute(query2 % (tran_type, tran_class, mytime, tran_delim, tran_user, tran_device, message_id, tran_data3, tran_source ))
		
		## get data record
		data_fields = cur.fetchone()
		#print data_fields
		if data_fields is None:
			print "no record id "
			record_id = ""
		else:
			#print data_fields
			record_id = data_fields[0]
		
		print "record id %s" % record_id
		
		# finally add web_services
		
		query3 = """select default_pick_priority from control
		"""
		
		cur.execute(query3 )
		
		## get data record
		data_fields = cur.fetchone()
		#print data_fields
		if data_fields is None:
			print "no record id "
			tran_priority = ""
		else:
			#print data_fields
			tran_priority = data_fields[0]
		
		print "priority %s" % tran_priority
		
		cur.callproc("add_message_v4",(
			message_id,
			'V4', 
			tran_type,
			tran_class,
			mytime,
			tran_user,
			tran_device,
			tran_priority,
			'WS',
			'',
			None))
		print "called proc to add message"
	
		# ok so all are rerun
		#must update the trn_class to E1
		data_fields = cur2.fetchone()

print "end of reading transactions4 " 

query4 = """update transactions4_archive set trn_class = 'e' where trn_date > 'YESTERDAY' and trn_class='E' """

cur2.execute(query4)

query5 = """update transactions4_archive set trn_class = 'r' where trn_date > 'YESTERDAY' and trn_class='R' """

cur2.execute(query5)

print "end of order groups" 

con.commit()

print "end - of "


#revert stdin stdout and stderr
if (havelog == 1):
	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__
	out.close()

###
