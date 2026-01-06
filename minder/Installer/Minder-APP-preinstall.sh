#!/bin/bash
#Portions (C) 2013 Integrity Net P/L, Sydney, Australia

INSTALLERDIR=/data/minder/Installer
# Source the library
. ${INSTALLERDIR}/lib/Installer-lib.sh

SERVERTYPE="Minder Series Application"
TITLE="Minder Series pre-installation valedator for ${SERVERTYPE} Server v5.4"
VER="3.1"
LOG="/tmp/Minder-PreInstall-Check-Appserver.log"
TARGETMODE=$1

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
# this really only works on EL lookalikes
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

INS_logSection "Examine overall platform processing capability"
INS_logSubSection "CPU cores available"
NCORES=`nproc --all`
${ECHO} "#Cores" >> ${LOG}
# Testmode
if [ "${TARGETMODE}" = "TEST" ] ; then
  MINCORE=2
else
  MINCORE=4
fi
if [ ${NCORES} -ge "${MINCORE}" ] ; then
  CORESTAT="OK"
else
  CORESTAT="ER"
fi
INS_testPrefix ${CORESTAT}
${ECHO} "${NCORES} Logical CPU's" | tee -a ${LOG}

INS_logSubSection "Available RAM"
if [ "$ELVER" = "el7" ] ; then
  RAM=`free | tail -n 2 | head -n 1 | awk '{s+=$2} END {print s / 1024 / 1024}'`
elif [ "$ELVER" = "el6" -o "$ELVER" = "el6uek" ] ; then
  RAM=`free -o | tail -n 2 | head -n 1 | awk '{s+=$2} END {print s / 1024 / 1024}'`
else
  ${ECHO} "FATAL: O/S Version not based on el6 or el7" | tee -a ${LOG}
  ${ECHO} "${BIRed}Please contact Support${RCol}"
  exit -1
fi
${ECHO} "#RAM" >> ${LOG}
if [ "${TARGETMODE}" = "TEST" ] ; then
  MINRAM=2
else
  MINRAM=8
fi
if is_first_floating_number_bigger ${RAM} ${MINRAM}; then
  RAMSTAT="OK"
else
  RAMSTAT="ER"
fi
INS_testPrefix ${RAMSTAT}
${ECHO} "${RAM} Gb of RAM" | tee -a ${LOG}

INS_logSection "Examine overall platform Storage capability"
INS_logSubSection "Check HDD Storage available"
#fdisk -l | egrep 'Disk.*bytes' | awk '{ sub(/,/,""); sum +=$3; print $2 $3 $4} END { print "------"; print "total" sum "GB"; }'
cat /proc/partitions | egrep "sd[a-z][0-9]|md[0-9]|dm-[0-9]" | sort -k4 | tee -a ${LOG}
# check if /data is large enough
DISKSTAT="OK"

VALID="ERR"
if [ ${ARCHSTAT} = "OK" -a ${OSSTAT} = "OK" -a ${RAMSTAT} = "OK" -a ${DISKSTAT} = "OK" ] ; then
  VALID="OK"
fi
INS_validateResult ${VALID} "Platform"

INS_logSection "Check and Configure Groups, Supplemental Groups and Users"

TFILE="/etc/group"
INS_logSubSection "${TFILE} contents"

INS_testGroup "firebird" "84"
INS_testGroup "bdcs" "500"


TFILE="/etc/passwd"
INS_logSubSection "${TFILE} contents"

INS_testUser "firebird" "84" "-r"
INS_testUser "bdcs" "500" "-m"
echo 'bdcs:$1$abcde$QhHDLHQZHE/lE6lBKx2wq1' | chpasswd -e

INS_logSection "Check and Configure Minder Directory trees"
INS_logSubSection "/data"
TREE="/data"
EXIST_DATA="UK"
${ECHO} -n "Result: " | tee -a ${LOG}
if [ -d ${TREE} ] ; then
  ${ECHO} -n "${Gre}${TREE} "
  ${ECHO} "OK" >> ${LOG}
else
  mkdir ${TREE}
  ${ECHO} -n "${Yel}${TREE} "
  ${ECHO} "WARNING: Created" >> ${LOG}
fi
  chmod 775 ${TREE}
  chown firebird:bdcs ${TREE}

  INS_testDir ${TREE}/backup bdcs:bdcs 770
  INS_testDir ${TREE}/ftp apache:bdcs 770
  INS_testDir ${TREE}/ftp/default apache:bdcs 770
  INS_testDir ${TREE}/sites apache:bdcs 770
  INS_testDir ${TREE}/reports apache:bdcs 770
  INS_testDir ${TREE}/tmp firebird:bdcs 770
  INS_testDir ${TREE}/logs apache:bdcs 770

  TSTDIR="${TREE}/minder"
  if [ -d ${TSTDIR} ] ; then
    ${ECHO} -n "${Gre}${TSTDIR} "
  else
    EXIST_DATA="UK"
    ${ECHO} -n "${Yel}${TSTDIR} "
    ${ECHO} "Created ${TSTDIR}" >>  ${LOG}
    mkdir ${TSTDIR}
    chown bdcs:bdcs ${TSTDIR}
    chmod 775 ${TSTDIR}
    EXIST_DATA="OK"
  fi
  INS_testDir ${TREE}/minder firebird:bdcs 770
  if [ ${EXIST_DATA} = "UK" ] ; then                                                       
    EXIST_DATA="OK"
  fi
${ECHO}
INS_validateResult ${EXIST_DATA} "Directory structure"

cd ${INSTALLERDIR}
if [ -f MinderBundle.cpio.gz ] ; then
  ${ECHO} "${BICya}Unpacking Minder File Bundle${RCol}"
  ${ECHO} "Unpacking Minder File Bundle" >> ${LOG}
  cd /
  gunzip -cd ${INSTALLERDIR}/MinderBundle.cpio.gz | cpio -icdumB
  cd ${INSTALLERDIR}
  rm MinderBundle.cpio.gz
fi
