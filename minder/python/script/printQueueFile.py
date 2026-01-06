#!/usr/bin/env python2
"""
<title>
printQueueFile.py, Version 27:08:10
</title>
<long>
Updates PRINT_REQUEST in the database
Sends files to a host and queue
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
import ftplib

if os.name == 'nt':
	import win32api

#import kinterbasdb
#import mx.DateTime
import kinterbasdb;kinterbasdb.init(type_conv=200)

#####################################################
#def sendFile( toHost , toQueue, prnFile ):
def sendFile( toHost , toQueue, prnFile, prnBrand ):
	" send file to printer "	
	#global  prt, wk_total_pageno
	print "print to " + toHost + "queue" + toQueue + " file " + prnFile
	print time.asctime()
	sys.stdout.flush()
	#host = 'localhost'
	host = toHost
	if host == "":
		host = None
	if host == "None":
		host = None
	queue = toQueue
	if queue == "":
		queue = None
	if queue == "None":
		queue = None
	wk_sendok = True
	try:
		if os.name == 'nt':
			if queue == None:
				win32api.ShellExecute(0, "print", prnFile, None, ".", 0)
			else:
				if host == None:
					win32api.ShellExecute(0, "print", "/D:" + queue ,prnFile, None, ".", 0)
				else:
					win32api.ShellExecute(0, "print", "/D:\\\\" + host + "\\" + queue ,prnFile, None, ".", 0)
		else:
			if queue == None:
				sys.stdout.flush()
				print "no queue to print"
				print time.asctime()
				sys.stdout.flush()
				wk_sendok = False
			else:
				#os.system("lp -d " + queue + " " + prnFile)
				# if a pdf file and using RAW or GENERIC brand
				wkRawBrands = ";RAW;GENERIC;"
				fileRest, fileExt = os.path.splitext(prnFile)
				if fileExt.lower() == ".pdf" and wkRawBrands.find(prnBrand) > 0 :
					os.system("pdf2ps " + prnFile + " - | lp -d " + queue + " " )
				else:
					os.system("lp -d " + queue + " " + prnFile)
	except (os.error, win32api.error):
		sys.stdout.flush()
		print "failed in print"
		print time.asctime()
		sys.stdout.flush()
		wk_sendok = False
	return (wk_sendok )
#####################################################
def sendFTPFile( toHost , toQueue, prnFile ):
	" send file to printer via ftp "	
	#global  prt, wk_total_pageno
	print "print to " + toHost + "queue" + toQueue + " file " + prnFile + "via ftp "
	print time.asctime()
	sys.stdout.flush()
	#host = 'localhost'
	host = toHost
	if host == "":
		host = None
	queue = toQueue
	if queue == "":
		queue = None
	wk_sendok = True
	basename, extent = os.path.splitext(prnFile)
	try:
		ftp = ftplib.FTP(host)
	except (os.error, ftplib.all_errors):
		sys.stdout.flush()
		print "failed connect to remote host"
		print time.asctime()
		sys.stdout.flush()
		wk_sendok = False
	if wk_sendok:
		try:
			print ftp.login()
		except (ftplib.all_errors):
			sys.stdout.flush()
			print "failed to login to remote server"
			print time.asctime()
			sys.stdout.flush()
			wk_sendok = False
	if wk_sendok:
		try:
			print ftp.storbinary("STOR lpt1" , open(prnFile,"rb"),1024)
		except (os.error, ftplib.all_errors):
			sys.stdout.flush()
			print "failed to send file to remote server"
			print time.asctime()
			sys.stdout.flush()
			wk_sendok = False
	if wk_sendok:
		try:
			print ftp.quit()
		except (os.error, ftplib.all_errors):
			sys.stdout.flush()
			print "failed to quit ftp session"
			print time.asctime()
			sys.stdout.flush()
			wk_sendok = False
	else:
		try:
			print ftp.close()
		except (os.error, ftplib.all_errors):
			sys.stdout.flush()
			print "failed to close ftp session"
			print time.asctime()
			sys.stdout.flush()
			wk_sendok = False
	if wk_sendok:
		sys.stdout.flush()
		print "file sent OK in print"
		print time.asctime()
		sys.stdout.flush()
	return (wk_sendok )
#####################################################
def check4Html( prnFile ):
	" check whether the print file is html data "	
	html = ""
	print "print file " + prnFile 
	print time.asctime()
	sys.stdout.flush()
	wk_checkok = True
	try:
		testfile = open(prnFile,'rb')
	#except (os.error ):
	except (OSError ):
		sys.stdout.flush()
		# no file or directory not found or cannot read it
		print "Cannot open the File"
		return (wk_checkok )
	try:
		html = testfile.read(14)
	except (os.error ):
		sys.stdout.flush()
		# cannot read it
		print "Cannot read the File"
		html = "<html>\n<head>\n"
	testfile.close()
	if html != "<html>\n<head>\n":
		wk_checkok = False
	return (wk_checkok )
###############################################################################
def getFile(  ):
	" get the files to send to printer "	
	global  con
	cur = con.cursor()
	cur2 = con.cursor()
	# first get the next file name to send
	#query1 = """select pr.message_id, pr.base_file_name, pr.device_id , se.working_directory, se.ip_address, pr.send_copy, se.computer_queue, se.computer_queue_server  
	query1 = """select pr.message_id, pr.base_file_name, pr.device_id , se.working_directory, se.ip_address, pr.send_copy, se.computer_queue, se.computer_queue_server , se.se_brand 
from print_requests pr   
join sys_equip se  on  se.device_id = pr.device_id
where pr.request_status = 'NQ'
"""
	print query1
	print time.asctime()
	sys.stdout.flush()
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
		prnCopy = ""
		prnQueue = ""
		prnRemoteServer = ""
		prnBrand = "None"
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
				prnAddress = None
			else:
				prnAddress = data_fields[4]
			wk_realFile = prnFolder + prnFile
			if data_fields[5] is None:
				prnCopy = 1
			else:
				prnCopy = data_fields[5]
			if data_fields[6] is None:
				prnQueue = "None"
			else:
				prnQueue = data_fields[6]
			if data_fields[7] is None:
				prnRemoteServer = "None"
			else:
				prnRemoteServer = data_fields[7]
			if data_fields[8] is None:
				prnBrand = "None"
			else:
				prnBrand = data_fields[8]
				if prnBrand == "":
					prnBrand = "None"
			print wk_realFile 
			print time.asctime()
			sys.stdout.flush()
			#if sendFile( prnAddress , wk_realFile ):
			# must do this prncopy times
			#(wk_sendok, wk_sendtype) =  sendFile( prnAddress , wk_realFile )
			wk_sendok = False
			wkSeq = 0
			# check whether file is html from reportman ie no data
			# then dont print
			if check4Html(wk_realFile):
				# got html data
				print "print file has html in it - so no print"
				wkSeq = prnCopy
			while wkSeq < prnCopy:
				if prnQueue == "FTP":
					wk_sendok =  sendFTPFile(prnRemoteServer, prnQueue , wk_realFile )
				else:
					#wk_sendok =  sendFile(prnRemoteServer, prnQueue , wk_realFile )
					wk_sendok =  sendFile(prnRemoteServer, prnQueue , wk_realFile, prnBrand )
				if wk_sendok == False:
					wkSeq = prnCopy
				wkSeq = wkSeq + 1
			if wk_sendok :
				print "print of file was successfull"
				print time.asctime()
				sys.stdout.flush()
				query2 = """update print_requests set request_status = 'XQ' where message_id = '%s' """ % message_id
				print query2
				sys.stdout.flush()
				cur2.execute(query2)
			else:
				print "print of file was NOT successfull"
				print time.asctime()
				sys.stdout.flush()
				query2 = """update print_requests set request_status = 'EQ' where message_id = '%s' """ % message_id
				print query2
				sys.stdout.flush()
				cur2.execute(query2)
			data_fields = cur.fetchone()
###############################################################################
#connect to db

if os.name == 'nt':
	logfile = "d:/tmp/printQueue."
else:
	logfile = "/tmp/printQueue."
print "logfile", logfile
sys.stdout.flush()
havelog = 1;

mydb = "minder"
wkConduitWait = None
wkConduitLimit = None
#if len(sys.argv)>1:
for i in range( len(sys.argv)):
	inData = sys.argv[i]
	myparms = inData.split("=")
	if "db"  == myparms[0]:
		mydb = myparms[1]
	if "tmp"  == myparms[0]:
		logfile =  myparms[1] + "/printQueue."
	if "condwait"  == myparms[0]:
		wkConduitWait = int(myparms[1] )
	if "condlimit"  == myparms[0]:
		wkConduitLimit =  int(myparms[1] )
print "mydb", mydb
print "logfile", logfile
sys.stdout.flush()
logfile = logfile + mydb + ".log"
print "logfile", logfile
print time.asctime()
sys.stdout.flush()
#
#redirect stdout and stderr
if (havelog == 1):
	#out = open(logfile,'w')
	out = open(logfile,'a')
	sys.stdout = out
	sys.stderr = out

if os.name == 'nt':
	#dsn="127.0.0.1:minder",
	con = kinterbasdb.connect(
		dsn="127.0.0.1:" + mydb,
		user="sysdba",
		password="masterkey")
else:
	#dsn="127.0.0.1:minder",
	con = kinterbasdb.connect(
		dsn="127.0.0.1:" + mydb,
		user="sysdba",
		password="masterkey")

print "connected to db"
print time.asctime()
sys.stdout.flush()

wk_date = time.strftime("%d/%m/%y")
#read std or 1st input parm

# for records in print request NQ status
# send to device
getFile( )

con.commit()
#out.flush()

wkConduitEvents = 0
#wkConduitWait = 60
#wkConduitLimit = 200

#if 1:
while 1:
	# ok now start to wait for event
	MY_EVENT = [ 'PRN_REQUEST_NQ', 'PRN_REQUEST_NQ_END'  ]
	conduit = con.event_conduit(MY_EVENT)
	print "about to wait for %s\n" % MY_EVENT
	print time.asctime()
	#result = conduit.wait()
	if wkConduitWait is None:
		result = conduit.wait()
	else:
		result = conduit.wait(wkConduitWait)
	print "event occurred "
	wkConduitEvents = wkConduitEvents + 1
	print "event no " + str( wkConduitEvents)
	print result
	print time.asctime()
	repr (result)
	if result is None:
		# timeout occurred
		print "got a timeout in wait \n" 
		getFile( )
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
		if result['PRN_REQUEST_NQ'] > 0:
			# for records in print request NQ status
			# send to device
			getFile( )
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
			conduit.flush()
			conduit.close()
			break

print "end of requests" 
print time.asctime()

#con.commit()

print "end - of "


#revert stdin stdout and stderr
if (havelog == 1):
	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__
	out.close()

###
