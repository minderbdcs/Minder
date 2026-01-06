#!/usr/bin/env python2
"""
<title>
despatchmanifest.py, Version 21.01.04
</title>
<long>
Creates todays despatch manifest of all ssns in a location
<br>
Parameters: <tt>input file</tt></tt>log file</tt><tt>output file</tt>
the input file holds <tt>wh_id</tt><tt>location</tt>
<br>
This scans the <tt>input file</tt>and write a report for the location including all the non product ssns for despatchs of today
and puts the files into the output file
<br>
</long>
"""
import sys
import string
import time , os ,glob
import fileinput
#import win32api
import win32print

import kinterbasdb
import mx.DateTime

#redirect stdout and stderr

def printline( ):
	" print a line to report file "	
	global wk_lineno, warehouse_firstname, warehouse_lastname, warehouse_addr1, warehouse_addr2, warehouse_city, current_ssn, current_desc, prt, warehouse_state, warehouse_post, wk_itemno, wk_pageno, wk_date, warehouse_country, warehouse_phone, contact_1st_name, contact_lst_name, warehouse_name, hPrinter		
	if wk_lineno > 48:
		if wk_lineno < 60:
			prt.write("\n\n\n\n\n\n\n")
			win32print.WritePrinter( hPrinter, "\r\n\r\n\r\n\r\n\r\n\r\n\r\n")
		
		#print page header
		wk_lineno = 10
		#prt.write("\f")
		#if wk_line_no < 66:
		#	prt.write("\n")
		wk_pageno = wk_pageno + 1
		#prt.write("\t\t\t\t\t Date Shipped %s\tPage %d\n" % (wk_date , wk_pageno))
		prt.write("\t\t\t\t\t Date Shipped %s\n" % (wk_date ))
		win32print.WritePrinter( hPrinter, "\t\t\t\t\t Date Shipped %s\r\n" % (wk_date ))
		prt.write("\t%s\n" % (warehouse_name ))
		win32print.WritePrinter( hPrinter, "\t%s\r\n" % (warehouse_name ))
		prt.write(" TO\t%s %s\n" % (contact_1st_name , contact_lst_name))
		win32print.WritePrinter( hPrinter, " TO\t%s %s\r\n" % (contact_1st_name , contact_lst_name))
		prt.write("\t%s %s\n" % (warehouse_firstname , warehouse_lastname))
		win32print.WritePrinter( hPrinter, "\t%s %s\r\n" % (warehouse_firstname , warehouse_lastname))
		prt.write("\t%s\n" % (warehouse_addr1))
		win32print.WritePrinter( hPrinter, "\t%s\r\n" % (warehouse_addr1))
		prt.write("\t%s\n" % (warehouse_addr2))
		win32print.WritePrinter( hPrinter, "\t%s\r\n" % (warehouse_addr2))
		prt.write("\t%s\n" % (warehouse_city))
		win32print.WritePrinter( hPrinter, "\t%s\r\n" % (warehouse_city))
		prt.write("\t%s %s\n" % (warehouse_state, warehouse_post))
		win32print.WritePrinter( hPrinter, "\t%s %s\r\n" % (warehouse_state, warehouse_post))
		prt.write("\t%s\n" % (warehouse_country))
		win32print.WritePrinter( hPrinter, "\t%s\r\n" % (warehouse_country))
		prt.write("\tPhone %s\n\n\n" % (warehouse_phone))
		win32print.WritePrinter( hPrinter, "\tPhone %s\r\n\r\n\r\n" % (warehouse_phone))
		prt.write("%s\t\t%s\n\n" % ('Tool', 'Description'))
		win32print.WritePrinter( hPrinter, "%s\t\t%s\r\n\r\n" % ('Tool', 'Description'))


	wk_lineno = wk_lineno + 1
	prt.write("%s\t%s\n" % (current_ssn, current_desc))
	win32print.WritePrinter( hPrinter, "%s\t%s\r\n" % (current_ssn, current_desc))
	wk_itemno = wk_itemno + 1

if len(sys.argv)>0:
	print "despatchmanifest ", sys.argv[1]
	infile = sys.argv[1]
	havein = 1;
else:
	print "despatchmanifest stdin"
	infile = '-'
	havein = 0;

if len(sys.argv)>1:
	print "log ", sys.argv[2]
	logfile = sys.argv[2]
	havelog = 1;
else:
	print "log stdin"
	havelog = 0;

if len(sys.argv)>2:
	print "output ", sys.argv[3]
	prtfile = sys.argv[3]
else:
	print "output manifest.txt"
	prtfile = "manifest.txt"

#
#redirect stdout and stderr
if (havelog == 1):
	out = open(logfile,'a')
	sys.stdout = out
	sys.stderr = out

prt = open(prtfile,'w')

printer_name = "MULPRT01"

hPrinter = win32print.OpenPrinter(printer_name)
hJob = win32print.StartDocPrinter(hPrinter,1,("Despatch Manifest",None, "RAW"))

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

wk_lineno = 66
wk_itemno = 0
wk_pageno = 0
wk_date = time.strftime("%d/%m/%y")
	
#read std or 1st input parm
for line in fileinput.input(infile):
	print "line",line
	#wk_code = line
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
		order by ssn_id
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
	 	prt.write("\n\t\t%s %d %s\n" % ('Total', wk_itemno, 'Tools'))
	 	win32print.WritePrinter( hPrinter, "\r\n\t\t%s %d %s\r\n" % ('Total', wk_itemno, 'Tools'))

con.commit()

print "end - of SSNs"
#revert stdin stdout and stderr
if (havelog == 1):
	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__
	out.close()

#win32api.ShellExecute(
#	0,
#	"print",
#	prtfile,
#	None,
#	".",
#	0)

win32print.EndDocPrinter(hPrinter)
win32print.ClosePrinter(hPrinter)
prt.close()

###
