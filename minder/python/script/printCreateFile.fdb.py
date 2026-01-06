#!/usr/bin/env python2
"""
<title>
printCreateFile.py, Version 25.02.09
</title>
<long>
Updates PRINT_REQUEST in the database
Creates Print File for a Print Request
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

#import mx.DateTime
#import fdb;fdb.init(type_conv=200)
import fdb
import re
import operator, math
from datetime import datetime
import ConfigParser

###############################################################################
#1st read a NP status print request
#for this record
#
#grab the list of variables from sys_label_var
#populate the vars that can from the passed variables
#do the 1st pass for calculating the other variables
#do 2nd
#do 3rd
#by that time should have all the variables
#
#note in the functions allowed
#chr(ss)
#substr(ss,e,r)
#(expr) ? value1 : value2
#what about concat ing strings
#use %x%%y%
#
# if base filename is empty or null
#   must calculate the print file to use
#   read from the options table for this
#   the end file name may include variables
#
#then create or overwrite the file
#one by one add the lines from sys_label use the variable replacement
#
#lastly update the print_request to 'CP' (ie ready to send)
# 08/07/10 add splitbyWordandLen(seq, maxlength, memberno)
# 27/07/10 add PRNofQty
# 28/07/10 want to put the copies in the "PF" line for DP printers
#          rather than repeat files for the copies
#          so this will require a copies of labels command for a printer language
#          so use options table with a group-code of LABELPRINT code of the firmware eg DP
#          then the print command to replace in the description field eg PF
# 29/09/15 Add functions rightstr and leftstr
###############################################################################
def getPRNNewLines(wkDevice):
	# get the new line (record) delimiters for the print device
	global  con 
	wkNewLine = ""
	cur4 = con.cursor()
	query1 = """select pr.description
from options  pr   
where pr.group_code = '%s'
and   pr.code = 'LABEL RECORD SEPERATOR' 
""" % (wkDevice)
	print query1
	cur4.execute(query1)
	## get data record
	data_fields = cur4.fetchone()
	#print data_fields
	if data_fields is None:
		print "no label record seperator"
		wkNewLine = ""
	else:
		while not data_fields is None:
			#print data_fields
			if data_fields[0] is None:
				wkNewLine = ""
			else:
				wkNewLine = data_fields[0]
			print "label record seperator", wkNewLine 
			#
			data_fields = cur4.fetchone()
	if wkNewLine == "":
		# no default
		wkNewLine = "chr(10)"
	wkNewLineResult =  eval(wkNewLine)
	return wkNewLineResult
###############################################################################
def getLabelFieldWrapper():
	# get the characters used for field wrappers in the variable names
	global  con 
	wk_wrap = ""
	cur6 = con.cursor()
	query1 = """select label_field_wrapper
from control  
"""  
	print query1
	cur6.execute(query1)
	## get data record
	data_fields = cur6.fetchone()
	#print data_fields
	if data_fields is None:
		print "no label variable field wrappers"
		wk_wrap = ""
	else:
		while not data_fields is None:
			#print data_fields
			if data_fields[0] is None:
				wk_wrap = ""
			else:
				wk_wrap = data_fields[0]
			print "label field wrapper", wk_wrap 
			#
			data_fields = cur6.fetchone()
	if wk_wrap == "":
		# no default
		wk_wrap = "%"
	return wk_wrap
###############################################################################
def doStr(wkStr):
	# for the passed string replace variables using the calculated values 
	global  con, lbvVars
	wk_this_expr = wkStr
	print " in doStr with string " + wkStr
	for myprn in lbvVars:
		wk_var = myprn[0]
		wk_value =  myprn[3] 
		if string.find( wk_this_expr, wk_var) > -1:
			# variable found
			print "found var " + wk_var
			if isinstance(wk_value, str) :
				wk_test = 1
			else :
				wk_value = str(wk_value)
			print "try to replace with " + wk_value
			wk_temp_expr = string.replace(wk_this_expr, wk_var, wk_value)
			wk_this_expr = wk_temp_expr
	print "get calced string"
	return wk_this_expr
###############################################################################
def getFilename(wkType):
	# get the format filename expression to use
	# first try to look in options table for
	# group_code "LABEL_FILE" CODE wkType 
	# otherwise use the default
	# try to look in options table for
	# group_code "LABEL_FILE" CODE "DEFAULT"
	global  con 
	wk_mask = ""
	cur3 = con.cursor()
	cur7 = con.cursor()
	query1 = """select pr.description
from options  pr   
where pr.group_code = 'LABEL_FILE' 
and   pr.code = '%s'
""" % (wkType)
	print query1
	cur3.execute(query1)
	## get data record
	data_fields = cur3.fetchone()
	#print data_fields
	if data_fields is None:
		print "no file mask for type", wkType
		wk_mask = ""
	else:
		while not data_fields is None:
			#print data_fields
			if data_fields[0] is None:
				wk_mask = ""
			else:
				wk_mask = data_fields[0]
			print "file mask", wk_mask, "Type", wkType
			#
			data_fields = cur3.fetchone()
	if wk_mask == "":
		# try the default
		query1 = """select pr.description
from options  pr   
where pr.group_code = 'LABEL_FILE' 
and   pr.code = '%s'
""" % ("DEFAULT")
		print query1
		cur7.execute(query1)
		## get data record
		data_fields = cur7.fetchone()
		#print data_fields
		if data_fields is None:
			print "no file mask for type", "DEFAULT"
			wk_mask = ""
		else:
			while not data_fields is None:
				#print data_fields
				if data_fields[0] is None:
					wk_mask = ""
				else:
					wk_mask = data_fields[0]
				print "file mask", wk_mask, "Type", wkType
				#
				data_fields = cur7.fetchone()
	if wk_mask == "":
		# no default
		wk_mask = "%PRNType%.%PRNMessageId%.prn"
	return wk_mask
###############################################################################
def writePRNFile(wkFilename, wkFormat, wkCopys, wkNewLines):
	# wkNewLines is the record seperator for the device
	# get the lines from the format 
	# for each calc the line
	# and write to the file name
	# loop through this for each copy 
	global  con, lbvVars
	wk_mask = ""
	wk_firmware = ""
	wk_firmwareName = ""
	wk_firmwarePrint = ""
	firmWareVars = []
	cur5 = con.cursor()
	# get the list of firmwares that we know about
	query2 = """select pr.code, pr.description  
from options  pr   
where pr.group_code = '%s' 
order by pr.code 
""" % ("LABELPRINT")
 	print query2
	cur5.execute(query2)
	## get data record
	data_fields = cur5.fetchone()
	#print data_fields
	if data_fields is None:
		print "no firmware OPTION known", "LABELPRINT"
	else:
		while not data_fields is None:
			#print data_fields
			if data_fields[0] is None:
				wk_firmwareName = ""
			else:
				wk_firmwareName = data_fields[0]
			if data_fields[1] is None:
				wk_firmwarePrint = ""
			else:
				wk_firmwarePrint = data_fields[1]
			if wk_firmwareName <> "" and wk_firmwarePrint <> "":
				firmWareVars.append([wk_firmwareName, wk_firmwarePrint])
			data_fields = cur5.fetchone()
	#
	wkWrapper = getLabelFieldWrapper()
	wkVarName1 = "%s%s%s" % (wkWrapper, "PRNofQty",wkWrapper)
	# open file
	outFile = open(wkFilename, 'w')
	#for wkSeq in range(wkCopys):
	wkSeq = 0
	while wkSeq < wkCopys:
		# populate of copy number
		for myprn in lbvVars:
			if myprn[0] == wkVarName1:
				myprn[3] = wkSeq + 1
		query1 = """select pr.sl_line, pr.sl_firmware  
from sys_label  pr   
where pr.sl_name = '%s' 
order by pr.sl_sequence 
""" % (wkFormat)
 		print query1
		cur5.execute(query1)
		## get data record
		data_fields = cur5.fetchone()
		#print data_fields
		if data_fields is None:
			print "no lines in format", wkFormat
			wk_mask = ""
			wk_firmware = ""
		else:
			while not data_fields is None:
				#print data_fields
				if data_fields[0] is None:
					wk_mask = ""
				else:
					wk_mask = data_fields[0]
				if data_fields[1] is None:
					wk_firmware = ""
				else:
					wk_firmware = data_fields[1]
				print "line mask", wk_mask, "Format", wkFormat
				prnLine = doStr(wk_mask)
				# if prnLine matches the print command for this firmware then 
				#     add the copies to the print command
				# and add to the copies
				for myfirm in firmWareVars:
					if myfirm[0] == wk_firmware:
						#if myfirm[1] == prnLine:
						if string.find( prnLine, myfirm[1]) == 0:
							# found line for print command
							#prnLine = prnLine +  wkCopies
							#do not alter the print line
							# add to copies to stop creating more print files
							wkSeq = wkCopys
				print "line ", prnLine 
				# write line
				#outFile.write("%s\n" % (prnLine))
				wkLineMask = "%s" + wkNewLines
				print "line ", wkLineMask
				outFile.write(wkLineMask % (prnLine))
				#
				data_fields = cur5.fetchone()
		# add to copy no
		wkSeq = wkSeq + 1
	# close file
	outFile.close()
###############################################################################
def mround(wkStr, wkBy ):
	print "in mround"
	print "instring is", wkStr
	# do a round of the 2 parms
	if isinstance(wkStr, str) :
		wkMyStr = wkStr
	else :
		wkMyStr = str(wkStr)
	if wkMyStr[-1:] == '"':
		# end in a double quote
		wkMyStr = wkMyStr [:-1] 
	if wkMyStr[0:1] == '"':
		# start in a double quote
		wkMyStr = wkMyStr [1:]
	if wkMyStr[-1:] == '"':
		# end in a double quote
		wkMyStr = wkMyStr [:-1] 
	if wkMyStr[0:1] == '"':
		# start in a double quote
		wkMyStr = wkMyStr [1:]
	if wkMyStr[-1:] == '"':
		# end in a double quote
		wkMyStr = wkMyStr [:-1] 
	if wkMyStr[0:1] == '"':
		# start in a double quote
		wkMyStr = wkMyStr [1:]
	if wkMyStr == '':
		wkMyStr = '0'
	print "instring now is",wkMyStr
	#print "in str:",wkMyStr
	return " " + str(round(float(wkMyStr),wkBy)) + " "
###############################################################################
def add(wkStr, wkBy ):
	print "in add"
	print "instring is", wkStr
	# do an add of the 2 parms
	if isinstance(wkStr, str) :
		wkMyStr = wkStr
	else :
		wkMyStr = str(wkStr)
	if wkMyStr[-1:] == '"':
		# end in a double quote
		wkMyStr = wkMyStr [:-1] 
	if wkMyStr[0:1] == '"':
		# start in a double quote
		wkMyStr = wkMyStr [1:]
	if wkMyStr[-1:] == '"':
		# end in a double quote
		wkMyStr = wkMyStr [:-1] 
	if wkMyStr[0:1] == '"':
		# start in a double quote
		wkMyStr = wkMyStr [1:]
	if wkMyStr[-1:] == '"':
		# end in a double quote
		wkMyStr = wkMyStr [:-1] 
	if wkMyStr[0:1] == '"':
		# start in a double quote
		wkMyStr = wkMyStr [1:]
	if wkMyStr == '':
		wkMyStr = '0'
	print "instring now is",wkMyStr
	#print "in str:",wkMyStr
	return " " + str(operator.add(float(wkMyStr),wkBy)) + " "
###############################################################################
def sub(wkStr, wkBy ):
	print "in sub"
	print "instring is", wkStr
	# do a sub of the 2 parms
	if isinstance(wkStr, str) :
		wkMyStr = wkStr
	else :
		wkMyStr = str(wkStr)
	if wkMyStr[-1:] == '"':
		# end in a double quote
		wkMyStr = wkMyStr [:-1] 
	if wkMyStr[0:1] == '"':
		# start in a double quote
		wkMyStr = wkMyStr [1:]
	if wkMyStr[-1:] == '"':
		# end in a double quote
		wkMyStr = wkMyStr [:-1] 
	if wkMyStr[0:1] == '"':
		# start in a double quote
		wkMyStr = wkMyStr [1:]
	if wkMyStr[-1:] == '"':
		# end in a double quote
		wkMyStr = wkMyStr [:-1] 
	if wkMyStr[0:1] == '"':
		# start in a double quote
		wkMyStr = wkMyStr [1:]
	if wkMyStr == '':
		wkMyStr = '0'
	print "instring now is",wkMyStr
	#print "in str:",wkMyStr
	return " " + str(operator.sub(float(wkMyStr),wkBy)) + " "
###############################################################################
def mul(wkStr, wkBy ):
	print "in mul"
	print "instring is", wkStr
	# do a mul  of the 2 parms
	if isinstance(wkStr, str) :
		wkMyStr = wkStr
	else :
		wkMyStr = str(wkStr)
	if wkMyStr[-1:] == '"':
		# end in a double quote
		wkMyStr = wkMyStr [:-1] 
	if wkMyStr[0:1] == '"':
		# start in a double quote
		wkMyStr = wkMyStr [1:]
	if wkMyStr[-1:] == '"':
		# end in a double quote
		wkMyStr = wkMyStr [:-1] 
	if wkMyStr[0:1] == '"':
		# start in a double quote
		wkMyStr = wkMyStr [1:]
	if wkMyStr[-1:] == '"':
		# end in a double quote
		wkMyStr = wkMyStr [:-1] 
	if wkMyStr[0:1] == '"':
		# start in a double quote
		wkMyStr = wkMyStr [1:]
	if wkMyStr == '':
		wkMyStr = '0'
	print "instring now is",wkMyStr
	#print "in str:",wkMyStr
	return " " + str(operator.mul(float(wkMyStr),wkBy)) + " "
###############################################################################
def div(wkStr, wkBy ):
	print "in div"
	print "instring is", wkStr
	# do a div of the 2 parms
	if isinstance(wkStr, str) :
		wkMyStr = wkStr
	else :
		wkMyStr = str(wkStr)
	if wkMyStr[-1:] == '"':
		# end in a double quote
		wkMyStr = wkMyStr [:-1] 
	if wkMyStr[0:1] == '"':
		# start in a double quote
		wkMyStr = wkMyStr [1:]
	if wkMyStr[-1:] == '"':
		# end in a double quote
		wkMyStr = wkMyStr [:-1] 
	if wkMyStr[0:1] == '"':
		# start in a double quote
		wkMyStr = wkMyStr [1:]
	if wkMyStr[-1:] == '"':
		# end in a double quote
		wkMyStr = wkMyStr [:-1] 
	if wkMyStr[0:1] == '"':
		# start in a double quote
		wkMyStr = wkMyStr [1:]
	if wkMyStr == '':
		wkMyStr = '0'
	print "instring now is",wkMyStr
	#print "in str:",wkMyStr
	return " " + str(operator.div(float(wkMyStr),wkBy)) + " "
###############################################################################
def mod(wkStr, wkBy ):
	print "in mod"
	print "instring is", wkStr
	# do a mod of the 2 parms
	if isinstance(wkStr, str) :
		wkMyStr = wkStr
	else :
		wkMyStr = str(wkStr)
	if wkMyStr[-1:] == '"':
		# end in a double quote
		wkMyStr = wkMyStr [:-1] 
	if wkMyStr[0:1] == '"':
		# start in a double quote
		wkMyStr = wkMyStr [1:]
	if wkMyStr[-1:] == '"':
		# end in a double quote
		wkMyStr = wkMyStr [:-1] 
	if wkMyStr[0:1] == '"':
		# start in a double quote
		wkMyStr = wkMyStr [1:]
	if wkMyStr[-1:] == '"':
		# end in a double quote
		wkMyStr = wkMyStr [:-1] 
	if wkMyStr[0:1] == '"':
		# start in a double quote
		wkMyStr = wkMyStr [1:]
	if wkMyStr == '':
		wkMyStr = '0'
	print "instring now is",wkMyStr
	#print "in str:",wkMyStr
	return " " + str(operator.mod(float(wkMyStr),wkBy)) + " "
###############################################################################
def ceil(wkStr  ):
	print "in ceil"
	print "instring is", wkStr
	# do a ceil of the  parm
	if isinstance(wkStr, str) :
		wkMyStr = wkStr
	else :
		wkMyStr = str(wkStr)
	if wkMyStr[-1:] == '"':
		# end in a double quote
		wkMyStr = wkMyStr [:-1] 
	if wkMyStr[0:1] == '"':
		# start in a double quote
		wkMyStr = wkMyStr [1:]
	if wkMyStr[-1:] == '"':
		# end in a double quote
		wkMyStr = wkMyStr [:-1] 
	if wkMyStr[0:1] == '"':
		# start in a double quote
		wkMyStr = wkMyStr [1:]
	if wkMyStr[-1:] == '"':
		# end in a double quote
		wkMyStr = wkMyStr [:-1] 
	if wkMyStr[0:1] == '"':
		# start in a double quote
		wkMyStr = wkMyStr [1:]
	if wkMyStr == '':
		wkMyStr = '0'
	print "instring now is",wkMyStr
	#print "in str:",wkMyStr
	return " " + str(math.ceil(float(wkMyStr))) + " "
###############################################################################
def floor(wkStr  ):
	print "in floor"
	print "instring is", wkStr
	# do a ceil of the  parm
	if isinstance(wkStr, str) :
		wkMyStr = wkStr
	else :
		wkMyStr = str(wkStr)
	if wkMyStr[-1:] == '"':
		# end in a double quote
		wkMyStr = wkMyStr [:-1] 
	if wkMyStr[0:1] == '"':
		# start in a double quote
		wkMyStr = wkMyStr [1:]
	if wkMyStr[-1:] == '"':
		# end in a double quote
		wkMyStr = wkMyStr [:-1] 
	if wkMyStr[0:1] == '"':
		# start in a double quote
		wkMyStr = wkMyStr [1:]
	if wkMyStr[-1:] == '"':
		# end in a double quote
		wkMyStr = wkMyStr [:-1] 
	if wkMyStr[0:1] == '"':
		# start in a double quote
		wkMyStr = wkMyStr [1:]
	if wkMyStr == '':
		wkMyStr = '0'
	print "instring now is",wkMyStr
	#print "in str:",wkMyStr
	return " " + str(math.floor(float(wkMyStr))) + " "
###############################################################################
def mfprintf(wkStr, wkFormat):
	print "in mpercent"
	# do a % - like sprintf
	if isinstance(wkStr, str) :
		wkMyStr = wkStr
	else :
		wkMyStr = str(wkStr)
	if wkMyStr[-1:] == '"':
		# end in a double quote
		wkMyStr = wkMyStr [:-1] 
	if wkMyStr[0:1] == '"':
		# start in a double quote
		wkMyStr = wkMyStr [1:]
	if wkMyStr[-1:] == '"':
		# end in a double quote
		wkMyStr = wkMyStr [:-1] 
	if wkMyStr[0:1] == '"':
		# start in a double quote
		wkMyStr = wkMyStr [1:]
	if wkMyStr[-1:] == '"':
		# end in a double quote
		wkMyStr = wkMyStr [:-1] 
	if wkMyStr[0:1] == '"':
		# start in a double quote
		wkMyStr = wkMyStr [1:]
	if wkMyStr == '':
		wkMyStr = '0'
	#return wkReturn
	return   wkFormat % float(wkMyStr) 
###############################################################################
def miprintf(wkStr, wkFormat):
	print "in mpercent"
	# do a % - like sprintf
	if isinstance(wkStr, str) :
		wkMyStr = wkStr
	else :
		wkMyStr = str(wkStr)
	if wkMyStr[-1:] == '"':
		# end in a double quote
		wkMyStr = wkMyStr [:-1] 
	if wkMyStr[0:1] == '"':
		# start in a double quote
		wkMyStr = wkMyStr [1:]
	if wkMyStr[-1:] == '"':
		# end in a double quote
		wkMyStr = wkMyStr [:-1] 
	if wkMyStr[0:1] == '"':
		# start in a double quote
		wkMyStr = wkMyStr [1:]
	if wkMyStr[-1:] == '"':
		# end in a double quote
		wkMyStr = wkMyStr [:-1] 
	if wkMyStr[0:1] == '"':
		# start in a double quote
		wkMyStr = wkMyStr [1:]
	if wkMyStr == '':
		wkMyStr = '0'
	#return wkReturn
	return   wkFormat % int(wkMyStr) 
###############################################################################
def msprintf(wkStr, wkFormat):
	print "in mpercent"
	# do a % - like sprintf
	if isinstance(wkStr, str) :
		wkMyStr = wkStr
	else :
		wkMyStr = str(wkStr)
	#return wkReturn
	return   wkFormat % str(wkMyStr) 
###############################################################################
def substr(wkStr, wkFrom, wkFor):
	print "in substr"
	# do a substr
	if isinstance(wkStr, str) :
		wkMyStr = wkStr
	else :
		wkMyStr = str(wkStr)
	# add slashes to string
	#print "in str:",wkMyStr
	#wkMyEscapeStr = re.escape(wkMyStr)
	#print "escape str:",wkMyEscapeStr
	#return wkStr[wkFrom -1:wkFor]
	#return wkMyStr[wkFrom -1:wkFor]
	#return wkMyEscapeStr[wkFrom -1:wkFor]
	#wkReturn = wkMyEscapeStr[wkFrom -1:wkFor]
	#print "return str:",wkReturn
	#return wkReturn
	return wkMyStr[wkFrom -1:wkFor]
###############################################################################
def rightstr(wkStr, wkFrom):
	print "in rightstr"
	# do a substr using the right end of the string
	if isinstance(wkStr, str) :
		wkMyStr = wkStr
	else :
		wkMyStr = str(wkStr)
	return wkMyStr[wkFrom:]
###############################################################################
def leftstr(wkStr, wkFrom):
	print "in leftstr"
	# do a substr using the right end of the string
	if isinstance(wkStr, str) :
		wkMyStr = wkStr
	else :
		wkMyStr = str(wkStr)
	return wkMyStr[:wkFrom]
###############################################################################
def assignIf(wkTest, wkTrue, wkFalse):
	# do a test
	if wkTest:
		wkValue = wkTrue
	else:
		wkValue = wkFalse
	return wkValue
###############################################################################
def now():
	# get the current date
	wkDateTime = time.strftime("%d/%m/%y %H:%M:%S")
	return wkDateTime
###############################################################################
def splitbyWordandLen(seq, maxlength, memberno):
	s = string.split(seq)
	j = 0
	k = 0
	buffer1 = ""
	out = []
	# j = s index
	# k = current length
	while j < len(s):
		if k == 0  and  len(s[j])  <= maxlength:
			buffer1 = buffer1 + s[j]
			k = k +  len(s[j])
			j = j + 1
		else:
			if (k + len(s[j]) + 1) <= maxlength:
				buffer1 = buffer1 + " " + s[j]
				k = k + 1 + len(s[j])
				j = j + 1
			else:
				# wont fit on current line
				out.append( buffer1)
				buffer1 = ""	
				k = 0
				if (k + len(s[j]))  <= maxlength:
					buffer1 = s[j]
					k = k + len(s[j])
					j = j + 1
				else:
					# the word is too long to fit on a line so split it
					buffer2 = s[j]
					s[j] = buffer2[maxlength:]
					buffer1 = buffer2[:maxlength]
					out.append( buffer1)
					buffer1 = ""	
					k = 0
	out.append( buffer1)
	buffer1 = ""	
	k = 0
	if memberno < 0:
		return out
	else:
		if memberno < len(out):
			return out[memberno]
		else:
			return ""
###############################################################################
def trim(wkStr ):
	# do a split 
	if isinstance(wkStr, str) :
		wkMyStr = wkStr
	else :
		wkMyStr = str(wkStr)
	return wkMyStr.strip()
###############################################################################
def rtrim(wkStr ):
	# do a rsplit 
	if isinstance(wkStr, str) :
		wkMyStr = wkStr
	else :
		wkMyStr = str(wkStr)
	return wkMyStr.rstrip()
###############################################################################
def ltrim(wkStr ):
	# do a lsplit 
	if isinstance(wkStr, str) :
		wkMyStr = wkStr
	else :
		wkMyStr = str(wkStr)
	return wkMyStr.lstrip()
###############################################################################
def llen(wkStr ):
	# do a llen  the length of a string
	if isinstance(wkStr, str) :
		wkMyStr = wkStr
	else :
		wkMyStr = str(wkStr)
	return len(wkMyStr)
###############################################################################
def mint(wkStr ):
	# get the ist integer part of a string
	if isinstance(wkStr, str) :
		wkMyStr = wkStr
	else :
		wkMyStr = str(wkStr)
	wkMyInts = re.findall(r'\d+', wkMyStr)
	if len(wkMyInts) >0:
		return wkMyInts[0]
	else:
		return '0'
###############################################################################
def TSCesc(wkStr ):
	# only used for TSC firmware
	# escape any quotes in the string
	if isinstance(wkStr, str) :
		wkMyStr = wkStr
	else :
		wkMyStr = str(wkStr)
	wkMyList = wkMyStr.split('"')
	wkMyResult = ""
	for i in wkMyList:
		wkMyResult += str(i) + """\["]"""
	wkMyResult = wkMyResult[:-4]
	return wkMyResult
###############################################################################
def doPass(wkPass):
	# pass variables using the previous pass values to construct this one
	# now do the next pass 
	# this relies on pass  less than wkPass values
	# where value is ""
	global  con, lbvVars
	wk_result = True
	for myprn in lbvVars:
		if myprn[2] == wkPass and myprn[3] == "":
			# expression is myprn[1]
			wk_this_expr = myprn[1]
			# now look for previous pass vars in the expression
			# if there are any replace the var by the value
			#	#do chr(ss) function
			#	#do substr(x,y,z) function
			#	#do (sss) ? valuet : valuef
			#	# so use   y = assignIf( test, truevalue, falsevalue)
			#	# here use x= eval(string) for all 3
			#	#do  now() function for current time
			#	#do  splitbyWordandLen(seq, maxlength, memberno) for string seperation by word
			#	#do mround(x,y) function
			#	#do add(x,y) function
			#	#do sub(x,y) function
			#	#do mul(x,y) function
			#	#do div(x,y) function
			#	#do mod(x,y) function
			#	#do ceil(x) function
			#	#do floor(x) function
			#	#do trim(x) function
			#	#do ltrim(x) function
			#	#do rtrim(x) function
 			#	mfprintf(wkStr, wkFormat):$
			# 	miprintf(wkStr, wkFormat):$
			# 	msprintf(wkStr, wkFormat):$
			# 	assignIf(wkTest, wkTrue, wkFalse):$
			# 	llen(wkStr ):$
			wk_changed = False
			for myprevprn in lbvVars:
				if myprevprn[2] < wkPass:
					wk_var = myprevprn[0]
					wk_testvalue = myprevprn[3] 
					if isinstance(wk_testvalue, str) :
						wkValueStr = wk_testvalue
					else :
						wkValueStr = str(wk_testvalue)
					if wkValueStr[-1:] == '"':
						# end in a double quote
						wkValueStr = wkValueStr + " "
					if wkValueStr[0:1] == '"':
						# start in a double quote
						wkValueStr = " " + wkValueStr
					wk_value = '"""' + wkValueStr + '"""'
					if string.find( wk_this_expr, wk_var) > -1:
						#print "variable found"
						# variable found
						wk_temp_expr = string.replace(wk_this_expr, wk_var, wk_value)
						wk_this_expr = wk_temp_expr
						wk_changed = True
			if wk_changed:
				myprn[1] = wk_this_expr 
			try:
				wk_new_value = eval(wk_this_expr)
			except:
				# error occurred
				print "error to doing eval",wk_this_expr
				wk_result = False
				wk_new_value = ""
			wk_testpic = myprn[5] 
			if wk_testpic != "":
				wk_testtype = wk_testpic[-1:]
				wk_testvalue = wk_new_value 
				#print "testpic", wk_testpic
				#print "testtype", wk_testtype
				#print "testvalue", wk_testvalue
				if isinstance(wk_testvalue, str) :
					wkValueStr = wk_testvalue
				else :
					wkValueStr = str(wk_testvalue)
				if string.find( "aAeEfFgG", wk_testtype ) > -1 :
					if len(wkValueStr) == 0:
						wkValueStr = 0
					wk_temp_expr = wk_testpic %  float(wkValueStr)
				else:
					if string.find( "lLhidujz", wk_testtype ) > -1 :
						if len(wkValueStr) == 0:
							wkValueStr = 0
						wk_temp_expr = wk_testpic %  int(wkValueStr)
					else:
						wk_temp_expr = wk_testpic %  wk_new_value
				wk_this_expr = wk_temp_expr
			else:
				wk_this_expr = wk_new_value
			#myprn[3] = wk_new_value
			myprn[3] = wk_this_expr
	print "get passed values"
	return wk_result
###############################################################################
def getFormatVars(wkFormat):
	# get the vars for a format
	global  con, lbvVars
	cur2 = con.cursor()
	cur8 = con.cursor()
	#lbvVars = []
	# first get variables for this format
	#query1 = """select pr.slv_name, pr.slv_expression, pr.slv_sequence, pr.slv_default, pr.slv_pick 
	#from sys_label_var pr   
	#where pr.sl_name = '%s'
	#order by slv_sequence, slv_name
	# have to get the 1st firmware for this format seperately
	query2 = """select first 1  pl.sl_firmware
from sys_label pl   
where pl.sl_name = '%s'
order by sl_sequence 
""" % (wkFormat)
	print query2
	lbvFirmware = ""
	cur8.execute(query2)
	## get data record
	data_fields2 = cur8.fetchone()
	#print data_fields
	if data_fields2 is None:
		print "no vars "
		lbvFirmware = ""
	else:
		while not data_fields2 is None:
			wk_now = datetime.now()
			wk_date = wk_now.strftime("%d/%m/%y %H:%M:%S.%f")
			print "in get formatvars firmware", wk_date
			#print data_fields
			if data_fields2[0] is None:
				lbvFirmware = ""
			else:
				lbvFirmware = data_fields2[0]
			print "Firmware", lbvFirmware 
			data_fields2 = cur8.fetchone()
	#
	query1 = """select pr.slv_name, pr.slv_expression, pr.slv_sequence, pr.slv_default, pr.slv_pick  
from sys_label_var pr   
where pr.sl_name = '%s'
order by slv_sequence, slv_name
""" % (wkFormat)
	print query1
	lbvValue = ""
	cur2.execute(query1)
	## get data record
	data_fields = cur2.fetchone()
	#print data_fields
	if data_fields is None:
		print "no vars "
		lbvName = ""
		lbvExpression = ""
		lbvSequence = 0
		lbvDefault = ""
		lbvPic = ""
		#lbvFirmware = ""
	else:
		while not data_fields is None:
			wk_now = datetime.now()
			wk_date = wk_now.strftime("%d/%m/%y %H:%M:%S.%f")
			print "in get formatvars", wk_date
			#print data_fields
			if data_fields[0] is None:
				lbvName = ""
			else:
				lbvName = data_fields[0]
			if data_fields[1] is None:
				lbvExpression = ""
			else:
				lbvExpression = data_fields[1]
			if data_fields[2] is None:
				lbvSequence = 0
			else:
				lbvSequence = data_fields[2]
			if data_fields[3] is None:
				lbvDefault = ""
			else:
				lbvDefault = data_fields[3]
			if data_fields[4] is None:
				lbvPic = ""
			else:
				lbvPic = data_fields[4]
			#if data_fields[5] is None:
			#	lbvFirmware = ""
			#else:
			#	lbvFirmware = data_fields[5]
			print "varName", lbvName, "Expression", lbvExpression, "sequence", lbvSequence,"default",lbvDefault,"format", wkFormat, "Picture", lbvPic
			#lbvVars.append([lbvName, lbvExpression, lbvSequence, lbvValue, lbvDefault, lbvPic])
			lbvVars.append([lbvName, lbvExpression, lbvSequence, lbvValue, lbvDefault, lbvPic, lbvFirmware])
			#print lbvVars
			#
			data_fields = cur2.fetchone()
	#print "read vars"
	#print lbvVars
###############################################################################
def getRequest(  ):
	# get the requests for labels to create files
	global  con, lbvVars
	print "Start getRequest"
	print time.asctime()
	cur = con.cursor()
	cur2 = con.cursor()
	# first get the next file name to send
	#query1 = """select first 5 pr.message_id, pr.base_file_name, pr.device_id , se.working_directory, se.ip_address, pr.prn_type, pr.prn_copy, pr.prn_data , o1.description, pr.person_id """
	#query1 = """select pr.message_id, pr.base_file_name, pr.device_id , se.working_directory, se.ip_address, pr.prn_type, pr.prn_copy, pr.prn_data , o1.description, pr.person_id """
	query1 = """select pr.message_id, pr.base_file_name, pr.device_id , se.working_directory, se.ip_address, pr.prn_type, pr.prn_copy, pr.prn_data , o1.description, pr.person_id 
from print_requests pr   
join sys_equip se  on  se.device_id = pr.device_id
join options o1 on o1.group_code = (pr.device_id || '_FORMAT') and o1.code = pr.prn_type
where pr.request_status = 'NP'
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
		prnType = ""
		prnCopys = 1
		prnData = ""
		prnFormat = ""
		prnUserId = ""
		prnNow = ""
	else:
		while not data_fields is None:
			wk_now = datetime.now()
			wk_date = wk_now.strftime("%d/%m/%y %H:%M:%S.%f")
			print "got a request", wk_date
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
				prnType = ""
			else:
				prnType = data_fields[5]
			if data_fields[6] is None:
				prnCopys = 1
			else:
				prnCopys = data_fields[6]
			if data_fields[7] is None:
				prnData = ""
			else:
				prnData = data_fields[7]
			if data_fields[8] is None:
				prnFormat = ""
			else:
				prnFormat = data_fields[8]
			if data_fields[9] is None:
				prnUserId = ""
			else:
				prnUserId = data_fields[9]
			#
			print "message", message_id, "fileName", prnFile, "Device", prnDevice, "Folder", prnFolder, "Type", prnType, "data", prnData , "format", prnFormat, "user", prnUserId
			# now get the full variables list for the format
			lbvVars = []
			wk_now = datetime.now()
			wk_date = wk_now.strftime("%d/%m/%y %H:%M:%S.%f")
			print "before get formatvars", wk_date
			getFormatVars(prnFormat)
			wk_now = datetime.now()
			wk_date = wk_now.strftime("%d/%m/%y %H:%M:%S.%f")
			print "after get formatvars", wk_date
			print "get vars"
			print lbvVars
			#repr(lbvVars)
			# do pass 0 
			# this puts in the passed vars
			myparms = prnData.split("|")
			myparmslen = len(myparms)
			for myvar in myparms:
				if len(myvar) > 0:
					myparmvalue = myvar.split("=")
					# now have the var in myparmvalue[0]
					# and passed value in myparmvalue[1]
					for myprn in lbvVars:
						if myprn[0] == myparmvalue[0]:
							# myprn[0] is the name
							# myprn[1] is the expression
							# myprn[2] is the sequence
							# myprn[3] is the value
							# myprn[4] is the default value
							# myprn[5] is the output format
							# myprn[6] is the firmware
							if myprn[1] == "":
								# the variable has an expression so keep it 
								myprn[3] = myparmvalue[1]
							#myprn[3] = myparmvalue[1]
			print "get passed values"
			#repr(lbvVars)
			print lbvVars
			# if var PRNMessageId or PRNType already exist then add prn to var name
			# to stop use of the vars
			wkWrapper = getLabelFieldWrapper()
			for myprn in lbvVars:
				wkVarName1 = "%s%s%s" % (wkWrapper, "PRNMessageId",wkWrapper)
				wkVarName2 = "%s%s%s" % (wkWrapper, "PRNType",wkWrapper)
				wkVarName3 = "%s%s%s" % (wkWrapper, "PRNNow",wkWrapper)
				wkVarName4 = "%s%s%s" % (wkWrapper, "PRNUserId",wkWrapper)
				wkVarName5 = "%s%s%s" % (wkWrapper, "PRNofQty",wkWrapper)
				wkVarName6 = "%s%s%s" % (wkWrapper, "PRNCopys",wkWrapper)
				#if myprn[0] == "PRNType" or myprn[0] == "PRNMessageId" or myprn[0] == "PRNNow" or myprn[0] == "PRNUserId":
				if myprn[0] == wkVarName1 or myprn[0] == wkVarName2 or myprn[0] == wkVarName3 or myprn[0] == wkVarName4 or myprn[0] == wkVarName5 or myprn[0] == wkVarName6:
					myprn[0] = "PRN" + myprn[0] 
			# must put in the default message_id
			#lbvVars.append(["%PRNMessageId%", "", 0, message_id, 0])
			wkVarName = "%s%s%s" % (wkWrapper, "PRNMessageId",wkWrapper)
			#lbvVars.append([wkVarName, "", 0, message_id, 0, ""])
			lbvVars.append([wkVarName, "", 0, message_id, 0, "", ""])
			# must put in the default user_id
			wkVarName = "%s%s%s" % (wkWrapper, "PRNUserId",wkWrapper)
			#lbvVars.append([wkVarName, "", 0, prnUserId, 0, ""])
			lbvVars.append([wkVarName, "", 0, prnUserId, 0, "", ""])
			# must put in the run date 
			wkVarName = "%s%s%s" % (wkWrapper, "PRNNow",wkWrapper)
			prnNow = now()
			#lbvVars.append([wkVarName, "", 0, prnNow, 0, ""])
			lbvVars.append([wkVarName, "", 0, prnNow, 0, "", ""])
			# must put in the default print type
			#lbvVars.append(["%PRNType%", "", 0, prnType, 0])
			wkVarName = "%s%s%s" % (wkWrapper, "PRNType",wkWrapper)
			#lbvVars.append([wkVarName, "", 0, prnType, 0, ""])
			lbvVars.append([wkVarName, "", 0, prnType, 0, "", ""])
			#lbvVars.append(["%PRNType%", "", 0, prnType, 0])
			# must put in the of qty   
			wkVarName = "%s%s%s" % (wkWrapper, "PRNofQty",wkWrapper)
			prnofQty = 0
			#lbvVars.append([wkVarName, "", 0, prnofQty, 0 , ""])
			lbvVars.append([wkVarName, "", 0, prnofQty, 0 , "", ""])
			# must put in the copy qty 
			wkVarName = "%s%s%s" % (wkWrapper, "PRNCopys",wkWrapper)
			#lbvVars.append([wkVarName, "", 0, prnCopys, 1, ""])
			lbvVars.append([wkVarName, "", 0, prnCopys, 1, "", ""])
			# now do the passes populating from previous passes data
			# where value is ""
			# need to clear out passed parameters with an expression
			wkVarName = "%s%s%s" % (wkWrapper, "DEFAULT",wkWrapper)
			wkUseDefault = False
			# if DEFAULT iexists in passed parms then defaulting allowed
			for myvar in myparms:
				if len(myvar) > 0:
					myparmvalue = myvar.split("=")
					# now have the var in myparmvalue[0]
					# and passed value in myparmvalue[1]
					if wkVarName == myparmvalue[0]:
						wkUseDefault = True
			#if only 1 param passed and it has the default value then treat this as default is true
			if myparmslen == 2:
				for myvar in myparms:
					if len(myvar) > 0:
						myparmvalue = myvar.split("=")
						for myprn in lbvVars:
							if myprn[0] == myparmvalue[0]:
								if myprn[3] == myprn[4]:
									wkUseDefault = True
			# do pass end 
			# this puts in the default value for any missed parameters
			if wkUseDefault:
				for myprn in lbvVars:
					if myprn[3] == "":
						myprn[3] = myprn[4]
			print lbvVars
			wk_isok = True
			# do the TSC prepass
			for myprn in lbvVars:
				if myprn[6] == "TSC":
					# replace the value with TSCesc of the value
					wk_new_str = myprn[3]
					try:
						wk_new_str = TSCesc(myprn[3])
					except:
						wk_isok = False
						wk_new_str = myprn[3]
						print "error in doing TSCesc",myprn[3]
					if wk_new_str != myprn[3]:
						myprn[3] = wk_new_str
			print lbvVars
			for wkPass in range(1,5):
				wk_now = datetime.now()
				wk_date = wk_now.strftime("%d/%m/%y %H:%M:%S.%f")
				print "before doPass", wk_date
				#doPass( wkPass)
				wk_isok = wk_isok and doPass( wkPass)
				wk_now = datetime.now()
				wk_date = wk_now.strftime("%d/%m/%y %H:%M:%S.%f")
				print "after doPass", wk_date
			print lbvVars
			# now should have all the vars
			# deal with file name
			wkFileChanged = False
			if prnFile == "":
				# dont have a filename yet
				prnFilenameMask = getFilename(prnType)
				print "file mask", prnFilenameMask
				prnFile = doStr(prnFilenameMask)
				wkFileChanged = True
			prnRealFile = prnFolder + prnFile
			prnNewLines = getPRNNewLines(prnDevice)
			print "Real file name", prnRealFile
			# now line by line read sys_label
			# replace the vars
			# and write the output
			wk_now = datetime.now()
			wk_date = wk_now.strftime("%d/%m/%y %H:%M:%S.%f")
			print "before write file", wk_date
			writePRNFile(prnRealFile, prnFormat, prnCopys, prnNewLines)
			wk_now = datetime.now()
			wk_date = wk_now.strftime("%d/%m/%y %H:%M:%S.%f")
			print "after write file", wk_date
			# update the status to CP for the send process
			#if wkFileChanged:
			#	query2 = """update print_requests set request_status = 'CP', base_file_name = '%s' where message_id = '%s' """ % (prnFile, message_id)
			#else:
			#	query2 = """update print_requests set request_status = 'CP' where message_id = '%s' """ % message_id
			if wk_isok:
				if wkFileChanged:
					query2 = """update print_requests set request_status = 'CP', base_file_name = '%s' where message_id = '%s' """ % (prnFile, message_id)
				else:
					query2 = """update print_requests set request_status = 'CP' where message_id = '%s' """ % message_id
			else:
				query2 = """update print_requests set request_status = 'EN' where message_id = '%s' """ % message_id
			print query2
			cur2.execute(query2)
			wk_now = datetime.now()
			wk_date = wk_now.strftime("%d/%m/%y %H:%M:%S.%f")
			print "after update",wk_date
			data_fields = cur.fetchone()
	print "End getRequest"
	print time.asctime()
###############################################################################
#connect to db

if os.name == 'nt':
	logfile = "d:/tmp/printWrite."
else:
	logfile = "/tmp/printWrite."
havelog = 1;

mydb = "minder"
myHost = "localhost"
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
		myHost = myparms[1]
	if "user"  == myparms[0]:
		myuser = myparms[1]
	if "passwd"  == myparms[0]:
		mypasswd = myparms[1]
	if "tmp"  == myparms[0]:
		logfile  = myparms[1] + "/printWrite."
	if "condwait"  == myparms[0]:
		wkConduitWait = int(myparms[1] )
	if "condlimit"  == myparms[0]:
		wkConduitLimit = int( myparms[1] )
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
print "myhost", myHost
print "myuser", myuser
print "mypassword", mypasswd
con = fdb.connect(
	dsn=myHost+":"+ mydb,
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
lbvVars = []
#read std or 1st input parm

# for records in print request NP status
# create Print Files
getRequest( )

wk_now = datetime.now()
wk_date = wk_now.strftime("%d/%m/%y %H:%M:%S.%f")
print "after getRequests",wk_date
con.commit()
out.flush()

wkConduitEvents = 0

wk_now = datetime.now()
wk_date = wk_now.strftime("%d/%m/%y %H:%M:%S.%f")
print "after commit and flush",wk_date

print "before register events",wk_date
# ok now start to wait for event
MY_EVENT = [ 'PRN_REQUEST_NP', 'PRN_REQUEST_NP_END'  ]
conduit = con.event_conduit(MY_EVENT)
print "after register events"
wk_now = datetime.now()
wk_date = wk_now.strftime("%d/%m/%y %H:%M:%S.%f")
print wk_date
print wkConduitEvents

#
#if 1:
while 1:
	print "Start Event loop"
	wk_now = datetime.now()
	wk_date = wk_now.strftime("%d/%m/%y %H:%M:%S.%f")
	print wk_date
	print wkConduitEvents
	out.flush()
	# ok now start to wait for event
	#MY_EVENT = [ 'PRN_REQUEST_NP', 'PRN_REQUEST_NP_END'  ]
	#conduit = con.event_conduit(MY_EVENT)
	print "about to wait for %s\n" % MY_EVENT
	print wk_date
	# ok now start to wait for event
	#MY_EVENT = [ 'PRN_REQUEST_NP', 'PRN_REQUEST_NP_END'  ]
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
		if result['PRN_REQUEST_NP'] > 0:
			# for records in print request NP status
			# create print file
			getRequest( )
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
			if result['PRN_REQUEST_NP_END'] > 0:
				conduit.flush()
				#conduit.close()
				break
			else:
				# got unexpected timeout but not via null result
				print "got a timeout not in results \n" 
				getRequest( )
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
