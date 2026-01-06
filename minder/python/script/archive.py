#!/usr/bin/env python
"""
<title>
archivetrans.py, Version 10.08.05
</title>
<long>
Creates/Updates tables in the database

archive method is 'DT' a date based archival using the archival field
                  'AR' take all - eg the log table
                  'DQ' using a date field based anded with a query in the archive query 
archive status is 'AR' for do the archive
                  'RM' for removing the older data
                  'CM' for a commented out line
                  'IN' for the installation removal of records in the dbs 
                       this would clear records from tables that are DT 
                       or DQ methods that need to be empty for the following days
                  'TS' for testing the selection of records  no updates occur 
                  'AT' for archiving the archive_table to archive_to_table in the same db uses date operator ">"
                  'TA' for archiving the archive_table to archive_to_table in the archive db
                  'FA' for archiving the archive_table in the archive database to archive_to_table in the live db
                  'AD' for archiving the archive_table to archive_to_table in the same db but use "<" date operator

archive field is the field in the table to compare for the (date based) archiving

archive days is the number of days to keep in the date query

archive query is the query to and with the date querys when date is not enough
                     to reduce the dataset
archive_to_table is the table to move to for 'AT' status

02/12/07 - I see that using date only in the selection is not unique enough
	PICK_ITEM_DETAIL
	PICK_ITEM
	by create date 
	ISSN by into_date

TABLE ARCHIVE_LAYOUT (
        SEQUENCE INTEGER DEFAULT 1,
        ARCHIVE_TABLE CODE NOT NULL,
        ARCHIVE_FIELD CODE,
        ARCHIVE_METHOD STATUS,
        ARCHIVE_STATUS STATUS,
        ARCHIVE_QUERY VARCHAR(1024),
        ARCHIVE_DAYS INTEGER,
	ARCHIVE_TO_TABLE CODE

 read the archive layout order by sequence
 where the status is 'OP'

10/01/08 - I see that some of the date archives do not remove the older data 
           only copying the data

28/02/08 - Add the 'TS' status - Test
24/05/12 - Add the 'AT' status - Move to different dataset in the same db with date diff > days
11/07/12 - Add the 'TA' status - Move to different dataset in archive db with date diff > days
31/01/14 - Add the 'FA' status - Restore from archive database to a different table so with date < days
14/07/16 - Add the 'AD' status move from one dataset to another dataset in same db and date diff < days
           is the same as AT status except the date compare is the reverse
           is for restoring data from an AT dataset
22/07/16 - Change to use firebirds internal function dateadd for date compares
            rather than using diffdate(,,4)
           so take out zerotime as well
           select dateadd(day,-60,timestamp 'NOW') from control;
07/10/16 - use fdb and pass parameters for db's to use

<br>
Parameters: <tt>log file</tt>
<br>
<br>
</long>
"""
import sys
import string
import time , os ,glob
import fileinput

import fdb
#import fdb;fdb.init(type_conv=200)
#import mx.DateTime
from datetime import datetime

#redirect stdout and stderr

if os.name == 'nt':
	logfile = "d:/tmp/archive."
else:
	logfile = "/tmp/archive."
havelog = 1

mydb = "minder"
myarcdb = "archive"
myHost = "localhost"
myuser = "minder"
mypasswd = "minder"
for i in range( len(sys.argv)):
	inData = sys.argv[i]
	myparms = inData.split("=")
	if "db"  == myparms[0]:
		mydb = myparms[1]
	if "archivedb"  == myparms[0]:
		myarcdb = myparms[1]
	if "host"  == myparms[0]:
		myHost = myparms[1]
	if "user"  == myparms[0]:
		myuser = myparms[1]
	if "passwd"  == myparms[0]:
		mypasswd = myparms[1]
	if "tmp"  == myparms[0]:
		logfile  = myparms[1] + "/archive."
print "mydb", mydb
logfile = logfile + mydb + ".log"
print "logfile", logfile
print time.asctime()
wk_now = datetime.now()
wk_date = wk_now.strftime("%d/%m/%y %H:%M:%S.%f")
print wk_date
#
#redirect stdout and stderr
if (havelog == 1):
	#out = open(logfile,'w')
	out = open(logfile,'a')
	sys.stdout = out
	sys.stderr = out

print "mydb", mydb
print "myarcdb", myarcdb
print "myhost", myHost
print "myuser", myuser
print "mypassword", mypasswd
con = fdb.connect(
	dsn=myHost+":"+ mydb,
	user=myuser,
	password=mypasswd)
conarc = fdb.connect(
	dsn=myHost+":"+ myarcdb,
	user=myuser,
	password=mypasswd)

print "connected to db"
# read archive options
cur = con.cursor()
# read archive record 
cur2 = con.cursor()
# delete older records
cur3 = con.cursor()
curarc = conarc.cursor()

wk_date = time.strftime("%d/%m/%y")
wk_line = 0	

#mytime = mx.DateTime.now()
#mydate = mx.DateTime.today()

def arc_query(wk_arc_table , wk_arc_field = "", wk_arc_days = 0, wk_arc_andquery = "", wk_query_only = "N", wk_arc_table_to=""):
	" calculate the query for the table "	
	global cur, cur2, cur3, curarc
	wk_arc_query = "select * from %s" % wk_arc_table
	if (wk_arc_field != ""):
		# must use the date for the select
		#wk_arc_query = wk_arc_query + " WHERE (" + wk_arc_field + " IS NOT NULL) AND (DIFFDATE(ZEROTIME(" + wk_arc_field + "),'TODAY',4) > " + str(wk_arc_days) + ")"
		wk_arc_query = wk_arc_query + " WHERE (" + wk_arc_field + " IS NOT NULL) AND (DATEADD(DAY,-" + str(wk_arc_days) + ",TIMESTAMP 'TODAY') > " + wk_arc_field + ")"
	if (wk_arc_andquery != ""):
		# must use the query in the select
		wk_arc_query = wk_arc_query + " AND (" + wk_arc_andquery + ")"
	# now must start getting the data
	wk_1st = "T";
	cur2.execute(wk_arc_query)
	# get data record
	if (wk_arc_table_to == ""):
		wk_add = "INSERT INTO %s (" % wk_arc_table
	else:
		wk_add = "INSERT INTO %s (" % wk_arc_table_to
	wk_fields = " VALUES ("
	#wk_del = "DELETE FROM %s WHERE " % wk_arc_table
	for fieldDesc in cur2.description:
		wk_add = wk_add + str(fieldDesc[fdb.DESCRIPTION_NAME]) + ","
		wk_fields = wk_fields + "?,"
		#wk_del = wk_del + str(fieldDesc[fdb.DESCRIPTION_NAME]) + " = ? AND "
	wk_fields = wk_fields[:-1] + ")"
	wk_add = wk_add[:-1] + ")" + wk_fields
	#wk_del = wk_del[:-5] 
	print wk_add
	#print wk_del
	data_fields2 = cur2.fetchone()
	#print data_fields
	if data_fields2 is None:
		print "no record to archive"
	else:
		while not data_fields2 is None:
			#print data_fields2
			# construct the add
			print data_fields2
			if wk_query_only == "N":
				curarc.execute(wk_add, data_fields2)
				# construct the delete
				#cur3.execute(wk_del, data_fields2)
			#end of getting record
			data_fields2 = cur2.fetchone()
	return wk_arc_query

def del_query(wk_arc_table , wk_arc_field = "", wk_arc_days = 0, wk_arc_andquery = "", wk_arc_db = ""):
	" calculate the query for the  removal of records from the table "	
	global cur, cur2, cur3, curarc 
	wk_arc_query = "delete from %s" % wk_arc_table
	if (wk_arc_field != ""):
		if (wk_arc_db == ""):
			#wk_arc_query = wk_arc_query + " WHERE (" + wk_arc_field + " IS NOT NULL) AND (DIFFDATE(ZEROTIME(" + wk_arc_field + "),'TODAY',4) > " + str(wk_arc_days) + ")"
		        wk_arc_query = wk_arc_query + " WHERE (" + wk_arc_field + " IS NOT NULL) AND (DATEADD(DAY,-" + str(wk_arc_days) + ",TIMESTAMP 'TODAY') > " + wk_arc_field + ")"
		elif (wk_arc_db == "AR"):
			#wk_arc_query = wk_arc_query + " WHERE (" + wk_arc_field + " IS NOT NULL) AND (DIFFDATE(ZEROTIME(" + wk_arc_field + "),'TODAY',4) <= " + str(wk_arc_days) + ")"
		        wk_arc_query = wk_arc_query + " WHERE (" + wk_arc_field + " IS NOT NULL) AND (DATEADD(DAY,-" + str(wk_arc_days) + ",TIMESTAMP 'TODAY') <= " + wk_arc_field + ")"
	if (wk_arc_andquery != ""):
		wk_arc_query = wk_arc_query + " AND (" + wk_arc_andquery + ")"
	print wk_arc_query
	if (wk_arc_db == ""):
		cur3.execute(wk_arc_query)
	elif (wk_arc_db == "AR"):
		curarc.execute(wk_arc_query)
	return wk_arc_query


def arc_query_same_db(wk_arc_table , wk_arc_field = "", wk_arc_days = 0, wk_arc_andquery = "", wk_query_only = "N", wk_arc_table_to="", wk_date_direction = ">"):
	" calculate the query for the table "	
	global cur, cur2, cur3, curarc
	wk_arc_query = "select * from %s" % wk_arc_table
	if (wk_arc_field != ""):
		# must use the date for the select
                if (wk_date_direction == ">"):
		    #wk_arc_query = wk_arc_query + " WHERE (" + wk_arc_field + " IS NOT NULL) AND (DIFFDATE(ZEROTIME(" + wk_arc_field + "),'TODAY',4) > " + str(wk_arc_days) + ")"
		    wk_arc_query = wk_arc_query + " WHERE (" + wk_arc_field + " IS NOT NULL) AND (DATEADD(DAY,-" + str(wk_arc_days) + ",TIMESTAMP 'TODAY') > " + wk_arc_field + ")"
                else:
		    #wk_arc_query = wk_arc_query + " WHERE (" + wk_arc_field + " IS NOT NULL) AND (DIFFDATE(ZEROTIME(" + wk_arc_field + "),'TODAY',4) < " + str(wk_arc_days) + ")"
		    wk_arc_query = wk_arc_query + " WHERE (" + wk_arc_field + " IS NOT NULL) AND (DATEADD(DAY,-" + str(wk_arc_days) + ",TIMESTAMP 'TODAY') <= " + wk_arc_field + ")"
	if (wk_arc_andquery != ""):
		# must use the query in the select
		wk_arc_query = wk_arc_query + " AND (" + wk_arc_andquery + ")"
	# now must start getting the data
	wk_1st = "T";
	cur2.execute(wk_arc_query)
	# get data record
	#wk_add = "INSERT INTO %s (" % wk_arc_table
	wk_add = "INSERT INTO %s (" % wk_arc_table_to
	wk_fields = " VALUES ("
	#wk_del = "DELETE FROM %s WHERE " % wk_arc_table
	for fieldDesc in cur2.description:
		wk_add = wk_add + str(fieldDesc[fdb.DESCRIPTION_NAME]) + ","
		wk_fields = wk_fields + "?,"
		#wk_del = wk_del + str(fieldDesc[fdb.DESCRIPTION_NAME]) + " = ? AND "
	wk_fields = wk_fields[:-1] + ")"
	wk_add = wk_add[:-1] + ")" + wk_fields
	#wk_del = wk_del[:-5] 
	print wk_add
	#print wk_del
	data_fields2 = cur2.fetchone()
	#print data_fields
	if data_fields2 is None:
		print "no record to archive"
	else:
		while not data_fields2 is None:
			#print data_fields2
			# construct the add
			print data_fields2
			if wk_query_only == "N":
				cur3.execute(wk_add, data_fields2)
				# construct the delete
				#cur3.execute(wk_del, data_fields2)
			#end of getting record
			data_fields2 = cur2.fetchone()
	return wk_arc_query

def arc_query_archive_db(wk_arc_table , wk_arc_field = "", wk_arc_days = 0, wk_arc_andquery = "", wk_query_only = "N", wk_arc_table_to=""):
	" calculate the query for the table "	
	global cur, cur2, cur3, curarc
	wk_arc_query = "select * from %s" % wk_arc_table
	if (wk_arc_field != ""):
		# must use the date for the select
		#wk_arc_query = wk_arc_query + " WHERE (" + wk_arc_field + " IS NOT NULL) AND (DIFFDATE(ZEROTIME(" + wk_arc_field + "),'TODAY',4) < " + str(wk_arc_days) + ")"
		wk_arc_query = wk_arc_query + " WHERE (" + wk_arc_field + " IS NOT NULL) AND (DATEADD(DAY,-" + str(wk_arc_days) + ",TIMESTAMP 'TODAY') <= " + wk_arc_field + ")"
	if (wk_arc_andquery != ""):
		# must use the query in the select
		wk_arc_query = wk_arc_query + " AND (" + wk_arc_andquery + ")"
	# now must start getting the data
	wk_1st = "T";
	curarc.execute(wk_arc_query)
	# get data record
	if (wk_arc_table_to == ""):
		wk_add = "INSERT INTO %s (" % wk_arc_table
	else:
		wk_add = "INSERT INTO %s (" % wk_arc_table_to
	wk_fields = " VALUES ("
	#wk_del = "DELETE FROM %s WHERE " % wk_arc_table
	for fieldDesc in curarc.description:
		wk_add = wk_add + str(fieldDesc[fdb.DESCRIPTION_NAME]) + ","
		wk_fields = wk_fields + "?,"
		#wk_del = wk_del + str(fieldDesc[fdb.DESCRIPTION_NAME]) + " = ? AND "
	wk_fields = wk_fields[:-1] + ")"
	wk_add = wk_add[:-1] + ")" + wk_fields
	#wk_del = wk_del[:-5] 
	print wk_add
	#print wk_del
	data_fields2 = curarc.fetchone()
	#print data_fields
	if data_fields2 is None:
		print "no record to archive"
	else:
		while not data_fields2 is None:
			#print data_fields2
			# construct the add
			print data_fields2
			if wk_query_only == "N":
				cur2.execute(wk_add, data_fields2)
				# construct the delete
				#cur3.execute(wk_del, data_fields2)
			#end of getting record
			data_fields2 = curarc.fetchone()
	return wk_arc_query


print "getting open archivals"
query3 = """select a.archive_table, 
            a.archive_field, 
            a.archive_method, 
            a.archive_query, 
            a.archive_days,
            a.archive_status,
            a.archive_to_table 
	from archive_layout a
	where a.archive_status in ('AR','RM','IN','TS','AT','TA','FA','AD')
        order by a.sequence """

cur.execute(query3)
		
# get data record
data_fields = cur.fetchone()
#print data_fields
if data_fields is None:
	print "no archive to perform"
else:
	while not data_fields is None:
		#print data_fields
		if data_fields[0] is None:
			wk_table = ""
			print "Table is null"
		else:
			wk_table = data_fields[0]
		if data_fields[1] is None:
			wk_field = ""
		else:
			wk_field = data_fields[1]
		if data_fields[2] is None:
			wk_method = ""
			print "Method is null"
		else:
			wk_method = data_fields[2]
		if data_fields[3] is None:
			wk_query = ""
		else:
			wk_query = data_fields[3]
		if data_fields[4] is None:
			wk_days = 0
		else:
			wk_days = int(data_fields[4])
		if data_fields[5] is None:
			wk_status = ""
		else:
			wk_status = data_fields[5]
		if data_fields[6] is None:
			wk_to_table = ""
		else:
			wk_to_table = data_fields[6]
		#print "method %s" % wk_method
		# now first do the take all
		if (wk_method == 'AR'):
			if (wk_table == ''):
				print "Table is empty"
			else:
				#print "status %s" % wk_status
				if (wk_status == 'RM'):
					wk_arc_query = del_query(wk_table) 
					print wk_arc_query
				elif (wk_status == 'AR'):
					wk_arc_query = arc_query(wk_table) 
					print wk_arc_query
					wk_arc_query = del_query(wk_table, "", 0, "" ) 
					print wk_arc_query
				elif (wk_status == 'IN'):
					wk_arc_query = del_query(wk_table, "", 0, "", "AR") 
					#wk_arc_query = del_query(wk_table ) 
					print wk_arc_query
				elif (wk_status == 'TS'):
					wk_arc_query = arc_query(wk_table, "", 0, "", "Y") 
					#wk_arc_query = del_query(wk_table ) 
					print wk_arc_query
				elif (wk_status == 'AT'):
					wk_arc_query = arc_query_same_db(wk_table, "", 0, "", "N",wk_to_table) 
					print wk_arc_query
					wk_arc_query = del_query(wk_table, "", 0, "" ) 
					print wk_arc_query
				elif (wk_status == 'TA'):
					wk_arc_query = arc_query(wk_table, "", 0, "", "N",wk_to_table) 
					print wk_arc_query
					wk_arc_query = del_query(wk_table, "", 0, "" ) 
					print wk_arc_query
				#elif (wk_status == 'FA'):
				#	wk_arc_query = arc_query_archive_db(wk_table, "", 0, "", "N",wk_to_table) 
				#	print wk_arc_query
				#	wk_arc_query = del_query(wk_table, "", 0, "", "AR" ) 
				#	print wk_arc_query
				elif (wk_status == 'AD'):
					wk_arc_query = arc_query_same_db(wk_table, "", 0, "", "N",wk_to_table, "<") 
					print wk_arc_query
					wk_arc_query = del_query(wk_table, "", 0, "") 
					print wk_arc_query
		elif (wk_method == 'DT'):
			# then the date query on a field
			if (wk_table == ''):
				print "Table is empty"
			elif (wk_field == ''):
				print "Date Field is empty"
			else:
				#print "status %s" % wk_status
				if (wk_status == 'RM'):
					wk_arc_query = del_query(wk_table, wk_field, wk_days) 
					print wk_arc_query
				elif (wk_status == 'AR'):
					wk_arc_query = arc_query(wk_table, wk_field, wk_days) 
					print wk_arc_query
					wk_arc_query = del_query(wk_table, wk_field, wk_days ) 
					print wk_arc_query
				elif (wk_status == 'IN'):
					wk_arc_query = del_query(wk_table, wk_field, wk_days, "", "AR") 
					print wk_arc_query
					wk_arc_query = del_query(wk_table, wk_field, wk_days ) 
					print wk_arc_query
				elif (wk_status == 'TS'):
					wk_arc_query = arc_query(wk_table, wk_field, wk_days, "", "Y") 
					#wk_arc_query = del_query(wk_table ) 
					print wk_arc_query
				elif (wk_status == 'AT'):
					wk_arc_query = arc_query_same_db(wk_table, wk_field, wk_days, "", "N",wk_to_table) 
					print wk_arc_query
					wk_arc_query = del_query(wk_table, wk_field, wk_days ) 
					print wk_arc_query
				elif (wk_status == 'TA'):
					wk_arc_query = arc_query(wk_table, wk_field, wk_days, "", "N",wk_to_table) 
					print wk_arc_query
					wk_arc_query = del_query(wk_table, wk_field, wk_days ) 
					print wk_arc_query
				elif (wk_status == 'FA'):
					wk_arc_query = arc_query_archive_db(wk_table, wk_field, wk_days, "", "N",wk_to_table) 
					print wk_arc_query
					wk_arc_query = del_query(wk_table, wk_field, wk_days,"","AR" ) 
					print wk_arc_query
				elif (wk_status == 'AD'):
					wk_arc_query = arc_query_same_db(wk_table, wk_field, wk_days, wk_query, "N",wk_to_table, "<") 
					print wk_arc_query
					wk_arc_query = del_query(wk_table, wk_field, wk_days, wk_query) 
					print wk_arc_query
		elif (wk_method == 'DQ'):
			# then the date query anded with a query 
			if (wk_table == ''):
				print "Table is empty"
			elif (wk_field == ''):
				print "Date Field is empty"
			elif (wk_query == ''):
				print "Query Field is empty"
			else:
				#print "status %s" % wk_status
				if (wk_status == 'RM'):
					wk_arc_query = del_query(wk_table, wk_field, wk_days, wk_query) 
					print wk_arc_query
				elif (wk_status == 'AR'):
					wk_arc_query = arc_query(wk_table, wk_field, wk_days, wk_query) 
					print wk_arc_query
					wk_arc_query = del_query(wk_table, wk_field, wk_days, wk_query) 
					print wk_arc_query
				elif (wk_status == 'IN'):
					wk_arc_query = del_query(wk_table, wk_field, wk_days, wk_query, "AR") 
					print wk_arc_query
					wk_arc_query = del_query(wk_table, wk_field, wk_days, wk_query ) 
					print wk_arc_query
				elif (wk_status == 'TS'):
					wk_arc_query = arc_query(wk_table, wk_field, wk_days, wk_query, "Y") 
					#wk_arc_query = del_query(wk_table ) 
					print wk_arc_query
				elif (wk_status == 'AT'):
					wk_arc_query = arc_query_same_db(wk_table, wk_field, wk_days, wk_query, "N",wk_to_table) 
					print wk_arc_query
					wk_arc_query = del_query(wk_table, wk_field, wk_days, wk_query) 
					print wk_arc_query
				elif (wk_status == 'TA'):
					wk_arc_query = arc_query(wk_table, wk_field, wk_days, wk_query, "N",wk_to_table) 
					print wk_arc_query
					wk_arc_query = del_query(wk_table, wk_field, wk_days, wk_query) 
					print wk_arc_query
				elif (wk_status == 'FA'):
					wk_arc_query = arc_query_archive_db(wk_table, wk_field, wk_days, wk_query, "N",wk_to_table) 
					print wk_arc_query
					wk_arc_query = del_query(wk_table, wk_field, wk_days, wk_query, "AR") 
					print wk_arc_query
				elif (wk_status == 'AD'):
					wk_arc_query = arc_query_same_db(wk_table, wk_field, wk_days, wk_query, "N",wk_to_table, "<") 
					print wk_arc_query
					wk_arc_query = del_query(wk_table, wk_field, wk_days, wk_query) 
					print wk_arc_query
		#end of getting archival options
		data_fields = cur.fetchone()

con.commit()

conarc.commit()

print "end - of archive "

#revert stdin stdout and stderr
if (havelog == 1):
	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__
	out.close()

###
