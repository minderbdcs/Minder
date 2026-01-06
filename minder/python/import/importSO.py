#!/usr/bin/env python2
"""
<title>
importfile.py, Version 16.06.04
</title>
<long>
Creates/Updates tables in the database
<br>
Parameters: <tt>input file</tt></tt>log file</tt>
the input file holds <tt>wh_id</tt><tt>location</tt>
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
	wk_select_stmt = """select freight_tax_amount,freight
from pick_order 
where pick_order = '%s' """ % (wk_order)
	print wk_select_stmt
	cur.execute(wk_select_stmt )
	data_fields = cur.fetchone()
	if data_fields is None:
		return None
	
	while not data_fields is None:
		for pos in range(len(data_fields)):
			if  data_fields[pos] is None:
				wk_dummy = 0.0
			else:
				if pos == 0:
					wk_freight_tax = float(data_fields[pos])
				else:
					wk_freight = float(data_fields[pos])
		data_fields = cur.fetchone()
	# get tax and sub total for lines
	wk_select_stmt = """select sum(pi.tax_amount),sum(pi.line_total)
from pick_item pi 
where pi.pick_order = '%s' """ % (wk_order)
	print wk_select_stmt
	cur.execute(wk_select_stmt )
	data_fields = cur.fetchone()
	if data_fields is None:
		return None
	
	while not data_fields is None:
		for pos in range(len(data_fields)):
			if  data_fields[pos] is None:
				wk_dummy = 0.0
			else:
				if pos == 0:
					wk_tax = float(data_fields[pos])
				else:
					wk_sub_total = float(data_fields[pos])
		data_fields = cur.fetchone()
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
	wk_update_stmt = """update pick_order set net_weight = '%.3f', tax_amount = '%.2f',sub_total_amount = '%.2f',due_amount='%.2f',pallet_base='%s' 
 """ % (wk_weight+wk_pallet_weight,wk_tax+wk_freight_tax,wk_sub_total,wk_sub_total+wk_tax+wk_freight_tax+wk_freight,wk_pallet_size)
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
	wk_update_stmt2 = " where pick_order = '%s'" % (wk_last_order)
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
	print "importSO ", sys.argv[1]
	infile = sys.argv[1]
	havein = 1;
else:
	print "importSO stdin"
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

print "%s %s" % ("dataset",wk_dataset)

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
wk_last_order = ''
#read std or 1st input parm
for line in fileinput.input(infile):
	wk_line = wk_line + 1
	print "line",line
	#wk_code = line
	if wk_line == 1:
		print "1st line" 
	else:
		#buffer = line.split(',')
		buffer = line.split('","')
		wk_end = len(buffer) -1
		# field1 is Country
		# field2 is Company
		# field3  Transaction Date  
		# field4 is WO PO Order No (Their order)
		# field5 is Order No
		# field6 is Order Date
		# field7 is Order Priority
		# field8 is Customer
		# field9 is Customer Name
		# field10 is Address line 1
		# field11 is Address line 2
		# field12 is Address line 3
		# field13 is Address line 4
		# field14 is Address line 5
		#   field15 is State
		# field16 is Post Code
		#   field17 is Aust 5 state DPID Post Code
		# field18 is Comments 1
		# field19 is Comments 2
		# field20 is Line Other 1 (their Catalogue) 
		# field21 is Prod IDs
		# field22 is Line Other 2 (Product Description)
		# field23 is Qty Ordered
		#  field24 is Qty Despatched
		# field25 is Line Unit Price
		# field26 is Line Tax Rate
		#  field27 is Goods (Order) Value
		# field28 is Freight (Postage)
		#  field29 is Order Value (Includes Postage)
		# field30 is (Payment Method)
		# field31 is Amount Paid
		# field32 is Ship Via
		# field33 is LineOther3 Other 1
		# field34 is LineOther4 Other 2
		# field35 is LineOther5 Other 3
		# field36 is LineOther6 Other 4
		# field37 is LineOther7 Other 5
		# field38 is LineOther8 Other 6
		# field39 is LineOther9 Other 7
		# field40 is OrderOther1 Other 8 (pick batch)
		# field41 is OrderOther2 Other 9 (Distributor Name)
		#  field42 is OrderOther3  (order prefix)
		#  field43 is OrderOther4  (Distributor No)
		#  field44 is OrderOther5  (Area Manager)
		#  field45 is OrderOther6  (Cust Phone)
		#  field46 is OrderOther7  (Dist Phone)
		# field47 Line_Status
		# field48 Order Status
		# field49 Person Type
		#  field50 is Follow 1
		#  field51 is Follow 2
		#  field52 is Follow 3
		#  field53 is Remarks 1
		#  field54 is Remarks 2
		#  field55 is Pay Details1
		#  field56 is Pay Details2
		#  field57 is Pay Details3
		#  field58 is Pay Details4
		#  field59 is Footer1
		#  field60 is Footer2
		#  field61 is Footer3
		#  field62 is Footer4
		#  field63 is Footer5
		# 	fixed field64	Order Type
		# 	fixed field65	Customer
		# 	fixed field66	Record No
		# 	fixed field67	No Person for Order
		# 	fixed field68	Partial Pick Allowed

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
		wk3 = buffer[41] + buffer[4]
		buffer[4] = wk3
		#buffer.append("OP") # line status
		#buffer.append("DA") # order status
		#buffer.append("IN") # person type
		buffer.append("SO") # order type
		buffer.append(buffer[8]) # first_name
		wk = "%2.2s-%2.2s-%2.2s" % (buffer[2][4:6],buffer[2][2:4],buffer[2][:2])
		print wk
		wk2 = mx.DateTime.DateTimeFrom(wk)
		print str(wk2)
		buffer[2] = wk2
		wk = "%2.2s-%2.2s-%2.2s" % (buffer[5][4:6],buffer[5][2:4],buffer[5][:2])
		print wk
		wk2 = mx.DateTime.DateTimeFrom(wk)
		print str(wk2)
		buffer[5] = wk2
		wk_str = buffer[29] #payment method
		buffer[29] = wk_str.replace("Method of Payment ","")
		buffer.append(wk_record_no) # order line no in order
		buffer.append("T") # no person for order
		buffer.append("F") # no partial pick allowed
		# check that we can import this product
		wk_prod = buffer[20]
		if (checkProduct(wk_prod) == 0)
			# dont import - so cancel it
			buffer[46] = "CN"
		#
		wk_record_no = wk_record_no + 1
		#print "length of buffer ",len(buffer)
		#print repr(buffer)
		# calc select , update and insert statements
		if (len(buffer) == 68):
			wk_insert_stmt = """insert into pick_order_item_temp (P_COUNTRY,
H_COMPANY_ID,
H_CREATE_DATE,
H_CUSTOMER_PO_WO,
H_PICK_ORDER,
H_PICK_DUE_DATE,
H_PICK_PRIORITY,
H_PERSON_ID,
H_CONTACT_NAME, 
P_ADDRESS_LINE1,
P_ADDRESS_LINE2,
P_ADDRESS_LINE3,
P_ADDRESS_LINE4,
P_ADDRESS_LINE5,
P_STATE,
P_POST_CODE,
 P_AUST_POST_4STATE_ID,
H_SPECIAL_INSTRUCTIONS1,
H_SPECIAL_INSTRUCTIONS2,
L_OTHER1,
L_PROD_ID,
L_OTHER2,
 L_OTHER_QTY1,
L_PICK_ORDER_QTY,
L_SALE_PRICE,
L_TAX_RATE,
 H_OTHER_NUM1,
H_FREIGHT,
 H_OTHER_NUM2,
H_PAYMENT_METHOD,
H_AMOUNT_PAID,
H_SHIP_VIA,
L_OTHER3,
L_OTHER4,
L_OTHER5,
L_OTHER6,
L_OTHER7,
L_OTHER8,
L_OTHER9,
H_OTHER1,
H_OTHER2,
H_OTHER3,
H_OTHER4,
H_OTHER5,
H_OTHER6,
H_OTHER7,
L_PICK_LINE_STATUS,
H_PICK_STATUS,
P_PERSON_TYPE,
L_SPECIAL_INSTRUCTIONS1,
L_SPECIAL_INSTRUCTIONS2,
L_SPECIAL_INSTRUCTIONS3,
H_REMARKS1,
H_REMARKS2,
H_REMARKS3,
H_REMARKS4,
H_REMARKS5,
H_REMARKS6,
H_FOOTER1,
H_FOOTER2,
H_FOOTER3,
H_FOOTER4,
H_FOOTER5,
H_PICK_ORDER_TYPE,
P_FIRST_NAME,
L_BATCH_LINE,
P_NO_PERSON_UPD,
H_PARTIAL_PICK_ALLOWED
)values('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
'%s','%s','%s','%s','%s','%s','%s','%s')"""%tuple(buffer)
			print wk_insert_stmt	
			#print query4 
			cur.execute(wk_insert_stmt )
			if (wk_last_order <> buffer[4]):
				# order no changed
				if (wk_last_order <> ''):
					# update orders weight
					updateOrder(wk_last_order)
				wk_last_order = buffer[4]
		else:
			print "TOO MANY FIELDS IN IMPORT FOR INSERT"
			print "TOO MANY FIELDS IN IMPORT FOR INSERT"
			print "TOO MANY FIELDS IN IMPORT FOR INSERT"
			print "TOO MANY FIELDS IN IMPORT FOR INSERT"
			print "TOO MANY FIELDS IN IMPORT FOR INSERT"
			print "TOO MANY FIELDS IN IMPORT FOR INSERT"
			print "TOO MANY FIELDS IN IMPORT FOR INSERT"
			
if (wk_last_order <> ''):
	# update last orders weight
	updateOrder(wk_last_order)

con.commit()

print "end - of datafile"


#revert stdin stdout and stderr
if (havelog == 1):
	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__
	out.close()

###
