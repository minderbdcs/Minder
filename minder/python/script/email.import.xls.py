import imaplib
import os
import sys
import time
import email
import errno
import mimetypes
from email.utils import getaddresses

from optparse import OptionParser

import xlrd
import csv

import kinterbasdb
kinterbasdb.init(type_conv=200)

import datetime 


def get_emails(email_ids):
    data = []
    for e_id in email_ids:
        #_, response = imap_server.fetch(e_id, '(UID BODY[TEXT])')
        _, response = mail.fetch(e_id, '(RFC822)')
        data.append(response[0][1])
    return data

def csv_from_excel(xlsfile,csvfile,fromlist):
    wb = xlrd.open_workbook(xlsfile)
    sh = wb.sheet_by_name('Sheet1')
    your_csv_file = open(csvfile, 'wb')
    wr = csv.writer(your_csv_file, quoting=csv.QUOTE_ALL)
    numcols = -1
    for rownum in xrange(sh.nrows):
	mr = []
	mr.append(xlsfile)
	mr.append(fromlist)
	for colnum in xrange(0,sh.ncols):
		mr.append(sh.row_values(rownum)[colnum])
	if numcols == -1:
		numcols = sh.ncols
        #wr.writerow(sh.row_values(rownum))
        wr.writerow(mr)
    your_csv_file.close()
    return numcols

def csv_from_csv(csvfile,csvfileout,fromlist):
    csv_file_in = open(csvfile, 'rb')
    wb = csv.reader(csvfile)
    your_csv_file = open(csvfileout, 'wb')
    wr = csv.writer(your_csv_file, quoting=csv.QUOTE_ALL)
    numcols = -1
    for row in wb:
	mr = []
	mr.append(csvfile)
	mr.append(fromlist)
	if numcols == -1:
		numcols = len(row)
	for col in row:
		mr.append(col)
        wr.writerow(mr)
    your_csv_file.close()
    csv_file_in.close()
    return numcols

def import_csv(csvfile, numCols, dbHost, dbAlias):
	wk_dataset = "import_csv"
	#wk_dsn = opts.host + ":" + opts.db
	wk_dsn = dbHost + ":" + dbAlias
	#	dsn="127.0.0.1:minder",
	con = kinterbasdb.connect(
		dsn=wk_dsn,
		user="sysdba",
		password="masterkey")
	#
	print "connected to db"
	cur = con.cursor()
	cur2 = con.cursor()
	cur3 = con.cursor()
	#
	wk_date = time.strftime("%d/%m/%y")
	# construct the first line from the num cols
	# first field is the filename
	# 2nd field is the from
	wk_buffer = []
	wk_buffer.append("IC_FILE_NAME")
	wk_buffer.append("IC_EMAIL_FROM")
	wk_char_adj = ord('A')
	for col in xrange(0,numCols):
		wk_buffer.append(chr(wk_char_adj+col))
	fields = wk_buffer
	keys = []
	keys_no = []
	fields_nokey = []
	fields_nokey_no = []
	#fields = line.split(',')
	#fields = line
	fields_type = []
	wk_end = len(fields) -1
	print "wk_end",wk_end
	#if wk_end < 1:
	#	break
        #fields[wk_end] = fields[wk_end][:-1]
	if fields[wk_end] == '':
		del fields[wk_end]
	wk_insert = "insert into %s (" % (wk_dataset )
	wk_insert_sfx = ""
	wk_select = "select first 1 1 from %s " % (wk_dataset )
	wk_select21 = "select first 1  " 
	wk_select22 = " from %s " % (wk_dataset )
	wk_update = "update %s set " % (wk_dataset )
	wk_where = "where "
	for xindex in range(0,len(fields)):
		wk_str = fields[xindex]
		# want to trim first and last white space
		wk_str = wk_str.strip()
		if wk_str[:1] == '"':
			if wk_str[-1:] == '"':
				fields[xindex] = wk_str[1:-1]
			else:
				wk_str = wk_str.strip()
				if wk_str[-1:] == '"':
					fields[xindex] = wk_str[1:-1]
		wk_str = fields[xindex]
		# want to trim first and last white space
		wk_str = wk_str.strip()
		if wk_str[:1] == "*":
			print "key"
			keys.append(wk_str[1:])
			keys_no.append(xindex)
			fields[xindex] = wk_str[1:]
			#wk_where += "%s %s and " % ( wk_str[1:], " = '%s'")
			wk_where += "%s %s and " % ( wk_str[1:], " = ? ")
		else :
			fields_nokey.append(wk_str)
			fields_nokey_no.append(xindex)
			fields[xindex] = wk_str
			#wk_update += "%s %s , " % ( wk_str, " = '%s'")
			wk_update += "%s %s , " % ( wk_str, " = ?")
		print xindex,fields[xindex]
		wk_insert += "%s ," % (fields[xindex])
		#wk_insert_sfx += "'%s' ,"
		wk_insert_sfx += "? ,"
		wk_select21  += "%s ," % (fields[xindex])
	print "keys", str(keys)
	print "keys_no", str(keys_no)
	print "fields_nokey", str(fields_nokey)
	print "fields_nokey_no", str(fields_nokey_no)
	# calc where clause for select and update
	# then calc select statement
	if wk_where == "where ":
		wk_select = ""
	else:	
		wk_select += wk_where[:-4] 
	#print wk_select	
	wk_select2 = wk_select21[:-1] + wk_select22
	if wk_select != "":
		#wk_select_prep = cur.prep(wk_select)
		wk_select_prep = wk_select
	else:
		wk_select_prep = ""
	print "select",wk_select	
	# insert statement
	wk_insert = wk_insert[:-1] + ") values (" + wk_insert_sfx[:-1] + ")"
	print "insert",wk_insert	
	#wk_insert_prep = cur3.prep(wk_insert)
	wk_insert_prep = wk_insert
	# update statement
	wk_update = wk_update[:-2] + wk_where[:-4] 
	if wk_select == "":
		wk_update = wk_update[:-2]  
	print "update",wk_update	
	#wk_update_prep = cur2.prep(wk_update)
	wk_update_prep = wk_update
	print "select2",wk_select2
	cur.execute(wk_select2)
	## get data record
	data_fields = cur.fetchone()
	if data_fields is None:
		print "no select2 record not found "
		record_found = None
		for pos in range(len(fields)):
			if len(fields_type) <= pos:
				fields_type.append( str(fields[pos] ) )
			else:
				fields_type[pos] =  str(fields[pos]) 
	else:
		print "select2 record found "
		#print data_fields
		record_found = data_fields[0]
		for pos in range(len(data_fields)):
			#print "pos no", pos
			#print  "description for pos",str(cur.description[pos] )
			#print  "description_name for pos",str(cur.description[pos][kinterbasdb.DESCRIPTION_NAME] )
			#str(cur.description[pos][kinterbasdb.DESCRIPTION_NAME])  - the field name
			#print  "DESCRIPTION_TYPE_CODE for pos", str(cur.description[pos][kinterbasdb.DESCRIPTION_TYPE_CODE] )
			#print  str(cur.description[pos][kinterbasdb.DESCRIPTION_TYPE_CODE])[7:-2] 
			if len(fields_type) <= pos:
				fields_type.append( str(cur.description[pos][kinterbasdb.DESCRIPTION_TYPE_CODE])[7:-2] )
			else:
				fields_type[pos] =  str(cur.description[pos][kinterbasdb.DESCRIPTION_TYPE_CODE])[7:-2] 
			wk_str =  fields_type[pos]
			wk_str = wk_str.upper()
			fields_type[pos] = wk_str
	#print "fields_type", str(fields_type)
	print "fields_type", fields_type
	wk_no_fields = len(fields)
	print " number of 1st line fields", wk_no_fields
	wk_line = 1
	reader = csv.reader(open(csvfile,'rb'),delimiter=',',quotechar='"')
	for line in reader:
		wk_line = wk_line + 1
		print "line",wk_line,line
		#buffer = line.split(',')
		buffer = line
		wk_end = len(buffer) -1
		#print "wk_end",wk_end
		if wk_end < 1:
			break
		print "wk_end",wk_end
		print "line no", wk_line, "expected fields", wk_no_fields, "actual fields", wk_end
		if wk_end >= wk_no_fields:
			del buffer[wk_end]
			print " dropped last field"
		#buffer[wk_end] = buffer[wk_end][:-1]
		for xindex in range(0,len(buffer)):
			print "index", xindex
			wk_str = buffer[xindex]
			# want to trim first and last white space
			wk_str = wk_str.strip()
			if wk_str[:1] == '"':
				buffer[xindex] = wk_str[1:-1]
			wk_str = buffer[xindex]
			wk_str = wk_str.strip()
			buffer[xindex] = wk_str
			print "value", wk_str
			print "x",xindex,buffer[xindex]
		# calc select , update and insert statements
		wk_keys_data = []
		wk_nokeys_data = []
		for windex in range(0,len(keys_no)):
			wk_keys_data.append(buffer[keys_no[windex]])
		for windex in range(0,len(fields_nokey_no)):
			wk_nokeys_data.append(buffer[fields_nokey_no[windex]])
		#wk_select_stmt = wk_select % tuple(wk_keys_data)
		#wk_select_stmt = wk_select
		wk_select_stmt = wk_select_prep
		print wk_select_stmt	
		if len(wk_nokeys_data) > 0:
			#wk_update_stmt = wk_update % tuple(wk_nokeys_data + wk_keys_data)
			#wk_update_stmt = wk_update
			wk_update_stmt = wk_update_prep
		else:
			wk_update_stmt = ""
		#print wk_update_stmt	
		#wk_insert_stmt = wk_insert % tuple(buffer)
		#wk_insert_stmt = wk_insert
		wk_insert_stmt = wk_insert_prep
		#print wk_insert_stmt	

		# perform select
		# if record found do the update
		# else do the insert
		
		if wk_select_stmt > "":			
			#cur.execute(wk_select_stmt)
			cur.execute(wk_select_stmt, tuple(wk_keys_data))
			## get data record
			data_fields = cur.fetchone()
		else:
			data_fields = None
		
		#print data_fields
		if data_fields is None:
			print "record not found select "
			record_found = None
		else:
			print "record found select "
			#print data_fields
			record_found = data_fields[0]
		if record_found == 1 :
			print wk_update_stmt	
			query4 = wk_update_stmt
			if query4 <> "":
				cur2.execute(query4, tuple(wk_nokeys_data + wk_keys_data))
		else :
			print wk_insert_stmt	
			query4 = wk_insert_stmt
			if query4 <> "":
				cur3.execute(query4, tuple(buffer))        
		print "end of line", wk_line
	# commit work
	con.commit()
	con.close()
		
		
def main():
	parser = OptionParser(usage="""\
Read emails for a remote user 
looking for .xls .xlsx or .csv files.
Then import these into the import_csv table on the remote database.

Usage: %prog [options] 
""")
	parser.add_option('-d', '--directory',
                      type='string', action='store',
                      help="""Unpack the MIME message into the named
                      directory, which will be created if it doesn't already
                      exist.""")
	parser.add_option('-o', '--host',
                      type='string', action='store',
                      help="""The Host for the Database to import to
                      .""",
		      default="127.0.0.1")
	parser.add_option('-l', '--db',
                      type='string', action='store',
                      help="""The DB Alias to import to
                      .""",
		      default="minder")
	opts, args = parser.parse_args()
	if not opts.directory:
		parser.print_help()
		sys.exit(1)
	#	
	try:
		os.mkdir(opts.directory)
	except OSError as e:
		# Ignore directory exists error
		if e.errno != errno.EEXIST:
			raise
	print "db:",opts.db	
	print "host:",opts.host
	#sys.exit(1)
	#mail = imaplib.IMAP4('localhost')
	mail = imaplib.IMAP4('192.168.61.121')
	#mail = imaplib.IMAP4('mail.barcoding.com.au')
	print "got connection"
	mail.login('import', 'BDCS!bdcs')
	print "got login"
	mail.list()
	print "got list of folders"
	# Out: list of "folders" aka labels in gmail.
	mail.select("inbox") # connect to inbox.
	#	
	#########################################################################
	# use emails today
	#date = (datetime.date.today() - datetime.timedelta(1)).strftime("%d-%b-%Y")
	#result, data = mail.uid('search', None, '(SENTSINCE {date})'.format(date=date))
	#latest_email_uid = data[0].split()[-1]
	#	
	#########################################################################
	# use unseen emails 
	# Search for all unseen mail
	status, email_ids = mail.search(None, '(UNSEEN)')
	print status
	print email_ids
	if len(email_ids[0]) > 0:
		#raw_email = get_emails(email_ids)
		#msg = email.message_from_string(raw_email)
		#print msg 
		for e_id in email_ids[0].split():
			print e_id
	        	_, response = mail.fetch(e_id, '(RFC822)')
			email_body = response[0][1]
			msg = email.message_from_string(email_body)
			froms = msg.get_all('from', [])
			resent_froms = msg.get_all('resent-from', [])
			#all_recipients = getaddresses(tos + ccs + resent_tos + resent_ccs)
			all_recipients = getaddresses(froms + resent_froms )
			#all_recipients = getaddresses(resent_froms )
			print "from:", all_recipients
			#print msg 
	    		email.iterators._structure(msg)
			counter = 1
			for part in msg.walk():
				# multipart/* are just containers
				if part.get_content_maintype() == 'multipart':
					continue
				# Applications should really sanitize the given filename so that an
				# email message can't be used to overwrite important files
				filename = part.get_filename()
				if not filename:
					ext = mimetypes.guess_extension(part.get_content_type())
					if not ext:
						# Use a generic bag-of-bits extension
						ext = '.bin'
					filename = 'part-%03d%s' % (counter, ext)
				counter += 1
				print filename
				filerest, ext = os.path.splitext(filename)
				if ext.lower() == ".xls" or ext.lower() == ".xlsx" :
					print "save xls",filename
					fp = open(os.path.join(opts.directory, filename), 'wb')
					fp.write(part.get_payload(decode=True))
					fp.close()
					xlsfile  = os.path.join(opts.directory, filename)
					filerest2 = filerest + ".csv"
					csvfile  = os.path.join(opts.directory, filerest2)
					numCols = csv_from_excel(xlsfile,csvfile,all_recipients )
					#import_csv(csvfile, numCols)
					import_csv(csvfile, numCols, opts.host, opts.db)
				elif ext.lower() == ".csv":
					print "save csv",filename
					fp = open(os.path.join(opts.directory, filename), 'wb')
					fp.write(part.get_payload(decode=True))
					fp.close()
					csvfile  = os.path.join(opts.directory, filename)
					filerest2 = filerest + "2.csv"
					csvfileout  = os.path.join(opts.directory, filerest2)
					numCols = csv_from_csv(csvfile,csvfileout,all_recipients )
					# now import that csv
					#import_csv(csvfileout, numCols)
					import_csv(csvfileout, numCols, opts.host, opts.db)
	else:
		print "no emails unseen"
	
	
	mail.close()
	mail.logout()

if __name__ == '__main__':
    main()

