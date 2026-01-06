:
#
# backup the database and ancillary processing
#
#
# 
#echo "path" $PATH
PATH=${PATH}:/opt/firebird/bin:/data/minder/script
#echo "path" $PATH

NOW=`date`
ip addr show | grep "inet " | mailx -s "reboot apcd $NOW" frankl@barcoding.com.au
