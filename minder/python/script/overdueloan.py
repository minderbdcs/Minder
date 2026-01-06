#!/usr/bin/env python2
"""
<title>
savetodays.py, Version 21.01.04
</title>
<long>
Creates todays directory and moves files to it
<br>
Parameters: <tt> startdirectory </tt>
<br>
This scans the directory <tt>startdirectory</tt> for data 
and puts the files into the saved directory
<br>
</long>
"""
import sys
import string
import time , os ,glob

import kinterbasdb
import mx.DateTime

#redirect stdout and stderr

if len(sys.argv)>1:
	print "overdueloan ", sys.argv[1]
	logfile = sys.argv[1]
	havelog = 1;
else:
	print "overdueloan stdin"
	havelog = 0;

#
#redirect stdout and stderr
if (havelog == 1):
	out = open(logfile,'a')
	sys.stdout = out
	sys.stderr = out

if os.name == 'nt':
	#	dsn="c:/asset.rf/database/wh.v39.gdb",
	con = kinterbasdb.connect(
		dsn="127.0.0.1:minder",
		user="sysdba",
		password="masterkey")
else:
	#	dsn="/data/asset.rf/wh.v39.gdb",
	con = kinterbasdb.connect(
		dsn="127.0.0.1:minder",
		user="sysdba",
		password="masterkey")

print "connected to db"
cur = con.cursor()
cur2 = con.cursor()

#1st get the 2 periods available

#select loan_period_no_1, loan_period_no_2 from control;

query1 = """select loan_period_no_1, loan_period_no_2 from control """

cur.execute(query1)
		
# get data record
data_fields = cur.fetchone()
#print data_fields
if data_fields is None:
	print "no control data in list"
else:
	buffer = data_fields
	for pos in range(len(buffer)):
		if buffer[pos] is None:
			buffer[pos] = "NULL"

	print "loan period 1",buffer[0]
	print "loan period 2",buffer[1]
	wk_loan_period_1 = buffer[0]
	wk_loan_period_2 = buffer[1]

#update all ssn's to not be overdue
#update location set locn_stat = 'OK' where locn_stat = 'OD';

query2 = """update location set locn_stat = 'OK',locn_name=substr(locn_name,2,len(locn_name)) where locn_stat = 'OD' """

ret = cur.execute(query2)
		
print "update all locations to OK not overdue " 
con.commit()

#for locations not in zone
#	get employees occupation 
#	(thus loan length)
#	
#	get 1st ssn in location (not a product) and into_date not null
#	with	diffdate(zerotime(into_date),"TODAY",4) > loan length
#	if found
#		update locations locn_stat to 'OD'
#-------------------------------------------------------
# the alternative not yet written would
#for locations not in zone
#	get ssns loan_period and loan_period_no
#	  diffdate(zerotime(into_date),"TODAY",4) 
#         (not a prod and into_date no null)
#	calc the loan length in days
#	for loan_period in (D,W,M,Y)
#	loan length is (1,7,30,365) * loan_period_no
#	if diffdate(zerotime(into_date),"TODAY",4) > loan length
#		update locations locn_stat to 'OD'
#		stop retrieving ssns for location
#-------------------------------------------------------

print "getting locations out of zone"
query3 = """select l.locn_id, l.wh_id, e.occupation 
	from location l left outer join employee e on l.locn_id = e.employee_id
	where l.zone_c is null """

cur.execute(query3)
		
# get data record
data_fields = cur.fetchone()
#print data_fields
if data_fields is None:
	print "no locations out of zone"
else:
	while not data_fields is None:
		#print data_fields
		current_locn = data_fields[0]
		current_wh = data_fields[1]
		current_occupation = data_fields[2]

		#print "locn:%s" % current_locn
		#print "wh:%s" % current_wh
		#print "occuption:%s" % current_occupation
		if current_occupation is None:
			current_occupation = "None"
		if current_occupation.upper()[:10] == "SUPERVISOR":
			loan_length = wk_loan_period_2 
		else:
			loan_length = wk_loan_period_1 
		#print "loan length:%d" % loan_length
		date_today = mx.DateTime.today()
		# now to get 1st ssn in location
		query4 = """select first 1 ssn_id 
		from ssn 
		where wh_id = '%s' and locn_id = '%s' 
		and (not into_date is null)
		and (prod_id is null)
		and diffdate(into_date, '%s', 4) > %d """

		#print query4 % (current_wh, current_locn, date_today, loan_length)
		cur2.execute(query4 % (current_wh, current_locn, date_today, loan_length))
		
		# get data record
		data_fields2 = cur2.fetchone()
		#print data_fields2
		if data_fields2 is None:
			#print "no ssns overdue in location"
			dummy = 1
		else:
			# ssn found
			print "locn:%s" % current_locn
			print "wh:%s" % current_wh
			print "occuption:%s" % current_occupation
			print "found ssn overdue:%s" %data_fields2[0]
			#update location set locn_stat = 'OD' where wh_id = ? and locn_id = ?;

			query2 = """update location set locn_stat = 'OD',locn_name=substr(('*' || locn_name),1,50) where wh_id = '%s' and locn_id = '%s' """

			ret = cur2.execute(query2 % (current_wh, current_locn))
		
			print "update location to OD overdue " 

		#end of getting 1st ssn overdue
		data_fields = cur.fetchone()

con.commit()

print "end - of Overdues"
#revert stdin stdout and stderr
if (havelog == 1):
	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__

	out.close()
###
