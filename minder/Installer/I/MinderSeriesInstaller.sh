#!/bin/bash
#Portions (C) 2013 Integrity Net P/L, Sydney, Australia
SERVERTYPE="Minder Series 5.4"
INSTALLERVER="3.1"
TITLE="Minder Series installation for ${SERVERTYPE} Server"
INSTALLERPACKAGE="MinderInstaller.tgz"
INSTALLHOME="/data"
LOG="/tmp/Minder-Install.log"
IDIR="/data/minder/Installer"
TARGETMODE=$1
if [ "${TARGETMODE}" != "TEST" ] ; then
  TARGETMODE="DEPLOYMENT"
fi
ECHO="/bin/echo -e"
CLEAR="/usr/bin/clear"
GREP="/bin/grep"

RCol='\e[0m'    # Text Reset
# standard 16 colour ANSI pallete
# Regular           High Intensity      Bold                BoldHigh Intens     Underline           Background       High Intensity Background
Bla='\e[0;30m';     IBla='\e[0;90m';    BBla='\e[1;30m';    BIBla='\e[1;90m';   UBla='\e[4;30m';    On_Bla='\e[40m';    On_IBla='\e[0;100m';
Red='\e[0;31m';     IRed='\e[0;91m';    BRed='\e[1;31m';    BIRed='\e[1;91m';   URed='\e[4;31m';    On_Red='\e[41m';    On_IRed='\e[0;101m';
Gre='\e[0;32m';     IGre='\e[0;92m';    BGre='\e[1;32m';    BIGre='\e[1;92m';   UGre='\e[4;32m';    On_Gre='\e[42m';    On_IGre='\e[0;102m';
Yel='\e[0;33m';     IYel='\e[0;93m';    BYel='\e[1;33m';    BIYel='\e[1;93m';   UYel='\e[4;33m';    On_Yel='\e[43m';    On_IYel='\e[0;103m';
Blu='\e[0;34m';     IBlu='\e[0;94m';    BBlu='\e[1;34m';    BIBlu='\e[1;94m';   UBlu='\e[4;34m';    On_Blu='\e[44m';    On_IBlu='\e[0;104m';
Pur='\e[0;35m';     IPur='\e[0;95m';    BPur='\e[1;35m';    BIPur='\e[1;95m';   UPur='\e[4;35m';    On_Pur='\e[45m';    On_IPur='\e[0;105m';
Cya='\e[0;36m';     ICya='\e[0;96m';    BCya='\e[1;36m';    BICya='\e[1;96m';   UCya='\e[4;36m';    On_Cya='\e[46m';    On_ICya='\e[0;106m';
Whi='\e[0;37m';     IWhi='\e[0;97m';    BWhi='\e[1;37m';    BIWhi='\e[1;97m';   UWhi='\e[4;37m';    On_Whi='\e[47m';    On_IWhi='\e[0;107m';
#
${CLEAR}
${ECHO} "${RCol}${BICya}${TITLE} - Installer v${INSTALLERVER}${RCol}\n"
${ECHO} "${IGre}Results will be saved in ${IYel}${LOG}${RCol}"
${ECHO}
${ECHO} "${BICya}Checking Priviliges${RCol}"
if [ $EUID -ne 0 ]; then
  echo "${BIRed}This script must be run as root${RCol}"
  echo "FATAL: This script must be run as root" >> ${LOG}
  echo "Installer terminated" >> ${LOG}
  exit -1
fi 
if [ -f /tmp/Minder-Install.log ] ; then
  ${ECHO} "${BIYel}NOTE: This installer has been run previously.${RCol}" | tee -a ${LOG}
  ${ECHO} -n "${BICya}Do you want to clean up old Installer log files (recommend Y) (Y/N) [Y] ? ${RCol}" 
  read yn
  if [ "${yn}" == "n" -o "${yn}" == "N" ] ; then
    ${ECHO} "Current log output will be appended to the existing log files." | tee -a ${LOG}
  else
    rm -f /tmp/Minder*log
    ${ECHO} "Old logs have been deleted. New files will be created for this run." | tee -a ${LOG}
  fi
fi
${ECHO}
${ECHO} "${TITLE} - Installer v${INSTALLERVER}" > ${LOG}
${ECHO} -n "Run at " | tee -a ${LOG}
date | tee -a ${LOG}
${ECHO} -n ${BIYel}
${ECHO} -n "on Node " | tee -a ${LOG}
NODE=`uname -n`
${ECHO} ${NODE} >> ${LOG}
${ECHO} "${BIYel}${NODE}${RCol}"
${ECHO} "Installing as ${TARGETMODE}" | tee -a ${LOG}
${ECHO}
${ECHO} "${BICya}Unpacking Minder installer...${RCol}"
${ECHO} "Unpacking Minder installer..." >> ${LOG}
CDIR=`pwd`
if [ "${IDIR}" == "${CDIR}" ] ; then
  # Installation has been done, it is being re-run
  ${ECHO} "Installer is running in: ${IDIR}" | tee -a ${LOG}
else
  ${ECHO} "Installer is unpacking from: ${CDIR} to ${IDIR}" | tee -a ${LOG}
  mkdir -p ${IDIR}/lib 2>&1 > /dev/null
  mv MinderScripts.tar.gz ${IDIR} > /dev/null 2>&1
  mv MinderBundle.cpio.gz ${IDIR} > /dev/null 2>&1
  cp MinderSeriesInstaller.sh ${IDIR} > /dev/null 2>&1
fi
cd ${IDIR}
${ECHO} "Installer is now running in: ${IDIR}" | tee -a ${LOG}
if [ -f MinderScripts.tar.gz ] ; then
  ${ECHO} "${BICya}Unpacking Minder installer scripts${RCol}"
  ${ECHO} "Unpacking Minder installer scripts" >> ${LOG}
  tar zxf MinderScripts.tar.gz
  rm MinderScripts.tar.gz
  mv Installer-lib.sh lib
fi

${ECHO}
${ECHO} "${BICya}Site specific security selections:${RCol}"
${ECHO} "\nSite specific security selections:" >> ${LOG}
CREATEAUTH="N"
${ECHO} -n "Should this script create groups and users as necessary (Y/N) [Y] ? "
read yn
if [ "${yn}" == "n" -o "${yn}" == "N" ] ; then
  ${ECHO} "Groups and Users will not be created by this script and must be created manually."
else
  CREATEAUTH="Y"
  ${ECHO} "Groups and Users will be created as necessary by this script."
fi
export CREATEAUTH
${ECHO} "Create Groups and Users = ${CREATEAUTH}" >> ${LOG}

REPOACCESS="N"
${ECHO} -n "Can this script download O/S software packages as necessary (Y/N) [Y] ? "
read yn
if [ "${yn}" == "n" -o "${yn}" == "N" ] ; then
  ${ECHO} "O/S Software packages will not be downloaded and installed by this script and must be installed manually."
else
  REPOACCESS="Y"
  ${ECHO} "O/S Software packages will be downloaded and installed as necessary by this script."
fi
export REPOACCESS
${ECHO} "Install O/S Software Packages = ${REPOACCESS}" >> ${LOG}

EPELACCESS="N"
${ECHO} -n "Can this script download EPEL software packages as necessary (Y/N) [Y] ? "
read yn
if [ "${yn}" == "n" -o "${yn}" == "N" ] ; then
  ${ECHO} "EPEL Software packages will not be downloaded and installed by this script and must be installed manually."
else
  EPELACCESS="Y"
  ${ECHO} "EPEL Software packages will be downloaded and installed as necessary by this script."
fi
export EPELACCESS
${ECHO} "Install EPEL Software Packages = ${EPELACCESS}" >> ${LOG}

${ECHO}
${ECHO} "${BICya}Begin installation of Minder modules:${RCol}"
${ECHO} -e "\nBegin installation of Minder modules:" >> ${LOG}
# disable Screen clearing in installer components
alias clear='echo'
# Installer Components
cd ${IDIR}
if [ -f Minder-FDB-preinstall.sh ] ; then
  echo "Press <ENTER> to install the next module" ; read e
  ${ECHO} "Installer is now running Minder-FDB-preinstall.sh" | tee -a ${LOG}
  ./Minder-FDB-preinstall.sh ${TARGETMODE}
  RESULT=$?
  if [ "${RESULT}" -ne 0 ] ; then
    ${ECHO} "Script failed with code ${RESULT}" | tee -a ${LOG}
    echo "Installer terminated" >> ${LOG}
    exit -1
  fi
fi

cd ${IDIR}
if [ -f Minder-FDB-pkginstall.sh ] ; then
  echo "Press <ENTER> to install the next module" ; read e
  ${ECHO} "Installer is now running Minder-FDB-pkginstall.sh" | tee -a ${LOG}
  ./Minder-FDB-pkginstall.sh ${TARGETMODE}
  RESULT=$?
  if [ "${RESULT}" -ne 0 ] ; then
    ${ECHO} "Script failed with code ${RESULT}" | tee -a ${LOG}
    echo "Installer terminated" >> ${LOG}
    exit -1
  fi
fi
cd ${IDIR}

if [ -f Minder-FDB-MinderInstall.sh ] ; then
  echo "Press <ENTER> to install the next module" ; read e
  ${ECHO} "Installer is now running Minder-FDB-MinderInstall.sh" | tee -a ${LOG}
  ./Minder-FDB-MinderInstall.sh ${TARGETMODE}
  RESULT=$?
  if [ "${RESULT}" -ne 0 ] ; then
    ${ECHO} "Script failed with code ${RESULT}" | tee -a ${LOG}
    echo "Installer terminated" >> ${LOG}
    exit -1
  fi
fi
cd ${IDIR}

if [ -f Minder-APP-preinstall.sh ] ; then
  echo "Press <ENTER> to install the next module" ; read e
  ${ECHO} "Installer is now running Minder-APP-preinstall.sh" | tee -a ${LOG}
  ./Minder-APP-preinstall.sh ${TARGETMODE}
  RESULT=$?
  if [ "${RESULT}" -ne 0 ] ; then
    ${ECHO} "Script failed with code ${RESULT}" | tee -a ${LOG}
    echo "Installer terminated" >> ${LOG}
    exit -1
  fi
fi
cd ${IDIR}

if [ -f Minder-APP-pkginstall.sh ] ; then
  echo "Press <ENTER> to install the next module" ; read e
  ${ECHO} "Installer is now running Minder-APP-pkginstall.sh" | tee -a ${LOG}
  ./Minder-APP-pkginstall.sh ${TARGETMODE}
  RESULT=$?
  if [ "${RESULT}" -ne 0 ] ; then
    ${ECHO} "Script failed with code ${RESULT}" | tee -a ${LOG}
    echo "Installer terminated" >> ${LOG}
    exit -1
  fi
fi
cd ${IDIR}

if [ -f Minder-APP-webserver-config.sh ] ; then
  echo "Press <ENTER> to install the next module" ; read e
  ${ECHO} "Installer is now running Minder-APP-webserver-config.sh" | tee -a ${LOG}
  ./Minder-APP-webserver-config.sh ${TARGETMODE}
  RESULT=$?
  if [ "${RESULT}" -ne 0 ] ; then
    ${ECHO} "Script failed with code ${RESULT}" | tee -a ${LOG}
    echo "Installer terminated" >> ${LOG}
    exit -1
  fi
fi

cd ${IDIR}
if [ -f Minder-APP-MinderInstall.sh ] ; then
  echo "Press <ENTER> to install the next module" ; read e
  ${ECHO} "Installer is now running Minder-APP-MinderInstall.sh" | tee -a ${LOG}
  ./Minder-APP-MinderInstall.sh ${TARGETMODE}
  RESULT=$?
  if [ "${RESULT}" -ne 0 ] ; then
    ${ECHO} "Script failed with code ${RESULT}" | tee -a ${LOG}
    echo "Installer terminated" >> ${LOG}
    exit -1
  fi
fi

cd ${IDIR}
sync
if [ "${IS_FDB_SERVER}" = "Y" ] ; then
  ${ECHO} "Database Server instance installed" | tee -a ${LOG}
fi
if [ "${IS_APP_SERVER}" = "Y" ] ; then
  ${ECHO} "Minder*Series Application Server instance installed" | tee -a ${LOG}
  ${ECHO}
  ${ECHO} "NOTE: Minder*Series Client specific Overlay needs to be installed manually" | tee -a ${LOG}
fi
sleep 1
${ECHO} "Installer has completed the installation of Minder"
sync
sleep 2
if [ "${IS_APP_SERVER}" = "Y" ] ; then
  ${ECHO} "Starting GUI"
  sleep 5
  init 5
fi
