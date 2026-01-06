:
#
# re calc stats on all indexes
#
#
# 
#echo "path" $PATH
PATH=${PATH}:/opt/firebird/bin:/data/asset.rf/script
#echo "path" $PATH
#alias isql='isql-fb'

#isql-fb -u sysdba -p masterkey -i /data/asset.rf/script/dbstat.sql  minder 
#isql -u sysdba -p masterkey -o /tmp/db.$$.log   minder <<EOF
isql-fb -u sysdba -p masterkey -o /tmp/db.$$.log   minder <<EOF
SHOW INDICES;
EXIT;
EOF

awk 'BEGIN {print "SET ECHO;"} NR>1 {print "ALTER INDEX ",$1," INACTIVE;";print "ALTER INDEX ",$1," ACTIVE;"} END {print "EXIT;"}' /tmp/db.$$.log > /tmp/db.$$.1.sql
isql -u sysdba -p masterkey -i /tmp/db.$$.1.sql  minder

awk 'BEGIN {print "SET ECHO;"} NR>1 {print "SET STATISTICS INDEX ",$1,";"} END {print "EXIT;"}' /tmp/db.$$.log > /tmp/db.$$.2.sql
isql -u sysdba -p masterkey -i /tmp/db.$$.2.sql  minder


