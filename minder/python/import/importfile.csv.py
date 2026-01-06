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

import fdb
#import mx.DateTime
#import fdb;fdb.init(type_conv=200)
#fdb.init(type_conv=200)

import datetime 
import csv 

#redirect stdout and stderr

def importCsv(input, delimiter = ',', textQualifier = '"' ):
	wk_line = 0
	wk_started = "F"
	wk_start = 0
	wk_fielded = "F"
	wk_save_buffer = list()
	wk_save_xindex = 0
	fields = list()
	newdata = list()
	for line in input:
		wk_line = wk_line + 1
		#print "original line",wk_line, line
		#wk_code = line
		#if wk_line == 1:
		#	print "1st line" 
		#	print "dont want this"
		#else:
		if wk_line > 0:
			# if line ends \r want the \r out
			#if line[-1:] == chr(10):
			#	#1st field starts quote
			#	print "line ends \r"
			#	line = line[:-1]
			#line = line.strip()
			if len(wk_save_buffer) > 0:
				#buffer2 = line.split(',')
				buffer2 = line.split(delimiter)
				buffer = wk_save_buffer + buffer2
			else:
				#buffer = line.split(',')
				buffer = line.split(delimiter)
			#print "buffer",buffer
			#for xindex in range(0,len(buffer)):
			for xindex in range(wk_save_xindex,len(buffer)):
				#print xindex
				wk_str = buffer[xindex]
				#print wk_str
				wk_fielded = "F"
				# want to trim first and last white space
				wk_str = wk_str.strip()
				#if wk_str[:1] == '"' and wk_str[-1] == '"':
				if wk_str[:1] == textQualifier  and wk_str[-1] == textQualifier :
					#field starts and ends quote
					#print "field starts and ends quote"
					buffer[xindex] = wk_str[1:-1]
					wk_str = buffer[xindex]
					wk_started = "F"
					fields.append(wk_str)
					wk_fielded = "T"
				#if wk_str[:1] == '"' and wk_fielded == "F" :
				if wk_str[:1] == textQualifier  and wk_fielded == "F" :
					#field starts quote
					#print "field starts quote"
					buffer[xindex] = wk_str[1:]
					wk_str = buffer[xindex]
					wk_started = "T"
					#print "started T"
					wk_start = xindex
				#if wk_str[-1:] == '"' and wk_fielded == "F" :
				if wk_str[-1:] == textQualifier  and wk_fielded == "F" :
					#field ends quote
					#print "field ends quote"
					#print "y",xindex,buffer[xindex]
					buffer[xindex] = wk_str[:-1]
					#print "y",xindex,buffer[xindex]
					wk_str = buffer[xindex]
					# must deal with started T here
					if wk_started == "T":
						# must concat fields
						# from wk_start to xindex
						# into one field
						wk_bufstr = ""
						for yindex in range(wk_start, xindex+1):
							#print yindex
							#wk_bufstr = wk_bufstr + buffer[yindex] + ","
							wk_bufstr = wk_bufstr + buffer[yindex] + delimiter
							#print "bufstr",wk_start,xindex,wk_bufstr
						# remove last comma
						if len(wk_bufstr) > 0:
							wk_bufstr = wk_bufstr[:-1]
							#print "bufstr",wk_start,xindex,wk_bufstr
						fields.append(wk_bufstr)
						wk_fielded = "T"
						#print "y",wk_start,buffer[wk_start]
					wk_started = "F"
				if wk_fielded == "F" and wk_started == "F" :
					#field not in list and not added
					fields.append(wk_str)
					wk_fielded = "T"
				#print "x",xindex,buffer[xindex]
		 		#print fields
			# must save unadded fields from buffer to add to the next record
			# and adjust the wk_start from index
			# and adjust the xindex  process index 
			if wk_fielded == "F":
				# save the buffer
				wk_save_buffer = buffer
				# and the xindex
				wk_save_xindex = len(buffer)
			else:
				wk_save_xindex = 0
				# slash the single quotes
				# should be done by db module
				newdata.append(fields)
				fields = list()
				wk_save_buffer = list()
	return newdata 


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
print "%s %s" % ("base",base)
if base.rfind("(") > -1:
	path2 = base[:base.rfind("(") ]
	base = path2
wk_dataset = base

print "%s %s" % ("dataset",wk_dataset)

#                dsn="203.7.14.45/3051:bhp2",
#		dsn="127.0.0.1:/data/asset.rf/wh.v39.gdb",
#		dsn="127.0.0.1:minder",

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
#for line in fileinput.input(infile):
#reader = importCsv(open(infile,'rb'), delimiter = ',', textQualifier = '"' )
reader = csv.reader(open(infile,'rb'),delimiter=',',quotechar='"')
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
		                                #wk_where += "%s %s and " % ( wk_str[1:], " = '%s'")
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
		                cur.execute(wk_select2)
				## get data record
				data_fields = cur.fetchone()
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
						print  fields_type[xindex] 
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
							print buffer
			                                cur3.execute(query4, tuple(buffer))        
			
			                #print query4 
			                #if query4 <> "":
			                #        cur.execute(query4 )
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

###
