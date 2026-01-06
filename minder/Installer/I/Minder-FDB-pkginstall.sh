#!/bin/bash
#Portions (C) 2013 Integrity Net P/L, Sydney, Australia

INSTALLERDIR=/data/minder/Installer
# Source the library
. ${INSTALLERDIR}/lib/Installer-lib.sh

SERVERTYPE="Minder Firebird Database"
TITLE="Minder Series Linux Package Loader for ${SERVERTYPE} Server v5.4"
VER="3.1"
LOG="/tmp/Minder-PKGInstall-FBserver.log"

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

INS_logSection "Examine overall platform compatibility"
INS_logSubSection "Hardware Architecture"
ARCH0=`uname -m`
ARCH1=`uname -p`
ARCH2=`uname -i`
if [ ${ARCH0} = "x86_64" -a ${ARCH1} = "x86_64" -a ${ARCH2} = "x86_64" ] ; then
  ARCHSTAT="OK"
else
  ARCHSTAT="ERR"
fi
INS_testPrefix ${ARCHSTAT}
${ECHO} "${ARCH0} ${ARCH1} ${ARCH2}" | tee -a ${LOG}

INS_logSubSection "Kernel Version & Release"
KERN0=`uname -s`
KERN1=`uname -o`
KERN2=`uname -r`
KERN3=`uname -v`
OLD_IFS=${IFS}

IFS='- '
set ${KERN2}
OSREL="$1"
OSALL="$2"

IFS='. '
set ${OSREL}
OSMAJ="$1"
OSVER="$2"
OSPAT="$3"

# this really only works on EL lookalikes
IFS='. '
set ${OSALL}
ELVER="$4"
if [ -z "${ELVER}" ] ; then
 ELVER="$2"
fi
IFS=${OLD_IFS}

OSSTAT="ERR"
if [ ${OSMAJ} -gt 2 ] ; then
  OSSTAT="OK"
else
  if [ ${OSMAJ} -eq 2 ] ; then
    if [ ${OSVER} -ge 6 ] ; then
      if [ ${OSPAT} -ge 32 ] ; then
	OSSTAT="OK"
      fi
    fi
  fi
fi
INS_testPrefix ${OSSTAT}
${ECHO} "${KERN0} ${KERN1} ${KERN2} ${KERN3}" | tee -a ${LOG}
# ELVER contains the EL version as el6, el6uek or el7
INS_logSubSection "Distribution Style & Release"
if [ "${ELVER}" = "el6" -o "${ELVER}" = "el7" -o "${ELVER}" = "el6uek" ] ; then
  ${ECHO} "Enterprise Linux style distribution (CENTOS-RHEL-OL-SL)" | tee -a ${LOG}
else
  ${ECHO} "Unknown style distribution <${ELVER}>" | tee -a ${LOG}
fi

INS_logSubSection "O/S Distribution Version & Release"
if [ -f /etc/os-release ] ; then
  (
    . /etc/os-release
    echo "${NAME} ${VERSION}"
  ) | tee -a ${LOG}
else
  if [ -f /etc/system-release ] ; then
    cat /etc/system-release | tee -a ${LOG}
  else
    if [ -f /etc/oracle-release ] ; then
      cat /etc/redhat-release | tee -a ${LOG}
    else
      if [ -f /etc/redhat-release ] ; then
        cat /etc/redhat-release | tee -a ${LOG}
      fi
    fi
  fi
fi

INS_logSection "Check and Load Linux Software Packages"

if [ "${REPOACCESS}" = "Y" ] ; then
  INS_logSubSection "Load required O/S packages"
else
  INS_logSubSection "Load required O/S packages"
fi
for PKG in telnet wget net-tools php php-snmp expect openssl blktrace sysstat dstat iotop oprofile perf powertop oprofile-jit papi sdparm sg3_utils tuned tuned-utils cups printer-filters foomatic-db-ppds libncurses.so.5 libstdc++.so.6
do
	testOSInstalled "${PKG}"
done

${ECHO} | tee -a ${LOG}
INS_logSubSection "Load Database Server"
${ECHO} | tee -a ${LOG}
if [ "${EPELACCESS}" = "Y" ] ; then
  INS_logSubSection "Load required EPEL packages"
  if [ "$ELVER" = "el7" ] ; then
    EPELVER="epel-release-latest-7.noarch.rpm"
  elif [ "$ELVER" = "el6" -o "$ELVER" = "el6uek" ] ; then
    EPELVER="epel-release-latest-6.noarch.rpm"
  else
    ${ECHO} "FATAL: O/S Version ${ELVER} not based on el6 or el7" | tee -a ${LOG}
    ${ECHO} "${BIRed}Please contact Support${RCol}"
    exit -1
  fi
  if [ -f /etc/yum.repos.d/epel.repo ] ; then
    if [ -f ${EPELVER} ] ; then
      ${ECHO} "EPEL repository already configured" | tee -a ${LOG}
    fi
    ${ECHO} "EPEL repository already enabled" | tee -a ${LOG}
  else
    INS_logSubSection "Install EPEL repository"
    wget -t 2 -T 3 http://dl.fedoraproject.org/pub/epel/${EPELVER} > /dev/null 2>&1
    if [ -f ${EPELVER} ] ; then
      ${ECHO} "Enable EPEL repository" | tee -a ${LOG}
      rpm -i --quiet ${EPELVER} > /dev/null 2>&1
    else
      ${ECHO} "FATAL: Could not Download EPEL repository" | tee -a ${LOG}
      exit -1
    fi
  fi
else
  INS_logSubSection "Test for required EPEL packages"
fi
cd ${INSTALLERDIR}
#EPEL
for PKG in pbzip2 pigz php-interbase inotify-tools cups-pdf
do
	testEPELInstalled "${PKG}"
done

${ECHO} | tee -a ${LOG}
# calculate this...
ALL_PACKAGES="OK"

INS_validateResult ${ALL_PACKAGES} "Linux Package Installation"

INS_logSubSection "Shell Global rc files"

TFILE="/etc/bashrc"
# RHEL
KNOWNSUM=22098
if [ -f "${TFILE}" ] ; then
  CSUM=`sum -r ${TFILE}`
  CALCSUM=`expr "${CSUM}" : '^\(.*\) .*$'`
  if [ "${CALCSUM}" -eq ${KNOWNSUM} ] ; then
    cat <<- 'EOF' >> ${TFILE}
	LS_COLORS=${LS_COLORS}'di=00;36:' ; export LS_COLORS
	alias ls='ls --color=auto'
EOF
	INS_logResult ${Gre} "${TFILE} Updated"
  fi
else
  if [ -f "${TFILE}" ] ; then
	INS_logResult ${BIRed} "${TFILE}: Checksum was: ${CALCSUM} expected ${KNOWNSUM}"
  else
	INS_logResult ${BYel} "NOTE: ${TFILE} is not installed on this server"
  fi
fi

TFILE="/etc/kshrc"
# RHEL
KNOWNSUM=29181
if [ -f "${TFILE}" ] ; then
  CSUM=`sum -r ${TFILE}`
  CALCSUM=`expr "${CSUM}" : '^\(.*\) .*$'`
  if [ "${CALCSUM}" -eq "${KNOWNSUM}" ] ; then
    cat <<- 'EOF' >> ${TFILE}
	LS_COLORS=${LS_COLORS}'di=00;36:' ; export LS_COLORS
	alias ls='ls --color=auto'
EOF
	INS_logResult ${Gre} "${TFILE} Updated"
  fi
else
  if [ -f "${TFILE}" ] ; then
	INS_logResult ${BIRed} "${TFILE}: Checksum was: ${CALCSUM} expected ${KNOWNSUM}"
  else
	INS_logResult ${BYel} "NOTE: ${TFILE} is not installed on this server"
  fi
fi
${ECHO}
INS_validateResult "OK" "Packages Loaded"
