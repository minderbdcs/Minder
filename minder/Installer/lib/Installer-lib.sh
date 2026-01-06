# Installer Library
# source this into the installer

ECHO="/bin/echo -e"
CLEAR="/usr/bin/clear"
GREP="/bin/grep"

LIBMSG="Installer utility Library v3.1 loaded"

RCol='\e[0m'    # Text Reset
# standard 16 colour ANSI pallete
# Regular           High Intensity      Bold                BoldHigh Intens     Underline           Background       High Intensity
# Background
Bla='\e[0;30m';     IBla='\e[0;90m';    BBla='\e[1;30m';    BIBla='\e[1;90m';   UBla='\e[4;30m';    On_Bla='\e[40m';    On_IBla='\e
[0;100m';
Red='\e[0;31m';     IRed='\e[0;91m';    BRed='\e[1;31m';    BIRed='\e[1;91m';   URed='\e[4;31m';    On_Red='\e[41m';    On_IRed='\e
[0;101m';
Gre='\e[0;32m';     IGre='\e[0;92m';    BGre='\e[1;32m';    BIGre='\e[1;92m';   UGre='\e[4;32m';    On_Gre='\e[42m';    On_IGre='\e
[0;102m';
Yel='\e[0;33m';     IYel='\e[0;93m';    BYel='\e[1;33m';    BIYel='\e[1;93m';   UYel='\e[4;33m';    On_Yel='\e[43m';    On_IYel='\e
[0;103m';
Blu='\e[0;34m';     IBlu='\e[0;94m';    BBlu='\e[1;34m';    BIBlu='\e[1;94m';   UBlu='\e[4;34m';    On_Blu='\e[44m';    On_IBlu='\e
[0;104m';
Pur='\e[0;35m';     IPur='\e[0;95m';    BPur='\e[1;35m';    BIPur='\e[1;95m';   UPur='\e[4;35m';    On_Pur='\e[45m';    On_IPur='\e
[0;105m';
Cya='\e[0;36m';     ICya='\e[0;96m';    BCya='\e[1;36m';    BICya='\e[1;96m';   UCya='\e[4;36m';    On_Cya='\e[46m';    On_ICya='\e
[0;106m';
Whi='\e[0;37m';     IWhi='\e[0;97m';    BWhi='\e[1;37m';    BIWhi='\e[1;97m';   UWhi='\e[4;37m';    On_Whi='\e[47m';    On_IWhi='\e
[0;107m';
#

# Argument1 = <section header>
function INS_logSection {
  ${ECHO}
  ${ECHO} "${RCol}${BICya}$1${RCol}"
  ${ECHO} "##$1" >> ${LOG}
}

# Argument1 = <Subsection header>
function INS_logSubSection {
  ${ECHO} "${IPur}Check $1${RCol}"
  ${ECHO} "#$1" >> ${LOG}
}

# Argument1 = <log entry>
function INS_logComponent {
  ${ECHO} -n "$1" | tee -a ${LOG}
}

# Argument1 = <log entry>
function INS_logItem {
  ${ECHO} "$1" | tee -a ${LOG}
}

# Argument1 = colour, Argument2 = <info>
function INS_logResult {
  ${ECHO} -n "$1$2${RCol} "
  if [ "$1" = "${Gre}" ] ; then
    ${ECHO} -n "$2 " >> ${LOG}
  else
    ${ECHO} -n "($2) " >> ${LOG}
  fi
}

# As above, but No Space
# Argument1 = colour, Argument2 = <info>
function INS_logNsResult {
  ${ECHO} -n "$1$2${RCol}"
  if [ "$1" = "${Gre}" ] ; then
    ${ECHO} -n "$2" >> ${LOG}
  else
    ${ECHO} -n "($2)" >> ${LOG}
  fi
}

# Argument1 = <value?OK:<other>>
function INS_testPrefix {
  if [ "$1" == "OK" ] ; then
    ${ECHO} -n "${BIGre}OK!${RCol} "
    ${ECHO} -n "OK! " >> ${LOG}
  else
    ${ECHO} -n "${BIRed}$1 ${RCol}"
    ${ECHO} -n "$1 " >> ${LOG}
  fi
}

# Argument1 = groupname, Argument2 <id info>
function INS_testGroup {
  GROUP="$1"
  ITEM=`${GREP} "^${GROUP}:" ${TFILE}`
  GPRESENT=$?
  if [ ${GPRESENT} -eq 0 ] ; then
    ${ECHO} "Found group ${GROUP}" | tee -a ${LOG}
  else
    ${ECHO} -n "Group ${GROUP} NOT found" | tee -a ${LOG}
    if [ "${CREATEAUTH}" == "Y" ] ; then
      ${ECHO} " - Creating ${GROUP}" | tee -a ${LOG}
      RES=`groupadd -r -g $2 $1` 
      if [ ${RES} ] ; then
        ${ECHO}
        ${ECHO} "${BIRed}FATAL: Please manually create the group ${GROUP} with GID $2 if available"
        ${ECHO} "${BIYel}Then re-run this script${RCol}"
        exit -1
      fi
    else
      ${ECHO}
      ${ECHO} "${BIRed}FATAL: Please create the group ${GROUP} with GID $2 if available"
      ${ECHO} "${BIYel}Then re-run this script${RCol}"
      exit -1
    fi
  fi
}

# Argument1 = username, Argument2 <id info>, Argument3 <flags>
function INS_testUser {
  USER="$1"
  ITEM=`${GREP} "^${USER}:" ${TFILE}`
  UPRESENT=$?
  if [ ${UPRESENT} -eq 0 ] ; then
    ${ECHO} "Found user ${USER}" | tee -a ${LOG}
  else
    ${ECHO} -n "User ${USER} NOT found" | tee -a ${LOG}
    if [ "${CREATEAUTH}" == "Y" ] ; then
      ${ECHO} " - Creating ${USER}" | tee -a ${LOG}
      RES=`useradd -g $2 -u $2 $3 $1 2>/dev/null` 
      if [ ${RES} ] ; then
        ${ECHO}
        ${ECHO} "${BIRed}FATAL: Please manually create the user ${USER} with UID $2 if available"
        ${ECHO} "${BIYel}Then re-run this script${RCol}"
        exit -1
      fi
    else
      ${ECHO}
      ${ECHO} "${BIRed}FATAL: Please create the user ${USER} with UID $2 if available"
      ${ECHO} "${BIYel}Then re-run this script${RCol}"
      exit -1
    fi
  fi
}

# Argument1 = <dirname>, Argunmen2 = <user:group>, Argument3 = <mode>
function INS_testDir {
  TSTDIR="$1"
  RESULT=0
  if [ -d ${TSTDIR} ] ; then
    ${ECHO} -n "${Gre}${TSTDIR} "
    chown "$2" ${TSTDIR}
    chmod "$3" ${TSTDIR}
  else
    mkdir ${TSTDIR}
    if [ -d ${TSTDIR} ] ; then
      ${ECHO} -n "${Yel}${TSTDIR} "
      ${ECHO} "Created ${TSTDIR}" >>  ${LOG}
      chown "$2" ${TSTDIR}
      chmod "$3" ${TSTDIR}
    else
      ${ECHO} -n "${Red}${TSTDIR} "
      ${ECHO} "Failed ${TSTDIR}" >>  ${LOG}
      RESULT=-1
    fi
  fi
  return ${RESULT}
}

# Argument1 = <package>
function testOSInstalled {
  if [ "$1" == "php-firebird" ] ; then
    BUNDLE="php-interbase"	# alias
  else
    BUNDLE="$1"
  fi
  #TODO If we use rpm
  # rpm -q --quiet "$1"
  # if we use yum...
  if yum list installed "${BUNDLE}" >/dev/null 2>&1; then
    INS_logResult ${Gre} "$1"
    true
  else
    INS_logNsResult ${Yel} "$1"
    if [ "${REPOACCESS}" = "Y" ] ; then
      yum -q -y install "${BUNDLE}" > /dev/null 2>&1
      RES=$?
      if [ "${RES}" -eq 0 ] ; then
	INS_logResult ${Gre} OK
      else
	INS_logResult ${Red} FAIL
      fi
    fi
    false
  fi
}

# Argument1 = <package>
function testEPELInstalled {
  if [ "$1" == "php-firebird" ] ; then
    BUNDLE="php-interbase"	# alias
  else
    BUNDLE="$1"
  fi
  #TODO If we use rpm
  # rpm -q --quiet "$1"
  # if we use yum...
  if yum list installed "${BUNDLE}" >/dev/null 2>&1; then
    INS_logResult ${Gre} "$1"
    true
  else
    INS_logNsResult ${Yel} "$1"
    if [ "${EPELACCESS}" = "Y" ] ; then
      yum -q -y install "${BUNDLE}" > /dev/null 2>&1
      RES=$?
      if [ ${RES} -eq 0 ] ; then
	INS_logResult ${Gre} OK
      else
	INS_logResult ${Red} FAIL
      fi
    fi
    false
  fi
}

# Argument1 = <value?OK:<other>>, Argument2 = <section name>
function INS_validateFileSystem {
  ${ECHO} "FATAL: ${1} filesystem not found"
  ${ECHO} "${BIRed}Please create and mount the ${1} filesystem"
  ${ECHO} "${BIYel}Then re-run this script${RCol}"
  exit -1
}

# Argument1 = <value?OK:<other>>, Argument2 = <section name>
function INS_validateResult {
${ECHO} "${Pur}===============================================================${RCol}"
if [ "$1" = "OK" ] ; then
  ${ECHO} "\n"$2" PASSED validation" >> ${LOG}
  ${ECHO} "${BIGre}"$2" PASSED validation - CONTINUING${RCol}"
else
  ${ECHO} "\n"$2" FAILED validation" >> ${LOG}
  ${ECHO} "${BIRed}\n$2 FAILED validation - Manual correction required${RCol}"
  ${ECHO} "${BIYel}Then re-run this script${RCol}"
  exit -1
fi
${ECHO} "${Pur}===============================================================${RCol}"
}

is_first_floating_number_bigger () {
  number1="$1"
  number2="$2"

  [ ${number1%.*} -eq ${number2%.*} ] && [ ${number1#*.} \> ${number2#*.} ] || [ ${number1%.*} -gt ${number2%.*} ];

  result=$?
  if [ "$result" -eq 0 ]; then result=1; else result=0; fi

  __FUNCTION_RETURN="${result}"
}
