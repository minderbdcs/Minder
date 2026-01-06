#!/usr/bin/env python
"""
<title>
pilthumb.py, Version 09.04.08
</title>
<long>
Create Thumnails for images 
<br>
Parameters: <tt>thumb size x</tt><tt>thumb size y</tt><tt>input files</tt>
<br>
</long>
"""
#
# want to pass folder where the input files are
# output size
# output folder
#
# experiments with the Python Image Library (PIL)
# free from:  http://www.pythonware.com/products/pil/index.htm
# create 128x128 (max size) thumbnails of all JPEG images in the working folder
# Python23 tested    vegaseat    25feb2005

import os, sys
import glob
import Image

if len(sys.argv)>1:
	print "pilthumb ", sys.argv[1]
else:
	print "pilthumb sizex"

if len(sys.argv)>2:
	print sys.argv[2]
else:
	print "sizey"

if len(sys.argv)>3:
	print sys.argv[3:]
else:
	print "fileset"
	raise RuntimeError,"Please enter the Command line parameters"


#size = 128, 128
size = (sys.argv[1], sys.argv[2])

# get all the jpg files from the current folder

#for infile in glob.glob("*.jpg"):
#for infile in  glob.glob(sys.argv[3:]):
for infile in  sys.argv[3:]:
	outfile = os.path.splitext(infile)[0] + ".thumbnail"
	if infile != outfile:
		try:
			im = Image.open(infile)
			# convert to thumbnail image
			print infile
			print im.size
  			#im.thumbnail(size, Image.ANTIALIAS)
  			im.thumbnail(size )
			im.save(outfile, "JPEG")
		except IOError:
			print "cannot create thumbnail for", infile

