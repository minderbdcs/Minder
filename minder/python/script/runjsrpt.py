#!/usr/bin/env python2
"""
<title>
runjsrpt.py, Version 21.07.14
</title>
<long>
Runs a Jasper Report
Creates Print File for a Print Request
<br>
Parameters: <tt>Input File  and output Log File</tt>
<br>
<br>
</long>
"""
import sys
import string
import time , os ,glob
import fileinput
#import socket
import urllib

#import kinterbasdb;kinterbasdb.init(type_conv=200)
import re
from pprint import pprint
from pyjasperclient import JasperClient

def pathtodir(path):
	if not os.path.exists(path):
		l=[]
		p = "/"
		l = path.split("/")
		i = 1
		while i < len(l):
			p = p + l[i] + "/"
			i = i + 1
			if not os.path.exists(p):
				os.mkdir(p, 0777)


# get 1st line from the input file

if len(sys.argv)>0:
	print "runjsprt ", sys.argv[1]
	infile = sys.argv[1]
	havein = 1;
else:
	print "runjsprt stdin"
	infile = '-'
	havein = 0;

if len(sys.argv)>1:
	print "log ", sys.argv[2]
	logfile = sys.argv[2]
	havelog = 1;
else:
	print "log stdin"
	havelog = 0;

#
#redirect stdout and stderr
if (havelog == 1):
	out = open(logfile,'a')
	sys.stdout = out
	sys.stderr = out

wk_date = time.strftime("%d/%m/%y")
wk_line = 0	
#read std or 1st input parm
wk_in_line = ""
for line in fileinput.input(infile):
	wk_line = wk_line + 1
	print "line",wk_line,line
	wk_in_line = line
	if wk_line == 1:
		break
#
# fields in it are order company reportno uri emailto
wk_in_parms = wk_in_line.split("|")

# get the order no to report
wk_order = wk_in_parms[0]
# get the company for the order
wk_company = wk_in_parms[1]
# get the report to run for a pack slip for the company
wk_report_id = wk_in_parms[2]
# get the reports uri to run
wk_report_uri = wk_in_parms[3]
#wk_report_uri = '%2Freports%2faldinvoice'
# convert to have slashes
wk_report_uri =  urllib.unquote(wk_report_uri)
# get email to send to
wk_to_email = wk_in_parms[4]
# get email to copy to
wk_cc_email = wk_in_parms[5]
# get invoice type
wk_invoice_type = wk_in_parms[6]
 # trim white space
wk_invoice_type = wk_invoice_type.rstrip()
if wk_invoice_type == "PS":
	wk_report_type = "F"
	wk_report_prefix = "PACK_"
else:
	wk_report_type = "T"
	wk_report_prefix = "INVOICE_"
#
# find folder to write into
wk_date_yymm = time.strftime("%Y/%m/")
wk_pid = os.getpid()
wk_out_path = "/data/" + wk_company + "/TAX_INVOICE/" + wk_date_yymm   
pathtodir(wk_out_path)
wk_out_file = "/data/" + wk_company + "/TAX_INVOICE/" + wk_date_yymm  + wk_report_prefix + wk_order + "_" + str(wk_pid) + ".pdf"
#
url = 'http://localhost:8080/jasperserver/services/repository?wsdl'
js = JasperClient()
#js.login(url, 'jlogin', 'jpass')
js.login(url, 'jasperadmin', 'jasperadmin')
#	report = js.run('/Reports/aldinvoice', 'PDF', {'PICK_ORDER': 'S000059225', 'Report_Type': 'F'},{})
report = js.run(wk_report_uri, 'PDF', {'PICK_ORDER': wk_order, 'Report_Type': wk_report_type},{})

#report_file = file('report.xls','wb')
report_file = file(wk_out_file,'wb')
report_file.write(report[1]['data'])
report_file.close()
print "end - of datafile"
print wk_out_file
#revert stdin stdout and stderr
if (havelog == 1):
	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__
	out.close()
###
