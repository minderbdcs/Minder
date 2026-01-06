#!/usr/bin/env python2
import sys, time , os

import fdb
#import fdb;fdb.init(type_conv=200)
#import mx

import datetime 

import StringIO

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
			outp = open('/data/ftp/default/ftproot/' + ftptype + '/' + dataset + '.csv','wb')
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
			for pos in range(len(data_fields)):
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
			if os.name == 'nt':
				print( wk_rowdata[:-1] )
			else:
				print( wk_rowdata[:-1] + "\r")
			data_fields = cur.fetchone()
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
	err = open('/data/tmp/export.csv.log','a')
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


#		dsn="localhost:minder",
#		dsn="192.168.61.77:apcd",
#	dsn="als-win:minder",
#	dsn="als-rtm2:minder",
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

#mydata.getXMLString("PERSON","export", "  ", ",NONE,")
#mydata.getXMLString("PERSON","export", " where company_id='AITS' ", ",PERSON_ID,COMPANY_ID," )
#mydata = None
mydata = xmldata()
#mydata.getXMLString("PERSON_ADDRESS","export", " where company_id='AITS' ", ",NONE," )
#mydata = None
mydata = xmldata()
#mydata.getXMLString("ISSN","export", "  ", ",SSN_ID,")
#mydata.getXMLString("ISSN","export", " where last_update_date > cast('2013-08-20 10:20' as timestamp)  ", ",SSN_ID," )
#mydata.getXMLString("ISSN","export", " where last_update_date > cast('2013-08-20 10:20' as timestamp)  ", ",SSN_ID,", "WH_ID,LOCN_ID,SSN_ID" )
mydata.getXMLString("ISSN","export", " where last_update_date > cast('2020-04-08 11:20' as timestamp)  ", ",SSN_ID,"  )
#mydata.getXMLString("ISSN","export", " where company_id='AITS'  ", ",SSN_ID,"  )
mydata = None
mydata = xmldata()
#mydata.getXMLString("PICK_ORDER","export", "  ", ",PICK_ORDER,")
#mydata.getXMLString("PICK_ORDER","export", " where company_id='AITS'  ", ",PICK_ORDER,")
mydata.getXMLString("PICK_ORDER","export", " where last_update_date>'2020-04-08 11:20' ", ",PICK_ORDER,")
mydata = None
mydata = xmldata()
#mydata.getXMLString("PICK_ITEM","export", "  ", ",PICK_LABEL_NO,")
#mydata.getXMLString("PICK_ITEM","export", " where company_id='AITS'  ", ",PICK_LABEL_NO,")
mydata.getXMLString("PICK_ITEM","export", " where last_update_date>'2020-04-08 11:20' ", ",PICK_LABEL_NO,")
mydata = None
mydata = xmldata()
#mydata.getXMLString("PICK_ITEM_DETAIL","export", "  ", ",PICK_DETAIL_ID,")
#mydata.getXMLString("PICK_ITEM_DETAIL","export", " where company_id='AITS' ", ",PICK_DETAIL_ID,")
mydata.getXMLString("PICK_ITEM_DETAIL","export", " where last_update_date>'2020-04-08 11:20' ", ",PICK_DETAIL_NO,")
mydata = None
mydata = xmldata()
#mydata.getXMLString("PICK_ITEM_LINE_NO","export", "  ", ",PICK_ORDER,")
#mydata.getXMLString("PICK_ITEM_LINE_NO","export", " where pick_order in (select po.pick_order from pick_order po where po.company_id='AITS') ", ",PICK_ORDER,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("PICK_ITEM_CANCEL","export", "  ", ",NONE,")
mydata = None
mydata = xmldata()
#mydata.getXMLString("PICK_DESPATCH","export", "  ", ",DESPATCH_ID,")
#mydata.getXMLString("PICK_DESPATCH","export", " where despatch_id > 132242  ", ",DESPATCH_ID," )
#mydata.getXMLString("PICK_DESPATCH","export", " where despatch_id in (select pid.despatch_id from pick_item_detail pid where pid.company_id='AITS' and (pid.despatch_id is not null) group by pid.despatch_id )    ", ",DESPATCH_ID," )
mydata = None
mydata = xmldata()
#mydata.getXMLString("PACK_ID","export", "  ", ",PACK_ID,")
#mydata.getXMLString("PACK_ID","export", "  where despatch_id in (select pid.despatch_id from pick_item_detail pid where pid.company_id='AITS' and (pid.despatch_id is not null) group by pid.despatch_id ) ", ",PACK_ID,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("SSN_HIST","export", "  ", ",RECORD_ID,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("LOCATION","export", "  ", ",WH_ID,LOCN_ID,")
#mydata.getXMLString("LOCATION","export", " where WH_ID  starting 'ZW'  ", ",NONE,", "WH_ID,LOCN_ID,LOCN_NAME, LOCN_STAT,MOVE_STAT, STORE_TYPE,STORE_AREA,STORE_METH, INSTANCE_ID,MAX_QTY,MIN_QTY,REORDER_QTY, MOVEABLE_LOCN, LOCN_TYPE,LABEL_DIRECTION,LABEL_SIDE, LOCN_INT_DIMENSION_X,LOCN_INT_DIMENSION_Y,LOCN_INT_DIMENSION_Z,LOCN_DIMENSION_UOM" )
#mydata.getXMLString("LOCATION","export", " where WH_ID  starting '0'  ", ",NONE,"  )

mydata = None
mydata = xmldata()
#mydata.getXMLString("SSN","export", "  ", ",SSN_ID,")
#mydata.getXMLString("SSN","export", " where last_update_date > cast('2013-08-20 10:22:00' as timestamp)  ", ",SSN_ID," )
#mydata.getXMLString("SSN","export", " where last_update_date > cast('2013-08-20 10:22:00' as timestamp)  ", ",SSN_ID,", "WH_ID,LOCN_ID,SSN_ID" )
#mydata.getXMLString("SSN","export", " where company_id='AITS'  ", ",SSN_ID,"  )
mydata = None
mydata = xmldata()
#mydata.getXMLString("PICK_INVOICE","export", " where company_id='AITS'  ", ",INVOICE_ID,"  )
mydata = None
mydata = xmldata()
#mydata.getXMLString("PICK_INVOICE_LINE","export", " where invline_invoice_id in (select pi.invoice_id from pick_invoice pi where pi.company_id='AITS')  ", ",INVLINE_LINE_ID,"  )
mydata = None
#mydata = xmldata()
#mydata.getXMLString("TRANSACTIONS_ARCHIVE","export", " ", ",NONE,")
#mydata.getXMLString("TRANSACTIONS_ARCHIVE","export", " WHERE TRN_DATE>cast('AUG-01-2009' as timestamp) ORDER BY TRN_DATE ", ",NONE,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("OPTIONS","export", " where group_code starting 'SCN'  ", ",NONE,")
#mydata.getXMLString("OPTIONS","export", "  ", ",NONE,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("SYS_LABEL","export", " ", ",NONE,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("SYS_LABEL_VAR","export", " ", ",NONE,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("SYS_SCREEN","export", " ", ",RECORD_ID,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("SYS_SCREEN_VAR","export", " ", ",RECORD_ID,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("SYS_SCREEN_COLOUR","export", " ", ",RECORD_ID,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("SYS_SCREEN_TAB","export", " ", ",RECORD_ID,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("SYS_SCREEN_ORDER","export", " ", ",RECORD_ID,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("SYS_SCREEN_TABLE","export", " ", ",RECORD_ID,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("SYS_USER", "export", " WHERE NOT MOVE_STAT IN ('ST')")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("GRN","export", "  ", ",GRN,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("GRN_ORDER","export", "  ", ",GRN_LABEL_NO,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("PRODUCT_CONDITION","export", "  ", ",CODE,DESCRIPTION,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("PRODUCT_COND_STATUS","export", "  ", ",SSN_ID,CODE,DESCRIPTION,ORIGINAL_TEST,")
#mydata.getXMLString("PROD_PROFILE","export", "  ", ",NONE,", "PROD_ID,SHORT_DESC,LONG_DESC,ALTERNATE_ID,PROD_TYPE,STOCK,SPECIAL_INSTR, HOME_LOCN_ID,SUPPLIER_NO1,SUPPLIER_NO2,SUPPLIER_NO3,SUPPLIER_NO1_PROD,SUPPLIER_NO2_PROD,SUPPLIER_NO3_PROD,SUPPLIER_PREFER,UOM,ISSUE_UOM,ORDER_UOM,ISSUE_PER_ORDER_UNIT,PALLET_CFG_C,PERM_LEVEL,SSN_TRACK,TOG_C,DEFAULT_ISSUE_QTY,BACK_ORDER_QTY,RESERVED_QTY,MAX_QTY,MIN_QTY,REORDER_QTY,MAX_ISSUE_QTY, PROD_RETRIEVE_STATUS,COMPANY_ID,ISSUE_PER_INNER_UNIT,ORDER_WEIGHT,NET_WEIGHT_UOM,ORDER_WEIGHT_UOM,ISSUE,INNER_UOM,INNER_WEIGHT_UOM,PALLET_CFG_INNER,PALLET_CFG_ALTERNATE,NET_WEIGHT, ALTERNATE_COMPANY_ID, UOM_SIZE,MAX_WEIGHT_UNDER,MAX_WEIGHT_OVER,DIMENSION_X,DIMENSION_Y,DIMENSION_Z,DIMENSION_X_UOM,DIMENSION_Y_UOM,DIMENSION_Z_UOM,EXPORT_CATEGORY,PICK_IMPORT,TEMPERATURE_ZONE,TAX_APPLICABLE,SSN_TYPE,PP_PACKAGE_TYPE,VOLUME_UOM,ORIENTATION,PROD_TYPE_COMMENT,HAZARD_TYPE, SALE_PER_MTH, SALE_VOL_PER_MTH, STORE_TYPE, ALT_PROD_TYPE,ALT_HOME_LOCN,ALT_HOME_LOCN2,ALT_HOME_LOCN3,PP_MATERIAL_SAFETY_DATA,PP_MATERIAL_SAFETY_DATA_NO,PP_HAZARD_STATUS,PP_HAZARD_WARNING,PP_HAZARD_IMAGE1,PP_HAZARD_IMAGE2,PP_HAZARD_IMAGE3")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("SSN_TEST","export", "  ", ",TEST_ID,")
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("SSN_TEST_RESULTS","export", "  ", ",NONE,")
mydata = None
#tables 
#GRN
#PROD_COND_STATUS
#PRODUCT_CONDITION
#SSN_TEST
#SSN_TEST_RESULTS
#mydata = None
#mydata = xmldata()
#mydata.getXMLString("TRANSACTIONS_ARCHIVE","export", " where trn_date > cast('2013-08-20 10:20' as timestamp)  ", ",NONE," )
mydata = None
mydata = xmldata()
#mydata.getXMLString("SYS_LABEL","export", " ", ",NONE," )
mydata = None
mydata = xmldata()
#mydata.getXMLString("SYS_LABEL_VAR","export", " ", ",NONE," )
mydata = None
mydata = xmldata()
#mydata.getXMLString("PROD_PROFILE","export", " ", ",PROD_ID,COMPANY_ID," )
#mydata.getXMLString("PROD_PROFILE","export", " where last_update_date > cast('2013-08-20 10:20' as timestamp)  ", ",PROD_ID,COMPANY_ID," )
mydata = None
mydata = xmldata()
#mydata.getXMLString("WAREHOUSE","export", " ", ",WH_ID," )
mydata = None
mydata = xmldata()
#mydata.getXMLString("BRAND","export", " ", ",CODE," )
mydata = None
mydata = xmldata()
#mydata.getXMLString("SSN_TYPE","export", " ", ",CODE," )
mydata = None
mydata = xmldata()
#mydata.getXMLString("GENERIC","export", " ", ",SSN_TYPE,CODE," )
mydata = None
mydata = xmldata()
#mydata.getXMLString("PRODUCT_DESCRIPTION","export", " ", ",TYPE_CODE,FIELD_CODE,DESCRIPTION," )
mydata = None
mydata = xmldata()
#mydata.getXMLString("GLOBAL_CONDITIONS","export", " ", ",OTHER_NO,DESCRIPTION," )
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

