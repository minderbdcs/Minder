#!/bin/bash
#Portions (C) 2013-2016 Integrity Net P/L, Sydney, Australia

INSTALLERDIR=/data/minder/Installer
# Source the library
. ${INSTALLERDIR}/lib/Installer-lib.sh

SERVERTYPE="Minder Firebird Database"
TITLE="Minder Series Firerbird Configurator for ${SERVERTYPE} Server v5.4"
VER="3.1"
LOG="/tmp/Minder-MinderInstall-FBserver.log"

${CLEAR}
${ECHO} "${RCol}${BICya}${TITLE} - v${VER}${RCol}"
${ECHO} ${LIBMSG}
${ECHO} "${IGre}Results will be saved in ${IYel}${LOG}${RCol}"
INS_logComponent "Run at: "
date | tee -a ${LOG}
${ECHO} -n ${BIYel}
INS_logComponent "on Node: "
NODE=`uname -n`
${ECHO} -n ${BIYel}
INS_logItem $NODE
CDIR=`pwd`

INS_logSection "Installing Minder Series configuration for Firebird Database Server"
cd ${INSTALLERDIR}
INS_logSubSection "Installing Minder UDF libraries"
${ECHO} "Updating Function Library: "
INS_logItem "Minder UDF libraries "
cp ../../pkgs/UDF/* /opt/firebird/UDF
cd  /opt/firebird/UDF
cp udf_file.so udf_file.dll.so
cd ${INSTALLERDIR}
${ECHO}
INS_logSubSection "Creating new random Database Master password"
INS_logItem "1) Generate unique password "
ISC_PASSWD=ictWHHaB
# openssl generates random data.
    /usr/bin/openssl </dev/null >/dev/null 2>/dev/null
    if [ $? -eq 0 ]
    then
        # We generate 20 random chars, strip any '/''s and get the first 8
        NewPasswd=`/usr/bin/openssl rand -base64 20 | tr -d '/' | cut -c1-8`
    fi

    # mkpasswd is a bit of a hassle, but check to see if it's there
    if [ -z "$NewPasswd" ]
    then
        if [ -f /usr/bin/mkpasswd ]
        then
            NewPasswd=`/usr/bin/mkpasswd -l 8`
        fi
    fi
    /opt/firebird/bin/gsec -user sysdba -password $ISC_PASSWD -di > /dev/null 2>&1
    RC=$?
    if [ ${RC} -eq 0 ] ; then
INS_logItem "2) Install unique password  into Database"
	/opt/firebird/bin/gsec -user sysdba -password ${ISC_PASSWD} -modify sysdba -pw $NewPasswd > /dev/null 2>&1
INS_logItem "3) Save unique password in Secured files"
	DBAPasswordFile=/opt/firebird/SYSDBA.password

        cat << EOT >$DBAPasswordFile
	# Firebird generated password for user SYSDBA is:

	ISC_USER=sysdba
	ISC_PASSWD=$NewPasswd
EOT
  chown root:root ${DBAPasswordFile}
  chmod 400 ${DBAPasswordFile}

	DBAPasswordFile=/etc/minder/SYSDBA.password

        cat << EOT >$DBAPasswordFile
	# Firebird generated password for user SYSDBA is:

	ISC_USER=sysdba
	ISC_PASSWD=$NewPasswd
EOT
  chown bdcs:bdcs ${DBAPasswordFile}
  chmod 440 ${DBAPasswordFile}

    else
	${ECHO} "ERROR: Unexpected Master Password in Security Database - Contact BDCS Support" | tee -a ${LOG}
    fi
${ECHO}
INS_logSubSection "Configuration of Firewalls (if active)"
if [ -f "/etc/sysconfig/iptables" ] ; then
INS_logSubSection "IPtables Firewall is active: Configuring IPtables Firewall"
  REJECT_RULE_NO=$(iptables -L INPUT --line-numbers | grep 'REJECT' | awk '{print $1}');/sbin/iptables -I INPUT $REJECT_RULE_NO -m state --state NEW -m tcp -p tcp --dport 3049 -j ACCEPT -m comment --comment "Permit Firebird Event Listener"
  INS_logComponent "FirebirdEvents "
  REJECT_RULE_NO=$(iptables -L INPUT --line-numbers | grep 'REJECT' | awk '{print $1}');/sbin/iptables -I INPUT $REJECT_RULE_NO -m state --state NEW -m tcp -p tcp --dport 3050 -j ACCEPT -m comment --comment "Permit Firebird SQL requests"
  INS_logComponent "Firebird "
  REJECT_RULE_NO=$(iptables -L INPUT --line-numbers | grep 'REJECT' | awk '{print $1}');/sbin/iptables -I INPUT $REJECT_RULE_NO -m state --state NEW -m tcp -p tcp --dport 10000 -j ACCEPT -m comment --comment "Permit Webmin access"
  INS_logItem "Webmin "
  service iptables save
  INS_logComponent "Reloading Firewall... "
  service iptables restart
  INS_logItem "NOTE: Firewall ACTIVATED"
  ${ECHO}
else
INS_logSubSection "IPtables Firewall is NOT in use"
fi
if [ -d /etc/firewalld/services ] ; then
INS_logSubSection "FirewallD Firewall is active: Configuring FirewallD Firewall"
FWDzone=`firewall-cmd --get-active-zones | head -1`
  INS_logComponent "FirebirdEvents "
  firewall-cmd --permanent --zone=${FWDzone} --add-port=3049/tcp
  INS_logComponent "Firebird "
  firewall-cmd --permanent --zone=${FWDzone} --add-port=3050/tcp
  INS_logComponent "Webmin "
  firewall-cmd --permanent --zone=${FWDzone} --add-port=10000/tcp
  ${ECHO}
  INS_logComponent "Reloading Firewall... "
  firewall-cmd --reload
  INS_logItem "NOTE: Firewall ACTIVATED"
  ${ECHO}
else
INS_logSubSection "FirewallD Firewall is NOT in use"
fi
cd ${INSTALLERDIR}
INS_logSubSection "Setting File & Directory permissions & ownership"
  INS_logComponent "Databases "
chown firebird:bdcs /data /data/fbdb /data/fbext /data/fbtmp /data/pkgs /data/tmp
chmod 775 /data /data/tmp
chmod 770 /data/fbdb /data/fbext /data/fbtmp /data/pkgs
  INS_logComponent "DatabaseConfig "
chown firebird:bdcs /etc/firebird /etc/firebird/* /opt/firebird/firebird.conf /opt/firebird/aliases.conf /data/minder /data/logs
chmod 770 /etc/firebird
chmod 664 /opt/firebird/firebird.conf /opt/firebird/aliases.conf /opt/firebird/firebird.log
  INS_logItem "MinderConfig "
chown bdcs:bdcs /etc/minder /etc/minder/* /data/minder/Installer
chmod 770 /etc/minder
chmod g+w /etc/minder/*
chmod 660 /etc/firebird/login.conf 
INS_logSubSection "Configuring Cron Tasks"
chown bdcs:bdcs /data/minder/cronjobs /data/minder/cronjobs/*
chmod 770 /data/minder/cronjobs /data/minder/cronjobs/*
crontab < ../../pkgs/minder.crontab.root

IS_FDB_SERVER="Y"
export IS_FDB_SERVER
INS_validateResult "OK" "Configuration"
