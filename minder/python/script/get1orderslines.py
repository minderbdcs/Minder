#!/usr/bin/env python2
"""
<title>
get1orderslines.py, Version 10.04.05
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
	logfile = "d:/tmp/get1orderslines.log"
else:
	logfile = "/tmp/get1orderslines.log"
havelog = 1;

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
#read std or 1st input parm
tran_type = "GCLO"
tran_class = "S"
tran_delim = "|"
tran_user = "BDCS"
tran_device = "XX"
tran_source = "KSSS"
print "trans type",tran_type,"class",tran_class
print "user",tran_user,"dev",tran_device,"source",tran_source

mytime = mx.DateTime.now()

query3 = """select default_pick_priority, default_wh_id from control
"""

cur.execute(query3 )

## get data record
data_fields = cur.fetchone()
#print data_fields
if data_fields is None:
	print "no record id "
	tran_priority = ""
	tran_wh = ""
else:
	#print data_fields
	tran_priority = data_fields[0]
	tran_wh = data_fields[1]

print "priority %s" % tran_priority
print "wh_id %s" % tran_wh

# first must get message id
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
original_message_id = message_id

# then add transactions4

tran_data = tran_user + tran_delim + tran_device + tran_delim + message_id
tran_data += tran_delim + "<ContactInstanceID>2102040" + tran_delim
print "tran data", tran_data

query2 = """select rec_id from add_tran_v4('V4',
	'%s','%s','%s','%s','%s','%s','%s','%s','F','','MASTER',0,'%s')
"""

cur.execute(query2 % (tran_type, tran_class, mytime, tran_delim, tran_user, tran_device, message_id, tran_data, tran_source ))

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

con.commit()

# ok now start to wait for event
MY_EVENT_STR = 'MESSAGE_ID_%s' % message_id
MY_EVENT = [ MY_EVENT_STR ]

conduit = con.event_conduit(MY_EVENT)

print "about to wait for %s\n" % MY_EVENT

result = conduit.wait()

print "event occurred "
print result

conduit.close()

# ok for all the orders downloaded
# must send the message to update the order status to DA UCIS
# then
# must send the message to get the address GCNA and instructions GSMD

# must get the orders we downloaded
query4 = """select pick_order from pick_order where update_id='%s' """
#
print "original message ", original_message_id
cur.execute(query4 % (original_message_id))
#
## get data record
data_fields = cur.fetchone()
#print data_fields
if data_fields is None:
	print "no orders "
	order_id = ""
else:
	while not data_fields is None:
		#print data_fields
		order_id = data_fields[0]
		print "order ", order_id
		# do the UCIS for this order 
		tran_type = "UCIS"
		tran_class = "S"
		tran_source = "SSSSSS"
		print "trans type",tran_type,"class",tran_class
		print "user",tran_user,"dev",tran_device,"source",tran_source
		
		mytime = mx.DateTime.now()
		
		# first must get message id
		query1 = """select message_id from get_next_message """
		
		cur2.execute(query1)
		
		## get data record
		data_fields2 = cur2.fetchone()
		#print data_fields
		if data_fields2 is None:
			print "no message_id "
			message_id = ""
		else:
			#print data_fields
			message_id = data_fields2[0]
		
		print "message id %s" % message_id
		
		# then add transactions4
		
		tran_data = tran_user + tran_delim + tran_device + tran_delim + message_id 
		tran_data += tran_delim + "<ContactInstanceID>" + order_id + tran_delim
		tran_data += "<ContactInstanceStatusCode>DA" + tran_delim
		#tran_data += "<ContactInstanceStatusCode>DT" + tran_delim
		#wk_datetime = time.strftime("%Y/%m/%dT%H:%M:%S")
		wk_datetime = time.strftime("%Y-%m-%dT%H:%M:%S")
		print "date time ",wk_datetime
		tran_data += "<UpdateDateTime>" + wk_datetime + tran_delim
		print "tran data", tran_data
		
		query2 = """select rec_id from add_tran_v4('V4',
			'%s','%s','%s','%s','%s','%s','%s','%s','F','','MASTER',0,'%s')
		"""
		
		cur2.execute(query2 % (tran_type, tran_class, mytime, tran_delim, tran_user, tran_device, message_id, tran_data, tran_source ))
		
		## get data record
		data_fields2 = cur2.fetchone()
		#print data_fields
		if data_fields2 is None:
			print "no record id "
			record_id = ""
		else:
			#print data_fields
			record_id = data_fields2[0]
		
		print "record id %s" % record_id
		
		# finally add web_services
		
		print "priority %s" % tran_priority
		
		cur2.callproc("add_message_v4",(
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
		# do the GSMD for this order 
		tran_type = "GSMD"
		tran_class = "S"
		tran_source = "SSSS"
		print "trans type",tran_type,"class",tran_class
		print "user",tran_user,"dev",tran_device,"source",tran_source
		
		mytime = mx.DateTime.now()
		
		# first must get message id
		query1 = """select message_id from get_next_message """
		
		cur2.execute(query1)
		
		## get data record
		data_fields2 = cur2.fetchone()
		#print data_fields
		if data_fields2 is None:
			print "no message_id "
			message_id = ""
		else:
			#print data_fields
			message_id = data_fields2[0]
		
		print "message id %s" % message_id
		
		# then add transactions4
		
		tran_data = tran_user + tran_delim + tran_device + tran_delim + message_id 
		tran_data += tran_delim + "<ContactInstanceID>" + order_id + tran_delim
		print "tran data", tran_data
		
		query2 = """select rec_id from add_tran_v4('V4',
			'%s','%s','%s','%s','%s','%s','%s','%s','F','','MASTER',0,'%s')
		"""
		
		cur2.execute(query2 % (tran_type, tran_class, mytime, tran_delim, tran_user, tran_device, message_id, tran_data, tran_source ))
		
		## get data record
		data_fields2 = cur2.fetchone()
		#print data_fields
		if data_fields2 is None:
			print "no record id "
			record_id = ""
		else:
			#print data_fields
			record_id = data_fields2[0]
		
		print "record id %s" % record_id
		
		# finally add web_services
		
		print "priority %s" % tran_priority
		
		cur2.callproc("add_message_v4",(
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
		# do the GCNA for this order 
		tran_type = "GCNA"
		tran_class = "S"
		tran_source = "SSSS"
		print "trans type",tran_type,"class",tran_class
		print "user",tran_user,"dev",tran_device,"source",tran_source
		
		mytime = mx.DateTime.now()
		
		# first must get message id
		query1 = """select message_id from get_next_message """
		
		cur2.execute(query1)
		
		## get data record
		data_fields2 = cur2.fetchone()
		#print data_fields
		if data_fields2 is None:
			print "no message_id "
			message_id = ""
		else:
			#print data_fields
			message_id = data_fields2[0]
		
		print "message id %s" % message_id
		
		# then add transactions4
		
		tran_data = tran_user + tran_delim + tran_device + tran_delim + message_id 
		tran_data += tran_delim + "<ContactInstanceID>" + order_id + tran_delim
		print "tran data", tran_data
		
		query2 = """select rec_id from add_tran_v4('V4',
			'%s','%s','%s','%s','%s','%s','%s','%s','F','','MASTER',0,'%s')
		"""
		
		cur2.execute(query2 % (tran_type, tran_class, mytime, tran_delim, tran_user, tran_device, message_id, tran_data, tran_source ))
		
		## get data record
		data_fields2 = cur2.fetchone()
		#print data_fields
		if data_fields2 is None:
			print "no record id "
			record_id = ""
		else:
			#print data_fields
			record_id = data_fields2[0]
		
		print "record id %s" % record_id
		
		# finally add web_services
		
		print "priority %s" % tran_priority
		
		cur2.callproc("add_message_v4",(
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
		data_fields = cur.fetchone()

print "end of orders" 

con.commit()

print "end - of "


#revert stdin stdout and stderr
if (havelog == 1):
	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__
	out.close()

###
