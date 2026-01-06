#!/usr/bin/env python2
"""
<title>
getgrnv.py, Version 10.04.05
</title>
<long>
Creates/Updates tables in the database
for prod profile linked to location 
via home_locn_id
create a transaction file for importfile
so that a grnv is run for a quantity of 100
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

#import kinterbasdb
#import mx.DateTime
import kinterbasdb;kinterbasdb.init(type_conv=200)

#redirect stdout and stderr

if os.name == 'nt':
	logfile = "d:/tmp/getgrnv.log"
else:
	logfile = "/tmp/getgrnv.log"
havelog = 1;

prtfile = "/tmp/grnvtran.txt"
#
#redirect stdout and stderr
if (havelog == 1):
	out = open(logfile,'w')
	sys.stdout = out
	sys.stderr = out

prt = open(prtfile,'w')

if os.name == 'nt':
	con = kinterbasdb.connect(
		dsn="127.0.0.1:d:/asset.rf/database/wh.v39.gdb",
		user="sysdba",
		password="masterkey")
else:
	#	dsn="/data/asset.rf/wh.v39.gdb",
	con = kinterbasdb.connect(
		dsn="127.0.0.1:minder",
		user="sysdba",
		password="masterkey")

print "connected to db"
cur = con.cursor()
cur2 = con.cursor()

wk_date = time.strftime("%d/%m/%y")
#read std or 1st input parm
tran_type = "GRNV"
tran_class = "P"
tran_delim = "|"
tran_user = "BDCS"
tran_device = "XX"
tran_source = "SSSSSSSSS"
wk_tran_date = time.strftime("%Y%m%d")
wk_tran_time = time.strftime("%H%M%S")
print "trans type",tran_type,"class",tran_class
print "user",tran_user,"dev",tran_device,"source",tran_source

#mytime = mx.DateTime.now()
mytime = "NOW"

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
query1 = """select p1.prod_id, h1.wh_id, h1.locn_id from prod_profile p1 join location h1 on substr(p1.home_locn_id,1,2) = h1.wh_id and substr(p1.home_locn_id,3,10) = h1.locn_id """

cur.execute(query1)

tran_object = ""
wk_prod_id = ""
tran_wh = ""
tran_locn = ""
wk_wh_id = ""
wk_locn_id = ""
## get data record
data_fields = cur.fetchone()
#print data_fields
if data_fields is None:
	print "no products with home location "
else:
	while not data_fields is None:
		#print data_fields
		wk_prod_id = ""
		wk_wh_id = ""
		wk_locn_id = ""
		wk_prod_id = data_fields[0]
		wk_wh_id = data_fields[1]
		wk_locn_id = data_fields[2]
		tran_object = "%-30.30s" % (wk_prod_id)
		tran_wh = "%-2.2s" % (wk_wh_id)
		tran_locn = "%-10.10s" % (wk_locn_id)
		tran_sublocn = "0013      "
		tran_ref = "LP|WLD00016|1|1|100|1|0|A               "
		tran_qty = "0000000100"
		tran_data = tran_type + tran_class
		tran_data = tran_data + wk_tran_date + wk_tran_time
		tran_data = tran_data + tran_object
		tran_data = tran_data + tran_wh + tran_locn
		tran_data = tran_data + tran_sublocn
		tran_data = tran_data + tran_ref
		tran_data = tran_data + tran_qty
		tran_data = tran_data + "BDCS    XXSSSSSSSSS"
		prt.write("%s\n" %(tran_data))
		#end of getting product and location
		data_fields = cur.fetchone()

con.commit()


con.commit()


print "end - of "


prt.close()

#revert stdin stdout and stderr
if (havelog == 1):
	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__
	out.close()

###
