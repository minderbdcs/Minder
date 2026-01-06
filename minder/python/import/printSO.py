#!/usr/bin/env python2
"""
<title>
printSO.py, Version 20.02.06
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

def addOrder(wk_order, wk_printer, cur3 ):
	""" export file for bartender  for this printer 
	Parameters order
	printer
	database cursor
	"""
	global wk_header, wk_last_printer, prt
	# export file to bartender for this order
	#wk_ref = str(wk_printer) + "|" + str(wk_header) + "|" + str(wk_order) + "|"
	wk_ref = '"' + str(wk_order) + '","' + str(wk_printer) + '","' + str(wk_header) + '"'
	wk_header = wk_header + 1
	#if wk_header == 'T':
	#	wk_header = "F"
	#print "about to call pc_label_sale_order"
	print wk_ref
	if wk_printer <> "NONE":
		# write to file
		if wk_printer <> wk_last_printer:
			prt.close()
			# must open a new file
			# get folder for printer
			wk_select_stmt = "select working_directory from sys_equip where device_id = '%s'" % (wk_printer)
			cur3.execute(wk_select_stmt )
			output_parm = cur3.fetchone()
			if output_parm is None:
				#print "response is Null"
				wk_path = ""
			while not output_parm is None:
				for pos in range(len(output_parm)):
					if  output_parm[pos] is None:
						wk_path = ""
					else:
						wk_path = output_parm[pos]
				output_parm = cur3.fetchone()
			prtfile = wk_path + "delivery.ext"
			print prtfile
			prt = open(prtfile,'w')
			wk_last_printer = wk_printer
		prt.write("%s\n" % (wk_ref))
	wk_update_stmt = "update pick_order set label_printed_date = 'NOW' where pick_order = '%s' " % (wk_order)
	cur3.execute(wk_update_stmt )
# end of function

def addWork(wk_order, wk_zone, wk_printer, wk_group, cur3 ):
	""" add to transactions work   
	Parameters order
	zones 
	printer 
	grouping no
	database cursor
	"""
	if len(wk_zone) > 50:
		wk_zone = wk_zone[:50]
	wk_add_stmt = "insert into transactions_work (record_id, object, description, locn_id) values ('%d','%s','%s') " % (wk_group, wk_order, wk_zone, wk_printer)
	cur3.execute(wk_add_stmt )
# end of function

def getZones(wk_order, cur3 ):
	""" add the list of zones for order   
	Parameters order
	database cursor
	Returns the list of zones in a single string field 
	"""
	wk_zones = ""
	wk_select_stmt = """select substr(pi.pick_location ,3 ,4)
	from pick_item pi
	where pi.pick_order = '%s'
	and pi.pick_line_status <> 'CN'
	and pi.pick_line_status <> 'HD'
	and pi.over_sized in ('F')
	order by substr(pi.pick_location, 3, 4)
""" % (wk_order)
	cur3.execute(wk_select_stmt )
	output_parm = cur3.fetchone()
	if output_parm is None:
		#print "response is Null"
		wk_zone = ""
	while not output_parm is None:
		for pos in range(len(output_parm)):
			if  output_parm[pos] is None:
				wk_zone = ""
			else:
				wk_zone = output_parm[pos]
				wk_zones = wk_zones + str(wk_zone)
		output_parm = cur3.fetchone()
	return wk_zones
# end of function

#redirect stdout and stderr

if len(sys.argv)>0:
	print "printSO ", sys.argv[1]
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
		dsn="127.0.01:d:/asset.rf/database/wh.v39.gdb",
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
#######################################################################
#
# here delete from transactions work where record_id < 20
wk_del_stmt = "delete from transactions_work where record_id < 20"
cur2.execute(wk_del_stmt )
con.commit()
#
#######################################################################
#wk_header = "T"
wk_header = 1
#
#	for MM AU
#	and MM NZ
#	get printer to use from options
#	want unprinted orders
#	first the over sized
#	then > 3 lines on order - 240807 - was >2
#
#	then  3 lines on order
#	order by zone of 1st line and zone of 2nd and zone on 3rd
#
#	then 2 line orders
#	order by zone of 1st line and zone of 2nd
#
#	lastly 1 line orders
#
#	now for these order groupings must get the zone with each to pick line
#	then order on order then zone
#	1st zone is the smallest
#	2nd zone is the next
#	3rd zone is the 3rd
#	must order across the grouping by 1st then 2nd then 3rd grouping
#	so must either have a db func or python function
#	which passed an order
#	get a list of the zones used (in order of zones)
#	append all these together to give the net zones for the order
#	return these net zones
#	otherwise for each grouping
#	sort the list of orders and netzones in order of net zones
#	put into transactions work
#	with record_id holding the grouping no
#	description holding the net zone (1st 40 bytes of it)
#	object holding the order
#	once all the orders added for a grouping
#	reread it back in order of description
#	do the add_order for this order
#
#	for PM NZ
#	get printer to use from options
#	print in order of created date
#	13/09/06
#	   print in order of Area Manager then 
#	   distributor no
#	for PM AU
#	no not print 
#	15/11/06 for PM NZ
#	   print in order of Area Manager then 
#	   orders with customs inspection required

#######################################################################
wk_printer = ""
wk_last_printer = ""
prtfile = "delivery.ext"
prt = open(prtfile,'w')
wk_date = time.strftime("%d/%m/%y %H:%M:%S")
print "about to start select " + wk_date
print " try  PMNZ orders"
wk_select_stmt = """select po.pick_order ,o1.description
from pick_order po
join options o1 on o1.group_code = 'CMPPKPR' and o1.code = (po.company_id || '|' || po.p_country) 
where po.pick_status in ('OP','DA')
and po.label_printed_date is null
and o1.description like '%FIRST'
order by po.other5, po.export_category, po.other4 
"""
#order by po.create_date
#order by po.other5, po.other4 
print wk_select_stmt
cur.execute(wk_select_stmt )
data_fields = cur.fetchone()
if data_fields is None:
	print "No PM Orders "
	wk_order = ""

while not data_fields is None:
	for pos in range(len(data_fields)):
		if  data_fields[pos] is None:
			if pos == 0:
				wk_order = ""
			else:
				wk_printer = "NONE"
		else:
			if pos == 0:
				wk_order = data_fields[pos]
				#addOrder(wk_order, wk_printer, cur2)
			else:
				wk_printer1 = data_fields[pos]
				wk_printer = wk_printer1[:2] 
				addOrder(wk_order, wk_printer, cur2)
	data_fields = cur.fetchone()

con.commit()

#######################################################################
if wk_printer <> "" and wk_printer <> "NONE":
	addOrder("",wk_printer, cur2)
wk_date = time.strftime("%d/%m/%y %H:%M:%S")
print "about to start select " + wk_date
print "try over sized items first "
wk_select_stmt = """select p1.pick_order,o1.description 
from pick_order p1 
join options o1 on o1.group_code = 'CMPPKPR' and o1.code = (p1.company_id || '|' || p1.p_country) 
where p1.pick_status in ('OP','DA')
and p1.label_printed_date is null
and p1.over_sized in ('T','P')
and o1.description <> 'NONE'
"""
print wk_select_stmt
cur.execute(wk_select_stmt )
data_fields = cur.fetchone()
if data_fields is None:
	print "No Orders Over Sized"
	wk_order = ""

while not data_fields is None:
	for pos in range(len(data_fields)):
		if  data_fields[pos] is None:
			if pos == 0:
				wk_order = ""
			else:
				wk_printer = "NONE"
		else:
			if pos == 0:
				wk_order = data_fields[pos]
				#addOrder(wk_order, wk_printer, cur2)
			else:
				wk_printer = data_fields[pos]
				addOrder(wk_order, wk_printer, cur2)
	data_fields = cur.fetchone()

con.commit()
#######################################################################
if wk_printer <> "" and wk_printer <> "NONE":
	addOrder("",wk_printer, cur2)
wk_date = time.strftime("%d/%m/%y %H:%M:%S")
print "about to start select " + wk_date
print " try > 3 line orders"
wk_group = 3
wk_select_stmt = """select po.pick_order,o1.description 
from pick_order po
join options o1 on o1.group_code = 'CMPPKPR' and o1.code = (po.company_id || '|' || po.p_country) 
where po.pick_status in ('OP','DA')
and po.label_printed_date is null
and o1.description <> 'NONE'
and (select count(*) from pick_item where pick_item.pick_order = pick_order.pick_order and pick_item.pick_line_status <> 'CN') > 3 
"""
print wk_select_stmt
cur.execute(wk_select_stmt )
data_fields = cur.fetchone()
if data_fields is None:
	print "No Orders Multi Lines"
	wk_order = ""

while not data_fields is None:
	for pos in range(len(data_fields)):
		if  data_fields[pos] is None:
			if pos == 0:
				wk_order = ""
			else:
				wk_printer = "NONE"
		else:
			if pos == 0:
				wk_order = data_fields[pos]
				#addOrder(wk_order, wk_printer, cur2)
			else:
				wk_printer = data_fields[pos]
				#addOrder(wk_order, wk_printer, cur2)
				# get zone list for order
				wk_zones = getZones(wk_order, cur2)
				# add to work table
				addWork(wk_order, wk_zones, wk_printer, wk_group, cur2)
	data_fields = cur.fetchone()
con.commit()
# now reread the work table
wk_select_stmt = """select object, locn_id 
	from transactions_work 
	where record_id = '%d'
	order by description
""" % (wk_group)
print wk_select_stmt
cur.execute(wk_select_stmt )
data_fields = cur.fetchone()
if data_fields is None:
	print "No Orders Multi Lines"
	wk_order = ""

while not data_fields is None:
	for pos in range(len(data_fields)):
		if  data_fields[pos] is None:
			if pos == 0:
				wk_order = ""
			else:
				wk_printer = "NONE"
		else:
			if pos == 0:
				wk_order = data_fields[pos]
			else:
				wk_printer = data_fields[pos]
				addOrder(wk_order, wk_printer, cur2)
	data_fields = cur.fetchone()
con.commit()
#######################################################################
if wk_printer <> "" and wk_printer <> "NONE":
	addOrder("",wk_printer, cur2)
wk_date = time.strftime("%d/%m/%y %H:%M:%S")
wk_group = 4
print "about to start select " + wk_date
print " try 3 line orders"
wk_select_stmt = """select po.pick_order,o1.description 
from pick_order po
join options o1 on o1.group_code = 'CMPPKPR' and o1.code = (po.company_id || '|' || po.p_country) 
where po.pick_status in ('OP','DA')
and po.label_printed_date is null
and o1.description <> 'NONE'
and (select count(*) from pick_item where pick_item.pick_order = pick_order.pick_order and pick_item.pick_line_status <> 'CN') = 3 
"""
print wk_select_stmt
cur.execute(wk_select_stmt )
data_fields = cur.fetchone()
if data_fields is None:
	print "No Orders 3 Lines"
	wk_order = ""

while not data_fields is None:
	for pos in range(len(data_fields)):
		if  data_fields[pos] is None:
			if pos == 0:
				wk_order = ""
			else:
				wk_printer = "NONE"
		else:
			if pos == 0:
				wk_order = data_fields[pos]
				#addOrder(wk_order, wk_printer, cur2)
			else:
				wk_printer = data_fields[pos]
				#addOrder(wk_order, wk_printer, cur2)
				# get zone list for order
				wk_zones = getZones(wk_order, cur2)
				# add to work table
				addWork(wk_order, wk_zones, wk_printer, wk_group, cur2)
	data_fields = cur.fetchone()
con.commit()
# now reread the work table
wk_select_stmt = """select object, locn_id 
	from transactions_work 
	where record_id = '%d'
	order by description
""" % (wk_group)
print wk_select_stmt
cur.execute(wk_select_stmt )
data_fields = cur.fetchone()
if data_fields is None:
	print "No Orders 3 Lines"
	wk_order = ""

while not data_fields is None:
	for pos in range(len(data_fields)):
		if  data_fields[pos] is None:
			if pos == 0:
				wk_order = ""
			else:
				wk_printer = "NONE"
		else:
			if pos == 0:
				wk_order = data_fields[pos]
			else:
				wk_printer = data_fields[pos]
				addOrder(wk_order, wk_printer, cur2)
	data_fields = cur.fetchone()
con.commit()
#######################################################################
if wk_printer <> "" and wk_printer <> "NONE":
	addOrder("",wk_printer, cur2)
wk_date = time.strftime("%d/%m/%y %H:%M:%S")
wk_group = 5
print "about to start select " + wk_date
print " try 2 line orders"
wk_select_stmt = """select po.pick_order,o1.description 
from pick_order po
join options o1 on o1.group_code = 'CMPPKPR' and o1.code = (po.company_id || '|' || po.p_country) 
where po.pick_status in ('OP','DA')
and po.label_printed_date is null
and o1.description <> 'NONE'
and (select count(*) from pick_item where pick_item.pick_order = pick_order.pick_order and pick_item.pick_line_status <> 'CN') = 2 
"""
print wk_select_stmt
cur.execute(wk_select_stmt )
data_fields = cur.fetchone()
if data_fields is None:
	print "No Orders 2 Lines"
	wk_order = ""

while not data_fields is None:
	for pos in range(len(data_fields)):
		if  data_fields[pos] is None:
			if pos == 0:
				wk_order = ""
			else:
				wk_printer = "NONE"
		else:
			if pos == 0:
				wk_order = data_fields[pos]
				#addOrder(wk_order, wk_printer, cur2)
			else:
				wk_printer = data_fields[pos]
				#addOrder(wk_order, wk_printer, cur2)
				# get zone list for order
				wk_zones = getZones(wk_order, cur2)
				# add to work table
				addWork(wk_order, wk_zones, wk_printer, wk_group, cur2)
	data_fields = cur.fetchone()
con.commit()
# now reread the work table
wk_select_stmt = """select object, locn_id 
	from transactions_work 
	where record_id = '%d'
	order by description
""" % (wk_group)
print wk_select_stmt
cur.execute(wk_select_stmt )
data_fields = cur.fetchone()
if data_fields is None:
	print "No Orders 2 Lines"
	wk_order = ""

while not data_fields is None:
	for pos in range(len(data_fields)):
		if  data_fields[pos] is None:
			if pos == 0:
				wk_order = ""
			else:
				wk_printer = "NONE"
		else:
			if pos == 0:
				wk_order = data_fields[pos]
			else:
				wk_printer = data_fields[pos]
				addOrder(wk_order, wk_printer, cur2)
	data_fields = cur.fetchone()
con.commit()
########################################################################
#if wk_printer <> "" and wk_printer <> "NONE":
#	addOrder("",wk_printer, cur2)
#wk_date = time.strftime("%d/%m/%y %H:%M:%S")
#print "about to start select " + wk_date
#print " try  2 line orders not connected"
#wk_select_stmt = """select po.pick_order ,pi.wh_id, pi.pick_location, o1.description
#from pick_order po
#join options o1 on o1.group_code = 'CMPPKPR' and o1.code = (po.company_id || '|' || po.p_country) 
#join pick_item_line_no pl on po.pick_order = pl.pick_order
#join pick_item pi on po.pick_order = pi.pick_order
#where po.pick_status in ('OP','DA')
#and po.label_printed_date is null
#and pl.last_line_no = 2
#and o1.description <> 'NONE'
#order by po.pick_order
#"""
#print wk_select_stmt
#wk_order = ""
#wk_last_order = ""
#wk_wh_1 = "XX"
#wk_locn_1 = "XX000000"
#wk_wh_2 = "XX"
#wk_locn_2 = "XX000000"
#wk_recno = 0
#wk_distance = 0
#cur.execute(wk_select_stmt )
#data_fields = cur.fetchone()
#if data_fields is None:
#	print "No Orders 2 Lines"
#	wk_distance = 0
#
#while not data_fields is None:
#	wk_recno = wk_recno + 1
#	for pos in range(len(data_fields)):
#		if  data_fields[pos] is None:
#			print " got Null "
#			if pos == 0:
#				wk_last_order = wk_order
#				wk_order = ""
#				if wk_order <> wk_last_order:
#					wk_recno = 1
#			elif pos == 1:
#				if wk_recno == 1:
#					wk_wh_1 = "XX"
#				else:
#					wk_wh_2 = "XX"
#			elif pos == 2:
#				if wk_recno == 1:
#					wk_locn_1 = "XX000000"
#				else:
#					wk_locn_2 = "XX000000"
#			elif pos == 3:
#				wk_printer = "NONE"
#		else:
#			if pos == 0:
#				wk_last_order = wk_order
#				wk_order = data_fields[pos]
#				if wk_order <> wk_last_order:
#					wk_recno = 1
#			elif pos == 1:
#				if wk_recno == 1:
#					wk_wh_1 = data_fields[pos]
#				else:
#					wk_wh_2 = data_fields[pos]
#			elif pos == 2:
#				if wk_recno == 1:
#					wk_locn_1 = data_fields[pos]
#				else:
#					wk_locn_2 = data_fields[pos]
#			elif pos == 3:
#				wk_printer = data_fields[pos]
#		if wk_recno == 2:
#			wk_bay1 = int(wk_locn_1[2:4])
#			wk_bay2 = int(wk_locn_2[2:4])
#			wk_distance = wk_bay1 - wk_bay2
#			print "distance is %d for bay1 %s bay2 %s" % (wk_distance, wk_bay1, wk_bay2)
#			wk_recno = 0
#			if wk_distance > 1 or wk_distance < -1:	
#				addOrder(wk_order, wk_printer, cur2)
#	data_fields = cur.fetchone()
#con.commit()
########################################################################
#if wk_printer <> "" and wk_printer <> "NONE":
#	addOrder("",wk_printer, cur2)
#wk_date = time.strftime("%d/%m/%y %H:%M:%S")
#print "about to start select " + wk_date
#print " try  2 line orders connected"
#wk_select_stmt = """select po.pick_order ,pi.wh_id, pi.pick_location, o1.description
#from pick_order po
#join options o1 on o1.group_code = 'CMPPKPR' and o1.code = (po.company_id || '|' || po.p_country) 
#join pick_item_line_no pl on po.pick_order = pl.pick_order
#join pick_item pi on po.pick_order = pi.pick_order
#where po.pick_status in ('OP','DA')
#and po.label_printed_date is null
#and pl.last_line_no = 2
#and o1.description <> 'NONE'
#order by po.pick_order
#"""
#print wk_select_stmt
#wk_last_order = ""
#wk_order = ""
#wk_wh_1 = "XX"
#wk_locn_1 = "XX990000"
#wk_wh_2 = "XX"
#wk_locn_2 = "XX000000"
#wk_recno = 0
#wk_distance = 0
#cur.execute(wk_select_stmt )
#data_fields = cur.fetchone()
#if data_fields is None:
#	print "No Orders 2 Lines"
#	wk_distance = 0
#
#while not data_fields is None:
#	wk_recno = wk_recno + 1
#	for pos in range(len(data_fields)):
#		if  data_fields[pos] is None:
#			print " got Null "
#			if pos == 0:
#				wk_last_order = wk_order
#				wk_order = ""
#				if wk_order <> wk_last_order:
#					wk_recno = 1
#			elif pos == 1:
#				if wk_recno == 1:
#					wk_wh_1 = "XX"
#				else:
#					wk_wh_2 = "XX"
#			elif pos == 2:
#				if wk_recno == 1:
#					wk_locn_1 = "XX990000"
#				else:
#					wk_locn_2 = "XX000000"
#			elif pos == 3:
#				wk_printer = "NONE"
#		else:
#			if pos == 0:
#				wk_last_order = wk_order
#				wk_order = data_fields[pos]
#				if wk_order <> wk_last_order:
#					wk_recno = 1
#			elif pos == 1:
#				if wk_recno == 1:
#					wk_wh_1 = data_fields[pos]
#				else:
#					wk_wh_2 = data_fields[pos]
#			elif pos == 2:
#				if wk_recno == 1:
#					wk_locn_1 = data_fields[pos]
#				else:
#					wk_locn_2 = data_fields[pos]
#			elif pos == 3:
#				wk_printer = data_fields[pos]
#		if wk_recno == 2:
#			wk_bay1 = int(wk_locn_1[2:4])
#			wk_bay2 = int(wk_locn_2[2:4])
#			wk_distance = wk_bay1 - wk_bay2
#			print "distance is %d for bay1 %s bay2 %s" % (wk_distance, wk_bay1, wk_bay2)
#			wk_recno = 0
#			if wk_distance == 1 or wk_distance == -1 or wk_distance == 0:	
#				addOrder(wk_order, wk_printer, cur2)
#	data_fields = cur.fetchone()
#con.commit()
#######################################################################
if wk_printer <> "" and wk_printer <> "NONE":
	addOrder("",wk_printer, cur2)
wk_date = time.strftime("%d/%m/%y %H:%M:%S")
print "about to start select " + wk_date
print " try  1 line orders"
wk_group = 6
wk_select_stmt = """select po.pick_order ,o1.description
from pick_order po
join options o1 on o1.group_code = 'CMPPKPR' and o1.code = (po.company_id || '|' || po.p_country) 
join pick_item pi on po.pick_order = pi.pick_order
where po.pick_status in ('OP','DA')
and po.label_printed_date is null
and o1.description <> 'NONE'
and (select count(*) from pick_item where pick_item.pick_order = pick_order.pick_order and pick_item.pick_line_status <> 'CN') = 1 
"""
print wk_select_stmt
cur.execute(wk_select_stmt )
data_fields = cur.fetchone()
if data_fields is None:
	print "No Orders 1 Lines"
	wk_order = ""

while not data_fields is None:
	for pos in range(len(data_fields)):
		if  data_fields[pos] is None:
			if pos == 0:
				wk_order = ""
			else:
				wk_printer = "NONE"
		else:
			if pos == 0:
				wk_order = data_fields[pos]
				#addOrder(wk_order, wk_printer, cur2)
			else:
				wk_printer = data_fields[pos]
				#addOrder(wk_order, wk_printer, cur2)
				# get zone list for order
				wk_zones = getZones(wk_order, cur2)
				# add to work table
				addWork(wk_order, wk_zones, wk_printer, wk_group, cur2)
	data_fields = cur.fetchone()
con.commit()
# now reread the work table
wk_select_stmt = """select object, locn_id 
	from transactions_work 
	where record_id = '%d'
	order by description
""" % (wk_group)
print wk_select_stmt
cur.execute(wk_select_stmt )
data_fields = cur.fetchone()
if data_fields is None:
	print "No Orders 1 Lines"
	wk_order = ""

while not data_fields is None:
	for pos in range(len(data_fields)):
		if  data_fields[pos] is None:
			if pos == 0:
				wk_order = ""
			else:
				wk_printer = "NONE"
		else:
			if pos == 0:
				wk_order = data_fields[pos]
			else:
				wk_printer = data_fields[pos]
				addOrder(wk_order, wk_printer, cur2)
	data_fields = cur.fetchone()
con.commit()
#######################################################################
#if wk_printer <> "" and wk_printer <> "NONE":
#	addOrder("",wk_printer, cur2)
#wk_date = time.strftime("%d/%m/%y %H:%M:%S")
#print "about to start select " + wk_date
#print " try  0 line orders"
#wk_select_stmt = """select po.pick_order ,o1.description
#from pick_order po
#join options o1 on o1.group_code = 'CMPPKPR' and o1.code = (po.company_id || '|' || po.p_country) 
#where po.pick_status in ('OP','DA')
#and po.label_printed_date is null
#and o1.description <> 'NONE'
#and (select count(*) from pick_item where pick_item.pick_order = pick_order.pick_order and pick_item.pick_line_status <> 'CN') = 0 
#"""
#print wk_select_stmt
#cur.execute(wk_select_stmt )
#data_fields = cur.fetchone()
#if data_fields is None:
#	print "No Orders 0 Lines"
#	wk_order = ""
#
#while not data_fields is None:
#	for pos in range(len(data_fields)):
#		if  data_fields[pos] is None:
#			if pos == 0:
#				wk_order = ""
#			else:
#				wk_printer = "NONE"
#		else:
#			if pos == 0:
#				wk_order = data_fields[pos]
#				#addOrder(wk_order, wk_printer, cur2)
#			else:
#				wk_printer = data_fields[pos]
#				addOrder(wk_order, wk_printer, cur2)
#	data_fields = cur.fetchone()
#
#con.commit()
#######################################################################
print "end - of unprinted orders"
wk_date = time.strftime("%d/%m/%y %H:%M:%S")
print wk_date

prt.close()

#revert stdin stdout and stderr
if (havelog == 1):
	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__
	out.close()

###
print "end - of unprinted orders"
wk_date = time.strftime("%d/%m/%y %H:%M:%S")
print wk_date

prt.close()

#revert stdin stdout and stderr
if (havelog == 1):
	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__
	out.close()

###
