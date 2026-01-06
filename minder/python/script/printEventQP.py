#!/usr/bin/env python2
"""
<title>
printCreateEvent.py, Version 11.04.16
</title>
<long>
Reads PRINT_REQUEST records in the database of QP status
Creates a Signal File for the Print Request in the folder setup by options record
WEB_REQUEST
<br>
Parameters: <tt>db</tt>
            <tt>host</tt>
            <tt>user</tt>
            <tt>passwd</tt>
            <tt>tmp</tt>
            <tt>condwait</tt>
            <tt>condlimit</tt>
<br>
<br>
</long>
"""
import sys
import string
import time , os ,glob
import fileinput
import socket

#import kinterbasdb
#import kinterbasdb;kinterbasdb.init(type_conv=200)
import fdb

###############################################################################
#1st read a QP status print request
#for this record
#
#then create the signal file for the message id
if the file already exists then ignore the record
#
###############################################################################
def now():
	# get the current date
	wkDateTime = time.strftime("%d/%m/%y %H:%M:%S")
	return wkDateTime
###############################################################################
def writePRNFile(wkFilename ):
	# does the file exist ?
	if os.path.isfile(wkFilename):
		wk_dummy = 0
	else:
		#
		# open file
		print "about to open file ", wkFilename
		outFile = open(wkFilename, 'w')
		# write line
		#outFile.write("%s\n" % (prnLine))
		wkLineMask = "%s\n" 
		print "line ", wkLineMask
		prnLine = now()
		outFile.write(wkLineMask % (prnLine))
		#
		# close file
		outFile.close()
		print "closed file ", wkFilename
###############################################################################
def getRequest(  ):
	# get the requests for labels to create files
	global  con 
	print "Start getRequest"
	print time.asctime()
	cur = con.cursor()
	cur2 = con.cursor()
	# first get the next file name to send
	# where pr.request_status = 'QP'
	query1 = """select pr.message_id, o1.description 
from print_requests pr   
join options o1 on o1.group_code =  'WEB_EVENT'  and o1.code = 'DIRECTORY'
	where pr.request_status = 'QP'
"""
	print query1
	cur.execute(query1)
	## get data record
	data_fields = cur.fetchone()
	#print data_fields
	if data_fields is None:
		print "no requests "
		message_id = ""
		prnFolder = ""
		prnNow = ""
	else:
		while not data_fields is None:
			#print data_fields
			if data_fields[0] is None:
				message_id = ""
			else:
				message_id = data_fields[0]
			if data_fields[1] is None:
				prnFolder = "/data/tmp/minder/"
			else:
				prnFolder = data_fields[1]
			#
			print "message", message_id, "Folder", prnFolder 
			# now create/update the file
			prnRealFile = prnFolder + message_id + ".queue"
			writePRNFile(prnRealFile )
			data_fields = cur.fetchone()
	print "End getRequest"
	print time.asctime()
###############################################################################
#connect to db

if os.name == 'nt':
	logfile = "d:/tmp/printEventFileQP."
else:
	logfile = "/tmp/printEventFileQP."
havelog = 1;

mydb = "minder"
myhost = "localhost"
myuser = "minder"
mypasswd = "minder"
wkConduitWait = None
wkConduitLimit = None
for i in range( len(sys.argv)):
	inData = sys.argv[i]
	myparms = inData.split("=")
	if "db"  == myparms[0]:
		mydb = myparms[1]
	if "host"  == myparms[0]:
		myhost = myparms[1]
	if "user"  == myparms[0]:
		myuser = myparms[1]
	if "passwd"  == myparms[0]:
		mypasswd = myparms[1]
	if "tmp"  == myparms[0]:
		logfile  = myparms[1] + "/printEventFileQP."
	if "condwait"  == myparms[0]:
		wkConduitWait = int(myparms[1] )
	if "condlimit"  == myparms[0]:
		wkConduitLimit = int( myparms[1] )
print "mydb", mydb
print "myhost", myhost
print "myuser", myuser
#print "mypasswd", mypasswd
logfile = logfile + mydb + ".log"
print "logfile", logfile
print time.asctime()

#
#redirect stdout and stderr
if (havelog == 1):
	#out = open(logfile,'w')
	out = open(logfile,'a')
	sys.stdout = out
	sys.stderr = out

if os.name == 'nt':
	#dsn="127.0.0.1:minder",
	con = fdb.connect(
		dsn=myhost + ":" + mydb,
		user=myuser,
		password=mypasswd)
else:
	#dsn="127.0.0.1:minder",
	con = fdb.connect(
		dsn=myhost + ":" + mydb,
		user=myuser,
		password=mypasswd)

print "connected to db"

wk_date = time.strftime("%d/%m/%y")
#read std or 1st input parm

# for records in print request QP status
# create Print Files
getRequest( )

con.commit()
out.flush()

wkConduitEvents = 0

#if 1:
while 1:
	# ok now start to wait for event
	MY_EVENT = [ 'PRN_REQUEST_QP', 'PRN_REQUEST_QP_END'  ]
	conduit = con.event_conduit(MY_EVENT)
	print "about to wait for %s\n" % MY_EVENT
	#result = conduit.wait()
	if wkConduitWait is None:
		result = conduit.wait()
	else:
		result = conduit.wait(wkConduitWait)
	print "event occurred "
	wkConduitEvents = wkConduitEvents + 1
	print "event no " + str(wkConduitEvents)
	print result
	repr (result)
	out.flush()
	if result is None:
		# timeout occurred
		print "got a timeout in wait \n" 
		getRequest( )
		con.commit()
		out.flush()
		conduit.flush()
		conduit.close()
		if wkConduitLimit is None:
			wk_dummy = 1
		else:
			if wkConduitEvents > wkConduitLimit:
				break
	else:
		if result['PRN_REQUEST_QP'] > 0:
			# for records in print request QP status
			# create print file
			getRequest( )
			con.commit()
			out.flush()
			conduit.flush()
			conduit.close()
			if wkConduitLimit is None:
				wk_dummy = 1
			else:
				if wkConduitEvents > wkConduitLimit:
					break
		else:
			# got close down request
			conduit.flush()
			conduit.close()
			break

print "end of requests" 

#con.commit()

print "end - of "


#revert stdin stdout and stderr
if (havelog == 1):
	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__
	out.close()

###
