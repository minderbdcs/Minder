#!/usr/bin/env python2
"""
<title>
importxls2csv.py, Version 22.10.14
</title>
<long>
Creates a csv from xls or xlsx file
<br>
Parameters: <tt> xlsfilename </tt>
Parameters: <tt> logfilename </tt>
<br>
This scans the file <tt>xlsfilename</tt> for data 
and puts the text into the csv 
<br>
</long>
"""
import sys
import string
import time , os ,glob
import fileinput
import shutil
import getpass 
import datetime

import xlrd
import csv

def csv_from_excel(xlsfile,csvfile):

    wb = xlrd.open_workbook(xlsfile)
    sh = wb.sheet_by_name('Sheet1')
    your_csv_file = open(csvfile, 'wb')
    wr = csv.writer(your_csv_file, quoting=csv.QUOTE_ALL)

    for rownum in xrange(sh.nrows):
        wr.writerow(sh.row_values(rownum))

    your_csv_file.close()

#print "importxls2csv xlsfilename logfilename savetodir "
if len(sys.argv)>1:
	print "importxls2csv infile ", sys.argv[1]
        xlsfilename = sys.argv[1]
	# get the file name
	rest, ext = os.path.splitext(xlsfilename)
	path, base = os.path.split(rest)
	outfilebase = base
	csvfilename = rest + ".csv"
else:
	print "importxls2csv xlsinfile   cannot use stdin"
        #csvfilename = "-"
        xlsfilename = sys.stdin
	outfilebase = "xlsimport"
	csvfilename = outfilebase + ".csv"
	exit(1)
print "csvfilename",csvfilename
if len(sys.argv)>2:
        print " log file ", sys.argv[2]
        logfile = sys.argv[2]
	# calc dataset from the file name
	rest, ext = os.path.splitext(logfile)
	path, base = os.path.split(rest)
	print "%s %s" % ("base",base)
	if base.rfind("(") > -1:
		path2 = base[:base.rfind("(") ]
		base = path2
	#logfile = "/tmp/" + base + ".log"
	if os.name == 'nt':
		logfile = "d:/tmp/" + base  + "."
	else:
		logfile = "/tmp/" + base  + "."
else:
        print " log file stdout"
        logfile = sys.stdout
#
if len(sys.argv)>3:
        print " save directory ", sys.argv[3]
        savedir = sys.argv[3]
else:
        print " savedir /tmp"
	if os.name == 'nt':
        	savedir = "d:/tmp"
	else:
        	savedir = "/tmp"
#
if isinstance(logfile, basestring):
	logfile = logfile + ".log"
	print "logfile", logfile
print datetime.datetime.now()

#redirect stdout and stderr
if isinstance(logfile, basestring):
	out = open(logfile,'a')
else:
	out = logfile
sys.stdout = out
sys.stderr = out

#wk_date = time.strftime("%d/%m/%y %H:%M:%S")
print "logfile", logfile
print datetime.datetime.now()

####################################################################################

csv_from_excel(xlsfilename,csvfilename)
####################################################################################
#revert stdin stdout and stderr
sys.stdout = sys.__stdout__
sys.stderr = sys.__stderr__

out.close()
###
