#!/usr/bin/env python2
"""
<title>
printSendFile.py, Version 25.02.09
</title>
<long>
Updates PRINT_REQUEST in the database
Sends files to a host and port
<br>
Parameters: <tt>None</tt>
<br>
<br>
</long>
"""
import sys
import string
import time , os ,glob
import fileinput
import socket

#import fdb
#import mx.DateTime
#import fdb;fdb.init(type_conv=200)
import fdb
from datetime import datetime
import ConfigParser

#####################################################
def sendFile( toHost , prnFile, prnAttempts, prnPause ):
	" send file to printer "	
	#global  prt, wk_total_pageno
	print "send to " + toHost + " file " + prnFile
	host = 'localhost'
	host = toHost
	port = 9100
	size = 10
	# want to be passed the try limit for socket rather than 5
	# want to be passed the sleep time rather than 10
	# if you go through all the try limits without a successful socket
	# then update the status to 'ES'
	s = None
	wkSeq = 0
	wkLimit = int(prnAttempts)
	wkPause = int(prnPause)
	#while wkSeq < 5 and  s is None:
	while wkSeq < wkLimit and  s is None:
		if wkSeq > 0:
			#time.sleep(10)
			time.sleep(wkPause)
		try:
			s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
			s.settimeout(15.0)
		except socket.error, msg:
			print "Failed to Create Socket level ",wkSeq
			print time.asctime()
			s = None
			#return (False, "S1")
		if s is None:
			print "could not connect to host ",wkSeq
			print time.asctime()
		else:		
			try:
				s.connect((host,port))
			except socket.error, msg:
				print "Failed to Connect Socket level ",wkSeq
				print time.asctime()
				s.close()
				s = None
				#return (False, "S2")
		# add to try no
		wkSeq = wkSeq + 1
	if s is None:
		print "could not connect to host ."
		print time.asctime()
		return (False, "S3")
	else:		
		print "connected to host"
		print time.asctime()
		try:
			inFile = open(prnFile, 'r')
			line = inFile.read()
			wk_readok = True
		except IOError, (errno, strerror):
			print "Failed to open and read input file %s (%s) %s" % (prnFile, errno, strerror)
			line = ""
			wk_readok = False
			# want to change request status to NP
		if wk_readok:
			try:
    				s.send(line)
				wk_sendok = True
			except socket.error, msg:
				print "failed in send"
				wk_sendok = False
	    		#data = s.recv(size)
			if wk_sendok:
    				print line
				inFile.close()
				s.close()
				return (True, "SF")
			else:
				inFile.close()
				s.close()
				return (False, "S4")
		else:
			#inFile.close()
			s.close()
			return (False, "F1")
###############################################################################
def getFile(  ):
	" get the files to send to printer "	
	global  con
	print "Start getFile"
	print time.asctime()
	cur = con.cursor()
	cur2 = con.cursor()
	# first get the next file name to send
	query1 = """select pr.message_id, pr.base_file_name, pr.device_id , se.working_directory, se.ip_address ,se.socket_attempts, se.socket_pause
from print_requests pr   
join sys_equip se  on  se.device_id = pr.device_id
where pr.request_status = 'CP'
order by pr.prn_date
"""
	print query1
	cur.execute(query1)
	## get data record
	data_fields = cur.fetchone()
	#print data_fields
	if data_fields is None:
		print "no requests "
		message_id = ""
		prnFile = ""
		prnDevice = ""
		prnFolder = ""
		prnAddress = ""
		prnAttempts = 0
		prnPause = 0
	else:
		while not data_fields is None:
			#print data_fields
			if data_fields[0] is None:
				message_id = ""
			else:
				message_id = data_fields[0]
			if data_fields[1] is None:
				prnFile = ""
			else:
				prnFile = data_fields[1]
			if data_fields[2] is None:
				prnDevice = ""
			else:
				prnDevice = data_fields[2]
			if data_fields[3] is None:
				prnFolder = ""
			else:
				prnFolder = data_fields[3]
			if data_fields[4] is None:
				prnAddress = "localhost"
			else:
				prnAddress = data_fields[4]
			if data_fields[5] is None:
				prnAttempts = 1
			else:
				prnAttempts = data_fields[5]
			if data_fields[6] is None:
				prnPause = 1
			else:
				prnPause = data_fields[6]
			wk_realFile = prnFolder + prnFile
			#if sendFile( prnAddress , wk_realFile ):
			(wk_sendok, wk_sendtype) =  sendFile( prnAddress , wk_realFile, prnAttempts, prnPause )
			if wk_sendok :
				print "send of file was successfull"
				query2 = """update print_requests set request_status = 'XP' where message_id = '%s' """ % message_id
				print query2
				cur2.execute(query2)
			else:
				print "send of file was NOT successfull"
				if wk_sendtype[0:1] == "F":
					print "could not read file so try to recreate"
					query2 = """update print_requests set request_status = 'NP' where message_id = '%s' """ % message_id
					print query2
					cur2.execute(query2)
				elif wk_sendtype == "S3": 
					print "could not open socket so leave as failed"
					query2 = """update print_requests set request_status = 'ES' where message_id = '%s' """ % message_id
					print query2
					cur2.execute(query2)
			data_fields = cur.fetchone()
	print "End getFile"
	print time.asctime()
###############################################################################
#connect to db

if os.name == 'nt':
	logfile = "d:/tmp/printSend."
else:
	logfile = "/tmp/printSend."
havelog = 1;

mydb = "minder"
myhost = "127.0.0.1"
myuser = "minder"
mypasswd = "minder"
wkConduitWait = None
wkConduitLimit = None
#if len(sys.argv)>1:
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
		logfile = myparms[1] + "/printSend."
	if "condwait"  == myparms[0]:
		wkConduitWait = int(myparms[1] )
	if "condlimit"  == myparms[0]:
		wkConduitLimit = int(myparms[1] )
print "mydb", mydb
#print "myhost", myhost
logfile = logfile + mydb + ".log"
print "logfile", logfile
#
#redirect stdout and stderr
if (havelog == 1):
	#out = open(logfile,'w')
	out = open(logfile,'a')
	sys.stdout = out
	sys.stderr = out

print "mydb", mydb
print "myhost", myhost
print "myuser", myuser
print "mypassword", mypasswd
con = fdb.connect(
	dsn=myhost + ":" + mydb,
	user=myuser,
	password=mypasswd)

print "connected to db"

#
# open /etc/minder/minder/minder.ini
# get the timezone field
cc = ConfigParser.ConfigParser()
cc.readfp(open('/etc/minder/minder/minder.ini'))
wk_out_timezone = cc.get('date','timezone', 0)
os.environ['TZ'] = wk_out_timezone
time.tzset()

#wk_date = time.strftime("%d/%m/%y")
wk_now = datetime.now()
wk_date = wk_now.strftime("%d/%m/%y %H:%M:%S.%f")
print "connected to db", wk_date
#read std or 1st input parm

# for records in print request CP status
# send to device
getFile( )

con.commit()
out.flush()

wkConduitEvents = 0
#wkConduitWait = 60
#wkConduitLimit = 200

print "Before Register events"
wk_now = datetime.now()
wk_date = wk_now.strftime("%d/%m/%y %H:%M:%S.%f")
print wk_date
# ok now start to wait for event
MY_EVENT = [ 'PRN_REQUEST_CP', 'PRN_REQUEST_CP_END'  ]
conduit = con.event_conduit(MY_EVENT)
print "After Register events"
wk_now = datetime.now()
wk_date = wk_now.strftime("%d/%m/%y %H:%M:%S.%f")
print wk_date

#if 1:
while 1:
	print "Start Event Loop"
	wk_now = datetime.now()
	wk_date = wk_now.strftime("%d/%m/%y %H:%M:%S.%f")
	print wk_date
	print wkConduitEvents
	out.flush()
	# ok now start to wait for event
	#MY_EVENT = [ 'PRN_REQUEST_CP', 'PRN_REQUEST_CP_END'  ]
	#conduit = con.event_conduit(MY_EVENT)
	#print "about to wait for %s\n" % MY_EVENT
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
	if result is None:
		# timeout occurred
		print "got a timeout in wait \n" 
		getFile( )
		con.commit()
		print "After Commit"
		wk_now = datetime.now()
		wk_date = wk_now.strftime("%d/%m/%y %H:%M:%S.%f")
		print wk_date
		out.flush()
		print "After out flush"
		wk_now = datetime.now()
		wk_date = wk_now.strftime("%d/%m/%y %H:%M:%S.%f")
		print wk_date
		conduit.flush()
		print "After conduit flush"
		wk_now = datetime.now()
		wk_date = wk_now.strftime("%d/%m/%y %H:%M:%S.%f")
		print wk_date
		#conduit.close()
		#print "After conduit close"
		#wk_now = datetime.now()
		#wk_date = wk_now.strftime("%d/%m/%y %H:%M:%S.%f")
		#print wk_date
		if wkConduitLimit is None:
			wk_dummy = 1
		else:
			if wkConduitEvents > wkConduitLimit:
				break
	else:
		if result['PRN_REQUEST_CP'] > 0:
			# for records in print request CP status
			# send to device
			getFile( )
			con.commit()
			print "After Commit"
			wk_now = datetime.now()
			wk_date = wk_now.strftime("%d/%m/%y %H:%M:%S.%f")
			print wk_date
			out.flush()
			print "After out flush"
			wk_now = datetime.now()
			wk_date = wk_now.strftime("%d/%m/%y %H:%M:%S.%f")
			print wk_date
			conduit.flush()
			print "After conduit flush"
			wk_now = datetime.now()
			wk_date = wk_now.strftime("%d/%m/%y %H:%M:%S.%f")
			print wk_date
			#conduit.close()
			#print "After conduit close"
			#wk_now = datetime.now()
			#wk_date = wk_now.strftime("%d/%m/%y %H:%M:%S.%f")
			#print wk_date
			if wkConduitLimit is None:
				wk_dummy = 1
			else:
				if wkConduitEvents > wkConduitLimit:
					break
		else:
			if result['PRN_REQUEST_CP_END'] > 0:
				conduit.flush()
				#conduit.close()
				break
			else:
				# got unexpected timeout but not via null result
				print "got a timeout not in results \n" 
				getFile( )
				con.commit()
				print "After Commit"
				wk_now = datetime.now()
				wk_date = wk_now.strftime("%d/%m/%y %H:%M:%S.%f")
				print wk_date
				out.flush()
				print "After out flush"
				wk_now = datetime.now()
				wk_date = wk_now.strftime("%d/%m/%y %H:%M:%S.%f")
				print wk_date
				conduit.flush()
				print "After conduit flush"
				wk_now = datetime.now()
				wk_date = wk_now.strftime("%d/%m/%y %H:%M:%S.%f")
				print wk_date
				#conduit.close()
				#print "After conduit close"
				#wk_now = datetime.now()
				#wk_date = wk_now.strftime("%d/%m/%y %H:%M:%S.%f")
				#print wk_date
				if wkConduitLimit is None:
					wk_dummy = 1
				else:
					if wkConduitEvents > wkConduitLimit:
						break

print "end of requests" 

conduit.close()
print "After conduit close"
wk_now = datetime.now()
wk_date = wk_now.strftime("%d/%m/%y %H:%M:%S.%f")
print wk_date

#con.commit()

print "end - of "


#revert stdin stdout and stderr
if (havelog == 1):
	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__
	out.close()

###
