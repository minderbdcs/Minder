#!/usr/bin/env python2
import sys, time , os

import fdb
#import fdb;fdb.init(type_conv=200)
#import mx

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
			#outp = open('d:/sysdata/iis/default/ftproot/' + ftptype + '/' + dataset + '.csv','wb')
			outp = open('d:/tmp/' + ftptype + '/' + dataset + '.csv','wb')
			errp = open('d:/tmp/' + ftptype + '/' + dataset + '.esv','wb')
		else:
			#outp = open('/sysdata/iis/default/ftproot/' + ftptype + '/' + dataset + '.csv','wb')
			outp = open('/data/tmp/' + ftptype + '/' + dataset + '.csv','wb')
			errp = open('/data/tmp/' + ftptype + '/' + dataset + '.esv','wb')
    		outwriter = csv.writer(outp, delimiter=',',
                            quotechar='"', quoting=csv.QUOTE_ALL)
    		errwriter = csv.writer(errp, delimiter=',',
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
			errp.write( wk_insert_hdr1[:-1])
			errp.write( "\n")
		else:
			print( wk_insert_hdr1[:-1] + "\r")
			errp.write( wk_insert_hdr1[:-1])
			errp.write( "\r\n")
		# get seperated list of key fields
		wk_key_delimiter = ','
		wk_keys_list = []
		wk_keys_list = keys.split(wk_key_delimiter)
		
		wk_do_export = True
		while not data_fields is None:
			wk_rowdata = ""
			wk_outdata = []
			wk_do_export = True
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
					if str((cur.description[pos][fdb.DESCRIPTION_NAME])) == "WH_ID" :
						fielddata = 'ME'
						wk_outdata[pos] = fielddata
				if  dataset == "SSN_HIST":
					#print(cur.description[pos][fdb.DESCRIPTION_NAME])
					if str((cur.description[pos][fdb.DESCRIPTION_NAME])) == "SSN_ID" :
						fielddata = 'None'
						if wk_outdata[pos] is None:
							wk_outdata[pos] = fielddata
						else:
							wk_str =  str(wk_outdata[pos])
							wk_str = wk_str.strip()
							if wk_str == '':
								wk_outdata[pos] = fielddata
				if  dataset == "SSN_LEGACY":
					#print(cur.description[pos][fdb.DESCRIPTION_NAME])
					if str((cur.description[pos][fdb.DESCRIPTION_NAME])) == "LEGACY_ID" :
						if wk_outdata[pos] is None:
							wk_dummy = 1
						else:
							fielddata = str(wk_outdata[pos])
							if fielddata.find("'") > -1:
								fielddata = fielddata.replace("'"," ")
							if fielddata.find('"') > -1:
								fielddata = fielddata.replace('"'," ")
							#if fielddata.find(',') > -1:
							#	fielddata = fielddata.replace(','," ")
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
				#
				# if this field is a key field
				#	if data is empty
				#		then no export
				#
				#for kpos in range(len(wk_keys_list )):
				#	keydata = wk_keys_list[kpos]
				#	#print(cur.description[pos][fdb.DESCRIPTION_NAME])
				#	if str((cur.description[pos][fdb.DESCRIPTION_NAME])) == wk_keys_list[kpos] :
				#		if wk_outdata[pos] is None:
				#			wk_do_export = False
				#		if wk_outdata[pos] == '':
				#			wk_do_export = False
				if keys.find("," + str(cur.description[pos][fdb.DESCRIPTION_NAME]) + ",") > -1:
					if wk_outdata[pos] is None:
						wk_do_export = False
					else:
						wk_str =  str(wk_outdata[pos])
						wk_str = wk_str.strip()
						if wk_str == '':
							wk_do_export = False
				#			
			#if os.name == 'nt':
			#	print( wk_rowdata[:-1] )
			#else:
			#	#print( wk_rowdata[:-1] + "\r")
			#outwriter.writerow(data_fields)
			if wk_do_export:
				outwriter.writerow(wk_outdata)
			else:
				#print "empty key field"
				err.write(dataset)
				err.write(" empty key field\n")
				errwriter.writerow(wk_outdata)
			data_fields = cur.fetchone()
		con.commit()
		#revert stdout 
		sys.stdout = sys.__stdout__
	
	
	def getXMLData(self, dataset, where, fields, ftptype, keys):
		self.runXMLData("SELECT  " + fields + " FROM " + dataset + " " + where, dataset, ftptype, keys)


	def getXMLString(self, dataset, ftptype, where="", keys="", fields = "*",dom=0):
		" export dataset to a flat file "	
		self.getXMLData(dataset, where, fields, ftptype, keys)



# /* mainline */

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

#
#redirect stdout and stderr
#if os.name == 'nt':
#	outp = open('d:/sysdata/iis/default/ftproot/export/data' + '.xml','w')
#else:
#	outp = open('/sysdata/iis/default/ftproot/export/data' + '.xml','w')

#sys.stdout = outp

#myendtime = mx.DateTime.now()
#cur.execute("SELECT LAST_MIRROR_DATE FROM CONTROL")
		
# get data record
#control_fields = cur.fetchone()
#if control_fields is None:
#	mystarttime = mx.DateTime.today()
#else:	
#	for pos in range(len(control_fields)):
#		if control_fields[pos] is None:
#			mystarttime = mx.DateTime.today()
#		else:
#			mystarttime = control_fields[pos]
		

mydata = xmldata()

#mydata.getXMLString("LOCATION","export", "  ", ",WH_ID,LOCN_ID,")
#mydata = None
#mydata = xmldata()
mydata.getXMLString("PRODUCT_DESCRIPTION","export", "  ", ",TYPE_CODE,FIELD_CODE,DESCRIPTION,")
mydata = None
mydata = xmldata()
#mydata.getXMLString("SSN_HIST","export", " where trn_date > cast('2013-08-20 10:20' as timestamp)  ", ",RECORD_ID,")
#mydata.getXMLString("SSN_HIST","export", " where trn_date > cast('2013-08-20 10:20' as timestamp)  ", ",RECORD_ID,SSN_ID,")
mydata = None
mydata = xmldata()
mydata.getXMLString("SSN_LEGACY","export", "  ", ",SSN_ID,LEGACY_ID,")
mydata = None
mydata = xmldata()
## person needs company
##mydata.getXMLString("PERSON","export", "  ", ",PERSON_ID,")
#mydata.getXMLString("PERSON","export", " ", ",PERSON_ID,COMPANY_ID,", " person.* , 'APCDVIC' as COMPANY_ID ")
#mydata = None
#mydata = xmldata()
##mydata.getXMLString("ISSN","export", "  ", ",SSN_ID,")
#mydata.getXMLString("ISSN","export", " where issn.wh_id in (select wh_id from warehouse)  ", ",SSN_ID,")
##mydata.getXMLString("ISSN","export", " where last_update_date > cast('2013-08-20 10:20' as timestamp)  ", ",SSN_ID," )
##mydata.getXMLString("ISSN","export", " where last_update_date > cast('2013-08-20 10:20' as timestamp)  ", ",SSN_ID,", "WH_ID,LOCN_ID,SSN_ID" )
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
##mydata.getXMLString("PICK_ITEM_CANCEL","export", "  ", ",NONE,")
##mydata = None
##mydata = xmldata()
#mydata.getXMLString("PICK_DESPATCH","export", "  ", ",DESPATCH_ID,")
##mydata.getXMLString("PICK_DESPATCH","export", " where despatch_id > 132242  ", ",DESPATCH_ID," )
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("PACK_ID","export", "  ", ",PACK_ID,")
#mydata = None
#mydata = xmldata()
##mydata.getXMLString("SSN_HIST","export", "  ", ",RECORD_ID,")
##mydata.getXMLString("SSN_HIST","export", " where trn_date > cast('2013-08-20 10:20' as timestamp)  ", ",RECORD_ID,")
##mydata.getXMLString("SSN_HIST","export", " where trn_date > cast('2013-08-20 10:20' as timestamp)  ", ",NONE,", "0 AS RECORD_ID,WH_ID,LOCN_ID,SSN_ID,TRN_DATE,TRN_TYPE,TRN_CODE,ERROR_TEXT,REFERENCE,QTY,SUB_LOCN_ID,DEVICE_ID,PERSON_ID" )
#mydata.getXMLString("SSN_HIST","export", " where trn_date > cast('2013-08-20 10:20' as timestamp)  ", ",RECORD_ID,")
#
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("LOCATION","export", "  ", ",WH_ID,LOCN_ID,")
##mydata.getXMLString("LOCATION","export", " where WH_ID  starting 'ZW'  ", ",NONE,", "WH_ID,LOCN_ID,LOCN_NAME, LOCN_STAT,MOVE_STAT, STORE_TYPE,STORE_AREA,STORE_METH, INSTANCE_ID,MAX_QTY,MIN_QTY,REORDER_QTY, MOVEABLE_LOCN, LOCN_TYPE,LABEL_DIRECTION,LABEL_SIDE, LOCN_INT_DIMENSION_X,LOCN_INT_DIMENSION_Y,LOCN_INT_DIMENSION_Z,LOCN_DIMENSION_UOM" )
##mydata.getXMLString("LOCATION","export", " where WH_ID  starting '0'  ", ",NONE,"  )
#
#mydata = None
#mydata = xmldata()
##mydata.getXMLString("SSN","export", "  ", ",SSN_ID,")
#mydata.getXMLString("SSN","export", " where ssn.wh_id in (select wh_id from warehouse)  ", ",SSN_ID,")
##mydata.getXMLString("SSN","export", " where last_update_date > cast('2013-08-20 10:22:00' as timestamp)  ", ",SSN_ID," )
##mydata.getXMLString("SSN","export", " where last_update_date > cast('2013-08-20 10:22:00' as timestamp)  ", ",SSN_ID,", "WH_ID,LOCN_ID,SSN_ID" )
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("SSN_LEGACY","export", "  ", ",SSN_ID,LEGACY_ID,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("TRANSACTIONS","export", " ", ",RECORD_ID,")
##mydata.getXMLString("TRANSACTIONS_ARCHIVE","export", " WHERE TRN_DATE>cast('AUG-01-2009' as timestamp) ORDER BY TRN_DATE ", ",NONE,")
#mydata = None
#mydata = xmldata()
##mydata.getXMLString("OPTIONS","export", " where group_code starting 'SCN'  ", ",NONE,")
#mydata.getXMLString("OPTIONS","export", "  ", ",GROUP_CODE,CODE,")
##mydata = None
##mydata = xmldata()
#mydata.getXMLString("BRAND","export", " ", ",CODE,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("COMPANY","export", " ", ",COMPANY_ID,")
#mydata = None
#mydata = xmldata()
## needs default brand and default model
##mydata.getXMLString("PARAM","export", " ", ",DATA_ID,")
#mydata.getXMLString("PARAM","export", " ", ",DATA_ID,DATA_BRAND,DATA_MODEL,", " param.* , 'DEFAULT' as DATA_BRAND, 'DEFAULT' as DATA_MODEL ")
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
##mydata.getXMLString("SYS_SCREEN_ORDER","export", " ", ",RECORD_ID,")
##mydata = None
##mydata = xmldata()
##mydata.getXMLString("SYS_SCREEN_TABLE","export", " ", ",RECORD_ID,")
##mydata = None
##mydata = xmldata()
#mydata.getXMLString("SYS_USER", "export", " ",",USER_ID,")
#mydata = None
#mydata = xmldata()
## need to add company to the grn 
##mydata.getXMLString("GRN","export", "  ", ",GRN,")
#mydata.getXMLString("GRN","export", " ", ",GRN,", " grn.* , 'APCDVIC' as OWNER_ID ")
#mydata = None
#mydata = xmldata()
##mydata.getXMLString("GRN_ORDER","export", "  ", ",GRN_LABEL_NO,")
##mydata = None
##mydata = xmldata()
#mydata.getXMLString("PRODUCT_CONDITION","export", "  ", ",CODE,DESCRIPTION,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("PRODUCT_COND_STATUS","export", "  ", ",SSN_ID,CODE,DESCRIPTION,ORIGINAL_TEST,")
#mydata = None
#mydata = xmldata()
##mydata.getXMLString("PROD_PROFILE","export", "  ", ",NONE,", "PROD_ID,SHORT_DESC,LONG_DESC,ALTERNATE_ID,PROD_TYPE,STOCK,SPECIAL_INSTR, HOME_LOCN_ID,SUPPLIER_NO1,SUPPLIER_NO2,SUPPLIER_NO3,SUPPLIER_NO1_PROD,SUPPLIER_NO2_PROD,SUPPLIER_NO3_PROD,SUPPLIER_PREFER,UOM,ISSUE_UOM,ORDER_UOM,ISSUE_PER_ORDER_UNIT,PALLET_CFG_C,PERM_LEVEL,SSN_TRACK,TOG_C,DEFAULT_ISSUE_QTY,BACK_ORDER_QTY,RESERVED_QTY,MAX_QTY,MIN_QTY,REORDER_QTY,MAX_ISSUE_QTY, PROD_RETRIEVE_STATUS,COMPANY_ID,ISSUE_PER_INNER_UNIT,ORDER_WEIGHT,NET_WEIGHT_UOM,ORDER_WEIGHT_UOM,ISSUE,INNER_UOM,INNER_WEIGHT_UOM,PALLET_CFG_INNER,PALLET_CFG_ALTERNATE,NET_WEIGHT, ALTERNATE_COMPANY_ID, UOM_SIZE,MAX_WEIGHT_UNDER,MAX_WEIGHT_OVER,DIMENSION_X,DIMENSION_Y,DIMENSION_Z,DIMENSION_X_UOM,DIMENSION_Y_UOM,DIMENSION_Z_UOM,EXPORT_CATEGORY,PICK_IMPORT,TEMPERATURE_ZONE,TAX_APPLICABLE,SSN_TYPE,PP_PACKAGE_TYPE,VOLUME_UOM,ORIENTATION,PROD_TYPE_COMMENT,HAZARD_TYPE, SALE_PER_MTH, SALE_VOL_PER_MTH, STORE_TYPE, ALT_PROD_TYPE,ALT_HOME_LOCN,ALT_HOME_LOCN2,ALT_HOME_LOCN3,PP_MATERIAL_SAFETY_DATA,PP_MATERIAL_SAFETY_DATA_NO,PP_HAZARD_STATUS,PP_HAZARD_WARNING,PP_HAZARD_IMAGE1,PP_HAZARD_IMAGE2,PP_HAZARD_IMAGE3")
##mydata = None
##mydata = xmldata()
##mydata.getXMLString("SSN_TEST","export", "  ", ",TEST_ID,")
##mydata = None
##mydata = xmldata()
##mydata.getXMLString("SSN_TEST_RESULTS","export", "  ", ",NONE,")
#mydata = None
#mydata = xmldata()
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
##mydata.getXMLString("SYS_EQUIP","export", "  ", ",DEVICE_ID,")
##mydata.getXMLString("SYS_EQUIP","export", " join ssn on sys_equip.ssn_id = ssn.ssn_id  ", ",DEVICE_ID,", "sys_equip.DEVICE_ID,sys_equip.ssn_id,sys_equip.operating_zone,sys_equip.create_date,sys_equip.created_by,sys_equip.last_update_date,sys_equip.update_user_id,sys_equip.working_directory,sys_equip.device_type,ssn.ip_address ")
#mydata.getXMLString("SYS_EQUIP","export", " join ssn on sys_equip.ssn_id = ssn.ssn_id  join control on control.record_id=1 ", ",DEVICE_ID,", "sys_equip.DEVICE_ID,sys_equip.ssn_id,sys_equip.operating_zone,sys_equip.create_date,sys_equip.created_by,sys_equip.last_update_date,sys_equip.update_user_id,sys_equip.working_directory,sys_equip.device_type,ssn.ip_address, control.default_wh_id as wh_id, 'OK' as equipment_status ")
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
##mydata.getXMLString("SSN_TYPE_COND","export", "  ", ",SSN_ID,STATUS,TYPE_CODE,TYPE_HEADER_CODE,")
##mydata = None
##mydata = xmldata()
##tables 
##GRN
##PROD_COND_STATUS
##PRODUCT_CONDITION
##SSN_TEST
##SSN_TEST_RESULTS
##mydata = None
##mydata = xmldata()
##mydata.getXMLString("TRANSACTIONS_ARCHIVE","export", " where trn_date > cast('2013-08-20 10:20' as timestamp)  ", ",NONE," )
#mydata = None
#mydata = xmldata()
##mydata.getXMLString("SYS_LABEL","export", " ", ",NONE," )
#mydata = None
#mydata = xmldata()
##mydata.getXMLString("SYS_LABEL_VAR","export", " ", ",NONE," )
#mydata = None
#mydata = xmldata()
##mydata.getXMLString("PROD_PROFILE","export", " ", ",PROD_ID,COMPANY_ID," )
##mydata.getXMLString("PROD_PROFILE","export", " where last_update_date > cast('2013-08-20 10:20' as timestamp)  ", ",PROD_ID,COMPANY_ID," )
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("WAREHOUSE","export", " ", ",WH_ID," )
#mydata = None
#mydata = xmldata()
##mydata.getXMLString("ARCHIVE_LAYOUT","export", " ", ",NONE," )
###################
con.commit()
#print("UPDATE CONTROL SET LAST_MIRROR_DATE = '%s' " % (myendtime))
#cur.execute("UPDATE CONTROL SET LAST_MIRROR_DATE = '%s' " % (myendtime))
con.commit()
con.close()

#revert stdout 
#sys.stdout = sys.__stdout__

err.write("closed database\n")

err.close()

