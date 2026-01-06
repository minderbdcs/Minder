#!/usr/bin/env python2
"""
<title>
importfile.py, Version 16.06.04
</title>
<long>
Creates/Updates tables in the database
<br>
the input file holds <tt>wh_id</tt><tt>location</tt>
<br>
Work on the first sheet.
The first record holds the columns to insert/update
Have function check2field(inCSVfile, logfile, field_headers, select_stmt )
Parameters: <tt>input Csv file</tt></tt>log file</tt>
            <tt>field headers</tt></tt>fixed prefixes</tt></tt>select_stmt</tt>
            field_headers is a list of field names to use in select
            prefixs is a list of prefixes to prefix to paramters in each row
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
#import openpyxl 

#redirect stdout and stderr

#
# check db constraints met for passed query and csv file
def check2field(inCSVfile, logfile, field_headers, field_prefixs, select_stmt ):
	is_ok = []
	
	havein = 1;
	havelog = 1;
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
	print "mydb", mydb
	logfile = logfile + "check.log"
	print "logfile", logfile
	#
	#redirect stdout and stderr
	if (havelog == 1):
		out = open(logfile,'a')
		sys.stdout = out
		sys.stderr = out
	
	rest, ext = os.path.splitext(inCSVfile)
	path, base = os.path.split(rest)
	print "%s %s" % ("base",base)
	#if base.rfind("(") > -1:
	#	path2 = base[:base.rfind("(") ]
	#	base = path2
	#wk_dataset = base
	
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
	
	wk_date = time.strftime("%d/%m/%y")
	wk_line = 0	
	#read std or 1st input parm
	fields_brand = []
	wk_brand_posn = []
	reader = csv.reader(open(inCSVfile,'rb'),delimiter=',',quotechar='"')
	try:
			# need to escape embedded ' in data
			for line in reader:
				wk_line = wk_line + 1
				print "line",wk_line,line
				#wk_code = line
				if wk_line == 1:
					keys = []
					fields = line
					fields_name = []
					wk_end = len(fields) -1
					print "wk_end",wk_end
					wk_no_fields = len(fields)
					##wk_brand_posn = fields.index("BRAND")
					#wk_brand_posn = fields.index(field_header)
					#print "brand posn", wk_brand_posn
					for field_header in field_headers:
						wk_brand_posn.append(fields.index(field_header))
						print field_header,"posn", wk_brand_posn[-1]
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
					wk_line_data_ok = False
					#for xindex in range(0,len(buffer)):
					for xindex in wk_brand_posn:
						#int "index", xindex
						wk_str = buffer[xindex]
						if wk_str <> "":
							wk_line_data_ok = True
							print "not ''", wk_str
						else:
							wk_line_data_ok = False
							print "is ''", wk_str
					wk_line_list = []
					for xindex in wk_brand_posn:
						#print "index", xindex
						wk_str = buffer[xindex]
						# want to trim first and last white space
						wk_str = wk_str.strip()
						#if xindex==wk_brand_posn:
						#    print "xindex 5",xindex,buffer[xindex]
						#    print "was"+':"'+wk_str+'"'
						#    if wk_str not in fields_brand:
						#		fields_brand.append(wk_str)
						#		print "added", wk_str
						print "xindex ",xindex,buffer[xindex]
						print "was"+':"'+wk_str+'"'
						wk_line_list.append(wk_str)
					if wk_line_data_ok: 
						if wk_line_list not in fields_brand:
							fields_brand.append(wk_line_list)
							print "added", wk_line_list
	except Exception as e   :
		exc_type, exc_obj, exc_tb = sys.exc_info()
		sys.exit('file %s,   %s , %s' % (inCSVfile, sys.exc_info(), exc_tb.tb_lineno  ))
				
	
	#print "brands:"
	#print fields_brand
	print "brands:"
	fields_brand.sort()
	print fields_brand
	
	#wk_select_stmt = "select first 1 1 from brand where code=?";
	try:
		for wk_brandcode in fields_brand:
			if select_stmt > "":			
				#cur.execute(select_stmt, tuple(wk_brandcode))
				#cur.execute(select_stmt, (wk_brandcode))
				cur.execute(select_stmt, tuple(field_prefixs + wk_brandcode))
				
				## get data record
				data_fields = cur.fetchone()
			else:
				data_fields = None
				
			#print data_fields
			if data_fields is None:
				print "record not found select ", field_prefixs, wk_brandcode
				record_found = None
			else:
				#print "record found select " 
				#print data_fields
				record_found = data_fields[0]
			if record_found == 1 :
				#print "code found" 	
				wk_dummy = 0
			else :
				print "code not found", field_prefixs, wk_brandcode	
				is_ok.append(field_prefixs + wk_brandcode)
	except   :
		sys.exit('file %s,   %s' % (infile, sys.exc_info() ))
				
	
	con.commit()
	con.close()
	
	print "end - of datafile"
	#revert stdin stdout and stderr
	if (havelog == 1):
		sys.stdout = sys.__stdout__
		sys.stderr = sys.__stderr__
		out.close()
	return is_ok

#
# add missing entrys for passed list and insert statement
def add2field(fields_add, logfile, insert_stmt ):
	havelog = 1;
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
	print "mydb", mydb
	logfile = logfile + "missing.log"
	print "logfile", logfile
	#
	#redirect stdout and stderr
	if (havelog == 1):
		out = open(logfile,'a')
		sys.stdout = out
		sys.stderr = out
	
	#rest, ext = os.path.splitext(inCSVfile)
	#path, base = os.path.split(rest)
	#print "%s %s" % ("base",base)
	#if base.rfind("(") > -1:
	#	path2 = base[:base.rfind("(") ]
	#	base = path2
	#wk_dataset = base
	
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
	
	wk_date = time.strftime("%d/%m/%y")
	wk_line = 0	
	#read std or 1st input parm
	
	#print "brands:"
	#print fields_add
	
	#wk_select_stmt = "select first 1 1 from brand where code=?";
	try:
		for wk_brandcode in fields_add:
			if insert_stmt > "":			
				cur.execute(insert_stmt, tuple(wk_brandcode))
				
	except Exception as e   :
		exc_type, exc_obj, exc_tb = sys.exc_info()
		#sys.exit('file %s,   %s' % (infile, sys.exc_info() ))
		sys.exit('error    %s , %s' % (sys.exc_info(), exc_tb.tb_lineno  ))
				
	
	con.commit()
	con.close()
	
	print "end - of datafile"
	#revert stdin stdout and stderr
	if (havelog == 1):
		sys.stdout = sys.__stdout__
		sys.stderr = sys.__stderr__
		out.close()

###
