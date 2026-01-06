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

#import kinterbasdb
import kinterbasdb;kinterbasdb.init(type_conv=200)
#import mx.DateTime

#redirect stdout and stderr

if os.name == 'nt':
	logfile = "d:/tmp/getredsdx.log"
else:
	logfile = "/tmp/getredsdx.log"
havelog = 1;
#print "getreruns log ", logfile

####################################################################

#
mydb  = "minder"
myManId  = ""
#print  sys.argv
#print  len(sys.argv)
if len(sys.argv)>1:
	inData = sys.argv[1]
	myparms = inData.split("=")
	if "db"  == myparms[0]:
		mydb = myparms[1]
	if "manifest"  == myparms[0]:
		myManId = myparms[1]
if len(sys.argv)>2:
	inData = sys.argv[2]
	myparms = inData.split("=")
	if "db"  == myparms[0]:
		mydb = myparms[1]
	if "manifest"  == myparms[0]:
		myManId = myparms[1]
if len(sys.argv)>3:
	inData = sys.argv[3]
	myparms = inData.split("=")
	if "db"  == myparms[0]:
		mydb = myparms[1]
	if "manifest"  == myparms[0]:
		myManId = myparms[1]
print "mydb", mydb
print "logfile", logfile
print "ManifestId", myManId
print time.asctime()
####################################################################
#
#redirect stdout and stderr
if (havelog == 1):
	out = open(logfile,'w')
	sys.stdout = out
	sys.stderr = out

if os.name == 'nt':
	#	dsn="127.0.0.1:d:/asset.rf/database/wh.v39.gdb",
	#	dsn="127.0.0.1:furnlive",
	con = kinterbasdb.connect(
		dsn="127.0.0.1:" + mydb,
		user="sysdba",
		password="masterkey")
else:
	#	dsn="/data/asset.rf/wh.v39.gdb",
	#	dsn="localhost:furnlive",
	#	dsn="localhost:minder",
	con = kinterbasdb.connect(
		dsn="127.0.0.1:" + mydb,
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
#print "trans type",tran_type,"class",tran_class
#print "user",tran_user,"dev",tran_device,"source",tran_source

#mytime = mx.DateTime.now()

# first must get errored transactions
#query0 = """select trn_type, trn_code, trn_date, person_id, device_id, input_source, instance_id, wh_id, locn_id, object, reference, qty, sub_locn_id  from transactions_archive where  complete in ('R') """
query0 = """select t1.trn_type, t1.trn_code, t1.trn_date, t1.person_id, t1.device_id, t1.input_source, t1.instance_id, t1.wh_id, 
t1.locn_id, t1.object, t1.reference, t1.qty, t1.sub_locn_id , t1.record_id 
from transactions_archive t1 
join pick_despatch p1 on t1.reference = p1.awb_consignment_no
where  t1.trn_type = ? 
and t1.complete in ('R') 
and p1.pickd_manifest_id = ?
and p1.despatch_status = ?
"""

#cur2.execute(query0)
cur2.execute(query0,('DSDX', myManId, 'DC'))

## get data record
data_fields = cur2.fetchone()
trans_record = []
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
		if data_fields[13] is None:
			tran_record = 0
		else:
			tran_record = data_fields[13]
			trans_record.append(tran_record)
		print "got %s %s %s " EC_ID = GEN_ID(TRANSACTION_ID, 1);ject)
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

#cur2.execute(query4)

query5 = """update transactions_archive set complete = 'r' where trn_date > 'YESTERDAY' and complete='R' """
#cur2.execute(query5)
# change transactions archive so that record is populated for any R or E complete transactions
# then can use the record id as the one to update
# have the complete and awb in the reference field - but awb is not unique at tifs even for the same carrier
query5 = """update transactions_archive set complete = 'b' where record_id in (?)"""
for record in trans_record:
	print record
	wk_record_id = int(record)
	cur2.execute(query5,(wk_record_id))

print "end of updates" 

con.commit()

print "end - of "


#revert stdin stdout and stderr
if (havelog == 1):
	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__
	out.close()

###
