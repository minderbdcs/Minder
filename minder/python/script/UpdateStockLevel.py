#!/usr/bin/env python2
"""
<title>
UpdateStockLevel.py, Version 10.04.05
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
	logfile = "d:/tmp/updatestocklevel.log"
else:
	logfile = "/tmp/updatestocklevel.log"
havelog = 1;

#
#redirect stdout and stderr
if (havelog == 1):
	out = open(logfile,'w')
	sys.stdout = out
	sys.stderr = out

#

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

tran_type = "UASL"
tran_class = "S"
tran_delim = "|"
tran_user = "BDCS"
tran_device = "XX"
tran_source = "BSSKKKKK"
print "trans type",tran_type,"class",tran_class
print "user",tran_user,"dev",tran_device,"source",tran_source

query1 = """select prod_id from prod_profile """

cur.execute(query1)

## get data record
data_fields = cur.fetchone()
#print data_fields
if data_fields is None:
	print "no products "
	tran_item = ""
else:
	#print data_fields
	while not data_fields is None:
		tran_item = data_fields[0]
		print "product id %s" % tran_item
		if len(tran_item) > 1:
			mytime = mx.DateTime.now()
			# first must get message id
			cur2.callproc("send_pickable_stock",(
				tran_item, 
				tran_user,
				tran_device,
				mytime))
			print "called send stock"
		data_fields = cur.fetchone()

con.commit()


#revert stdin stdout and stderr
if (havelog == 1):
	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__
	out.close()

###
