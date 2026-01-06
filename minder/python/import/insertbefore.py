#!/usr/bin/python
"""
Python script by Tobias Polzin, polzin@gmx.de
If you like the module, please give me feedback.
All rights reserved.

<title>
insertbefore.py, Version 24.11.99
</title>
<long>
Inserts some text at a specific position of a text file.
<br>
Paramters: <tt>[-d (delete line)] [-a (after)] filename label [string]</tt>
<br>
If no <tt>[string]</tt> is given on the command line, the string is read from stdin.
<br>
This scans the file <tt>filename</tt> for all tags <tt><label></tt>
and puts the text <tt>string</tt> before (with option <tt>-a</tt>
                                          after) the line containing
the tag. With option <tt>-d</tt> the line containing the tag is
deleted.
<br>
Uses <linkto show scannew.py> module.
</long>
"""
import scannew
import sys
import string

print "insertbefore [-d (delete line)] [-a (after)] filename label [string]"
dell="-d" in sys.argv
after="-a" in sys.argv
if dell: sys.argv.remove("-d")
if after: sys.argv.remove("-a")
fn=sys.argv[1]
label=sys.argv[2]
print "-a",after,"-d",dell,"-file",fn,"-label",label
if len(sys.argv)>3:
    ins=sys.argv[3]+"\n"
else:
    print "reading from stdin, CTRL-D for exit"
    ins=string.join(scannew.readStdinLines())
count=0

def insert(m):
    global count,dell
    count=count+1
    l=[m.group("line"),""][dell]
    if after:
        l=l+ins
    else:
        l=ins+l
    return l

reg="(?m)^(?P<line>.*"+label+".*\n)"
scannew.updateFile(fn,reg,insert)
print "done:",fn,count


###
