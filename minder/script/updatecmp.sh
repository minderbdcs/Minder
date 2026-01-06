#!/bin/sh
#
# for database    - passed to me
#     host        - passed to me
#     tocmp       - passed to me
#     fromcmp     - passed to me
# update company records in setup tables  
mydb=${1:-MINDER}
export mydb
tocmp=${2:-PINPOINT}
export tocmp
fromcmp=${3:-PINPOINT}
export fromcmp
myhost=${4:-127.0.0.1}
export myhost
#PATH=${PATH}:/usr/local/bin
PATH=${PATH}:/usr/local/bin:/sbin
echo $0 >> /tmp/run.doupdcmp.$$.log
echo $1 >> /tmp/run.doupdcmp.$$.log
echo $2 >> /tmp/run.doupdcmp.$$.log
echo $3 >> /tmp/run.doupdcmp.$$.log
echo $4 >> /tmp/run.doupdcmp.$$.log
echo "db:" $mydb >> /tmp/run.doupdcmp.$$.log
echo "tocmp:" $tocmp >> /tmp/run.doupdcmp.$$.log
echo "fromcmp:" $fromcmp >> /tmp/run.doupdcmp.$$.log
echo "host:" $myhost  >> /tmp/run.doupdcmp.$$.log
#
# function to downshift
# usage: cmmd=$(down_shift $cmmd)
function down_shift {
#echo $1 | tr "[:upper:]" "[:lower:]"
echo $1 | tr '[A-Z]' '[a-z]'
} # end down_shift

# who am i
me=`id -u -n`
if [ $# -lt "1" ]
then
	echo $0
	echo "Update the system company in the database"
	echo "parameter 1 is the database to update"
	echo "parameter 4 is the server ipaddress that the database is on - this defaults to 127.0.0.1"
	echo "parameter 2 is the new company id to use - this defaults to PINPOINT"
	echo "parameter 3 is the old company id to use - this defaults to PINPOINT"
	echo "system help requested" >> /tmp/run.doupdcmp.$$.log
	exit 1
fi
# now down shift the System Type
#mydb=$(down_shift $mydb)
#
# must ensure that the reportman prod and test files are in different folders
# then can edit the connection for prod to use the prod database
# the reportman report must use the manifestId parameter
#
# the report can be emailed with the xml file
#
echo "system type is prod" >> /tmp/run.doupdcmp.$$.log
isql -u sysdba -p masterkey $myhost:$mydb <<EOF
update control set company_id = "$tocmp" where company_id = "$fromcmp";
update company set company_id = "$tocmp" where company_id = "$fromcmp";
update access_company set company_id = "$tocmp" where company_id = "$fromcmp";
update options        set code       = "$tocmp" where  code      = "$fromcmp";
update sys_user       set company_id = "$tocmp" where company_id = "$fromcmp";
update zone           set company_id = "$tocmp" where company_id = "$fromcmp";
commit;
update options        set code       = "$tocmp" || substr(code,9,40) where  code   starting   "$fromcmp";
commit;
exit;
EOF
