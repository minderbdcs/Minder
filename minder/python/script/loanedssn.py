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
	print "loanedssn ", sys.argv[1]
	logfile = sys.argv[1]
	havelog = 1;
else:
	print "loanedssn stdin"
	havelog = 0;

if len(sys.argv)>2:
	print "outputcsv ", sys.argv[2]
	csvfile = sys.argv[2]
else:
	print "outputcsv overdue.csv"
	csvfile = "overdue.csv"

#
#redirect stdout and stderr
if (havelog == 1):
	out = open(logfile,'a')
	sys.stdout = out
	sys.stderr = out

csv = open(csvfile,'w')

if os.name == 'nt':
	#		dsn="d:/asset.rf/database/wh.v39.gdb",
	con = kinterbasdb.connect(
		dsn="127.0.01:d:/asset.rf/database/wh.v39.gdb",
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

#for locations not in zone
#	get all ssn in location 
#-------------------------------------------------------

print "getting locations out of zone"
query3 = """select l.locn_id, l.wh_id, l.locn_name 
	from location l
	where l.zone_c is null and (l.wh_id not starting 'X')
	order by l.wh_id, l.locn_id """

cur.execute(query3)
		
# get data record
data_fields = cur.fetchone()
#print data_fields
if data_fields is None:
	print "no locations out of zone"
	csv.write("no locations out of zone\r\n")
else:
	while not data_fields is None:
		#print data_fields
		current_locn = data_fields[0]
		current_wh = data_fields[1]
		current_name = data_fields[2]

		#print "locn:%s" % current_locn
		#print "wh:%s" % current_wh
		# now to get ssn in location
		query4 = """select ssn_id, ssn_description, into_date 
		from ssn 
		where wh_id = '%s' and locn_id = '%s' 
		order by ssn_id
		"""

		#print query4 % (current_wh, current_locn, date_today, loan_length)
		cur2.execute(query4 % (current_wh, current_locn))
		
		# get data record
		data_fields2 = cur2.fetchone()
		#print data_fields2
		if data_fields2 is None:
			#print "no ssns overdue in location"
			csv.write("%s\t%s\t\tno ssns in location\r\n" % (current_wh, current_locn))
			dummy = 1
		else:
			while not data_fields2 is None:
				#print data_fields
				current_ssn = data_fields2[0]
				current_desc = data_fields2[1]
				current_date = data_fields2[2]
				# ssn found
				#print "locn:%s" % current_locn
				#print "wh:%s" % current_wh
				csv.write("%s\t%s\t%s\t%s\t%s\r\n" % (current_wh, current_locn, current_ssn, current_desc, current_date))
				data_fields2 = cur2.fetchone()

		#end of getting 1st ssn overdue
		data_fields = cur.fetchone()

con.commit()

print "end - of SSNs"
#revert stdin stdout and stderr
if (havelog == 1):
	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__

	out.close()
csv.close()
###
