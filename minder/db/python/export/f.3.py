import sys, time , os

import fdb

import StringIO
import csv

class xmldata:

	def runXMLData(self, sql, dataset, ftptype, keys):
		" export a dataset to an XML string "	
		#cur.execute(sql, (mystarttime, myendtime))
		cur.execute(sql )
		
		# get data record
		data_fields = cur.fetchone()
		if data_fields is None:
			return None
		#redirect stdout and stderr
		if os.name == 'nt':
			outp = open('d:/sysdata/iis/default/ftproot/' + ftptype + '/' + dataset + '.csv','wb')
		else:
			#outp = open('/sysdata/iis/default/ftproot/' + ftptype + '/' + dataset + '.csv','wb')
			outp = open('/data/tmp/' + ftptype + '/' + dataset + '.csv','wb')
    		outwriter = csv.writer(outp, delimiter=',',
                            quotechar='"', quoting=csv.QUOTE_ALL)
		sys.stdout = outp
	
		wk_insert = ""
		wk_insert_hdr1 = ""				
		for pos in range(len(data_fields)):
			# if this field is in the keys string then prefix it with a '*'
			if keys.find("," + str(cur.description[pos][fdb.DESCRIPTION_NAME]) + ",") > -1:
				wk_insert_hdr1 = wk_insert_hdr1 + '"*'
			else:
				wk_insert_hdr1 = wk_insert_hdr1 + '"'
				
			wk_insert_hdr1 = wk_insert_hdr1 + \
				str(cur.description[pos][fdb.DESCRIPTION_NAME]) + '",'

		if os.name == 'nt':
			print( wk_insert_hdr1[:-1])
		else:
			print( wk_insert_hdr1[:-1] + "\r")
		
		while not data_fields is None:
			wk_rowdata = ""
			wk_outdata = []
			for pos in range(len(data_fields)):
				fielddata = data_fields[pos]
				wk_outdata.append(fielddata)
				#print str(cur.description[pos][fdb.DESCRIPTION_TYPE_CODE])
				if str(cur.description[pos][fdb.DESCRIPTION_TYPE_CODE])[7:-2] == "DateTime" and \
				    data_fields[pos] is None:
					wk_rowdata = wk_rowdata + \
					 	'"1900-01-01 00:00:00",' 
				elif str(cur.description[pos][fdb.DESCRIPTION_TYPE_CODE])[7:-2] == "datetime.datetime" and \
				    data_fields[pos] is None:
					wk_rowdata = wk_rowdata + \
					 	'"1900-01-01 00:00:00",' 
				elif str(cur.description[pos][fdb.DESCRIPTION_TYPE_CODE])[7:-2] == "datetime.datetime" :
					fielddata = str(data_fields[pos])
					if fielddata.find(".") > -1:
						myparms = fielddata.split(".")
						fielddata = myparms[0] 
					wk_rowdata = wk_rowdata + \
					 	'"' + str(fielddata) + '",'
				elif str(cur.description[pos][fdb.DESCRIPTION_TYPE_CODE])[7:-2] == "int" and \
				    data_fields[pos] is None:
					#wk_rowdata = wk_rowdata + \
					# 	'"0",' 
					wk_rowdata = wk_rowdata + \
					 	'"",' 
				elif str(cur.description[pos][fdb.DESCRIPTION_TYPE_CODE])[7:-2] == "float" and \
				    data_fields[pos] is None:
					#wk_rowdata = wk_rowdata + \
					# 	'"0.0",' 
					wk_rowdata = wk_rowdata + \
					 	'"",' 
				elif str(cur.description[pos][fdb.DESCRIPTION_TYPE_CODE]).find("ecimal") > -1 and \
				    data_fields[pos] is None:
					#wk_rowdata = wk_rowdata + \
					# 	'"0.0",' 
					wk_rowdata = wk_rowdata + \
					 	'"",' 
				elif str(cur.description[pos][fdb.DESCRIPTION_TYPE_CODE])[7:-2] == "buffer" or \
						data_fields[pos] is None:
						wk_rowdata = wk_rowdata + \
							'"",' 
				else:
					fielddata = str(data_fields[pos])
					if fielddata.find("'") > -1:
						fielddata = fielddata.replace("'"," ")
					if fielddata.find('"') > -1:
						fielddata = fielddata.replace('"'," ")
					if fielddata.find(',') > -1:
						fielddata = fielddata.replace(','," ")
					if fielddata.find('\n') > -1:
						fielddata = fielddata.replace('\n'," ")
					wk_rowdata = wk_rowdata + \
					 	'"' + str(fielddata) + '",'
				if  dataset == "COMPANY":
					#print(cur.description[pos][fdb.DESCRIPTION_NAME])
					if str((cur.description[pos][fdb.DESCRIPTION_NAME])) == "COMPANY_ID" :
						fielddata = 'APCDVIC'
						wk_outdata[pos] = fielddata
				if  dataset == "ISSN":
					#print(cur.description[pos][fdb.DESCRIPTION_NAME])
					if str((cur.description[pos][fdb.DESCRIPTION_NAME])) == "COMPANY_ID" :
						fielddata = 'APCDVIC'
						wk_outdata[pos] = fielddata
				if  dataset == "SSN":
					#print(cur.description[pos][fdb.DESCRIPTION_NAME])
					if str((cur.description[pos][fdb.DESCRIPTION_NAME])) == "COMPANY_ID" :
						fielddata = 'APCDVIC'
						wk_outdata[pos] = fielddata
				if  dataset == "PICK_ORDER":
					#print(cur.description[pos][fdb.DESCRIPTION_NAME])
					if str((cur.description[pos][fdb.DESCRIPTION_NAME])) == "COMPANY_ID" :
						fielddata = 'APCDVIC'
						wk_outdata[pos] = fielddata
				#if  dataset == "PICK_DESPATCH":
				#	#print(cur.description[pos][fdb.DESCRIPTION_NAME])
				#	if str((cur.description[pos][fdb.DESCRIPTION_NAME])) == "DESPATCH_ID" :
				#		if data_fields[pos] is None:
				#			wk_dummy = 1
				#		else:
				#			fielddata = int(data_fields[pos])  + 80000
				#			wk_outdata[pos] = fielddata
				#if  dataset == "PICK_ITEM_DETAIL":
				#	#print(cur.description[pos][fdb.DESCRIPTION_NAME])
				#	if str((cur.description[pos][fdb.DESCRIPTION_NAME])) == "DESPATCH_ID" :
				#		if data_fields[pos] is None:
				#			wk_dummy = 1
				#		else:
				#			fielddata = int(data_fields[pos])  + 80000
				#			wk_outdata[pos] = fielddata
				#	if str((cur.description[pos][fdb.DESCRIPTION_NAME])) == "PICK_DETAIL_ID" :
				#		if data_fields[pos] is None:
				#			wk_dummy = 1
				#		else:
				#			fielddata = int(data_fields[pos])  + 900000
				#			wk_outdata[pos] = fielddata
				#	if str((cur.description[pos][fdb.DESCRIPTION_NAME])) == "PACK_ID" :
				#		if data_fields[pos] is None:
				#			wk_dummy = 1
				#		else:
				#			fielddata = int(data_fields[pos])  + 200000
				#			wk_outdata[pos] = fielddata
				#if  dataset == "PACK_ID":
				#	#print(cur.description[pos][fdb.DESCRIPTION_NAME])
				#	if str((cur.description[pos][fdb.DESCRIPTION_NAME])) == "DESPATCH_ID" :
				#		if data_fields[pos] is None:
				#			wk_dummy = 1
				#		else:
				#			fielddata = int(data_fields[pos])  + 80000
				#			wk_outdata[pos] = fielddata
				#	if str((cur.description[pos][fdb.DESCRIPTION_NAME])) == "PACK_ID" :
				#		if data_fields[pos] is None:
				#			wk_dummy = 1
				#		else:
				#			fielddata = int(data_fields[pos])  + 200000
				#			wk_outdata[pos] = fielddata
						
			#if os.name == 'nt':
			#	print( wk_rowdata[:-1] )
			#else:
			#	print( wk_rowdata[:-1] + "\r")
			#outwriter.writerow(data_fields)
			outwriter.writerow(wk_outdata)
			data_fields = cur.fetchone()
		con.commit()
		#revert stdout 
		sys.stdout = sys.__stdout__
	
	
	def getXMLData(self, dataset, where, fields, ftptype, keys):
		self.runXMLData("SELECT  " + fields + " FROM " + dataset + " " + where, dataset, ftptype, keys)


	def getXMLString(self, dataset, ftptype, where="", keys="", fields = "*",dom=0):
		" export dataset to a flat file "	
		self.getXMLData(dataset, where, fields, ftptype, keys)




if os.name == 'nt':
	err = open('d:/tmp/export.csv.log','w')
else:
	err = open('/data/tmp/export.csv.log','w')

sys.stderr = err

mydb = "minder"
myHost = "localhost"
myuser = "minder"
mypasswd = "minder"
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
print "mydb", mydb

print "myhost", myHost
print "myuser", myuser
print "mypassword", mypasswd
con = fdb.connect(
        dsn=myHost+":"+ mydb,
        user=myuser,
        password=mypasswd)

print "connected to db"

con.begin()
err.write("opened prod database\n")
cur = con.cursor()



		
		

mydata = xmldata()

mydata.getXMLString("SYS_EQUIP","export", " join ssn on sys_equip.ssn_id = ssn.ssn_id  join control on control.record_id=1 ", ",DEVICE_ID,", "sys_equip.DEVICE_ID,sys_equip.ssn_id,sys_equip.operating_zone,sys_equip.create_date,sys_equip.created_by,sys_equip.last_update_date,sys_equip.update_user_id,sys_equip.working_directory,sys_equip.device_type,ssn.ip_address, control.default_wh_id as wh_id, 'OK' as equipment_status ")
mydata = None
mydata = xmldata()
#mydata.getXMLString("SSN_HIST","export", " where trn_date > cast('2013-08-20 10:20' as timestamp)  ", ",NONE,", "0 AS RECORD_ID,WH_ID,LOCN_ID,SSN_ID,TRN_DATE,TRN_TYPE,TRN_CODE,ERROR_TEXT,REFERENCE,QTY,SUB_LOCN_ID,DEVICE_ID,PERSON_ID" )
#
#mydata.getXMLString("SSN","export", " where ssn.wh_id in (select wh_id from warehouse) and ssn_id < '50010000' ", ",SSN_ID,")
mydata = None
mydata = xmldata()

#mydata.getXMLString("GRN","export", " ", ",GRN,", " grn.* , 'APCDVIC' as OWNER_ID ")
mydata.getXMLString("COMPANY","export", " ", ",GRN," )
mydata = None
mydata = xmldata()
mydata.getXMLString("PICK_DESPATCH","export", "  ", ",DESPATCH_ID,")
mydata = None
mydata = xmldata()
#mydata.getXMLString("PACK_ID","export", "  ", ",PACK_ID,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("PICK_ITEM_DETAIL","export", "  ", ",PICK_DETAIL_ID,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("PICK_ORDER","export", "  ", ",PICK_ORDER,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("PERSON","export", "  ", ",NONE,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("ISSN","export", "  ", ",SSN_ID,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("PICK_ORDER","export", "  ", ",PICK_ORDER,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("PICK_ITEM","export", "  ", ",PICK_LABEL_NO,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("PICK_ITEM_DETAIL","export", "  ", ",PICK_DETAIL_ID,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("PICK_ITEM_LINE_NO","export", "  ", ",PICK_ORDER,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("PICK_DESPATCH","export", "  ", ",DESPATCH_ID,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("PACK_ID","export", "  ", ",PACK_ID,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("SSN_HIST","export", " where trn_date > cast('2013-08-20 10:20' as timestamp)  ", ",RECORD_ID,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("LOCATION","export", "  ", ",WH_ID,LOCN_ID,")
#
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("SSN","export", "  ", ",SSN_ID,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("SSN_LEGACY","export", "  ", ",SSN_ID,LEGACY_ID,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("TRANSACTIONS","export", " ", ",NONE,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("OPTIONS","export", "  ", ",NONE,")
#mydata.getXMLString("BRAND","export", " ", ",CODE,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("COMPANY","export", " ", ",COMPANY_ID,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("PARAM","export", " ", ",DATA_ID,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("GLOBAL_CONDITIONS","export", " ", ",OTHER_NO,DESCRIPTION,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("ACCESS_USER","export", " ", ",USER_ID,WH_ID,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("SSN_TYPE","export", " ", ",CODE,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("GENERIC","export", " ", ",CODE,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("PROD_TYPE","export", " ", ",CODE,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("SYS_MOVES","export", " ", ",FROM_STATUS,INTO_STATUS,")
#mydata = None
#mydata = xmldata()
## continue here
#mydata.getXMLString("SYS_USER", "export", " ",",USER_ID,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("GRN","export", "  ", ",GRN,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("PRODUCT_CONDITION","export", "  ", ",CODE,DESCRIPTION,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("PRODUCT_COND_STATUS","export", "  ", ",SSN_ID,CODE,DESCRIPTION,ORIGINAL_TEST,")
#mydata = None
#mydata = xmldata()
#mydata = None
## continue here
#mydata.getXMLString("PRODUCT_DESCRIPTION","export", "  ", ",TYPE_CODE,FIELD_CODE,DESCRIPTION,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("UOM","export", "  ", ",CODE,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("WARRANTY","export", "  ", ",WARRANTY_CODE,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("ZONE","export", "  ", ",CODE,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("LABEL_LOCATION","export", "  ", ",CODE,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("TOG","export", "  ", ",CODE,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("SYS_EQUIP","export", " join ssn on sys_equip.ssn_id = ssn.ssn_id  ", ",DEVICE_ID,", "sys_equip.DEVICE_ID,sys_equip.ssn_id,sys_equip.operating_zone,sys_equip.create_date,sys_equip.created_by,sys_equip.last_update_date,sys_equip.update_user_id,sys_equip.working_directory,sys_equip.device_type,ssn.ip_address ")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("CARRIER","export", "  ", ",CARRIER_ID,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("CARRIER_SERVICE","export", "  ", ",CARRIER_ID,SERVICE_TYPE,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("SSN_GROUP","export", "  ", ",SSN_GROUP,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("WAREHOUSE","export", " ", ",WH_ID," )
#mydata = None
#mydata = xmldata()
con.commit()
con.commit()
con.close()


err.write("closed database\n")

err.close()

