#!/usr/bin/env python2
"""
<title>
importtest.py, Version 26.03.03
</title>
<long>
Inserts records into transactions table
<br>
Paramters: <tt> filename </tt>
<br>
This scans the file <tt>filename</tt> for data 
and puts the text into the database
<br>
</long>
"""
import sys
import string
import fileinput
import getpass, sys, time , os

import urllib

#print "importfile filename logfilename "
if len(sys.argv)>0:
	print "import file ", sys.argv[1]
else:
	print "import file stdin"

if len(sys.argv)>1:
        print "log file ", sys.argv[2]
        logfile = sys.argv[2]
else:
        print "log file stdout"
        logfile = sys.__stdout__
#
#redirect stdout and stderr
out = open(logfile,'a')
sys.stdout = out
sys.stderr = out


#read std or 1st input parm
for line in fileinput.input():
	tran_type = line[:4]
	tran_class = line[4:5] #1
	tran_date = line[5:13] #8
	tran_time = line[13:19] #6
	tran_item = line[19:49] #30
	tran_item = tran_item.strip()
	tran_locn = line[49:59] #10
	tran_locn = tran_locn.strip()
	tran_sublocn = line[59:69] #10
	tran_sublocn = tran_sublocn.strip()
	tran_ref = line[69:109] #40
	tran_ref = tran_ref.strip()
	tran_qty = line[109:119] #10
	tran_user = line[119:127] #8
	tran_user = tran_user.strip()
	tran_device = line[127:129] #2
	tran_source = line[129:138] #9
	print "trans type",tran_type,"class",tran_class,"date",tran_date
	print "time",tran_time,"item",tran_item,"locn",tran_locn
	print "sublocn",tran_sublocn,"ref",tran_ref,"qty",tran_qty
	print "user",tran_user,"dev",tran_device,"source",tran_source

	params = urllib.urlencode({'trans':line})
	print "urlled:",params
	filedata = urllib.urlopen("http://192.168.0.170/dbq/trans.php?%s" %params)
	print filedata.read()
	filedata.close()

print "end - closed database"
#revert stdin stdout and stderr
sys.stdout = sys.__stdout__
sys.stderr = sys.__stderr__

out.close()
###
