#!/usr/bin/env python2
"""
<title>
WHTimezone.py, Version 10.08.17
</title>
<long>
Updates Warehouse table in the database
Updating the Timezone fields  
1. Looks for Warehouse records with a Country and City
2. If the latitude or Longitude are not populated
   gets the Citys Latitude and Longitude from google maps
3. Gets the Timezone for the Latitude and Longtide using tzwhere
4. Calculates the Offset and Timezone short code using pytz
5. Calculates whether in Daylight saving using pytz and datetime.timedelta 
6. Updates the Warehouses Latitude, Longitude, Timezone , Timezone Short Code, In Daylight, UTC Offset
<br>
Parameters: <tt>None</tt>
<br>
<br>
</long>
"""
import sys
import string
import time , os ,glob
import fileinput
import requests
import datetime
import pytz
#import tzwhere
from tzwhere import tzwhere
import fdb
import urllib

###############################################################################
def is_dst(zonename):
    tz = pytz.timezone(zonename)
    now = pytz.utc.localize(datetime.datetime.utcnow())
    return now.astimezone(tz).dst() != datetime.timedelta(0)

###############################################################################
def getWarehouse(  ):
	" get the files to send to printer "	
	global  con
	print "Start getFile"
	print time.asctime()
	cur = con.cursor()
	cur2 = con.cursor()
	# first get the next file name to send
        #wk_tzwhere = tzwhere.tzwhere()
#/*
#WH_LATITUDE             
#WH_LONGITUDE           
#WH_TIMEZONE           
#WH_TIMEZONE_SHORT_CODE 
#WH_IN_DAYLIGHT_SAVING  
#WH_UTC_OFFSET
#*/
	query1 = """select wh_id, address1,address2,address3,address4 ,wh_city, wh_country, wh_latitude, wh_longitude
from warehouse   
where coalesce(wh_city,'') <> ''
and   coalesce(wh_country,'') <> ''
"""
	print query1
	cur.execute(query1)
	## get data record
	data_fields = cur.fetchone()
	#print data_fields
	if data_fields is None:
		print "no warehouses with city and country "
		wh_id = ""
		wh_address1 = ""
		wh_address2 = ""
		wh_address3 = ""
		wh_address4 = ""
		wh_city = ""
		wh_country = ""
		wh_latitude = None
		wh_longitude = None
	else:
		while not data_fields is None:
                        wk_lat = None
                        wk_lon = None
                        timezone = None
                        dt2 = None 
			#print data_fields
			if data_fields[0] is None:
				wh_id = ""
			else:
				wh_id = data_fields[0]
                                print "WH_ID:", wh_id
			if data_fields[1] is None:
				wh_address1 = ""
			else:
				wh_address1 = data_fields[1]
                                print "Address1:", wh_address1
			if data_fields[2] is None:
				wh_address2 = ""
			else:
				wh_address2 = data_fields[2]
                                print "Address2:", wh_address2
			if data_fields[3] is None:
				wh_address3 = ""
			else:
				wh_address3 = data_fields[3]
                                print "Address3:", wh_address3
			if data_fields[4] is None:
				wh_address4 = ""
			else:
				wh_address4 = data_fields[4]
                                print "Address4:", wh_address4
			if data_fields[5] is None:
				wh_city = ""
			else:
				wh_city = data_fields[5]
                                print "City:", wh_city
			if data_fields[6] is None:
				wh_country = ""
			else:
				wh_country = data_fields[6]
                                print "Country:", wh_country
			if data_fields[7] is None:
				wh_latitude = None
			else:
				wh_latitude = data_fields[7]
                                print "Lat:", wh_latitude
			if data_fields[8] is None:
				wh_longitude = None
			else:
				wh_longitude = data_fields[8]
                                print "Long:", wh_longitude

                        #
                        #if wh_latitude is None or wh_longitude is None:
                        # have to get lat and long from google
                        #url1 = "http://maps.googleapis.com/maps/api/geocode/json?address={},+{}&sensor=false".format(wh_city,wh_country)
                        url1 = "http://maps.googleapis.com/maps/api/geocode/json?address={},{},{},{}+{}&sensor=false".format(urllib.quote_plus(wh_address1),urllib.quote_plus(wh_address2),urllib.quote_plus(wh_address3),urllib.quote_plus(wh_address4),wh_country)
                        print url1
                        wjdata = requests.get(url1).json()
                        #print wjdata
                        #print wjdata['results']
                        if len(wjdata['results']) > 0:
	                        #print wjdata['results'][0]
	                        #print wjdata['results'][0]['geometry']['location']
	                        wk_lat = wjdata['results'][0]['geometry']['location']['lat']
	                        wk_lon = wjdata['results'][0]['geometry']['location']['lng']
	                        print wk_lat
	                        print wk_lon
                        else:
                                print "Address not found"
                                # use the values in the db
                                wk_lat = wh_latitude
                                wk_lon = wh_longitude
                        if wk_lat is None or wk_lon is None: 
                                print "Latitude or Longitude not populated"
                        else:
	                        # get timezone for lat and long
	                        wk_tzwhere = tzwhere.tzwhere()
	                        #
	                        wk_timezone_str = wk_tzwhere.tzNameAt(wk_lat, wk_lon)
	                        # the timezone
	                        print wk_timezone_str
	                        timezone = pytz.timezone(wk_timezone_str)
	                        dt2 = datetime.datetime.now(tz=timezone)
	                        wk_timezone_short = dt2.strftime('%Z')
	                        print dt2.strftime('%Z')
	                        # the timezone short code
	                        wk_secondsoffset = dt2.utcoffset().total_seconds()
	                        wk_hoursoffset = wk_secondsoffset / 3600
	                        #print wk_secondsoffset
	                        print wk_hoursoffset
	                        # get whether in daylight saving
	                        wk_is_daylight = is_dst(wk_timezone_str)
	                        print wk_is_daylight
	                        if wk_is_daylight:
	                            wk_is_daylight_text = "T" 
	                        else:
	                            wk_is_daylight_text = "F" 
	                        if wh_latitude is None or wh_longitude is None:
					query2 = """update warehouse  set 
	wh_latitude = '%f' ,
	wh_longitude = '%f' ,
	wh_timezone = '%s' ,
	wh_timezone_short_code = '%s' ,
	wh_in_daylight_saving = '%s' ,
	wh_utc_offset = '%f' 
	where wh_id = '%s' """ % (wk_lat, wk_lon, wk_timezone_str, wk_timezone_short, wk_is_daylight_text, wk_hoursoffset, wh_id)
	                                print query2
	                                cur2.execute(query2)
	                        else:
					#dont have to update lat and long
					query2 = """update warehouse  set 
	wh_timezone = '%s' ,
	wh_timezone_short_code = '%s' ,
	wh_in_daylight_saving = '%s' ,
	wh_utc_offset = '%f' 
	where wh_id = '%s' """ % (wk_timezone_str, wk_timezone_short, wk_is_daylight_text, wk_hoursoffset, wh_id)
	                                print query2
	                                cur2.execute(query2)
			data_fields = cur.fetchone()
	print "End getWarehouse"
	print time.asctime()
###############################################################################
#connect to db

if os.name == 'nt':
	logfile = "d:/tmp/WHTimeZone."
else:
	logfile = "/tmp/WHTimeZone."
havelog = 1;

mydb = "minder"
myhost = "127.0.0.1"
myuser = "minder"
mypasswd = "minder"
#if len(sys.argv)>1:
for i in range( len(sys.argv)):
	inData = sys.argv[i]
	myparms = inData.split("=")
	if "db"  == myparms[0]:
		mydb = myparms[1]
	if "host"  == myparms[0]:
		myhost = myparms[1]
	if "user"  == myparms[0]:
		myuser = myparms[1]
	if "passwd"  == myparms[0]:
		mypasswd = myparms[1]
	if "tmp"  == myparms[0]:
		logfile = myparms[1] + "/WHTimeZone."
print "mydb", mydb
#print "myhost", myhost
logfile = logfile + mydb + ".log"
print "logfile", logfile
#
#redirect stdout and stderr
if (havelog == 1):
	#out = open(logfile,'w')
	out = open(logfile,'a')
	sys.stdout = out
	sys.stderr = out

print "mydb", mydb
print "myhost", myhost
print "myuser", myuser
print "mypassword", mypasswd
con = fdb.connect(
	dsn=myhost + ":" + mydb,
	user=myuser,
	password=mypasswd)

print "connected to db"

wk_date = time.strftime("%d/%m/%y")
#read std or 1st input parm

print "Before Get Warehouses"
wk_now = datetime.datetime.now()
wk_date = wk_now.strftime("%d/%m/%y %H:%M:%S.%f")
print wk_date

# for records in Warehouse with a city and country
getWarehouse( )

print "After Get Warehouses"
wk_now = datetime.datetime.now()
wk_date = wk_now.strftime("%d/%m/%y %H:%M:%S.%f")
print wk_date

con.commit()
out.flush()


print "end of requests" 

wk_now = datetime.datetime.now()
wk_date = wk_now.strftime("%d/%m/%y %H:%M:%S.%f")
print wk_date

#con.commit()

print "end - of "


#revert stdin stdout and stderr
if (havelog == 1):
	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__
	out.close()

###
