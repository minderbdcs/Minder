#!/usr/bin/env python2
import sys
import string
import time , os ,glob
import fileinput
import socket

#import kinterbasdb
#import mx.DateTime
import kinterbasdb;kinterbasdb.init(type_conv=200)
import re
import operator, math


###############################################################################
def msprintf(wkStr, wkFormat):
	print "in msprintf"
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
	print "in str:",wkMyStr
	#wkMyEscapeStr = re.escape(wkMyStr)
	#print "escape str:",wkMyEscapeStr
	#return wkStr[wkFrom -1:wkFor]
	#return wkMyStr[wkFrom -1:wkFor]
	#return wkMyEscapeStr[wkFrom -1:wkFor]
	#wkReturn = wkMyEscapeStr[wkFrom -1:wkFor]
	#print "return str:",wkReturn
	print "from", wkFrom
	wkMyFrom = wkFrom -1
	print "from2", wkMyFrom
	print "for", wkFor
	#print "return str:",wkMyStr[wkFrom -1:wkFor]
	print "return2 str:",wkMyStr[wkMyFrom]
	print "return2 str:",wkMyStr[wkMyFrom:]
	print "return3 str:",wkMyStr[wkMyFrom:][:wkFor]
	print "return2 str:",wkMyStr[wkMyFrom:wkFor]
	#return wkReturn
	#return wkMyStr[wkFrom -1:wkFor]
	return ""
###############################################################################

print msprintf("abcdefghijkl","%-04.4s")
print msprintf("abcdefghijkl","%04.4s")
print -4, 0, substr("apcdefghijkl",-4, 0)
print -4, 1, substr("apcdefghijkl",-4, 1)
print -4, 2, substr("apcdefghijkl",-4, 2)
print -4, 3, substr("apcdefghijkl",-4, 3)
print -4, 4, substr("apcdefghijkl",-4, 4)
print -3, 0, substr("apcdefghijkl",-3, 0)
print -3, 1, substr("apcdefghijkl",-3, 1)
print -3, 2, substr("apcdefghijkl",-3, 2)
print -3, 3, substr("apcdefghijkl",-3, 3)
print -3, 4, substr("apcdefghijkl",-3, 4)
print -3, 4, substr("apcdefghijkl",-3, -1)
print -3, 4, substr("apcdefghijkl",-3, -2)
print -3, 4, substr("apcdefghijkl",-3, -3)
print -3, 4, substr("apcdefghijkl",-3, -4)
