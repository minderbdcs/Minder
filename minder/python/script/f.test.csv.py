import sys,os
import csv
filename = "f.test.csv"
reader = csv.reader(open(filename,'rb'),delimiter=',',quotechar='"')
try:
	for x in reader:
		print x
except csv.Error, e:
	sys.exit('file %s, line %d: %s' % (filename, reader.line_num, e))

