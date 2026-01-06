#!/usr/bin/env python2
"""
<title>
despatchmanifest.py, Version 21.01.04
</title>
<long>
Creates todays despatch manifest of all ssns in a location
<br>
Parameters: <tt>input file</tt><tt>log file</tt>
the input file holds <tt>wh_id</tt><tt>location</tt>
<br>
This scans the <tt>input file</tt>and write a report for the location including all the ssn loans of today
<br>
</long>
"""
import sys
import string
import time , os ,glob
import fileinput

import kinterbasdb
import mx.DateTime

from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, Frame
from reportlab.lib.styles import getSampleStyleSheet
from reportlab.lib.units import inch, cm
from reportlab.pdfgen import canvas
import cgi

import win32api

import Image

def printline( ):
	" print a line to report file "	
	global wk_lineno, warehouse_firstname, warehouse_lastname, warehouse_addr1, warehouse_addr2, warehouse_city, current_prod, current_to_wh, current_to_locn, current_qty, current_pri, prt, warehouse_state, warehouse_post, wk_itemno, wk_pageno, wk_date, warehouse_country, warehouse_phone, contact_1st_name, contact_lst_name, warehouse_name, hPrinter		
	global required_qty, wh_qty, unpicked_qty, fulfil_qty,company_name , company_addr1 , company_addr2 , company_addr3 , company_abn , company_phone , company_fax , pdfdata

	if wk_lineno > 54:
		if wk_lineno < 60:
			prt.write("\n")
		
		#print page header
		wk_lineno = 10
		#prt.write("\f")
		#if wk_line_no < 66:
		#	prt.write("\n")
		wk_pageno = wk_pageno + 1
		#prt.write("\t\t\t\t\t Date Shipped %s\tPage %d\n" % (wk_date , wk_pageno))
		#prt.write("\t\t\t\t\t Date %s\n" % (wk_date ))
		#prt_buffer = "\t%s" % (warehouse_name )
		#prt_len = len(prt_buffer)
		#prt.write("\t%s\n" % (warehouse_name ))
		#prt.write(" TO\t%s %s\n" % (warehouse_firstname , warehouse_lastname))
		#prt.write("\t%s\n" % (warehouse_addr1))
		#prt.write("\t%s\n" % (warehouse_addr2))
		#prt.write("\t%s\n" % (warehouse_city))
		#prt.write("\t%s %s\n" % (warehouse_state, warehouse_post))
		#prt.write("\t%s\n" % (warehouse_country))
		#prt.write("\t%s %s\n" % (contact_1st_name , contact_lst_name))
		prt.write("%s\t%s\t%-32.32s\t%s %s %s %s %s\t%s\n\n" % ('Location', 'Product','Desc','Reqd Qty','Current Qty','WH Qty','On Order','FulFill','Priority'))

	wk_lineno = wk_lineno + 1
	prt.write( "%s %s\t%s\t%-32.32s\t%5.5d\t%5.5d\t%5.5d\t%5.5d\t%5.5d\t\t\t%d\n" % (current_wh, current_locn, current_prod, current_proddesc,required_qty, current_qty, wh_qty, unpicked_qty,fulfil_qty,current_pri))
	wk_itemno = wk_itemno + 1

def myPage(canvas, doc) :
	" prepare page layout for pdf "	
	global warehouse_firstname, warehouse_lastname, warehouse_addr1, warehouse_addr2, warehouse_city, warehouse_state, warehouse_post, wk_date, warehouse_country, warehouse_phone, contact_1st_name, contact_lst_name, warehouse_name  
	global company_name , company_addr1 , company_addr2 , company_addr3 , company_abn , company_phone , company_fax, created_logo
	canvas.saveState()
	if created_logo == 0:
		created_logo = 1
        	#canvas.beginForm("companyLogo")
        	#path = bitfile
        	#path = '/tmp/logo.jpg'
        	#path = 'logo.bmp'
        	#canvas.drawImage(path, inch, inch * 10.8)
        	#canvas.endForm()

        	canvas.beginForm("address")
		canvas.setFont('Times-Roman',18)
		canvas.drawString( 4 * inch, 11.0 * inch, "Replenish" )
		canvas.setFont('Times-Roman',10)
		#canvas.drawString( inch, 10.8 * inch, " \t%s %s" % (warehouse_firstname , warehouse_lastname))
		#canvas.drawString( inch, 10.6 * inch, "\t%s" % (warehouse_addr1))
		#canvas.drawString( inch, 10.4 * inch, "\t%s" % (warehouse_addr2))
		#canvas.drawString( inch, 10.2 * inch, "\t%s" % (warehouse_city))
		#canvas.drawString( inch, 10.0 * inch, "\t%s %s" % (warehouse_state, warehouse_post))
		#canvas.drawString( inch, 9.8 * inch, "\t%s" % (warehouse_country))
		#canvas.drawString( inch, 9.6 * inch, "\t%s %s" % (contact_1st_name , contact_lst_name))
		#canvas.drawString( inch, 9.4 * inch, "\tPhone %s" % (warehouse_phone))
		canvas.setFont('Times-Roman',12)
		#canvas.drawString( inch, 9.0 * inch, "%s" % (' Location        Prod Qtys'))
		canvas.drawString( inch, 10.6 * inch, "%s" % (' Location        Prod ReqdQty  LocnQty WHQty   OnOrder FulFill'))
		canvas.setFont('Times-Roman',10)
		#canvas.drawString(6.6 * inch, 10.8 * inch, "%s" % (company_name  ))
		#canvas.drawString(6.6 * inch, 10.6 * inch, "ABN %s" % (company_abn ))
		#canvas.drawString(6.6 * inch, 10.4 * inch, "%s" % (company_addr1 ))
		#canvas.drawString(6.6 * inch, 10.2 * inch, "%s" % (company_addr2 ))
		#canvas.drawString(6.6 * inch, 10.0 * inch, "%s" % (company_addr3 ))
		#canvas.drawString(6.6 * inch, 9.8 * inch, "Ph: %s" % (company_phone ))
		#canvas.drawString(6.6 * inch, 9.6 * inch, "Fax: %s" % (company_fax ))
		canvas.setFont('Times-Roman',10)
		#canvas.drawString(4 * inch, 9.2 * inch, "%s" % (warehouse_name ))
		canvas.drawString(5.5 * inch, 10.6 * inch, "Date %s" % (wk_date ))
        	canvas.endForm()
	#canvas.doForm("companyLogo")
	canvas.doForm("address")

        # Footer.
	canvas.setFont('Times-Roman',8)
	canvas.drawString(inch, 0.75 * inch, "Page %d" % (doc.page))
	canvas.restoreState()

#redirect stdout and stderr

print "replenish "
havein = 0;
infile = "d:/asset.rf/pg/replenish.txt"

if len(sys.argv)>1:
	print "log ", sys.argv[1]
	logfile = sys.argv[1]
	havelog = 1;
else:
	print "log stdin"
	havelog = 0;

#if len(sys.argv)>2:
#	print "output ", sys.argv[3]
#	prtfile = sys.argv[3]
#else:
#	print "output manifest.txt"
#	prtfile = "manifest.txt"

#if len(sys.argv)>2:
#	print "bitfile ", sys.argv[3]
#	bitfile = sys.argv[3]
#else:
#	print "bitmap logo.jpg"
#	bitfile = "logo.jpg"

#pdffile = prtfile + ".pdf"
rest, ext = os.path.splitext(infile)
path, base = os.path.split(rest)
pdfformat = "%s%s" % (base, ".pdf")
prnformat = "%s%s" % (base, ".prn")
pdffile = os.path.join(path, pdfformat)
prtfile = os.path.join(path, prnformat)
print prtfile
print pdffile
created_logo = 0
#
#redirect stdout and stderr
if (havelog == 1):
	out = open(logfile,'a')
	sys.stdout = out
	sys.stderr = out

prt = open(prtfile,'w')

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
wk_date = time.strftime("%d/%m/%y %H:%M")
	
#now get the company address for heading

query1 = """select first 1 name, address1, address2, address3 , abn, phone_no , fax_no
from company
"""

cur.execute(query1 )
		
## get data record
data_fields = cur.fetchone()
#print data_fields
if data_fields is None:
	print "no company "
	company_name = ""
	company_addr1 = ""
	company_addr2 = ""
	company_addr3 = ""
	company_abn = ""
	company_phone = ""
	company_fax = ""
else:
	#print data_fields
	company_name = data_fields[0]
	company_addr1 = data_fields[1]
	company_addr2 = data_fields[2]
	company_addr3 = data_fields[3]
	company_abn = data_fields[4]
	company_phone = data_fields[5]
	company_fax = data_fields[6]

pdfdata = ""
#print "line",line
#wk_code = line
#buffer = line.split('","')
#wk_end = len(buffer) -1
#print "wk_end",wk_end
#if wk_end < 1:
#	break
#for xindex in range(0,len(buffer)):
#	print "x",xindex,buffer[xindex]
#wk_wh_id = buffer[0][1:]
#wk_locn_id = buffer[1]
#wk_printer = buffer[2][:2]
#print "wh",wk_wh_id
#print "locn",wk_locn_id
#print "printer",wk_printer

#now for this location get its person details for heading

	
## get data record
#print data_fields
warehouse_firstname = ""
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

#pdfdata += "%s Date Shipped %s\n" % (warehouse_name, wk_date )
pdfdata += "\n" 
query4 = """select r1.to_wh_id, r1.to_locn_id, r1.prod_id, p1.short_desc,r1.required_qty, r1.current_qty,r1.wh_qty,r1.unpicked_order_qty,(r1.unpicked_order_qty - r1.current_qty),r1.trn_priority
	from replenish_locn r1 
	join prod_profile p1 on p1.prod_id=r1.prod_id
	order by r1.trn_priority,r1.to_wh_id,r1.to_locn_id
	"""
# now want the qty to fulfill orders
# = min(unpicked qty  - locn_qty,0)

#print query4 % (wk_wh_id, wk_locn_id)
cur.execute(query4)
	
# get data record
data_fields = cur.fetchone()
#print data_fields
if data_fields is None:
	#print "no ssns in location for today"
	dummy = 1
else:
	while not data_fields is None:
		#print data_fields
		current_wh = data_fields[0]
		current_locn = data_fields[1]
		current_prod = data_fields[2]
		current_proddesc = data_fields[3]
		required_qty = data_fields[4]
		current_qty = data_fields[5]
		wk_wh_qty = data_fields[6]
		wh_qty = wk_wh_qty - current_qty
		unpicked_qty = data_fields[7]
		fulfil_qty = data_fields[8]
		current_pri = data_fields[9]
		# ssn found
		#print "ssn:%s" % current_ssn
		#print "desc:%s" % current_desc
		printline()
		pdfdata += "%s %s\t%s\t%-30.30s\t%5.5d\t-----\t%5.5d-----\t%5.5d\t-----\t%5.5d\t-----%5.5d\t----------\t%d\n" % (current_wh, current_locn, current_prod, current_proddesc, required_qty,current_qty, wh_qty, unpicked_qty,fulfil_qty,current_pri)
		data_fields = cur.fetchone()
	#report footer
 	prt.write("\n\t\t%s %d %s\n" % ('Total', wk_itemno, 'Products'))
	pdfdata += "\n\t\t%s %d %s\n" % ('Total', wk_itemno, 'Products')
#logoout = open("/tmp/logo.bmp",'wb')
#query = "select company_logo from control"
#ret = cur.execute(query)
#control_record = cur.fetchonemap()
#if control_record is None:
#	print ""
#else:
#	logoout.write(control_record['company_logo'])
#logoout.close()
#Image.open("/tmp/logo.bmp").save("/tmp/logo.jpg")

con.commit()

print "end - of prods"

prt.close()

styles = getSampleStyleSheet()
h1 = styles["h1"]
normal = styles["Normal"]
doc = SimpleDocTemplate (pdffile, topMargin=1.5*inch)

# need data as xml - so escape it
#text = cgi.escape (open(prtfile).read()).splitlines()
text = cgi.escape (pdfdata).splitlines()

story = [Paragraph (text[0], h1)]
for line in text[1:]:
	story.append (Paragraph(line, normal))
	story.append (Spacer (1, 0.1 * inch))

doc.build(story, onFirstPage=myPage, onLaterPages=myPage)

win32api.ShellExecute(0, "print", pdffile, None, ".", 0)

#revert stdin stdout and stderr
if (havelog == 1):
	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__
	out.close()

###
