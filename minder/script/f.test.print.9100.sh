#!/bin/bash
pref="192.168.3"
pref="192.168.1"
#pref="172.19.150"
pref="192.168"
j=0
j=30
while [  $j -le 30 ] 
do
	i=1
	while [  $i -le 255 ] 
	do
		#echo $i
		testip=$pref.$j.$i
		echo $testip
		result=0
		ping -c 1 -w 3 $testip | fgrep "bytes from"
		result=$?
		if [ $result -eq 0 ]
		then
			echo "success "$testip
			timeout 3 bash -c "cat </dev/null >/dev/tcp/$testip/9100"
			result2=$?
			if [ $result2 -eq 0 ]
			then
				echo "success 9100 "$testip
			fi
		fi
		i=$(($i+1))
	done
	j=$(($j+1))
done
