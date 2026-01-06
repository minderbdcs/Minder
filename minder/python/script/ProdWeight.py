#!/usr/bin/env python2
"""
<title>
ProdWeight.py, Version 07.11.06
</title>
<long>
Updates Pick_Orders weight and pallet_base for  Not Weighed Orders for a passed product
<br>
Parameters: <tt>input file</tt></tt>log file</tt>
the input file holds <tt>product</tt>
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

def updateOrder( wk_order):
	# update order 
	# get freight tax on order 
	wk_dummy = 0.0
	wk_tax = 0.0
	wk_sub_total = 0.0
	wk_freight_tax = 0.0
	wk_freight = 0.0
	wk_weight = 0.0
	# get weight to use
	wk_select_stmt = """select sum(pi.pick_order_qty * pp.net_weight * u1.to_standard_conv)
from pick_item pi 
left outer join prod_profile pp
on pi.prod_id = pp.prod_id
left outer join uom u1
on pp.net_weight_uom = u1.code
where pi.pick_order = '%s' """ % (wk_order)
	print wk_select_stmt
	cur.execute(wk_select_stmt )
	data_fields = cur.fetchone()
	if data_fields is None:
		return None
	
	while not data_fields is None:
		for pos in range(len(data_fields)):
			if  data_fields[pos] is None:
				wk_weight = 0.0
			else:
				wk_weight = float(data_fields[pos])
		data_fields = cur.fetchone()
	# get system prod type for pallet bases
	wk_select_stmt = """select pallet_base_prod_type,pick_order_max_weight from control
 """ 
	print wk_select_stmt
	cur.execute(wk_select_stmt )
	data_fields = cur.fetchone()
	if data_fields is None:
		wk_pallet_size_pt = ''
		wk_order_max_weight = 0.0
	
	while not data_fields is None:
		for pos in range(len(data_fields)):
			if  data_fields[pos] is None:
				if pos == 0:
					wk_pallet_size_pt = ''
				else:
					wk_order_max_weight = 0.0
			else:
				if pos == 0:
					wk_pallet_size_pt = data_fields[pos]
				else:
					wk_order_max_weight = float(data_fields[pos])
		data_fields = cur.fetchone()
	# get max x and y for all prods  on order (in conveyor)
	wk_select_stmt = """select max(pp.dimension_x * u1.to_standard_conv), max(pp.dimension_y * u2.to_standard_conv)
from pick_item pi 
left outer join prod_profile pp
on pi.prod_id = pp.prod_id
left outer join uom u1
on pp.dimension_x_uom = u1.code
left outer join uom u2
on pp.dimension_y_uom = u2.code
where pi.pick_order = '%s' and pi.over_sized = 'F' and pi.pick_order_qty>0 """ % (wk_order)
	print wk_select_stmt
	cur.execute(wk_select_stmt )
	data_fields = cur.fetchone()
	if data_fields is None:
		wk_dimension_x = 0
		wk_dimension_y = 0
	
	while not data_fields is None:
		for pos in range(len(data_fields)):
			if  data_fields[pos] is None:
				if pos == 0:
					wk_dimension_x = 0
				else:
					wk_dimension_y = 0
			else:
				if pos == 0:
					wk_dimension_x = float(data_fields[pos])
				else:
					wk_dimension_y = float(data_fields[pos])
		data_fields = cur.fetchone()
	# have max x and y
	# now need the pallet base to use
	wk_select_stmt = """select first 1 pp.prod_id , (pp.net_weight * u3.to_standard_conv)
from prod_profile  pp
left outer join uom u1
on pp.dimension_x_uom = u1.code
left outer join uom u2
on pp.dimension_y_uom = u2.code
left outer join uom u3
on pp.net_weight_uom = u3.code
where pp.prod_type = '%s'
and (pp.dimension_x * u1.to_standard_conv) >= '%f' and (pp.dimension_y * u2.to_standard_conv) >= '%f'
order by (pp.dimension_x * u1.to_standard_conv), (pp.dimension_y * u2.to_standard_conv)
""" % (wk_pallet_size_pt, wk_dimension_x, wk_dimension_y)
	print wk_select_stmt
	cur.execute(wk_select_stmt )
	data_fields = cur.fetchone()
	if data_fields is None:
		wk_pallet_size = ''
		wk_pallet_weight = 0.0
	
	while not data_fields is None:
		for pos in range(len(data_fields)):
			if  data_fields[pos] is None:
				if pos == 0:
					wk_pallet_size = ''
				else:
					wk_pallet_weight = 0.0
			else:
				if pos == 0:
					wk_pallet_size = data_fields[pos]
				else:
					wk_pallet_weight = float(data_fields[pos])
		data_fields = cur.fetchone()
	# have pallet size and weight
	if wk_pallet_size == "":
		# no base big enough
		# so use the biggest base available
		wk_select_stmt = """select first 1 pp.prod_id , (pp.net_weight * u3.to_standard_conv)
from prod_profile  pp
left outer join uom u1
on pp.dimension_x_uom = u1.code
left outer join uom u2
on pp.dimension_y_uom = u2.code
left outer join uom u3
on pp.net_weight_uom = u3.code
where pp.prod_type = '%s'
order by (pp.dimension_x * u1.to_standard_conv) desc, (pp.dimension_y * u2.to_standard_conv)  desc
""" % (wk_pallet_size_pt )
		print wk_select_stmt
		cur.execute(wk_select_stmt )
		data_fields = cur.fetchone()
		if data_fields is None:
			wk_pallet_size = ''
			wk_pallet_weight = 0.0
		while not data_fields is None:
			for pos in range(len(data_fields)):
				if  data_fields[pos] is None:
					if pos == 0:
						wk_pallet_size = ''
					else:
						wk_pallet_weight = 0.0
				else:
					if pos == 0:
						wk_pallet_size = data_fields[pos]
					else:
						wk_pallet_weight = float(data_fields[pos])
			data_fields = cur.fetchone()
	# have pallet size and weight
	wk_update_stmt = """update pick_order set net_weight = '%.3f', pallet_base='%s' 
 """ % (wk_weight+wk_pallet_weight,wk_pallet_size)
	if wk_pallet_size == "":
		wk_update_stmt =  wk_update_stmt + ",over_sized = 'T',over_sized_reason='No Trays Fit' "
		#wk_update_stmt4 = "update pick_item set over_sized = 'T' "
		wk_update_stmt4 = ""
	else:
		if (wk_weight+wk_pallet_weight) > wk_order_max_weight:
			wk_update_stmt =  wk_update_stmt + ",over_sized = 'T',over_sized_reason='Too Heavy' "
			#wk_update_stmt4 = "update pick_item set over_sized = 'T' "
			wk_update_stmt4 = ""
		else:
			wk_update_stmt4 = ""
			#wk_update_stmt =  wk_update_stmt + ",over_sized = 'F' "
	wk_update_stmt2 = " where pick_order = '%s'" % (wk_order)
	wk_update_stmt = wk_update_stmt + wk_update_stmt2
	print wk_update_stmt
	cur.execute(wk_update_stmt )
	if wk_update_stmt4 > "":
		wk_update_stmt5 = wk_update_stmt4 + wk_update_stmt2
		print wk_update_stmt5
		cur.execute(wk_update_stmt5 )

	#con.commit()

#redirect stdout and stderr

if len(sys.argv)>0:
	print "ProdWeight ", sys.argv[1]
	infile = sys.argv[1]
	havein = 1;
else:
	print "ProdWeight stdin"
	infile = '-'
	havein = 0;

if len(sys.argv)>1:
	print "log ", sys.argv[2]
	logfile = sys.argv[2]
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

rest, ext = os.path.splitext(infile)
path, base = os.path.split(rest)
print "%s %s" % ("base",base)
if base.rfind("(") > -1:
	path2 = base[:base.rfind("(") ]
	base = path2
wk_dataset = base

wk_record_no = 0;

#print "%s %s" % ("dataset",wk_dataset)

if os.name == 'nt':
	con = kinterbasdb.connect(
		dsn="d:/asset.rf/database/wh.v39.gdb",
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
wk_last_order = ''
#read std or 1st input parm
for line in fileinput.input(infile):
	wk_line = wk_line + 1
	print "line",line
	#wk_code = line
	#buffer = line.split(',')
	buffer = line.split('","')
	wk_end = len(buffer) -1
	# field1 is Prod ID
	#print "wk_end",wk_end
	if wk_end < 1:
		break
	buffer[wk_end] = buffer[wk_end][:-1]
	for xindex in range(0,len(buffer)):
		wk_str = buffer[xindex]
		if wk_str[:1] == '"' and xindex == 0:
			buffer[xindex] = wk_str[1:]
			wk_str = buffer[xindex]
		if wk_str[-1:] == '"' and xindex == len(buffer):
			buffer[xindex] = wk_str[:-1]
			wk_str = buffer[xindex]
		if wk_str[:1] == '"' and wk_str[-1] == '"':
			buffer[xindex] = wk_str[1:-1]
			wk_str = buffer[xindex]
		if wk_str.find("'") > -1:
			buffer[xindex] = wk_str.replace("'","`")
		print "x",xindex,buffer[xindex]
	wk_prod = buffer[0]
	wk_record_no = wk_record_no + 1
	query4 = "select distinct pick_order from pick_item where pick_line_status in ('AL','PG','PL','DS') and prod_id = '%s'" % (wk_prod)
	# calc select , update and insert statements
	#print query4 
	cur2.execute(query4 )
	data_fields4 = cur2.fetchone()
	if data_fields4 is None:
		return None

	while not data_fields4 is None:
		for pos in range(len(data_fields4)):
			if  data_fields4[pos] is None:
				wk_last_order = ""
			else:
			if pos == 0:
				wk_last_order = data_fields4[pos])
					# update orders weight
					updateOrder(wk_last_order)
			data_fields4 = cur2.fetchone()
con.commit()

print "end - of datafile"


#revert stdin stdout and stderr
if (havelog == 1):
	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__
	out.close()

###
