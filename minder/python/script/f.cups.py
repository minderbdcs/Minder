#!/usr/bin/python
import cups
cups.setUser('root')
cups.setServer('localhost')
conn = cups.Connection()


from pprint import pprint
#pprint(conn.getPrinters())
#pprint(c.getPrinterAttributes('stylus'))
printers = conn.getPrinters()
for printer in printers:
	print printer,printers[printer]['device-uri'],printers[printer]['printer-state'], printers[printer]['printer-state-message'], printers[printer]['printer-state-reasons']
