#!/bin/bash
#Portions (C) 2013 Integrity Net P/L, Sydney, Australia

INSTALLERDIR=/data/minder/Installer
# Source the library
. ${INSTALLERDIR}/lib/Installer-lib.sh

SERVERTYPE="Minder Series Application server"
TITLE="Minder Series Webserver configurator for ${SERVERTYPE} Server v5.4"
VER="3.1"
LOG="/tmp/Minder-WebServer-Config-Appserver.log"

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

INS_logSection "Check and Configure Apache WebServer"
INS_logSubSection " and Configure WebServer User, Groups and Supplemental Groups"

TFILE="/etc/group"
INS_logSubSection "${TFILE} contents"

INS_testGroup "firebird" "84"
INS_testGroup "bdcs" "500"

GROUP="apache"
ITEM=`${GREP} "^${GROUP}:" ${TFILE}`
GPRESENT=$?
if [ ${GPRESENT} -eq 0 ] ; then
  ${ECHO} "Found group ${GROUP}" | tee -a ${LOG}
else
  # some systems use www instead of apache
  GROUP2="www"
  ITEM=`${GREP} "^${GROUP2}:" ${TFILE}`
  GPRESENT=$?
  if [ ${GPRESENT} -eq 0 ] ; then
    ${ECHO} "Found user ${GROUP2} as the alternative to group ${GROUP}" | tee -a ${LOG}
  else
    ${ECHO} "Users ${GROUP} nor ${GROUP2} NOT found" | tee -a ${LOG}
    ${ECHO} "${BIRed}FATAL: Please create the group ${GROUP}"
    ${ECHO} "${BIYel}Then re-run this script${RCol}"
    exit -1
  fi
fi

TFILE="/etc/passwd"
INS_logSubSection "${TFILE} contents"

INS_testUser "firebird" "84" "-r"
INS_testUser "bdcs" "500" "-m"
echo 'bdcs:$1$abcde$QhHDLHQZHE/lE6lBKx2wq1' | chpasswd -e

USER="apache"
ITEM=`${GREP} "^${USER}:" ${TFILE}`
UPRESENT=$?
if [ ${UPRESENT} -eq 0 ] ; then
  ${ECHO} "Found user ${USER}" | tee -a ${LOG}
else
  # some systems use wwrun instead of apache
  USER2="wwwrun"
  ITEM=`${GREP} "^${USER2}:" ${TFILE}`
  UPRESENT=$?
  if [ ${UPRESENT} -eq 0 ] ; then
    ${ECHO} "Found user ${USER2} as the alternative to user ${USER}" | tee -a ${LOG}
  else
    ${ECHO} "Users ${USER} nor ${USER2} NOT found" | tee -a ${LOG}
    ${ECHO} "${BIRed}FATAL: Please create the user ${USER}"
    ${ECHO} "${BIYel}Then re-run this script${RCol}"
    exit -1
  fi
fi

INS_logSection "Check and Configure Apache WebServer Configuration files"
WEBSERVER="UK"
EXIST_SVR="UK"
${ECHO} -n "Startup: "

# Determine style of Apache Config
CFILE="/etc/init.d/httpd"
if [ -f ${CFILE} ] ; then
  CSUM=18529
  WEBSERVER="httpd"
else
  CFILE="/etc/init.d/apache2"
  if [ -f ${CFILE} ] ; then
    CSUM=12424
    WEBSERVER="apache2"
  else
    CFILE="/usr/lib/systemd/system/httpd.service"
    if [ -f ${CFILE} ] ; then
      CSUM=51853
      WEBSERVER="httpd"
    else
    ${ECHO} "${Red}Unknown WebServer startup configuration"
    fi
  fi
fi

# Check if the startup file is untouched
WEBSTART="UK"
if [ -f ${CFILE} ] ; then
  SUM=`sum -r ${CFILE}`
  CALCSUM=`expr "${SUM}" : '^\(.*\) .*$'`
  if [ ${CSUM} -eq ${CALCSUM} ] ; then
    WEBSTART="OK"
  else
    WEBSTART="ERR"
    EXIST_SVR="ERR"
  fi
fi
INS_testPrefix ${WEBSTART}
INS_logItem ${CFILE}

${ECHO} -n "${RCol}Running: "
WEBRUN="UK"
RPROC=`ps -e | grep ${WEBSERVER} | head -1`
if [ -z "${RPROC}" ] ; then
  WEBRUN="ERR"
  RPROC="(${WEBSERVER} not started)"
else
  WEBRUN="OK"
fi
INS_testPrefix "${WEBRUN}"
INS_logItem "${RPROC}"
${ECHO} -n "${RCol}Runlevels: "
${ECHO}

CONFIGPROG=`type chkconfig`
CONFIGOUT=`chkconfig ${WEBSERVER} on`
RUNNING=$?
  if [ ${RUNNING} -eq 0 ] ; then
  	${ECHO} -n "${Gre}${WEBSERVER} "
  else
  	${ECHO} -n "${Red}${WEBSERVER} "
  fi

  if [ "${EXIST_FILE}" = "UK" ] ; then                                                       
    EXIST_FILE="OK"
    ${ECHO} "${BIGre}OK${RCol}"
  fi
if [ "${RUNNING}" -eq 0 ] ; then
echo
  ${ECHO} "Web server is now running" | tee -a ${LOG}
else
  ${ECHO} "FATAL: ${CFILE} Startup Configuration not as expected" | tee -a ${LOG}
  ${ECHO} "${BIRed}Please correct ${CFILE} file" | tee -a ${LOG}
  ${ECHO} "${BIYel}Then re-run this script${RCol}" | tee -a ${LOG}
  exit -1
fi

INS_logSection "Check and Configure Minder Directory trees"
WEBROOT=/etc/httpd
if [ -d ${WEBROOT} ] ; then
  WEBCONF=/etc/httpd/conf/httpd.conf
else
  WEBROOT=/etc/apache2
  if [ -d ${WEBROOT} ] ; then
    WEBCONF=/etc/apache2/httpd.conf
  else
    ${ECHO} "${Red} Fatal: No WebServer config file found" | tee -a ${LOG}
    ${ECHO} "${BIRed}\nWebserver configuration FAILED validation - Manual correction required${RCol}" | tee -a ${LOG}
    ${ECHO} "${BIYel}Then re-run this script${RCol}" | tee -a ${LOG}
    exit -1
  fi
fi
TREE="apache"
INS_logSubSection "${TREE} tree"
  
#Renaming the nss.conf file to nss.conf.no (in /etc/httpd/conf.d/) so that it does not load
if [ -f /etc/httpd/conf.d/mod_dnssd.conf ] ; then
  ${ECHO} "Correcting Apache2 configuration re mod_dnss.conf"
  mv /etc/httpd/conf.d/mod_dnssd.conf /etc/httpd/conf.d/mod_dnssd.conf.no
fi
if [ -f /etc/httpd/conf.d/nss.conf ] ; then
  ${ECHO} "Correcting Apache2 configuration re nss.conf"
  mv /etc/httpd/conf.d/nss.conf /etc/httpd/conf.d/nss.conf.no
fi
  EXIST_FILE="OK"
${ECHO}
INS_validateResult ${EXIST_FILE} "Config structure"

INS_logSubSection "Setting File & Directory permissions & ownership"
  INS_logComponent "/data/logs/httpd "
chown  apache:bdcs /data/logs/httpd
chmod 775  /data/logs/httpd
  INS_logComponent "/etc/httpd/conf/httpd.conf "
chown  bdcs:bdcs /etc/httpd/conf/httpd.conf
chmod 664 /etc/httpd/conf/httpd.conf
  INS_logItem "/etc/httpd/virtual.d "
chown -R bdcs:bdcs /etc/httpd/virtual.d
chmod 775 /etc/httpd/virtual.d
chmod 664 /etc/httpd/virtual.d/*

${ECHO} "Starting Apache2"  | tee -a ${LOG}
chkconfig --levels 235 httpd on
service httpd restart
  
${ECHO} "${BICya}\nTODO: additional tests in next revision...${RCol}"
