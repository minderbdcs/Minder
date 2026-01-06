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
Work on the first sheet.
The first record holds the columns to insert/update
This 
    1. imports an SSN record.
       make up the unique key for the SSN using the SSN field
       So check if the unique record exists. Also read it's current GRN.
       If not then insert it. 
            If no GRN yet. Use the GRN for this RUN of the import.
            If none yet get the next GRN no , GRN order save as the GRN of this RUN of the Import
       else update it
    2. execute db procedure ADD_ISSN_FROM_SSN passing the SSN_ID to work on.
    3. insert or update the GRN.
    Return Name in Other32
    Intero Site in Other33
    Asset Grade in Other34 - they wanted in other19 - but already have other19
    Grade in Other35
    Erasure Details in Other36 = Move to Other11
    Damages in Other37 - they wanted in Notes - but already have notes
    Return ID in Other38
    GRN Date in Other39

    Have to replace "" in other5 - other10 also other 19 and other20 to use value "UNKNOWN"
<br>
</long>
"""
import sys
import string
import time , os ,glob
import fileinput

import fdb
#import mx.DateTime
#import fdb;fdb.init(type_conv=200)
#fdb.init(type_conv=200)

import datetime 
import csv 
import openpyxl 

from importCheck2 import check2field ,add2field


#redirect stdout and stderr

def checkFloat(hop):
	num_format = False
	try:
		wk_float = float(hop)
		num_format = True
	except:
		num_format = False
	return num_format

def checkDate(hop):
	date_string = hop
	date_string = date_string.strip()
	print hop
	time_tuple = None
	try:
		time_tuple = time.strptime(date_string,"%d/%m/%Y")
		date_format = "%d/%m/%Y"
	except:
		date_format = None
	if date_format is None:
		try:
			time_tuple = time.strptime(date_string,"%d-%m-%Y")
			date_format = "%d-%m-%Y"
		except:
			date_format = None
	if date_format is None:
		try:
			time_tuple = time.strptime(date_string,"%d/%m/%y")
			date_format = "%d/%m/%y"
		except:
			date_format = None
	if date_format is None:
		try:
			time_tuple = time.strptime(date_string,"%d-%m-%y")
			date_format = "%d-%m-%y"
		except:
			date_format = None
	if date_format is None:
		try:
			time_tuple = time.strptime(date_string,"%m/%d/%Y")
			date_format = "%m/%d/%Y"
		except:
			date_format = None
	if date_format is None:
		try:
			time_tuple = time.strptime(date_string,"%m-%d-%Y")
			date_format = "%m-%d-%Y"
		except:
			date_format = None
	if date_format is None:
		try:
			time_tuple = time.strptime(date_string,"%m/%d/%y")
			date_format = "%m/%d/%y"
		except:
			date_format = None
	if date_format is None:
		try:
			time_tuple = time.strptime(date_string,"%m-%d-%y")
			date_format = "%m-%d-%y"
		except:
			date_format = None
	if date_format is None:
		try:
			time_tuple = time.strptime(date_string,"%Y/%d/%m")
			date_format = "%Y/%d/%m"
		except:
			date_format = None
	if date_format is None:
		try:
			time_tuple = time.strptime(date_string,"%Y-%d-%m")
			date_format = "%Y-%d-%m"
		except:
			date_format = None
	if date_format is None:
		try:
			time_tuple = time.strptime(date_string,"%Y/%m/%d")
			date_format = "%Y/%m/%d"
		except:
			date_format = None
	if date_format is None:
		try:
			time_tuple = time.strptime(date_string,"%Y-%m-%d")
			date_format = "%Y-%m-%d"
		except:
			date_format = None
	if date_format is None:
		try:
			time_tuple = time.strptime(date_string,"%y/%d/%m")
			date_format = "%y/%d/%m"
		except:
			date_format = None
	if date_format is None:
		try:
			time_tuple = time.strptime(date_string,"%y-%d-%m")
			date_format = "%y-%d-%m"
		except:
			date_format = None
	if date_format is None:
		try:
			time_tuple = time.strptime(date_string,"%y/%m/%d")
			date_format = "%y/%m/%d"
		except:
			date_format = None
	if date_format is None:
		try:
			time_tuple = time.strptime(date_string,"%y-%m-%d")
			date_format = "%y-%m-%d"
		except:
			date_format = None
	print "date format",date_format
	if not time_tuple  is None:
		print "time tuple",time_tuple
	# convert to epoch date	
	if time_tuple  is None:
		return date_format
	else:
		EpochSeconds = time.mktime(time_tuple)
		dt = datetime.datetime.fromtimestamp(EpochSeconds)
		return dt,date_format

def import_xlsx(filepath):
	wb=openpyxl.load_workbook(filename=filepath, data_only=True)
	ws = wb.active
	df = list(ws.iter_rows(values_only=True))
	return(df)

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

mydb = "minder"
myHost = "localhost"
myuser = "minder"
mypasswd = "minder"
for i in range( len(sys.argv)):
	inData = sys.argv[i]
	myparms = inData.split("=")
	if "db"  == myparms[0]:
		mydb = myparms[1]
	if "host"  == myparms[0]:
		myHost = myparms[1]
	if "user"  == myparms[0]:
		myuser = myparms[1]
	if "passwd"  == myparms[0]:
		mypasswd = myparms[1]
	if "log"  == myparms[0]:
	    logfile = myparms[1]
	    havelog = 1;
	if "infile"  == myparms[0]:
	    infile = myparms[1]
	    havein = 1;
print "mydb", mydb
logfile = logfile + mydb + ".log"
print "logfile", logfile
#
#redirect stdout and stderr
if (havelog == 1):
	out = open(logfile,'a')
	sys.stdout = out
	sys.stderr = out

rest, ext = os.path.splitext(infile)
path, base = os.path.split(rest)
print "%s %s" % ("path",path)
print "%s %s" % ("base",base)
if base.rfind("(") > -1:
	path2 = base[:base.rfind("(") ]
	base = path2
#wk_dataset = base
wk_dataset = "SSN"

print "%s %s" % ("dataset",wk_dataset)

print "mydb", mydb
print "myhost", myHost
print "myuser", myuser
print "mypassword", mypasswd
con = fdb.connect(
	dsn=myHost+":"+ mydb,
	user=myuser,
	password=mypasswd)

print "connected to db"
cur = con.cursor()
cur2 = con.cursor()
cur3 = con.cursor()
cur5 = con.cursor()
cur6 = con.cursor()

wk_date = time.strftime("%d/%m/%y")
wk_line = 0	
reader = import_xlsx(infile)
# stage 1
wk_line = 0	
wk_current_qty_field = None
wk_ssn_id_field = None
wk_locn_id_field = None
wk_return_id_field = None
wk_grn_date_field = None
wk_current_return_id = None
wk_current_grn_date = None
wk_purchase_price_field = None
wk_other5_field = None
wk_other6_field = None
wk_other7_field = None
wk_other8_field = None
wk_other9_field = None
wk_other10_field = None
wk_other19_field = None
wk_other20_field = None
# read xlsx 
# convert names
#"Type","SSN","WH - Current","WH - Original","Brand","Model","Generic","Other 5","Other 6","Other 7","Other 8","Other 9","Other 10","Other 19","Other 20","ReturnID","Return Name","Intero Site","GRN Date","Label Date","Location","Serial #","Legacy ID","Other18 QTY","Purchase Price","Notes","Asset Grade","Grade","Company ID","File Number","Lot Batch No","Erasure Details","Damages"
#"Type","SSN","WH - Current","WH - Original","Brand","Model","Generic","Other 5","Other 6","Other 7","Other 8","Other 9","Other 10","Other 19","Other 20","ReturnID","Return Name","Intero Site","GRN Date","Label Date","Location","Serial #","Legacy ID","Other18 QTY","Purchase Price","NOTES","Asset Grade (other 19 ???)","Grade","Company ID","File Number","Lot Batch No","Erasure Details (OTHER11)","Damages (should go in NOTES for ease of upload)"
#"SSN_TYPE","SSN_ID","WH_ID","CURRENT_QTY","BRAND","MODEL","GENERIC","OTHER5","OTHER6","OTHER7","OTHER8","OTHER9","OTHER10","OTHER19","OTHER20","OTHER38","OTHER32","OTHER33","OTHER39","LABEL_DATE","LOCN_ID","SERIAL_NUMBER","LEGACY_ID","OTHER18_QTY","PURCHASE_PRICE","NOTES","OTHER34","OTHER35","COMPANY_ID","FILE_NUMBER","LOT_BATCH_NO","OTHER36","OTHER37"
# change "WH - Original" column to be "CURRENT_QTY"
# always set value at "1"
# save as  base + "-1.csv"
# infilestage1 = base + "-1.csv"
infilestage1 = base + "-1.CSV"
infilestage1 = path + "/" + base + "-1.CSV"
infilestage1 = path + os.sep + base + "-1.CSV"
infilestage1file = open(infilestage1,'wb')
writer = csv.writer(infilestage1file,delimiter=',',quotechar='"', quoting=csv.QUOTE_NONNUMERIC)
try:
		# need to escape embedded ' in data
		for line in reader:
			wk_line = wk_line + 1
			#print "line",wk_line,line
			print "line",wk_line,type(line), line
			#print "line",wk_line
			buffer = []
			wk_field_no = -1
			wk_line_ok = True
			for wk_line_field in line:
				wk_field_no = wk_field_no + 1
				#convert unicode to ascii
				if "unicode" == type(wk_line_field):
					wk_line_field = repr(wk_line_field)
				#print type(wk_line_field), wk_line_field
				if wk_line == 1:
					if wk_line_field == "Type":
						wk_line_field = "SSN_TYPE"
						buffer.append( wk_line_field)
					elif wk_line_field == "SSN":
						wk_line_field = "*SSN_ID"
						buffer.append( wk_line_field)
						wk_ssn_id_field = wk_field_no
					elif wk_line_field == "WH - Current":
						wk_line_field = "WH_ID"
						buffer.append( wk_line_field)
					elif wk_line_field == "WH - Original":
						wk_line_field = "CURRENT_QTY"
						buffer.append( wk_line_field)
						wk_current_qty_field = wk_field_no
					elif wk_line_field == "Brand":
						wk_line_field = "BRAND"
						buffer.append( wk_line_field)
					elif wk_line_field == "Model":
						wk_line_field = "MODEL"
						buffer.append( wk_line_field)
					elif wk_line_field == "Generic" :
						wk_line_field = "GENERIC"
						buffer.append( wk_line_field)
					elif wk_line_field == "Other 5" :
						wk_line_field = "OTHER5"
						buffer.append( wk_line_field)
						wk_other5_field = wk_field_no
					elif wk_line_field == "Other 6" :
						wk_line_field = "OTHER6"
						buffer.append( wk_line_field)
						wk_other6_field = wk_field_no
					elif wk_line_field == "Other 7" :
						wk_line_field = "OTHER7"
						buffer.append( wk_line_field)
						wk_other7_field = wk_field_no
					elif wk_line_field == "Other 8" :
						wk_line_field = "OTHER8"
						buffer.append( wk_line_field)
						wk_other8_field = wk_field_no
					elif wk_line_field == "Other 9" :
						wk_line_field = "OTHER9"
						buffer.append( wk_line_field)
						wk_other9_field = wk_field_no
					elif wk_line_field == "Other 10" :
						wk_line_field = "OTHER10"
						buffer.append( wk_line_field)
						wk_other10_field = wk_field_no
					elif wk_line_field == "Other 19" :
						wk_line_field = "OTHER19"
						buffer.append( wk_line_field)
						wk_other19_field = wk_field_no
					elif wk_line_field == "Other 20" :
						wk_line_field = "OTHER20"
						buffer.append( wk_line_field)
						wk_other20_field = wk_field_no
					elif wk_line_field == "ReturnID" :
						wk_line_field = "OTHER38"
						buffer.append( wk_line_field)
						wk_return_id_field = wk_field_no
					elif wk_line_field == "Return Name" :
						wk_line_field = "OTHER32"
						buffer.append( wk_line_field)
					elif wk_line_field == "Intero Site" :
						wk_line_field = "OTHER33"
						buffer.append( wk_line_field)
					elif wk_line_field == "GRN Date" :
						wk_line_field = "OTHER39"
						buffer.append( wk_line_field)
						wk_grn_date_field = wk_field_no
					elif wk_line_field == "Label Date" :
						wk_line_field = "LABEL_DATE"
						buffer.append( wk_line_field)
					elif wk_line_field == "Location" :
						wk_line_field = "LOCN_ID"
						buffer.append( wk_line_field)
						wk_locn_id_field = wk_field_no
					elif wk_line_field == "Serial #" :
						wk_line_field = "SERIAL_NUMBER"
						buffer.append( wk_line_field)
					elif wk_line_field == "Legacy ID" :
						wk_line_field = "LEGACY_ID"
						buffer.append( wk_line_field)
					elif wk_line_field == "Other18 QTY"  :
						wk_line_field = "OTHER18_QTY"
						buffer.append( wk_line_field)
					elif wk_line_field == "Purchase Price"  :
						wk_line_field = "PURCHASE_PRICE"
						buffer.append( wk_line_field)
						wk_purchase_price_field = wk_field_no
					elif wk_line_field == "NOTES (DO WE NEED THIS INFORMATION IN THE NOTES???)" :
						wk_line_field = "NOTES"
						buffer.append( wk_line_field)
					elif wk_line_field == "NOTES" :
						wk_line_field = "NOTES"
						buffer.append( wk_line_field)
					elif wk_line_field == "Asset Grade (other 19 ???)" :
						wk_line_field = "OTHER34"
						buffer.append( wk_line_field)
					elif wk_line_field == "Grade" :
						wk_line_field = "OTHER35"
						buffer.append( wk_line_field)
					elif wk_line_field == "Company ID" :
						wk_line_field = "COMPANY_ID"
						buffer.append( wk_line_field)
					elif wk_line_field == "File Number" :
						wk_line_field = "FILE_NUMBER"
						buffer.append( wk_line_field)
					elif wk_line_field == "Lot Batch No" :
						wk_line_field = "LOT_BATCH_NO"
						buffer.append( wk_line_field)
					elif wk_line_field == "Erasure Details (OTHER11)" :
						wk_line_field = "OTHER36"
						wk_line_field = "OTHER11"
						buffer.append( wk_line_field)
					elif wk_line_field == "Damages (should go in NOTES for ease of upload)" :
						wk_line_field = "OTHER37"
						buffer.append( wk_line_field)
				else:
					# reject any rows with null ssn_id
					if wk_ssn_id_field == wk_field_no:
						if wk_line_field is None:
							wk_line_ok = False
						else:
							wk_line_field = "%d" % wk_line_field
					elif wk_current_qty_field == wk_field_no:
						wk_line_field = "1"
						# set current_qty 1
					elif wk_return_id_field == wk_field_no:
						wk_current_return_id = wk_line_field
					elif wk_grn_date_field == wk_field_no:
						wk_current_grn_date = wk_line_field
					elif wk_locn_id_field == wk_field_no:
						# have preceeding wh_id in locn_id again
						if wk_line_field is None:
							wk_line_ok = False
						else:
							wk_line_field = wk_line_field[2:]
					elif wk_purchase_price_field == wk_field_no:
						# if string data make ""
						if wk_line_field is None:
							wk_purchase_price_ok = True
						else:
							if checkFloat(wk_line_field):
								wk_purchase_price_ok = True
							else:
								wk_line_field = None
								wk_line_field = "0.0"
					elif wk_other5_field == wk_field_no or wk_other6_field == wk_field_no or \
						wk_other7_field == wk_field_no or wk_other8_field == wk_field_no or \
						wk_other9_field == wk_field_no or wk_other10_field == wk_field_no or \
						wk_other19_field == wk_field_no or wk_other20_field == wk_field_no:
						# if  data "" make "UNKNOWN"
						if wk_line_field is None:
							wk_line_field = "UNKNOWN"
						else:
							if wk_line_field == "":
								wk_line_field = "UNKNOWN"
					print  wk_line_field
					buffer.append( wk_line_field)
			if wk_line_ok:
				writer.writerow(buffer)
except Exception as e   :
	exc_type, exc_obj, exc_tb = sys.exc_info()
	sys.exit('file %s,   %s , %s' % (infile, sys.exc_info(), exc_tb.tb_lineno  ))
infilestage1file.close()
#
#sys.exit()
#
# stage 2
#infilestage1file = open(infilestage1,'rb')
# check db constraint fields 

#
#WH_ID
# LOCN_ID 
#BRAND
#SSN_TYPE
# GENERIC
#OTHER5
#OTHER19
#OTHER20
#OTHER6
#OTHER7
#OTHER8
#OTHER9
#OTHER10
# get the unique values used
# then check exists
# if not found add the missing record
wk_line = 0	
wk_is_ok = True
is_wh_missing = []
wk_select_stmt = "select first 1 1 from warehouse where wh_id=?"
wk_wh_log = logfile + "wh.log"
is_wh_missing = check2field(infilestage1, wk_wh_log, ["WH_ID"], [], wk_select_stmt )
sys.stdout = out
sys.stderr = out
if len(is_wh_missing) > 0:
		print "missing warehouse", is_wh_missing
		wk_is_ok = False
is_whlocn_missing = []
wk_select_stmt = "select first 1 1 from location where wh_id=? and locn_id=?"
wk_whlocn_log = logfile + "whlocn.log"
is_whlocn_missing = check2field(infilestage1, wk_whlocn_log, ["WH_ID","LOCN_ID"], [], wk_select_stmt )
sys.stdout = out
sys.stderr = out
if len(is_whlocn_missing) > 0:
		print "missing whlocns", is_whlocn_missing
		wk_is_ok = False
is_brand_missing = []
wk_select_stmt = "select first 1 1 from brand where code=?"
wk_brand_log = logfile + "brand.log"
is_brand_missing = check2field(infilestage1, wk_brand_log, ["BRAND"], [], wk_select_stmt )
sys.stdout = out
sys.stderr = out
if len(is_brand_missing) > 0:
		print "missing brands", is_brand_missing
		wk_is_ok = False
is_ssntype_missing = []
wk_select_stmt = "select first 1 1 from ssn_type where code=?"
wk_ssntype_log = logfile + "ssntype.log"
is_ssntype_missing = check2field(infilestage1, wk_ssntype_log, ["SSN_TYPE"], [], wk_select_stmt )
sys.stdout = out
sys.stderr = out
if len(is_ssntype_missing) > 0:
		print "missing ssntypes", is_ssntype_missing
		wk_is_ok = False
is_generic_missing = []
wk_select_stmt = "select first 1 1 from generic where ssn_type=? and code=?"
wk_generic_log = logfile + "generic.log"
is_generic_missing = check2field(infilestage1, wk_generic_log, ["SSN_TYPE","GENERIC"], [], wk_select_stmt )
sys.stdout = out
sys.stderr = out
if len(is_generic_missing) > 0:
		print "missing generics", is_generic_missing
		wk_is_ok = False
is_other5_missing = []
wk_select_stmt = "select first 1 1 from global_conditions where other_no=? and description=?"
wk_other5_log = logfile + "other5.log"
is_other5_missing = check2field(infilestage1, wk_other5_log, ["OTHER5"], ["5"], wk_select_stmt )
sys.stdout = out
sys.stderr = out
if len(is_other5_missing) > 0:
		print "missing other5s", is_other5_missing
		wk_is_ok = False
is_other19_missing = []
wk_select_stmt = "select first 1 1 from global_conditions where other_no=? and description=?"
wk_other19_log = logfile + "other19.log"
is_other19_missing = check2field(infilestage1, wk_other19_log, ["OTHER19"], ["19"], wk_select_stmt )
sys.stdout = out
sys.stderr = out
if len(is_other19_missing) > 0:
		print "missing other19s", is_other19_missing
		wk_is_ok = False
is_other20_missing = []
wk_select_stmt = "select first 1 1 from global_conditions where other_no=? and description=?"
wk_other20_log = logfile + "other20.log"
is_other20_missing = check2field(infilestage1, wk_other20_log, ["OTHER20"], ["20"], wk_select_stmt )
sys.stdout = out
sys.stderr = out
if len(is_other20_missing) > 0:
		print "missing other20s", is_other20_missing
		wk_is_ok = False
is_other6_missing = []
wk_select_stmt = "select first 1 1 from product_description where field_code=? and type_code=? and description=?"
wk_other6_log = logfile + "other6.log"
is_other6_missing = check2field(infilestage1, wk_other6_log, ["SSN_TYPE", "OTHER6"], ["1"], wk_select_stmt )
sys.stdout = out
sys.stderr = out
if len(is_other6_missing) > 0:
		print "missing other6s", is_other6_missing
		wk_is_ok = False
is_other7_missing = []
wk_select_stmt = "select first 1 1 from product_description where field_code=? and type_code=? and description=?"
wk_other7_log = logfile + "other7.log"
is_other7_missing = check2field(infilestage1, wk_other7_log, ["SSN_TYPE", "OTHER7"], ["2"], wk_select_stmt )
sys.stdout = out
sys.stderr = out
if len(is_other7_missing) > 0:
		print "missing other7s", is_other7_missing
		wk_is_ok = False
is_other8_missing = []
wk_select_stmt = "select first 1 1 from product_description where field_code=? and type_code=? and description=?"
wk_other8_log = logfile + "other8.log"
is_other8_missing = check2field(infilestage1, wk_other8_log, ["SSN_TYPE", "OTHER8"], ["3"], wk_select_stmt )
sys.stdout = out
sys.stderr = out
if len(is_other8_missing) > 0:
		print "missing other8s", is_other8_missing
		wk_is_ok = False
is_other9_missing = []
wk_select_stmt = "select first 1 1 from product_description where field_code=? and type_code=? and description=?"
wk_other9_log = logfile + "other9.log"
is_other9_missing = check2field(infilestage1, wk_other9_log, ["SSN_TYPE", "OTHER9"], ["4"], wk_select_stmt )
sys.stdout = out
sys.stderr = out
if len(is_other9_missing) > 0:
		print "missing other9s", is_other9_missing
		wk_is_ok = False
is_other10_missing = []
wk_select_stmt = "select first 1 1 from product_description where field_code=? and type_code=? and description=?"
wk_other10_log = logfile + "other10.log"
is_other10_missing = check2field(infilestage1, wk_other10_log, ["SSN_TYPE", "OTHER10"], ["5"], wk_select_stmt )
sys.stdout = out
sys.stderr = out
if len(is_other10_missing) > 0:
		print "missing other10s", is_other10_missing
		wk_is_ok = False
#wk_is_ok = False
wk_is_ok = True
#
if not wk_is_ok:
	sys.exit(1)
#
# stage 3
# add missing
if len(is_wh_missing) > 0:
	wk_insert_stmt = "insert into warehouse(description,wh_id) values('UNKNOWN',?)"
	wk_wh_log = logfile + "wh.log"
	add2field(is_wh_missing, wk_wh_log, wk_insert_stmt )
	sys.stdout = out
	sys.stderr = out
if len(is_whlocn_missing) > 0:
	wk_insert_stmt = "insert into location(locn_name,wh_id,locn_id) values('UNKNOWN',?,?)"
	wk_whlocn_log = logfile + "whlocn.log"
	add2field(is_whlocn_missing, wk_whlocn_log, wk_insert_stmt )
	sys.stdout = out
	sys.stderr = out
if len(is_brand_missing) > 0:
	wk_insert_stmt = "insert into brand(description,code) values('UNKNOWN',?)"
	wk_brand_log = logfile + "brand.log"
	add2field(is_brand_missing, wk_brand_log, wk_insert_stmt )
	sys.stdout = out
	sys.stderr = out
if len(is_ssntype_missing) > 0:
	wk_insert_stmt = "insert into ssn_type(description,code) values('UNKNOWN',?)"
	wk_ssntype_log = logfile + "ssntype.log"
	add2field(is_ssntype_missing, wk_ssntype_log, wk_insert_stmt )
	sys.stdout = out
	sys.stderr = out
if len(is_generic_missing) > 0:
	wk_insert_stmt = "insert into generic(description,ssn_type,code) values('UNKNOWN',?,?)"
	wk_generic_log = logfile + "generic.log"
	add2field(is_generic_missing, wk_generic_log, wk_insert_stmt )
	sys.stdout = out
	sys.stderr = out
if len(is_other5_missing) > 0:
	wk_insert_stmt = "insert into global_conditions(other_no,description) values(?,?)"
	wk_other5_log = logfile + "other5.log"
	add2field(is_other5_missing, wk_other5_log, wk_insert_stmt )
	sys.stdout = out
	sys.stderr = out
if len(is_other19_missing) > 0:
	wk_insert_stmt = "insert into global_conditions(other_no,description) values(?,?)"
	wk_other19_log = logfile + "other19.log"
	add2field(is_other19_missing, wk_other19_log, wk_insert_stmt )
	sys.stdout = out
	sys.stderr = out
if len(is_other20_missing) > 0:
	wk_insert_stmt = "insert into global_conditions(other_no,description) values(?,?)"
	wk_other20_log = logfile + "other20.log"
	add2field(is_other20_missing, wk_other20_log, wk_insert_stmt )
	sys.stdout = out
	sys.stderr = out
if len(is_other6_missing) > 0:
	wk_insert_stmt = "insert into product_description(field_code,type_code,description) values(?,?,?)"
	wk_other6_log = logfile + "other6.log"
	add2field(is_other6_missing, wk_other6_log, wk_insert_stmt )
	sys.stdout = out
	sys.stderr = out
if len(is_other7_missing) > 0:
	wk_insert_stmt = "insert into product_description(field_code,type_code,description) values(?,?,?)"
	wk_other7_log = logfile + "other7.log"
	add2field(is_other7_missing, wk_other7_log, wk_insert_stmt )
	sys.stdout = out
	sys.stderr = out
if len(is_other8_missing) > 0:
	wk_insert_stmt = "insert into product_description(field_code,type_code,description) values(?,?,?)"
	wk_other8_log = logfile + "other8.log"
	add2field(is_other8_missing, wk_other8_log, wk_insert_stmt )
	sys.stdout = out
	sys.stderr = out
if len(is_other9_missing) > 0:
	wk_insert_stmt = "insert into product_description(field_code,type_code,description) values(?,?,?)"
	wk_other9_log = logfile + "other9.log"
	add2field(is_other9_missing, wk_other9_log, wk_insert_stmt )
	sys.stdout = out
	sys.stderr = out
if len(is_other10_missing) > 0:
	wk_insert_stmt = "insert into product_description(field_code,type_code,description) values(?,?,?)"
	wk_other10_log = logfile + "other10.log"
	add2field(is_other10_missing, wk_other10_log, wk_insert_stmt )
	sys.stdout = out
	sys.stderr = out
#
# stage 4
# import the ssn.csv created in stage 1 infilestage1
wk_line = 0	
#read std or 1st input parm
#for line in fileinput.input(infile):
reader = csv.reader(open(infilestage1,'rb'),delimiter=',',quotechar='"')
try:
		# need to escape embedded ' in data
		for line in reader:
			wk_line = wk_line + 1
			print "line",wk_line,line
			#wk_code = line
			if wk_line == 1:
				keys = []
				keys_no = []
				fields_nokey = []
				fields_nokey_no = []
				#fields = line.split(',')
				fields = line
				fields_type = []
				fields_name = []
				wk_end = len(fields) -1
				print "wk_end",wk_end
				if wk_end < 1:
					break
		                #fields[wk_end] = fields[wk_end][:-1]
				if fields[wk_end] == '':
					del fields[wk_end]
				wk_insert = "insert into %s (" % (wk_dataset )
				wk_insert_sfx = ""
				wk_select = "select first 1 1 from %s " % (wk_dataset )
				wk_select21 = "select first 1  " 
				wk_select22 = " from %s " % (wk_dataset )
				wk_update = "update %s set " % (wk_dataset )
				wk_where = "where "
				for xindex in range(0,len(fields)):
					wk_str = fields[xindex]
					# want to trim first and last white space
					wk_str = wk_str.strip()
					if wk_str[:1] == '"':
		                                if wk_str[-1:] == '"':
		                                        fields[xindex] = wk_str[1:-1]
		                                else:
		                                        wk_str = wk_str.strip()
		                                        if wk_str[-1:] == '"':
		                                                fields[xindex] = wk_str[1:-1]
					wk_str = fields[xindex]
					# want to trim first and last white space
					wk_str = wk_str.strip()
					if wk_str[:1] == "*":
						print "key"
						keys.append(wk_str[1:])
						keys_no.append(xindex)
						fields[xindex] = wk_str[1:]
		                                wk_where += "%s %s and " % ( wk_str[1:], " = ? ")
					else :
						fields_nokey.append(wk_str)
						fields_nokey_no.append(xindex)
						fields[xindex] = wk_str
		                                #wk_update += "%s %s , " % ( wk_str, " = '%s'")
		                                wk_update += "%s %s , " % ( wk_str, " = ?")
					print xindex,fields[xindex]
					wk_insert += "%s ," % (fields[xindex])
		                        #wk_insert_sfx += "'%s' ,"
		                        wk_insert_sfx += "? ,"
					wk_select21  += "%s ," % (fields[xindex])
				print "keys", str(keys)
				print "keys_no", str(keys_no)
				print "fields_nokey", str(fields_nokey)
				print "fields_nokey_no", str(fields_nokey_no)
				# calc where clause for select and update
				# then calc select statement
				if wk_where == "where ":
					wk_select = ""
				else:	
					wk_select += wk_where[:-4] 
				#print wk_select	
				wk_select2 = wk_select21[:-1] + wk_select22
				if wk_select != "":
					#wk_select_prep = cur.prep(wk_select)
					wk_select_prep = wk_select
				else:
					wk_select_prep = ""
				print "select",wk_select	
				# insert statement
				wk_insert = wk_insert[:-1] + ") values (" + wk_insert_sfx[:-1] + ")"
				print "insert",wk_insert	
				#wk_insert_prep = cur3.prep(wk_insert)
				wk_insert_prep = wk_insert
				# update statement
				wk_update = wk_update[:-2] + wk_where[:-4] 
				if wk_select == "":
					wk_update = wk_update[:-2]  
				print "update",wk_update	
				#wk_update_prep = cur2.prep(wk_update)
				wk_update_prep = wk_update
				print "select2",wk_select2
				#print "here"
		                cur.execute(wk_select2)
				## get data record
				#print "here2"
				data_fields = cur.fetchone()
				data_fields = None
				if data_fields is None:
					print "no select2 record not found "
					record_found = None
					for pos in range(len(fields)):
						if len(fields_type) <= pos:
							fields_type.append( str(fields[pos] ) )
						else:
							fields_type[pos] =  str(fields[pos]) 
				else:
					print "select2 record found "
					#print data_fields
					record_found = data_fields[0]
					for pos in range(len(data_fields)):
						#print "pos no", pos
						#print  "description for pos",str(cur.description[pos] )
						#print  "description_name for pos",str(cur.description[pos][fdb.DESCRIPTION_NAME] )
						#str(cur.description[pos][fdb.DESCRIPTION_NAME])  - the field name
						#print  "DESCRIPTION_TYPE_CODE for pos", str(cur.description[pos][fdb.DESCRIPTION_TYPE_CODE] )
						#print  str(cur.description[pos][fdb.DESCRIPTION_TYPE_CODE])[7:-2] 
						if len(fields_type) <= pos:
							fields_type.append( str(cur.description[pos][fdb.DESCRIPTION_TYPE_CODE])[7:-2] )
						else:
							fields_type[pos] =  str(cur.description[pos][fdb.DESCRIPTION_TYPE_CODE])[7:-2] 
						wk_str =  fields_type[pos]
						wk_str = wk_str.upper()
						fields_type[pos] = wk_str
				#print "fields_type", str(fields_type)
				print "fields_type", fields_type
				wk_no_fields = len(fields)
				print " number of 1st line fields", wk_no_fields
			else:
				#buffer = line.split(',')
				buffer = line
				wk_end = len(buffer) -1
				#print "wk_end",wk_end
				if wk_end < 1:
					break
				print "wk_end",wk_end
				print "line no", wk_line, "expected fields", wk_no_fields, "actual fields", wk_end
				if wk_end >= wk_no_fields:
					del buffer[wk_end]
					print " dropped last field"
		                #buffer[wk_end] = buffer[wk_end][:-1]
                                wk_line_data_ok = False
				for xindex in range(0,len(buffer)):
					#int "index", xindex
					wk_str = buffer[xindex]
                                        if wk_str <> "":
                                            wk_line_data_ok = True
				for xindex in range(0,len(buffer)):
					print "index", xindex
					wk_str = buffer[xindex]
					# want to trim first and last white space
					wk_str = wk_str.strip()
					if wk_str[:1] == '"':
						buffer[xindex] = wk_str[1:-1]
					wk_str = buffer[xindex]
					wk_str = wk_str.strip()
					buffer[xindex] = wk_str
					print "value", wk_str
					try:
						#print  fields_type[xindex] 
						if "DATE" in  fields_type[xindex]   or "TIME" in fields_type[xindex]  :
							# a date  or time or datetime or timestamp
							wk_field_none = False
							if buffer[xindex] == "":
								#buffer[xindex] = "1900-01-01 00:00:00"
								buffer[xindex] = None
								wk_field_none = True
							if buffer[xindex] == "1900-01-01 00:00:00":
								buffer[xindex] = None
								wk_field_none = True
							if wk_field_none == False:
								wk_date = checkDate(wk_str)
								if wk_date is None:
									print "got a none date"
									wk = wk_str.split(".")
									if len(wk) > 1:
										print "len wk gt 1"
										date_string = wk[0]
										print date_string
										buffer[xindex] = date_string
								else:
									if len(wk_date) > 1:
										buffer[xindex] = wk_date[0]
										print "got a date",buffer[xindex]
							else:
								print "got a null date",wk_str
						if "INT" in fields_type[xindex] or "FLOAT" in fields_type[xindex] or "DOUBLE" in fields_type[xindex] or "NUMERIC" in fields_type[xindex] or "DECIMAL" in fields_type[xindex]  :
							wk_field_none = False
							if buffer[xindex] == "":
								#buffer[xindex] = 0
								buffer[xindex] = None
								wk_field_none = True
							#if buffer[xindex] == "0":
							#	buffer[xindex] = None
							#	wk_field_none = True
							if buffer[xindex] == "0.0":
								buffer[xindex] = None
								wk_field_none = True
							if wk_field_none == True:
								print "got a null numeric",wk_str
						if "STR" in fields_type[xindex]:
							wk_field_none = False
							if buffer[xindex] == "":
								buffer[xindex] = None
								wk_field_none = True
							if wk_field_none == True:
								print "got a null char",wk_str
						if "BUFFER" in fields_type[xindex]:
							wk_field_none = False
							if buffer[xindex] == "":
								buffer[xindex] = None
								wk_field_none = True
							if wk_field_none == True:
								print "got a null blob",wk_str
					except:
						print "index not found in fields type array"
					print "x",xindex,buffer[xindex]
				# calc select , update and insert statements
				wk_keys_data = []
				wk_nokeys_data = []
				for windex in range(0,len(keys_no)):
					wk_keys_data.append(buffer[keys_no[windex]])
				for windex in range(0,len(fields_nokey_no)):
					wk_nokeys_data.append(buffer[fields_nokey_no[windex]])
                                if wk_line_data_ok == True:
			                #wk_select_stmt = wk_select % tuple(wk_keys_data)
			                #wk_select_stmt = wk_select
			                wk_select_stmt = wk_select_prep
					print wk_select_stmt	
					if len(wk_nokeys_data) > 0:
			                        #wk_update_stmt = wk_update % tuple(wk_nokeys_data + wk_keys_data)
			                        #wk_update_stmt = wk_update
			                        wk_update_stmt = wk_update_prep
					else:
						wk_update_stmt = ""
					#print wk_update_stmt	
			                #wk_insert_stmt = wk_insert % tuple(buffer)
			                #wk_insert_stmt = wk_insert
			                wk_insert_stmt = wk_insert_prep
					#print wk_insert_stmt	
			
			
					# perform select
					# if record found do the update
					# else do the insert
				
					if wk_select_stmt > "":			
			                        #cur.execute(wk_select_stmt)
			                        cur.execute(wk_select_stmt, tuple(wk_keys_data))
						
						## get data record
						data_fields = cur.fetchone()
					else:
						data_fields = None
						
					#print data_fields
					if data_fields is None:
						print "record not found select "
						record_found = None
					else:
						print "record found select "
						#print data_fields
						record_found = data_fields[0]
					if record_found == 1 :
						print wk_update_stmt	
						query4 = wk_update_stmt
			                        if query4 <> "":
			                                cur2.execute(query4, tuple(wk_nokeys_data + wk_keys_data))
					else :
						print wk_insert_stmt	
						query4 = wk_insert_stmt
			                        if query4 <> "":
			                                cur3.execute(query4, tuple(buffer))        
			
			                #print query4 
			                #if query4 <> "":
			                #        cur.execute(query4 )
except Exception as e   :
	exc_type, exc_obj, exc_tb = sys.exc_info()
	sys.exit('file %s,   %s , %s' % (infile, sys.exc_info(), exc_tb.tb_lineno  ))
#except   :
#	sys.exit('file %s,   %s' % (infile, sys.exc_info() ))
			

#sys.exit()
con.commit()
# stage 5
# run procedure add_issn_from_ssn using ssn's from stage 1 file
wk_line = 0	
wk_update_stmt = "execute procedure add_issn_from_ssn(?)"
query4 = wk_update_stmt
reader = csv.reader(open(infilestage1,'rb'),delimiter=',',quotechar='"')
try:
		# need to escape embedded ' in data
		for line in reader:
			wk_line = wk_line + 1
			print "line",wk_line,line
			#wk_code = line
			if wk_line > 1:
				wk_ssn_id = line[wk_ssn_id_field]
				#print wk_ssn_id 
				#cur5.execute(query4, tuple(line[wk_ssn_id_field]))
				#cur5.execute(query4, (line[wk_ssn_id_field]))
				cur5.execute(query4, (wk_ssn_id,))
except Exception as e   :
	exc_type, exc_obj, exc_tb = sys.exc_info()
	sys.exit('file %s,   %s , %s' % (infile, sys.exc_info(), exc_tb.tb_lineno  ))
			

con.commit()
#
# stage 6
# create grn for the ssn's
# been asked to re-use grn '126922' with supplier 'IRN001'
wk_my_grn = '126922'
wk_my_supplier = 'IRN001'
#
# stage 7
# update the ssns populating supplier_id and grn
wk_line = 0	
wk_update_stmt = "update ssn set grn = ?, supplier_id = ? where ssn_id = ?"
query4 = wk_update_stmt
reader = csv.reader(open(infilestage1,'rb'),delimiter=',',quotechar='"')
try:
		# need to escape embedded ' in data
		for line in reader:
			wk_line = wk_line + 1
			print "line",wk_line,line
			#wk_code = line
			if wk_line > 1:
				wk_ssn_id = line[wk_ssn_id_field]
				print wk_ssn_id 
				#cur6.execute(query4, tuple(wk_my_grn, wk_my_supplier , line[wk_ssn_id_field]))
				#cur6.execute(query4, (wk_my_grn, wk_my_supplier , line[wk_ssn_id_field]))
				cur6.execute(query4, (wk_my_grn, wk_my_supplier , wk_ssn_id))
except Exception as e   :
	exc_type, exc_obj, exc_tb = sys.exc_info()
	sys.exit('file %s,   %s , %s' % (infile, sys.exc_info(), exc_tb.tb_lineno  ))
			

con.commit()
#
# stage 8
# update the grn to use the last return_id and grn_date from csv in stage 1
#
wk_line = 0	
wk_update_stmt = "update grn set return_id = ?, grn_date = ? where grn = ?"
query4 = wk_update_stmt
try:
	cur6.execute(query4, (wk_current_return_id, wk_current_grn_date , wk_my_grn))
except Exception as e   :
	exc_type, exc_obj, exc_tb = sys.exc_info()
	sys.exit('file %s,   %s , %s' % (infile, sys.exc_info(), exc_tb.tb_lineno  ))
			
con.commit()
con.close()

print "end - of datafile"


#revert stdin stdout and stderr
if (havelog == 1):
	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__
	out.close()

###
