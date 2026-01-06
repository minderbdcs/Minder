#!/usr/bin/env python2
"""
<title>
reformatcsv.py, Version 03.10.07
</title>
<long>
Exports imported file into a fixed width file 
<br>
Parameters: <tt>input file</tt><tt>log file</tt>
<tt>params for widths seperated by | - with a code for a function to perform seperated by ;</tt>
<br>
</long>
"""
# 07.04.08
# add a funtion after the length for upshifting
#
import sys
import string
import time , os ,glob
import fileinput

#redirect stdout and stderr

print len(sys.argv)

print sys.argv

if len(sys.argv)>=2:
	print "reformatcsv ", sys.argv[1]
	infile = sys.argv[1]
	havein = 1;
else:
	print "reformatcsv stdin"
	infile = '-'
	havein = 0;

if len(sys.argv)>=3:
	print "log ", sys.argv[2]
	logfile = sys.argv[2]
	havelog = 1;
else:
	print "log stdin"
	havelog = 0;

if len(sys.argv)>=4:
	print "lengths ", sys.argv[3]
	lengths = sys.argv[3]
else:
	print "no lengths"
	lengths = "0"
lengthbuffer = lengths.split('|')
for zindex in range(0,len(lengthbuffer)):
	if lengthbuffer[zindex] == '':
		lengthbuffer[zindex] = "0"
	lengthfunc = lengthbuffer[zindex].split(';')
	lengthbuffer[zindex] = lengthfunc

print lengthbuffer	

#
#redirect stdout and stderr
if (havelog == 1):
	out = open(logfile,'a')
	sys.stdout = out
	sys.stderr = out

rest, ext = os.path.splitext(infile)
path, base = os.path.split(rest)
print "%s %s" % ("base",base)
if base.rfind("(") > -1:
	path2 = base[:base.rfind("(") ]
	base = path2
wk_dataset = base
prnformat = "%s%s" % (base, ".tsv")
prnformat2 = "%s%s" % (base, ".psv")
prtfile = os.path.join(path, prnformat)
prtfile2 = os.path.join(path, prnformat2)
print prtfile
#
prt = open(prtfile,'w')

print "%s %s" % ("dataset",wk_dataset)

wk_date = time.strftime("%d/%m/%y")
wk_time = time.strftime("%H")
wk_hour = int(wk_time)
wk_line = 0	
#read std or 1st input parm
for line in fileinput.input(infile):
	wk_line = wk_line + 1
	print "line",line
	#wk_code = line
	#if wk_line == 1:
	#	print "1st line" 
	#	print "dont want this"
	#else:
	if wk_line > 0:
		# if line ends \r want the \r out
		if line[-1:] == chr(10):
			#1st field starts quote
			print "line ends \r"
			line = line[:-1]
		buffer = line.split(',')
		fields = list()
		#buffer = line.split('","')
		wk_end = len(buffer) -1
		#print "wk_end",wk_end
		if wk_end < 1:
			break
		buffer[wk_end] = buffer[wk_end][:-1]
		wk_started = "F"
		wk_start = 0
		wk_fielded = "F"
		for xindex in range(0,len(buffer)):
			print xindex
			wk_str = buffer[xindex]
			print wk_str
			wk_fielded = "F"
			# want to trim first and last white space
			wk_str = wk_str.strip()
			if wk_str[:1] == '"' and wk_str[-1] == '"':
				#field starts and ends quote
				print "field starts and ends quote"
				buffer[xindex] = wk_str[1:-1]
				wk_str = buffer[xindex]
				wk_started = "F"
				fields.append(wk_str)
				wk_fielded = "T"
			if wk_str[:1] == '"' :
				#field starts quote
				print "field starts quote"
				buffer[xindex] = wk_str[1:]
				wk_str = buffer[xindex]
				wk_started = "T"
				#print "started T"
				wk_start = xindex
			if wk_str[-1:] == '"' :
				#field ends quote
				print "field ends quote"
				#print "y",xindex,buffer[xindex]
				buffer[xindex] = wk_str[:-1]
				#print "y",xindex,buffer[xindex]
				wk_str = buffer[xindex]
				# must deal with started T here
				if wk_started == "T":
					# must concat fields
					# from wk_start to xindex
					# into one field
					wk_bufstr = ""
					for yindex in range(wk_start, xindex+1):
						#print yindex
						wk_bufstr = wk_bufstr + buffer[yindex] + ","
						#print "bufstr",wk_start,xindex,wk_bufstr
					# remove last comma
					if len(wk_bufstr) > 0:
						wk_bufstr = wk_bufstr[:-1]
						#print "bufstr",wk_start,xindex,wk_bufstr
					fields.append(wk_bufstr)
					wk_fielded = "T"
					#print "y",wk_start,buffer[wk_start]
				wk_started = "F"
			if wk_fielded == "F" and wk_started == "F" :
				#field not in list and not added
				fields.append(wk_str)
				wk_fielded = "T"
			#if wk_str.find("'") > -1:
			#	wk_str = wk_str.replace("'","`")
			#	buffer[xindex] = wk_str
			print "x",xindex,buffer[xindex]
			#wk_str =  buffer[10]
			#if wk_str.find('"') > -1:
			#	wk_str = wk_str.replace('"',"")
			#	buffer[10] = wk_str
	 		#print buffer
	 		#print fields
		for zindex in range(0,len(fields)):
			if len(lengthbuffer) < len(fields):
				 #lengthbuffer.append("0")
				 lengthbuffer.append(["0"])
		print lengthbuffer	
		for zindex in range(0,len(fields)):
			#wk_format_num = "%d" % (int(lengthbuffer[zindex]))
			if len(lengthbuffer[zindex]) > 1:
				wk_format_num = "%d" % (int(lengthbuffer[zindex][0]))
				wk_format_func = lengthbuffer[zindex][1]
			else:
				print "else 184 :",lengthbuffer[zindex]
				print "else 184 :",lengthbuffer[zindex][0]
				print "else 184 :",int(lengthbuffer[zindex][0])
				wk_format_num = "%d" % (int(lengthbuffer[zindex][0]))
				wk_format_func = ""
			print wk_format_num
			wk_format = "%-" + wk_format_num + "." + wk_format_num + "s"
			print wk_format
			if wk_format_func == "U":
				wk_format_data = wk_format % (fields[zindex])
				wk_format_data = wk_format_data.upper()
			else:
				wk_format_data = wk_format % (fields[zindex])
			#print "data:" ,wk_format % (fields[zindex])
			print "data:" ,wk_format_data
			#prt.write(wk_format % (fields[zindex]))
			prt.write(wk_format_data)
		prt.write("\n")

prt.close()
# rename the file for commander
os.rename(prtfile,prtfile2)

#revert stdin stdout and stderr
if (havelog == 1):
	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__
	out.close()

###
