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
The input filename minus the extension is the dataset to work on
The first record holds the columns to insert/update
Any field names starting with a '*' mean that these fields together
make up the unique key for the dataset
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

#redirect stdout and stderr

if len(sys.argv)>0:
	print "importfile ", sys.argv[1]
	infile = sys.argv[1]
	havein = 1;
else:
	print "importfile stdin"
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

print "%s %s" % ("dataset",wk_dataset)

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
#read std or 1st input parm
for line in fileinput.input(infile):
	wk_line = wk_line + 1
	print "line",line
	#wk_code = line
	if wk_line == 1:
		fields = line.split(',')
		for xindex in range(0,len(fields)):
			if buffer[xindex]
			print "x",xindex,buffer[xindex]
	else:
		buffer = line.split('","')
	wk_end = len(buffer) -1
	#print "wk_end",wk_end
	if wk_end < 1:
		break
	#for xindex in range(0,len(buffer)):
	#	print "x",xindex,buffer[xindex]
	wk_wh_id = buffer[0][1:]
	wk_locn_id = buffer[1]
	wk_printer = buffer[2][:2]
	print "wh",wk_wh_id
	print "locn",wk_locn_id
	print "printer",wk_printer

	#now for this location get its person details for heading

	query1 = """select person.first_name, person.last_name, person.address_line1, person.address_line2, person.city, person.phone_no , person.state, person.post_code, person.contact_first_name, person.contact_last_name, warehouse.description, person.country
	from warehouse left outer join person on person.person_id = warehouse.person_id
	where warehouse.wh_id = '%s'
	"""

	cur.execute(query1 % (wk_wh_id))
		
	## get data record
	data_fields = cur.fetchone()
	#print data_fields
	if data_fields is None:
		print "no warehouse for location "
		warehouse_firstname = "Unknown"
		warehouse_lastname = ""
		warehouse_addr1 = ""
		warehouse_addr2 = ""
		warehouse_city = ""
		warehouse_state = ""
		warehouse_post = ""
		warehouse_phone = ""
		contact_1st_name = ""
		contact_lst_name = ""
		warehouse_name = ""
		warehouse_country = ""
	else:
		#print data_fields
		warehouse_firstname = data_fields[0]
		warehouse_lastname = data_fields[1]
		warehouse_addr1 = data_fields[2]
		warehouse_addr2 = data_fields[3]
		warehouse_city = data_fields[4]
		warehouse_phone = data_fields[5]
		warehouse_state = data_fields[6]
		warehouse_post = data_fields[7]
		contact_1st_name = data_fields[8]
		contact_lst_name = data_fields[9]
		warehouse_name = data_fields[10]
		warehouse_country = data_fields[11]

	#print warehouse_firstname 
	#print warehouse_lastname 
	#print warehouse_addr1 
	#print warehouse_addr2 
	#print warehouse_city 
	#print warehouse_phone 
	#then get the ssns - not products in this location
	#with an into date of today

	#and into_date > "TODAY"
	#	and into_date > 'TODAY'

	query4 = """select ssn_id, ssn_description, into_date 
		from ssn 
		where wh_id = '%s' and locn_id = '%s' 
		order by into_date, ssn_description
		"""

	#print query4 % (wk_wh_id, wk_locn_id)
	cur.execute(query4 % (wk_wh_id, wk_locn_id))
		
	# get data record
	data_fields = cur.fetchone()
	#print data_fields
	if data_fields is None:
		#print "no ssns in location for today"
		dummy = 1
	else:
		while not data_fields is None:
			#print data_fields
			current_ssn = data_fields[0]
			current_desc = data_fields[1]
			current_date = data_fields[2]
			# ssn found
			#print "ssn:%s" % current_ssn
			#print "desc:%s" % current_desc
			printline()
			data_fields = cur.fetchone()
		#report footer

con.commit()

print "end - of datafile"


#revert stdin stdout and stderr
if (havelog == 1):
	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__
	out.close()

###
