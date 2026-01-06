#!/bin/sh
if [ -d "/data/tmp/session"  ]
then
        echo "have directory for session :"
        # ensure is 777
        #owner=`stat -c '%U' /data/tmp/session`
        #group=`stat -c '%G' /data/tmp/session`
        permision=`stat -c '%a' /data/tmp/session`
        if [ $permision -ne 777 ]
        then
                chmod a+r,a+w,a+x /data/tmp/session
        fi
fi

