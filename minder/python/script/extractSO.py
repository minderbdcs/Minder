#!/usr/bin/env python2
"""
<title>
despatchmanifest.py, Version 21.01.04
</title>
<long>
Creates todays So Extract for printing SOs
<br>
Parameters: <tt>input file</tt><tt>log file</tt>
the input file holds <tt>wh_id</tt><tt>location</tt>
<br>
This scans the <tt>input file</tt>and write a report for the Order
<br>
</long>
"""
import sys
import string
import time , os ,glob
import fileinput

import kinterbasdb
import mx.DateTime

#import win32api
#import win32print

def printblankpage( ):
	" print blank page "	
	global  prt, wk_total_pageno
	prt.write("PAGE GROUP BREAK\n")
	prt.write("**************************************************************\n")
	prt.write("**************************************************************\n")
	prt.write("**************************************************************\n")
	prt.write("***************************************************\n\t\t%s %d %s\n" % ('Total', wk_itemno, 'Product Lines'))
	prt.write("\n\t\t%s %s \n" % ('Last Document No', wk_document ))
	prt.write("\n\t\t%s %d %s\n" % ('Total', wk_total_orders, 'Orders this printer'))
	prt.write("\n\t\t%s %d %s\n" % ('Total', wk_total_pageno, 'Pages this printer'))
	wk_date = time.strftime("%d/%m/%y %H:%M:%S")
	prt.write("\n\t\t%s %s\n" % ('Date', wk_date ))
	prt.write("**************************************************************\n")
	prt.write("**************************************************************\n")
	prt.write("**************************************************************\n")
	wk_lineno =  17
	while wk_lineno < 82:
		#print spacing
		prt.write("\n")
		wk_lineno = wk_lineno + 1
	wk_lineno = 0
	wk_total_pageno = wk_total_pageno + 1

def printdeviceheader(wk_printer ):
	" print header for this device "	
	global cur3, prt, wk_printer_name, hPrinter, hJob
	query1 = """
	select computer_name from sys_equip where device_id = '%s' 
	"""
	cur3.execute(query1 % (wk_printer))
	## get data record
	data_fields3 = cur3.fetchone()
	#print data_fields
	if data_fields3 is None:
		print "no printer name"
	else:
		while not data_fields3 is None:
			#print data_fields
			if data_fields3[0] is None:
				wk_printer_name = ""
			else:
				wk_printer_name = data_fields3[0]
				# append to the file
				#hPrinter = win32print.OpenPrinter(wk_printer_name)
				#try:
				#	hJob = win32print.StartDocPrinter(hPrinter, 1, ("delivery Advice",None,"RAW"))
			data_fields3 = cur3.fetchone()
	query1 = """
	select description from options where group_code = 'DEV-HEAD' and VSUBSTRING(CODE,1,3) = '%s' order by code
	"""
	cur3.execute(query1 % (wk_printer))
	## get data record
	data_fields3 = cur3.fetchone()
	#print data_fields
	if data_fields3 is None:
		print "no prefix"
	else:
		while not data_fields3 is None:
			#print data_fields
			if data_fields3[0] is None:
				wk_prefix = ""
			else:
				wk_prefix = data_fields3[0]
				# append to the file
				prt.write("%s\n" % (wk_prefix))
			data_fields3 = cur3.fetchone()

def printdevicefooter(wk_printer ):
	" print a footer for a page "	
	global cur3, prt, hPrinter, hJob
	query1 = """
	select description from options where group_code = 'DEV-FOOT' and VSUBSTRING(CODE,1,3) = '%s' order by code
	"""
	cur3.execute(query1 % (wk_printer))
	## get data record
	data_fields3 = cur3.fetchone()
	#print data_fields
	if data_fields3 is None:
		print "no prefix"
	else:
		while not data_fields3 is None:
			#print data_fields
			if data_fields3[0] is None:
				wk_prefix = ""
			else:
				wk_prefix = data_fields3[0]
				# append to the file
				prt.write("%s\n" % (wk_prefix))
			data_fields3 = cur3.fetchone()
	#if wk_printer_name <> "":
	#	try:
	#		win32print.EndDocPrinter (hPrinter)
	#	try:
	#		win32print.ClosePrinter (hPrinter)

def printfooter( ):
	" print a footer for a page "	
	global wk_lineno, prt, wk_itemno, wk_pageno, wk_date, hPrinter , wk_order_continue, wk_prods, wk_order_continue_foot		
	global  po_order , po_country , po_company , po_contact , po_address1 , po_address2 , po_address3 , po_address4 , po_address5 , po_customer_wo , po_person_id , po_special_instructions1 , po_due_date , po_first_name , po_last_name , po_title , po_post_code , po_aust_4state , po_other_num1 , po_other_num2 , po_freight , po_payment_method , po_amount_paid , po_ship_via , po_other1 , po_other2 , po_other3 , po_remarks1 , po_remarks2 , po_remarks3 , po_remarks4, po_remarks5 , po_remarks6 , po_footer1 , po_footer2 , po_footer3, po_footer4, po_footer5 , po_net_weight , po_pallet_base , po_over_sized ,po_create_date 
	while wk_lineno < 11:
		#print spacing
		prt.write("\n")
		prt.write("\n")
		prt.write("\n")
		wk_lineno = wk_lineno + 1
	# now print the page footer
	#prt.write("\n")
	prt.write("%100.100s   %s\n" % ("",wk_order_continue_foot))
	prt.write("\n")
	prt.write("  %s\n" % (po_remarks1))
	prt.write("  %s\n" % (po_remarks2))
	prt.write("\n")
	prt.write("      %s\n" % (po_remarks3))
	prt.write("      %s\n" % (po_remarks4))
	prt.write("      %s\n" % (po_remarks5))
	prt.write("      %s\n" % (po_remarks6))
	prt.write("\n")
	prt.write("  %s\n" % (po_footer1))
	prt.write("  %s\n" % (po_footer2))
	prt.write("  %s\n" % (po_footer3))
	prt.write("  %s\n" % (po_footer4))
	prt.write("  %s\n" % (po_footer5))
	prt.write("%87.87s%11.2f\n" % ("",po_other_num1))
	prt.write("%87.87s%11.2f\n" % ("",po_freight))
	prt.write("%87.87s%11.2f\n" % ("",po_other_num2))
	prt.write("%10.10s%s\n" % ("",po_person_id))
	prt.write("%-10.10s%s\n" % ("",po_customer_wo))
	if len(wk_prods) > 0:
		prt.write("%45.45s%s\n" % ("",wk_prods[0]))
	else:
		prt.write("\n")
	if len(wk_prods) > 1:
		prt.write("    %-40.40s %s\n" % (po_contact,wk_prods[1]))
	else:
		prt.write("    %s\n" % (po_contact))
	if len(wk_prods) > 2:
		prt.write("    %-40.40s %s\n" % (po_address1,wk_prods[2]))
	else:
		prt.write("    %s\n" % (po_address1))
	if len(wk_prods) > 3:
		prt.write("    %-40.40s %s\n" % (po_address2,wk_prods[3]))
	else:
		prt.write("    %s\n" % (po_address2))
	if len(wk_prods) > 4:
		prt.write("    %-40.40s %s\n" % (po_address3,wk_prods[4]))
	else:
		prt.write("    %s\n" % (po_address3))
	if len(wk_prods) > 5:
		prt.write("    %-40.40s %s\n" % (po_address4,wk_prods[5]))
	else:
		prt.write("    %s\n" % (po_address4))
	if len(wk_prods) > 6:
		prt.write("    %-40.40s %s\n" % (po_address5,wk_prods[6]))
	else:
		prt.write("    %s\n" % (po_address5))
	if len(wk_prods) > 7:
		prt.write("%45.45s%s\n" % ("",wk_prods[7]))
	else:
		prt.write("\n")
	if len(wk_prods) > 8:
		prt.write("%45.45s%s\n" % ("",wk_prods[8]))
	else:
		prt.write("\n")
	if len(wk_prods) > 9:
		prt.write("%45.45s%s\n" % ("",wk_prods[9]))
	else:
		prt.write("\n")
	prt.write("%-100.100s    %s %s\n" % ("",po_post_code, po_aust_4state))
	wk_lineno = 0
	wk_prods = []

def printheader( ):
	" print a page headerline to report file "	
	global wk_lineno, prt, wk_itemno, wk_pageno, wk_total_pageno,wk_date, hPrinter , wk_order_continue, wk_prods, wk_document, wk_order_continue_foot		
	global  pi_prod , pi_location , pi_order_qty , pi_other1 , pi_other2 , pi_other3 , pi_other4 , pi_other5 , pi_other6 , pi_other7 , pi_other8 , pi_other9 , pi_special_instructions1 , pi_special_instructions2 , pi_special_instructions3 , pi_other_qty1 , pi_other_qty2 , pi_batch_line , pi_over_sized , pi_sale_price , pi_tax_rate  
	global pp_export_category, pp_export_category_desc
	global  po_order , po_country , po_company , po_contact , po_address1 , po_address2 , po_address3 , po_address4 , po_address5 , po_customer_wo , po_person_id , po_special_instructions1 , po_due_date , po_first_name , po_last_name , po_title , po_post_code , po_aust_4state , po_other_num1 , po_other_num2 , po_freight , po_payment_method , po_amount_paid , po_ship_via , po_other1 , po_other2 , po_other3 , po_other4, po_other5, po_other6, po_other7, po_remarks1 , po_remarks2 , po_remarks3 , po_remarks4, po_remarks5 , po_remarks6 , po_footer1 , po_footer2 , po_footer3, po_footer4, po_footer5 , po_net_weight , po_pallet_base , po_over_sized ,po_create_date,po_over_sized_why

	#print page header
	wk_lineno = 1
	wk_pageno = wk_pageno + 1
	wk_total_pageno = wk_total_pageno + 1
	wk_real_order = po_order[len(po_other3):]
	if po_over_sized == "F":
		prt.write("%s%s%-80.80s%s\n" % (po_company,po_country,"","" )) # page identifier
	else:
		prt.write("%s%s%-80.80s%s\n" % (po_company,po_country,"****",po_over_sized_why )) # page identifier
	prt.write("\n")
	#prt.write("\n")
	prt.write("%100.100s   %s%s\n" % ("",pp_export_category,pp_export_category_desc))
	prt.write("\n")
	prt.write("\n")
	prt.write("\n")
	prt.write("%100.100s   Document No %s\n" % ("",wk_document))
	prt.write("%61.61s%s\n" % ("",po_person_id))
	prt.write("    %-56.56s %s\n" % (po_contact,po_customer_wo))
	prt.write("    %-56.56s %s\n" % (po_address1, po_due_date.strftime("%d/%m/%y")))
	prt.write("    %-56.56s %s\n" % (po_address2, po_create_date.strftime("%d/%m/%y")))
	prt.write("    %-56.56s %s\n" % (po_address3, wk_real_order))
	prt.write("    %-56.56s%40.40s   %s \n" % (po_address4,"",po_other6))
	prt.write("    %-56.56s%40.40s   %s \n" % (po_address5,"",po_other7))
	prt.write("%100.100s   OrderLabel %s\n" % ("", po_order))
	prt.write("%100.100s   Page %d\n" % ("", wk_pageno))
	#prt.write("%100.100s   %s\n" % ("", po_other1))
	prt.write("%100.100s   %s\n" % ("", pi_other1)) #AM name
	prt.write("%100.100s   %s\n" % ("", po_other2))
	prt.write("%100.100s   %-30.30s   %s\n" % ("",po_pallet_base,po_other4))
	prt.write("                %32.32s%52.52s   %s\n" % (wk_order_continue,"",po_other5))
	prt.write("%100.100s   Expected Weight %10.3f\n" % ("",po_net_weight))
	wk_order_continue = "***    CONTINUATION SHEET    ***"
	wk_order_continue_foot = "***    CONTINUATION    ***"

def printline( ):
	" print a line to report file "	
	global wk_lineno, prt, wk_itemno, wk_pageno, wk_total_pageno,wk_date, hPrinter , wk_order_continue, wk_prods, wk_document, wk_order_continue_foot		
	global  pi_prod , pi_location , pi_order_qty , pi_other1 , pi_other2 , pi_other3 , pi_other4 , pi_other5 , pi_other6 , pi_other7 , pi_other8 , pi_other9 , pi_special_instructions1 , pi_special_instructions2 , pi_special_instructions3 , pi_other_qty1 , pi_other_qty2 , pi_batch_line , pi_over_sized , pi_sale_price , pi_tax_rate  
	global  po_order , po_country , po_company , po_contact , po_address1 , po_address2 , po_address3 , po_address4 , po_address5 , po_customer_wo , po_person_id , po_special_instructions1 , po_due_date , po_first_name , po_last_name , po_title , po_post_code , po_aust_4state , po_other_num1 , po_other_num2 , po_freight , po_payment_method , po_amount_paid , po_ship_via , po_other1 , po_other2 , po_other3 , po_remarks1 , po_remarks2 , po_remarks3 , po_remarks4, po_remarks5 , po_remarks6 , po_footer1 , po_footer2 , po_footer3, po_footer4, po_footer5 , po_net_weight , po_pallet_base , po_over_sized ,po_create_date,po_over_sized_why

	if wk_lineno == 0:
		#print page header
		printheader()

	#################################################
	# qty to print of line is 
	# 1 if pi_other_qty = 0
	# else po_other_qty
	if pi_other_qty1 == 0:
		wk_toprint_qty = 1
	else:
		wk_toprint_qty = pi_other_qty1
	for wk_qty_cnt in range( 0, wk_toprint_qty ):
		#	if lines < 11
		if wk_lineno == 11:
			#print page footer
			printfooter()
			printheader()
		wk_lineno = wk_lineno + 1
		if pi_other_qty1 == 0:
			wk_bay = "  "
			wk_shelf = "  "
			wk_compartment = "  "
		elif pi_over_sized == "T":
			wk_bay = "**"
			wk_shelf = "**"
			wk_compartment = "**"
		else:
			wk_bay = pi_location[2:4]
			wk_shelf = pi_location[4:6]
			wk_compartment = pi_location[6:8]
		wk_size = ""
		wk_printprod = pi_prod
		if pi_prod.find('/') > -1:
			buffer2 = pi_prod.split('/')
			wk_printprod = buffer2[0]
			wk_size = buffer2[1]		
		wk_line_value = pi_order_qty * pi_sale_price
		if wk_qty_cnt == 0:
			prt.write("%s  %s  %s  %-12.12s  %-6.6s %5d %5d  %-31.31s %9.2f  %9.2f\n" % (wk_bay, wk_shelf, wk_compartment, wk_printprod, wk_size, pi_order_qty, pi_other_qty1, pi_other2, pi_sale_price, wk_line_value))
		else:
			prt.write("%s  %s  %s  %-12.12s  %-6.6s \n" % (wk_bay, wk_shelf, wk_compartment, wk_printprod, wk_size ))
		# if i > 1
		# then use blank qtys
		wk_description = pi_special_instructions1 + pi_special_instructions2 + pi_special_instructions3
		if len(wk_description) > 31:
			wk_description1 = wk_description[:31]
			if len(wk_description) > 62:
				wk_description2 = wk_description[31:62]
			else:
				wk_description2 = wk_description
		else:
			wk_description1 = wk_description
			wk_description2 = ""
		prt.write("%46.46s%s\n" % ("",wk_description1))
		prt.write("%46.46s%s\n" % ("",wk_description2))
	##################################################
	wk_itemno = wk_itemno + 1
	wk_prods.append(pi_prod)
	if wk_lineno == 11:
		#print page footer
		printfooter()

#redirect stdout and stderr

if len(sys.argv)>1:
	print "extractSO ", sys.argv[1]
	infile = sys.argv[1]
	havein = 1;
else:
	print "extractSO stdin"
	infile = '-'
	havein = 0;

if len(sys.argv)>2:
	print "log ", sys.argv[2]
	logfile = sys.argv[2]
	havelog = 1;
else:
	print "log stdin"
	havelog = 0;

rest, ext = os.path.splitext(infile)
path, base = os.path.split(rest)
prnformat = "%s%s" % (base, ".prn")
prnformat2 = "%s%s" % (base, ".prt")
prtfile = os.path.join(path, prnformat)
prtfile2 = os.path.join(path, prnformat2)
print prtfile
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
cur3 = con.cursor()

wk_lineno = 0
wk_itemno = 0
wk_pageno = 0
wk_total_pageno = 1
wk_total_orders = 0
wk_first_page = "T"
wk_date = time.strftime("%d/%m/%y")
hPrinter = ""
hJob = ""
wk_printer_name = ""

#read std or 1st input parm
for line in fileinput.input(infile):
	print "line",line
	wk_order_continue = ""
	wk_order_continue_foot = ""
	wk_pageno = 0
	wk_prods = []
	#wk_code = line
	buffer = line.split('","')
	wk_end = len(buffer) -1
	#print "wk_end",wk_end
	if wk_end < 1:
		break
	#for xindex in range(0,len(buffer)):
	#	print "x",xindex,buffer[xindex]
	wk_order = buffer[0][1:]
	wk_printer = buffer[1]
	wk_document = buffer[2][:-2]
	print "order",wk_order
	print "printer",wk_printer
	print "document no",wk_document
	
	if wk_first_page == "T":
		# now add printer header
		printdeviceheader(wk_printer)
		wk_first_page = "F"
	if wk_order == "":
		# print a blank page
		printblankpage()
	else:
		#now for this location get its person details for heading
		query1 = """
		select pick_order,p_country,company_id,contact_name,p_address_line1,p_address_line2,p_address_line3,p_address_line4,p_address_line5,customer_po_wo,person_id,special_instructions1,pick_due_date,p_first_name,p_last_name,p_title, p_post_code,p_aust_post_4state_id,other_num1,other_num2,freight,payment_method,amount_paid,ship_via,other1,other2,other3,remarks1,remarks2,remarks3,remarks4,remarks5,remarks6,footer1,footer2,footer3,footer4,footer5,net_weight,pallet_base,over_sized,create_date,over_sized_reason,p_state,other4,other5,other6,other7 from pick_order where pick_order='%s'
		"""
		cur.execute(query1 % (wk_order))
			
		## get data record
		data_fields = cur.fetchone()
		#print data_fields
		if data_fields is None:
			print "no order "
			po_order = "Unknown"
			po_country = ""
			po_company = ""
			po_contact = ""
			po_address1 = ""
			po_address2 = ""
			po_address3 = ""
			po_address4 = ""
			po_address5 = ""
			po_customer_wo = ""
			po_person_id = ""
			po_special_instructions1 = ""
			po_due_date = mx.DateTime.now()
			po_first_name = ""
			po_last_name = ""
			po_title = ""
			po_post_code = ""
			po_aust_4state = ""
			po_other_num1 = 0
			po_other_num2 = 0
			po_freight = 0
			po_payment_method = ""
			po_amount_paid = 0
			po_ship_via = ""
			po_other1 = ""
			po_other2 = ""
			po_other3 = ""
			po_other4 = ""
			po_other5 = ""
			po_other6 = ""
			po_other7 = ""
			po_remarks1 = ""
			po_remarks2 = ""
			po_remarks3 = ""
			po_remarks4= ""
			po_remarks5 = ""
			po_remarks6 = ""
			po_footer1 = ""
			po_footer2 = ""
			po_footer3= ""
			po_footer4= ""
			po_footer5 = ""
			po_net_weight = 0
			po_pallet_base = ""
			po_over_sized = ""
			po_over_sized_why = ""
			po_state = ""
			po_due_date = mx.DateTime.now()
		else:
			#print data_fields
			wk_total_orders = wk_total_orders + 1
			if data_fields[0] is None:
				po_order = ""
			else:
				po_order = data_fields[0]
			if data_fields[1] is None:
				po_country = ""
			else:
				po_country = data_fields[01]
			if data_fields[2] is None:
				po_company = ""
			else:
				po_company = data_fields[02]
			if data_fields[3] is None:
				po_contact = ""
			else:
				po_contact = data_fields[03]
			if data_fields[4] is None:
				po_address1 = ""
			else:
				po_address1 = data_fields[04]
			if data_fields[5] is None:
				po_address2 = ""
			else:
				po_address2 = data_fields[05]
			if data_fields[6] is None:
				po_address3 = ""
			else:
				po_address3 = data_fields[06]
			if data_fields[7] is None:
				po_address4 = ""
			else:
				po_address4 = data_fields[07]
			if data_fields[8] is None:
				po_address5 = ""
			else:
				po_address5 = data_fields[8]
			if data_fields[9] is None:
				po_customer_wo = ""
			else:
				po_customer_wo = data_fields[9]
			if data_fields[10] is None:
				po_person_id = ""
			else:
				po_person_id = data_fields[10]
			if data_fields[11] is None:
				po_special_instructions1 = ""
			else:
				po_special_instructions1 =  data_fields[11]
			if data_fields[12] is None:
				po_due_date = mx.DateTime.now()
			else:
				po_due_date = data_fields[12]
			if data_fields[13] is None:
				po_first_name = ""
			else:
				po_first_name = data_fields[13]
			if data_fields[14] is None:
				po_last_name = ""
			else:
				po_last_name = data_fields[14]
			if data_fields[15] is None:
				po_title = ""
			else:
				po_title = data_fields[15]
			if data_fields[16] is None:
				po_post_code = ""
			else:
				po_post_code = data_fields[16]
			if data_fields[17] is None:
				po_aust_4state = ""
			else:
				po_aust_4state = data_fields[17]
			if data_fields[18] is None:
				po_other_num1 = ""
			else:
				po_other_num1 = data_fields[18] # goods value 
			if data_fields[19] is None:
				po_other_num2 = 0
			else:
				po_other_num2 = data_fields[19] # order value 
			if data_fields[20] is None:
				po_freight = 0
			else:
				po_freight = data_fields[20]
			if data_fields[21] is None:
				po_payment_method = ""
			else:
				po_payment_method = data_fields[21]
			if data_fields[22] is None:
				po_amount_paid = 0
			else:
				po_amount_paid = data_fields[22]
			if data_fields[23] is None:
				po_ship_via = ""
			else:
				po_ship_via = data_fields[23]
			if data_fields[24] is None:
				po_other1 = ""
			else:
				po_other1 = data_fields[24] #* batch no 
			if data_fields[25] is None:
				po_other2 = ""
			else:
				po_other2 = data_fields[25] #dist name
			if data_fields[26] is None:
				po_other3 = ""
			else:
				po_other3 = data_fields[26] # order prefix 
			if data_fields[27] is None:
				po_remarks1 = ""
			else:
				po_remarks1 = data_fields[27] # remarks 1 
			if data_fields[28] is None:
				po_remarks2 = ""
			else:
				po_remarks2 = data_fields[28] # remarks 2 
			if data_fields[29] is None:
				po_remarks3 = ""
			else:
				po_remarks3 = data_fields[29] # pay details 1 
			if data_fields[30] is None:
				po_remarks4 = ""
			else:
				po_remarks4= data_fields[30] # pay details 2 
			if data_fields[31] is None:
				po_remarks5 = ""
			else:
				po_remarks5 = data_fields[31] # pay details 3 
			if data_fields[32] is None:
				po_remarks6 = ""
			else:
				po_remarks6 = data_fields[32] # pay details 4 
			if data_fields[33] is None:
				po_footer1 = ""
			else:
				po_footer1 = data_fields[33]
			if data_fields[34] is None:
				po_footer2 = ""
			else:
				po_footer2 = data_fields[34]
			if data_fields[35] is None:
				po_footer3 = ""
			else:
				po_footer3= data_fields[35]
			if data_fields[36] is None:
				po_footer4 = ""
			else:
				po_footer4= data_fields[36]
			if data_fields[37] is None:
				po_footer5 = ""
			else:
				po_footer5 = data_fields[37]
			if data_fields[38] is None:
				po_net_weight = 0
			else:
				po_net_weight = data_fields[38]
			if data_fields[39] is None:
				po_pallet_base = ""
			else:
				po_pallet_base = data_fields[39]
			if data_fields[40] is None:
				po_over_sized = ""
			else:
				po_over_sized = data_fields[40]
			if data_fields[41] is None:
				po_create_date = mx.DateTime.now()
			else:
				po_create_date = data_fields[41]
			if data_fields[42] is None:
				po_over_sized_why = ""
			else:
				po_over_sized_why = data_fields[42]
			if data_fields[43] is None:
				po_state = ""
			else:
				po_state = data_fields[43]
			if po_address1 == "":
				po_address1 = po_state + " " + po_post_code
			elif po_address2 == "":
				po_address2 = po_state + " " + po_post_code
			elif po_address3 == "":
				po_address3 = po_state + " " + po_post_code
			elif po_address4 == "":
				po_address4 = po_state + " " + po_post_code
			elif po_address5 == "":
				po_address5 = po_state + " " + po_post_code
			if data_fields[44] is None:
				po_other4 = ""
			else:
				po_other4 = data_fields[44] #* dist id 
			if data_fields[45] is None:
				po_other5 = ""
			else:
				po_other5 = data_fields[45] #* area manager id
			if data_fields[46] is None:
				po_other6 = ""
			else:
				po_other6 = data_fields[46] #* customer phone
			if data_fields[47] is None:
				po_other7 = ""
			else:
				po_other7 = data_fields[47] #* dist phone
			#print po_create_date
	
		#then get the products in this order
	
	
		query5 = """select first 1 p2.export_category,o1.description, o2.description
			from pick_item  p1
	                join prod_profile p2 on p2.prod_id = p1.prod_id 
	                left outer join options o1 on o1.group_code = 'PRODEXPCAT' and o1.code = p2.export_category 
	                join options o2 on o2.group_code = 'CMPPKEXP' and o2.code = '%s|%s' 
			where p1.pick_order = '%s'  
                        and (p2.export_category is not null)
                        and (p2.export_category <> '')
			"""
	
		#print query5 % (wk_wh_id, wk_locn_id)
		cur.execute(query5 % (po_company, po_country, wk_order ))
			
		# get data record
		data_fields = cur.fetchone()
		#print data_fields
		if data_fields is None:
			#print "no export categorys for order"
			pp_export_category = ""
			pp_export_category_desc = ""
		else:
			while not data_fields is None:
				#print data_fields
				if data_fields[0] is None:
					pp_export_category = ""
				else:
					pp_export_category = data_fields[0]
				if data_fields[1] is None:
					pp_export_category_desc = ""
				else:
					pp_export_category_desc = data_fields[1]
				if data_fields[2] is None:
					pp_export_category = ""
					pp_export_category_desc = ""
				else:
					if data_fields[2] != 'T':
						pp_export_category = ""
						pp_export_category_desc = ""
				data_fields = cur.fetchone()
		
		query4 = """select p1.prod_id, p1.pick_location, p1.other_qty1, p1.other1, p1.other2, p1.other3, p1.other4, p1.other5, p1.other6, p1.other7, p1.other8, p1.other9, p1.special_instructions1,p1.special_instructions2,p1.special_instructions3, p1.pick_order_qty, p1.other_qty2, p1.batch_line, p1.over_sized, p1.sale_price, p1.tax_rate 
			from pick_item  p1
	                left outer join location l1 on l1.wh_id=p1.wh_id and l1.locn_id=p1.pick_location 
	                left outer join prod_profile p2 on p2.prod_id = p1.prod_id 
			where p1.pick_order = '%s'  
			order by p1.wh_id, l1.locn_seq, p1.pick_location
			"""
	
		#print query4 % (wk_wh_id, wk_locn_id)
		cur.execute(query4 % (wk_order ))
			
		# get data record
		data_fields = cur.fetchone()
		#print data_fields
		if data_fields is None:
			#print "no prods for today"
			dummy = 1
		else:
			while not data_fields is None:
				#print data_fields
				if data_fields[0] is None:
					pi_prod = ""
				else:
					pi_prod = data_fields[0]
				if data_fields[1] is None:
					pi_location = ""
				else:
					pi_location = data_fields[01]
				if data_fields[2] is None:
					pi_order_qty = 0
				else:
					pi_order_qty = data_fields[02]
				if data_fields[3] is None:
					pi_other1 = ""
				else:
					pi_other1 = data_fields[03]
				if data_fields[4] is None:
					pi_other2 = ""
				else:
					pi_other2 = data_fields[04]
				if data_fields[5] is None:
					pi_other3 = ""
				else:
					pi_other3 = data_fields[05]
				if data_fields[6] is None:
					pi_other4 = ""
				else:
					pi_other4 = data_fields[06]
				if data_fields[7] is None:
					pi_other5 = ""
				else:
					pi_other5 = data_fields[07]
				if data_fields[8] is None:
					pi_other6 = ""
				else:
					pi_other6 = data_fields[8]
				if data_fields[9] is None:
					pi_other7 = ""
				else:
					pi_other7 = data_fields[9]
				if data_fields[10] is None:
					pi_other8 = ""
				else:
					pi_other8 = data_fields[10]
				if data_fields[11] is None:
					pi_other9 = ""
				else:
					pi_other9 = data_fields[11]
				if data_fields[12] is None:
					pi_special_instructions1 = ""
				else:
					pi_special_instructions1 = data_fields[12]
				if data_fields[13] is None:
					pi_special_instructions2 = ""
				else:
					pi_special_instructions2 = data_fields[13]
				if data_fields[14] is None:
					pi_special_instructions2 = ""
				else:
					pi_special_instructions3 = data_fields[14]
				if data_fields[15] is None:
					pi_other_qty1 = 0
				else:
					pi_other_qty1 = data_fields[15]
				if data_fields[16] is None:
					pi_other_qty2 = 0
				else:
					pi_other_qty2 = data_fields[16]
				if data_fields[17] is None:
					pi_batch_line = ""
				else:
					pi_batch_line = data_fields[17]
				if data_fields[18] is None:
					pi_over_sized = ""
				else:
					pi_over_sized = data_fields[18]
				if data_fields[19] is None:
					pi_sale_price = 0
				else:
					pi_sale_price = data_fields[19]
				if data_fields[20] is None:
					pi_tax_rate = 0
				else:
					pi_tax_rate = data_fields[20]
				printline()
				data_fields = cur.fetchone()
			#report footer
			wk_order_continue_foot = ""
			if wk_lineno > 0:
				printfooter()
con.commit()
# end of printer file
if wk_lineno == 0:
	prt.write("LAST PAGE\n")
	prt.write("**************************************************************\n")
	prt.write("**************************************************************\n")
	prt.write("**************************************************************\n")
	prt.write("***************************************************\n\t\t%s %d %s\n" % ('Total', wk_itemno, 'Product Lines'))
	prt.write("\n\t\t%s %s \n" % ('Last Document No', wk_document ))
	prt.write("\n\t\t%s %d %s\n" % ('Total', wk_total_orders, 'Orders this printer'))
	prt.write("\n\t\t%s %d %s\n" % ('Total', wk_total_pageno, 'Pages this printer'))
	wk_date = time.strftime("%d/%m/%y %H:%M:%S")
	prt.write("\n\t\t%s %s\n" % ('Date', wk_date ))
	prt.write("**************************************************************\n")
	prt.write("**************************************************************\n")
	prt.write("**************************************************************\n")
	wk_lineno =  17
	while wk_lineno < 82:
		#print spacing
		prt.write("\n")
		wk_lineno = wk_lineno + 1
	wk_lineno = 0

# now add printer trailer
printdevicefooter(wk_printer)

print "end - of Orders"

prt.close()
# rename the file for commander
os.rename(prtfile,prtfile2)

#win32api.ShellExecute(0, "print", prtfile, None, ".", 0)

#revert stdin stdout and stderr
if (havelog == 1):
	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__
	out.close()

###
