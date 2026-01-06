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
	logfile = "d:/tmp/getdsots.log"
else:
	logfile = "/tmp/getdsots.log"
havelog = 1;
#print "getdsots log ", logfile

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
cur3 = con.cursor()

wk_date = time.strftime("%d/%m/%y")
wk_line = 0	
wk_def_tran_type = "DSOT"
tran_class = "S"
tran_user = "BDCS"
tran_device = "XX"
tran_source = "SSOSSSSSS"
tran_instance = "MASTER    "

mytime = mx.DateTime.now()

# first must get pick items orders for which the order is
# completely picked

query0 = """select default_carrier_id,
default_despatch_printer,
default_connote_qty_labels,
default_connote_pack,
default_connote_pack_qty,
default_connote_weight
from control"""

cur2.execute(query0)

## get data record
data_fields = cur2.fetchone()
#print data_fields
if data_fields is None:
	print "no control "
	wk_def_carrier = "OTHER"
	wk_def_printer = "PC"
	wk_def_label_qty = 1
	wk_def_pack = "C"
	wk_def_pack_qty = 1
	wk_def_weight = 0
else:
	while not data_fields is None:
		#print data_fields
		wk_def_carrier = data_fields[0]
		wk_def_printer = data_fields[1]
		wk_def_label_qty = data_fields[2]
		wk_def_pack = data_fields[3]
		wk_def_pack_qty = data_fields[4]
		wk_def_weight = data_fields[5]
		data_fields = cur2.fetchone()

print "end of reading control " 

wk_pallet_qty = 0
wk_pallet_type = "NONE"
wk_carton_qty = 0
wk_satchel_qty = 0
wk_weight = 0
wk_volume = 0
wk_payer = "S"
wk_carrier = ""

if wk_def_pack == "P":
	wk_pallet_qty = wk_def_pack_qty
elif wk_def_pack == "C":
	wk_carton_qty = wk_def_pack_qty
elif wk_def_pack == "S":
	wk_satchel_qty = wk_def_pack_qty
# desp grouping either single or pallet
if wk_pallet_type == "NONE":
	wk_grouping = "S"
else:
	wk_grouping = "P"

wk_def_service = "GEN"
wk_printer = wk_def_printer

tran_qty = wk_def_label_qty

query1 = """select p1.pick_order, options.description
from pick_item p1
join pick_order p2 on p1.pick_order = p2.pick_order
left outer join options on options.group_code = 'DSOT'
and options.code = (p2.company_id || '|' || p2.p_country )
where p1.pick_line_status = 'DS'
and not exists( select 1 
 from pick_item p2
 where p2.pick_order = p1.pick_order
 and p2.pick_line_status in ('AL','PG','PL'))
group by p1.pick_order,options.description"""

cur2.execute(query1)

## get data record
wk_doit = 'T'
data_fields = cur2.fetchone()
#print data_fields
if data_fields is None:
	print "no orders completely picked "
else:
	while not data_fields is None:
		#print data_fields
		wk_order = data_fields[0]
                if data_fields[1] is None:
			wk_doit = 'F'
		else:
			wk_doit = data_fields[1]
		wk_order = data_fields[0]
		tran_date = mx.DateTime.now()
		tran_wh = wk_order[:2]
		tran_locn = wk_order[2:]
		tran_object = "%-20.20s%-10.10s" % (wk_order, "") 
		# for order get company -> carrier  and  weight 
		query2 = """select p2.net_weight, o1.description 
from pick_order p2
left outer join options o1 on o1.group_code = 'CMPSHIPVIA' and o1.code = p2.company_id
where p2.pick_order = '%s'""" % (wk_order)
		cur3.execute(query2)
		## get data record
		data_fields3 = cur3.fetchone()
		#print data_fields
		if data_fields3 is None:
			print "order not found !!! "
			wk_weight = wk_def_weight
			wk_carrier = wk_def_carrier
		else:
			while not data_fields3 is None:
				#print data_fields
				if data_fields3[0] is None:
					wk_weight = wk_def_weight
				else:
					wk_weight = data_fields3[0]
				if data_fields3[1] is None:
					wk_carrier = wk_def_carrier
				else:
					wk_carrier = data_fields3[1]
				data_fields3 = cur3.fetchone()
		# for this carrier get the transaction 
		# and 1st service 
		query4 = """select c1.trn_type, c2.service_type
from carrier c1
left outer join carrier_service c2 on c2.carrier_id = c1.carrier_id
where c1.carrier_id = '%s' """ % (wk_carrier)
		cur3.execute(query4)
		## get data record
		data_fields3 = cur3.fetchone()
		#print data_fields
		if data_fields3 is None:
			print "carrier not found !!! "
			tran_type = wk_def_tran_type
			wk_service = wk_def_service
		else:
			if not data_fields3 is None:
				#print data_fields
				if data_fields3[0] is None:
					tran_type = wk_def_tran_type
				else:
					tran_type = data_fields3[0]
				if data_fields3[1] is None:
					wk_service = wk_def_service
				else:
					wk_service = data_fields3[1]
		wk_volume = wk_weight * 0.250
		tran_ref = "%04d%-10.10s%04d%04d%05d%05d%s%s%-3.3s|%s" % (wk_pallet_qty,wk_pallet_type,wk_carton_qty,wk_satchel_qty,wk_weight,wk_volume,wk_payer,wk_grouping,wk_service,wk_printer)
		tran_sublocn = wk_carrier
		print "got %s %s %s " % (tran_type, tran_source, tran_object)
		print "trans type",tran_type,"class",tran_class
		print "item",tran_object,"locn",tran_locn
		print "sublocn",tran_sublocn,"ref",tran_ref,"qty",tran_qty
		print "user",tran_user,"dev",tran_device,"source",tran_source
		# then add transactions
		if wk_doit == 'T':
			query3 = """select response_text from add_tran_response(
				'%s','%s','%s','%s','%s','%s','%s','%s','F','','MASTER    ',0,'%s','%s','%s','%s')
			"""
			cur.execute(query3 % (tran_wh, tran_locn, tran_object, tran_type, tran_class, tran_date, tran_ref, tran_qty, tran_sublocn, tran_source, tran_user, tran_device ))
			## get data record
			data_fields4 = cur.fetchone()
			#print data_fields
			if data_fields4 is None:
				print "no record id "
				record_id = ""
			else:
				#print data_fields
				record_id = data_fields4[0]
			
			print "response %s" % record_id
			# if response is "" then must look at transactions table
			# to see whether worked or not	
			# ok so is dsot
		data_fields = cur2.fetchone()

print "end of reading transactions " 

con.commit()

print "end - of "


#revert stdin stdout and stderr
if (havelog == 1):
	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__
	out.close()

###
