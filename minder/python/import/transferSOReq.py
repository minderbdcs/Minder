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

def addTransfer(wk_prod, wk_wh, wk_locn, wk_qty, wk_priority, wk_batch, wk_seq):
	# check for record exists already
	wk_select = "select trn_line_no, qty from transfer_request where prod_id = '%s' and to_wh_id = '%s' and to_locn_id = '%s' and device_id is null and trn_status = 'OP' " % (wk_prod, wk_wh, wk_locn)
	print wk_select
	wk_do_add = "F"
	wk_current_qty = 0
	cur2.execute(wk_select )
	data_fields2 = cur2.fetchone()
	if data_fields2 is None:
		wk_do_add = "T"
	while not data_fields2 is None:
		for pos2 in range(len(data_fields2)):
			if  data_fields2[pos2] is None:
				if pos2 == 0:
					wk_do_add = "T"
			else:
				if pos2 == 0:
					wk_current_line = int(data_fields2[pos2])
				else:
					wk_current_qty = int(data_fields2[pos2])
		data_fields2 = cur2.fetchone()
	if wk_do_add == "T":
		wk_stmt = """insert into transfer_request(trn_priority, prod_id, qty, to_wh_id, to_locn_id, other1, seq) values ('%d','%s','%d','%s','%s','%s','%d') """ % ( wk_priority, wk_prod, wk_qty, wk_wh, wk_locn, wk_batch, wk_seq)
	else:
		wk_stmt = """update transfer_request set trn_priority ='%d' , qty = '%d', other1 = '%s', seq = '%d' 
where trn_line_no = '%d' """ % ( wk_priority, wk_qty, wk_batch, wk_seq, wk_current_line)
	cur2.execute(wk_stmt )


#redirect stdout and stderr

if len(sys.argv)>0:
	print "transferSOReq ", sys.argv[1]
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

wk_date = time.strftime("%d/%m/%y %H:%M:%S")
print wk_date

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
#
#for each 
#	pick_item prod AL status and is Over sized (a)
#	get sum of order qty -> (b)
#	check how much prod (d) in over sized pick location (c)
#	insert/update transfer request for prod (a)
#	to location (c) 
#	qty to get (b) - (d)
#

wk_date = time.strftime("%d/%m/%y %H:%M:%S")
print "about to start select " + wk_date
wk_select_stmt = "select pick_import_ssn_status from control"
print wk_select_stmt
cur.execute(wk_select_stmt )
data_fields = cur.fetchone()
if data_fields is None:
	print "No Control"
	wk_allowed = ""

while not data_fields is None:
	for pos in range(len(data_fields)):
		if  data_fields[pos] is None:
			wk_allowed = ""
		else:
			wk_allowed = data_fields[pos]
	data_fields = cur.fetchone()

wk_select_stmt = "select wh_id, pick_location, prod_id, sum(pick_order_qty), min(pick_label_no) from pick_item where pick_line_status='AL' and over_sized = 'T' group by prod_id, wh_id, pick_location"
print wk_select_stmt
cur.execute(wk_select_stmt )
data_fields = cur.fetchone()
if data_fields is None:
	print "No Over sized Items - all are ok"

while not data_fields is None:
	for pos in range(len(data_fields)):
		if  data_fields[pos] is None:
			if pos == 0:
				wk_wh_id=""
			elif pos == 1:
				wk_locn_id=""
			elif pos == 2:
				wk_prod=""
			elif pos == 3:
				wk_qty=0
			elif pos == 4:
				wk_label=""
		else:
			if pos == 0:
				wk_wh_id=data_fields[pos]
			elif pos == 1:
				wk_locn_id=data_fields[pos]
			elif pos == 2:
				wk_prod=data_fields[pos]
			elif pos == 3:
				wk_qty=int(data_fields[pos])
			elif pos == 4:
				wk_label=data_fields[pos]
	print "ok have location %s-%s prod %s qty %d" % (wk_wh_id, wk_locn_id, wk_prod,wk_qty)
	# now get qty in over sized location
	wk_select2_stmt = """select sum(current_qty) 
from issn 
where wh_id = '%s' and locn_id = '%s' and prod_id = '%s' 
and (pos( '%s', issn.issn_status,0,1) > -1) """ % (wk_wh_id, wk_locn_id, wk_prod, wk_allowed)
	cur2.execute(wk_select2_stmt )
	data_fields2 = cur2.fetchone()
	if data_fields2 is None:
		print "No Qty"
		wk_current_qty = 0
	while not data_fields2 is None:
		for pos2 in range(len(data_fields2)):
			if  data_fields2[pos2] is None:
				wk_current_qty = 0
			else:
				wk_current_qty = int(data_fields2[pos2])
		data_fields2 = cur2.fetchone()
	print "ok have current qty %d" % (wk_current_qty)
	# ok have data
	# set new qty to (wk_qty - wk_current_qty) for prod and to locn
	print "ok have new qty %d" % (wk_qty - wk_current_qty)
	wk_transfer_qty = wk_qty - wk_current_qty
	print "ok have current qty %d" % (wk_current_qty)
	wk_select3_stmt = "select pick_order.other1, pick_item.batch_line from pick_item join pick_order on pick_order.pick_order = pick_item.pick_order where pick_item.pick_label_no = '%s' " % (wk_label)
	cur2.execute(wk_select3_stmt )
	data_fields2 = cur2.fetchone()
	if data_fields2 is None:
		wk_batch = ""
		wk_seq = 0
	while not data_fields2 is None:
		for pos2 in range(len(data_fields2)):
			if  data_fields2[pos2] is None:
				if pos2 == 0:
					wk_batch = ""
				else:
					wk_seq = 0
			else:
				if pos2 == 0:
					wk_batch = data_fields2[pos2]
				else:
					wk_seq = int(data_fields2[pos2])
		data_fields2 = cur2.fetchone()
	addTransfer(wk_prod, wk_wh_id, wk_locn_id, wk_transfer_qty, 10, wk_batch, wk_seq)
	data_fields = cur.fetchone()

print "end - of transfer requests"
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
