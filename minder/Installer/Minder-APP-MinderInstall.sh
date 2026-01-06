#!/bin/bash
#Portions (C) 2013 Integrity Net P/L, Sydney, Australia

INSTALLERDIR=/data/minder/Installer
cd ${INSTALLERDIR}
# Source the library
. ${INSTALLERDIR}/lib/Installer-lib.sh

SERVERTYPE="Minder Application"
TITLE="Minder Series Configurator for ${SERVERTYPE} Server v5.4"
VER="3.1"
LOG="/tmp/Minder-MinderInstall-APPserver.log"

${CLEAR}
${ECHO} "${RCol}${BICya}${TITLE} - v${VER}${RCol}"
${ECHO} ${LIBMSG}
INS_logSection "Installing Minder Series configuration for ${SERVERTYPE} Server"
${ECHO} "${IGre}Results will be saved in ${IYel}${LOG}${RCol}"
INS_logComponent "Run at: "
date | tee -a ${LOG}
${ECHO} -n ${BIYel}
INS_logComponent "on Node: "
NODE=`uname -n`
${ECHO} -n ${BIYel}
INS_logItem $NODE
CDIR=`pwd`

cd ${INSTALLERDIR}
INS_logSubSection "Installing Minder Customisations"
${ECHO} "Updating Config Files: "
INS_logSubSection "Configuring Database password"
#TODO - teleport this from the DB server

INS_logSubSection "Configuring Firewalls (if active)"
if [ -f /etc/sysconfig/iptables ] ; then
INS_logSubSection "IPtables Firewall is active: Configuring IPtables Firewall"
  REJECT_RULE_NO=$(iptables -L INPUT --line-numbers | grep 'REJECT' | awk '{print $1}');/sbin/iptables -I INPUT $REJECT_RULE_NO -m state --state NEW -m tcp -p tcp --dport 21 -j ACCEPT -m comment --comment "Permit FTP server"
  INS_logComponent "FTP "
  REJECT_RULE_NO=$(iptables -L INPUT --line-numbers | grep 'REJECT' | awk '{print $1}');/sbin/iptables -I INPUT $REJECT_RULE_NO -m state --state NEW -m tcp -p tcp --dport 80 -j ACCEPT -m comment --comment "Permit HTTP server"
  INS_logComponent "HTTP "
  REJECT_RULE_NO=$(iptables -L INPUT --line-numbers | grep 'REJECT' | awk '{print $1}');/sbin/iptables -I INPUT $REJECT_RULE_NO -m state --state NEW -m tcp -p tcp --dport 443 -j ACCEPT -m comment --comment "Permit HTTPS server"
  INS_logComponent "HTTPS "
  REJECT_RULE_NO=$(iptables -L INPUT --line-numbers | grep 'REJECT' | awk '{print $1}');/sbin/iptables -I INPUT $REJECT_RULE_NO -m state --state NEW -m tcp -p tcp --dport 631 -j ACCEPT -m comment --comment "Permit Cups Admin server"
  INS_logComponent "CupsAdmin "
  REJECT_RULE_NO=$(iptables -L INPUT --line-numbers | grep 'REJECT' | awk '{print $1}');/sbin/iptables -I INPUT $REJECT_RULE_NO -m state --state NEW -m tcp -p tcp --dport 10000 -j ACCEPT -m comment --comment "Permit Webmin Access"
  INS_logComponent "Webmin "
  ${ECHO}
  service iptables save
  service iptables restart
  INS_logComponent "ACTIVATED"
  ${ECHO}
fi
if [ -d /etc/firewalld/services ] ; then
INS_logSubSection "FirewallD Firewall is active: Configuring FirewallD Firewall"
FWDzone=`firewall-cmd --get-active-zones | head -1`
  INS_logComponent "FTP "
  firewall-cmd --permanent --zone=${FWDzone} --add-service=ftp
  INS_logComponent "HTTP "
  firewall-cmd --permanent --zone=${FWDzone} --add-service=http
  INS_logComponent "HTTPS "
  firewall-cmd --permanent --zone=${FWDzone} --add-service=https
  INS_logComponent "CupsAdmin "
  firewall-cmd --permanent --zone=${FWDzone} --add-port=631/tcp
  INS_logComponent "Webmin "
  firewall-cmd --permanent --zone=${FWDzone} --add-port=10000/tcp
  ${ECHO}
  firewall-cmd --reload
  INS_logComponent "ACTIVATED"
  ${ECHO}
fi

INS_logSubSection "Configuring Service startup scripts"
chkconfig --add cmdr
chkconfig --levels 235 cmdr on
chkconfig --add bdcsprint
chkconfig --levels 235 bdcsprint on
INS_logSubSection "Configuring System to start GUI after reboot"
if [ "${ELVER}" = "el7" ] ; then
  INS_logComponent "Configuring SYSSTEMD target to 5"
  ln -sf /lib/systemd/system/runlevel5.target /etc/systemd/system/default.target
else
  INS_logComponent "Configuring INITTAB target to 5"
  cat /etc/inittab | sed "s/id:3:/id:5:/" > /tmp/inittab
  if [ -s /tmp/inittab ] ; then
    cp /tmp/inittab /etc/inittab
    rm /tmp/inittab
  fi
fi

cd ${INSTALLERDIR}
INS_logSubSection "Setting File & Directory permissions & ownership"
  INS_logComponent "/data/sites "
chown firebird:bdcs /data /data/sites
chmod 775 /data /data/sites
  INS_logComponent "/data/logs "
chown apache:bdcs /data/logs
chmod 775 /data /data/logs
  INS_logComponent "/data/tmp "
chown firebird:bdcs /data/tmp
chmod 775 /data/tmp
chmod 777 /data/tmp/session
  INS_logComponent "/data/minder/* "
chown -R bdcs:bdcs /data/minder
cd /data/minder
find . -type d -exec chmod 775 {} \;
find . -type f -exec chmod 664 {} \;
cd ${INSTALLERDIR}
  INS_logComponent "MinderConfig "
chown -R apache:bdcs /etc/minder
chmod 775 /etc/minder
cd /etc/minder
find . -type d -exec chmod 770 {} \;
find . -type f -exec chmod 660 {} \;
cd ${INSTALLERDIR}
chown apache:bdcs /etc/reportmanserver
chmod 660 /etc/reportmanserver
${ECHO}
INS_logSubSection "Configuring Cron Tasks"
chown bdcs:bdcs /data/minder/cronjobs /data/minder/cronjobs/*
chmod 770 /data/minder/cronjobs /data/minder/cronjobs/*
crontab < ../../pkgs/minder.crontab.root

INS_logSection "Check and Configure SELinux, if enabled"
if [ -x "/usr/sbin/semanage" ] ; then
  INS_logSubSection "Installing Minder SELinux Customisations - this takes a while!"
  semanage fcontext -a -t httpd_sys_content_t "/data/sites(/.*)?"
${ECHO} -n "."
  semanage fcontext -a -t httpd_sys_content_t "/data/reports(/.*)?"
${ECHO} -n "."
  semanage fcontext -a -t httpd_sys_content_t "/data/minder(/.*)?"
${ECHO} -n "."
  semanage fcontext -a -t httpd_sys_content_t "/data/logs(/.*)?"
${ECHO} -n "."
  semanage fcontext -a -t httpd_sys_content_t "/data/ftp(/.*)?"
${ECHO} -n "."
  semanage fcontext -a -t httpd_sys_content_t "/opt/reportman(/.*)?"
${ECHO} -n "."
  semanage fcontext -a -t httpd_sys_content_t "/etc/minder(/.*)?"
${ECHO} -n "."
  semanage fcontext -a -t httpd_config_t "/etc/httpd/virtual.d(/.*)?"
${ECHO} -n "."
  semanage fcontext -a -t httpd_user_content_t "/data/tmp(/.*)?"
${ECHO} -n "."
  semanage fcontext -a -t httpd_sys_content_t "/data/tmp/php_errors.log"
${ECHO} -n "."
  #
  setsebool -P httpd_can_network_connect=on httpd_execmem=on mmap_low_allowed=on
  chcon -R -u system_u -r object_r -t httpd_sys_content_t /data/sites/minder/html
${ECHO}
fi
IS_APP_SERVER="Y"
export IS_APP_SERVER
