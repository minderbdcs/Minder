#!/usr/bin/python
#import cups, json, sys
import cups, sys
try:
    import json
except ImportError:
    import simplejson as json

cups.setUser('root')
cups.setServer('localhost')
conn = cups.Connection()


from pprint import pprint
#pprint(conn.getPrinters())
#pprint(c.getPrinterAttributes('stylus'))
printers = conn.getPrinters()
#for printer in printers:
#	print printer,printers[printer]['device-uri'],printers[printer]['printer-state'], printers[printer]['printer-state-message'], printers[printer]['printer-state-reasons']
print json.dumps(printers)
