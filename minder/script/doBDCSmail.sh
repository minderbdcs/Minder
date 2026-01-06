#!/bin/bash
# get a trigger file to create an email 
# parameters are in extension , out extension
# in folder and out folder
echo $0
inext=$1
export inext
indir=$3
export indir
outext=$2
export outext
# inext is the extension to look for in the indir
sleep 1
umask 0000
for i in `ls ${indir}/*${inext}` 
do
	echo "have file $i"
	date +"%Y-%m-%d %H:%M:%S.%N"
	RESULT=0
	HHMM=`date +"%H%M"`
	export HHMM
	filename=`basename $i`
	export filename
	# line 1 is  ORDER|COMPANY_ID|REPORT_ID|REPORT_URI|TO_EMAIL|CC_EMAIL|ReportType|POWO|
	IN_LINE=`head -1 $i`
	#OFS=$IFS
	#IFS='|'
	#arr2=($IN_LINE)
	#ORDER="$arr2[0]"
	#COMPANY="$arr2[1]"
	#EMAIL="$arr2[4]"
	#COPYEMAIL="$arr2[5]"
	#INVOICETYPE="$arr2[6]"
	#IFS=$OFS
	echo $IN_LINE | awk -F '|' '{print $1;print $2;print $3;print $4;print $5;print $6;print $7;print $8}' > /tmp/mail.2.$$.log
	ORDER=`head -1 /tmp/mail.2.$$.log`
	COMPANY=`head -2 /tmp/mail.2.$$.log | tail -1`
	#REPNO=`head -3 /tmp/mail.2.$$.log | tail -1`
	#REPURI=`head -4 /tmp/mail.2.$$.log | tail -1`
	EMAIL=`head -5 /tmp/mail.2.$$.log | tail -1`
	COPYEMAIL=`head -6 /tmp/mail.2.$$.log | tail -1`
	INVOICETYPE=`head -7 /tmp/mail.2.$$.log | tail -1`
	INVOICETYPE2=${INVOICETYPE%%[[:space:]]}
	INVOICEMESSAGE="The items on the above invoice have been despatched from our warehouse."
	INVOICEPOWO=`head -8 /tmp/mail.2.$$.log | tail -1`
	MAILSIG=`cat /etc/minder/mail/*txt`
	# create invoice file
	RESULT=0
	python /data/asset.rf/python/script/runjsrpt.py $i /tmp/mail.$$.log
	RESULT=$?
	if [ "$RESULT" -eq 0 ]
	then
		EMAILFILE=`tail -1 /tmp/mail.$$.log`
		echo "EMAILFILE:"$EMAILFILE
	else
		echo "RESULT:"$RESULT
		EMAILFILE=""
	fi
	#if [ "$INVOICETYPE" = "PS" ] 
	if [ "$INVOICETYPE2" = "PS" ] 
	then
		INVOICEDESC="Packing Slip"
	else
		INVOICEDESC="Invoice"
	fi
	echo "INVOICEDESC:"$INVOICEDESC
	if [ "$EMAILFILE" = "" ] 
	then
		echo "No File to Send"
	else
		echo -e "$INVOICEDESC $ORDER \n\n $INVOICEMESSAGE" > /tmp/mail.3.$$.log 
		cat /etc/minder/mail/*txt >> /tmp/mail.3.$$.log
		if [ "$COPYEMAIL" = "" ] 
		then
			# no copy to
			echo "mutt -s \"$ORDER $INVOICEDESC\" -a $EMAILFILE -- $EMAIL"
			#echo -e "$INVOICEDESC $ORDER \n\n $INVOICEMESSAGE" | mutt -s "$ORDER $INVOICEDESC PO# $INVOICEPOWO" -i /tmp/mail.$$.log  -i /etc/minder/mail/*txt $EMAIL
			echo  "" | mutt -s "$ORDER $INVOICEDESC PO# $INVOICEPOWO"  -i /tmp/mail.3.$$.log -a $EMAILFILE /etc/minder/mail/*jpg --  $EMAIL
		else
			echo "mutt -s \"$ORDER $INVOICEDESC\" -a $EMAILFILE -- $COPYEMAIL $EMAIL"
			#echo -e "$INVOICEDESC $ORDER \n\n $INVOICEMESSAGE" | mutt -s "$ORDER $INVOICEDESC PO# $INVOICEPOWO"  -a $EMAILFILE --    $COPYEMAIL $EMAIL
			echo  "" | mutt -s "$ORDER $INVOICEDESC PO# $INVOICEPOWO" -i /tmp/mail.3.$$.log  -a $EMAILFILE /etc/minder/mail/*jpg --  $COPYEMAIL $EMAIL
		fi
	fi
	RESULT=$?
	if [ "$RESULT" -eq 0 ]
	then
		echo "EMAIL Sent OK"
	else
		echo "EMAIL Not Sent OK RESULT:"$RESULT
	fi
	mv $i ${i%%${inext}}${outext}
done
date +"%Y-%m-%d %H:%M:%S.%N"

