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

def addtran4(tran_type,tran_class,tran_delim,tran_user,tran_device,tran_source,tran_data,tran_web_request):
	wk_date = time.strftime("%d/%m/%y")
	wk_line = 0	
	print "trans type",tran_type,"class",tran_class
	print "user",tran_user,"dev",tran_device,"source",tran_source
	
	mytime = mx.DateTime.now()
	
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
	
	# then add transactions4
	
	tran_trandata = tran_user + tran_delim + tran_device + tran_delim + message_id + tran_data
	print "tran data", tran_trandata
	
	query2 = """select rec_id from add_tran_v4('V4',
		'%s','%s','%s','%s','%s','%s','%s','%s','F','','MASTER',0,'%s')
	"""
	
	cur.execute(query2 % (tran_type, tran_class, mytime, tran_delim, tran_user, tran_device, message_id, tran_trandata, tran_source ))
	
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
	if tran_web_request == 'Y':
		
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


#redirect stdout and stderr

if len(sys.argv)>0:
	print "importprods ", sys.argv[1]
	infile = sys.argv[1]
	havein = 1;
else:
	print "importprods stdin"
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
#print "%s %s" % ("base",base)
#if base.rfind("(") > -1:
#	path2 = base[:base.rfind("(") ]
#	base = path2
#wk_dataset = base

#print "%s %s" % ("dataset",wk_dataset)

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
#wk_oldest_var = mx.DateTime.today() + mx.DateTime.RelativeDateTime(days=-8)
#wk_delete_stmt = "delete from variance where run_date < '%s'" % wk_oldest_var
#print wk_delete_stmt	

#cur.execute(wk_delete_stmt )
#read std or 1st input parm
for line in fileinput.input(infile):
	wk_line = wk_line + 1
	print "line",line
	#wk_code = line
	if wk_line == 1:
		print "1st line" 
	else:
		buffer = line.split(',')
		wk_end = len(buffer) -1
		# field1  Prod ID
		# field2  Minder Qty
		# field3  Adjustment Qty

		#print "wk_end",wk_end
		if wk_end < 1:
			break
		buffer[wk_end] = buffer[wk_end][:-1]
		for xindex in range(0,len(buffer)):
			wk_str = buffer[xindex]
			if wk_str[:1] == '"':
				wk_str = wk_str.replace('""','"')
				buffer[xindex] = wk_str[1:-1]
				wk_str = buffer[xindex]
			if wk_str.find("'") > -1:
				wk_str = wk_str.replace("'","`")
				buffer[xindex] = wk_str
			print "x",xindex,buffer[xindex]
		# must calc the current qty of product onsite for Magnamail

		#buffer.insert(2,wk_alt_comp)
		if buffer[2] is  None:
			buffer[2] = '0';
		if buffer[2] == '':
			# null qty
			buffer[2] = '0'
		# zero fill the product up to length 5
		if str(buffer[0]).isdigit():
			buffer[0] = str(buffer[0]).zfill(5)	
		print buffer

		# if buffer[2] is not zero
		# if > 0
		# then add to 1st location for prod
		# (use stpa for prod locn status)
		# or if none the default receive location
		# (use stpa for prod locn status)
		# else < 0
		# must loop through locations reducing the qty
		# if not enough to reduce 
		# must create a negative qty in the default receive
		#
 
		# get default home locn as locn to make in
		wk_select_stmt = "select home_locn_id,company_id from prod_profile where prod_id = '%s' " % (buffer[0]) 
		cur.execute(wk_select_stmt)
		## get data record
		data_fields = cur.fetchone()
		#print data_fields
		if data_fields is None:
			print "record not found "
			wk_default_locn = 'RC010000'
			wk_default_comp = "XX"
		else:
			print "record found "
			#print data_fields
			wk_default_locn = data_fields[0]
			wk_default_comp = data_fields[1]
		wk_default_wh = wk_default_comp.substr[:2]
		if int(buffer[2]) > 0:
			wk_select_stmt = "select first 1 wh_id, locn_id from issn where prod_id = '%s' and issn_status = 'ST' " % (buffer[0])
			cur.execute(wk_select_stmt)
				
			## get data record
			data_fields = cur.fetchone()
			#print data_fields
			if data_fields is None:
				print "record not found "
				wk_used_wh = wk_default_wh
				wk_used_locn = wk_default_locn
			else:
				print "record found "
				wk_used_wh = data_fields[0]
				wk_used_locn = data_fields[1]
			# now do stpa for this locn

		print query4 
		cur.execute(query4 )
			
		#cur.execute(wk_insert_stmt )
		con.commit()
		
		# ok have variance - now update prod profile
		#tran_delim = "|"
		#tran_data = tran_delim 
		#tran_data +=  "<EANNumber>" + buffer[0] + tran_delim
		#tran_data +=  "<ShortDescription>" + buffer[1] + tran_delim
		#addtran4("PPA3","P",tran_delim,"BDCS","XX","SSSSSSSSSSSSS",tran_data,"N")
		
print "end - of datafile"
# now must add records in minder not in Legacy system

wk_select2_stmt = "select issn.company_id, issn.prod_id from issn where (issn.company_id is not null) and (issn.prod_id is not null) and issn.prod_id not in (select variance.prod_id from variance where variance.run_date = 'TODAY' group by variance.prod_id) group by issn.company_id, issn.prod_id"
print wk_select2_stmt	

cur2.execute(wk_select2_stmt )
tran2_record = cur2.fetchonemap()
if tran2_record is None:
	print "No products in Minder Not in Legacy"
else:
	while not tran2_record is None:
		mycompany = tran2_record['company_id']
		myprod = tran2_record['prod_id']
		buffer2 = list()
		buffer2.append(mycompany) #  company
		buffer2.append(myprod) # prod
		buffer2.append(0) # legacy qty
		wk_select_stmt = "select onsite_qty from product_stock_status('%s')" % (myprod)
		print wk_select_stmt	

		cur.execute(wk_select_stmt )
		tran_record = cur.fetchonemap()
		if tran_record is None:
			mysiteqty = None
		else:
			mysiteqty = tran_record['onsite_qty']
			
		## process it
		if mysiteqty is None:
			print "onsite_qty is none"
		else:
			legacystatus = "MN"
			print "onsite_qty is %s" % mysiteqty

			print "status is %s" % legacystatus
			buffer2.append(mysiteqty)
			buffer2.append(legacystatus)

			# calc select , update and insert statements
			wk_insert_stmt = "insert into variance (COMPANY_ID,PROD_ID,RUN_DATE,LEGACY_QTY,AVAILABLE_QTY,VARIANCE_STATUS,CREATE_DATE) values ('%s','%s','TODAY','%s','%s','%s','NOW')" % tuple(buffer2)
			print wk_insert_stmt	

			cur.execute(wk_insert_stmt )
		tran2_record = cur2.fetchonemap()
	
con.commit()

#revert stdin stdout and stderr
if (havelog == 1):
	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__
	out.close()

###
