#!/bin/bash
#Portions (C) 2013 Integrity Net P/L, Sydney, Australia

INSTALLERDIR=/data/minder/Installer
# Source the library
. ${INSTALLERDIR}/lib/Installer-lib.sh

SERVERTYPE="Minder Application Server"
TITLE="Minder Series Linux Package Loader for ${SERVERTYPE} Server v5.4"
VER="3.1"
LOG="/tmp/Minder-PKGInstall-APPserver.log"

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

INS_logSubSection "Load required O/S packages"
for PKG in telnet wget net-tools php php-snmp expect blktrace sysstat dstat iotop oprofile perf powertop oprofile-jit papi sdparm sg3_utils tuned tuned-utils libncurses.so.5 libstdc++.so.6 httpd crypto-utils mod_ssl policycoreutils-python php-pdo-abi php-pear php-pear-DB php-gd php-xml cups printer-filters foomatic-db-ppds
do
	testOSInstalled "${PKG}"
done

${ECHO} | tee -a ${LOG}
if [ "${ELVER}" = "el7" ] ; then
  INS_logSubSection "Load required EL7 O/S GUI packages"
  for PKG in xorg-x11-drivers xorg-x11-server-Xorg xorg-x11-xauth xorg-x11-xinit spice-vdagent firstboot gnome-classic-session gnome-terminal nautilus-open-terminal control-center liberation-mono-fonts glx-utils xorg-x11-server-utils xorg-x11-utils plymouth-system-theme xvattr gnu-free-fonts-common gnu-free-mono-fonts gnu-free-sans-fonts gnu-free-serif-fonts  initial-setup initial-setup-gui NetworkManager alsa-plugins-pulseaudio at-spi control-center dbus gdm gnome-screensaver gnome-session gnome-terminal gvfs-archive gvfs-fuse gvfs-smb metacity nautilus notification-daemon polkit-gnome xdg-user-dirs-gtk yelp control-center-extra eog gdm-plugin-fingerprint gnome-packagekit gnome-vfs2-smb openssh-askpass orca pulseaudio-module-gconf pulseaudio-module-x11 vino tigervnc-server atk cairo dbus dbus-libs fontconfig freetype glib2 gtk2 libICE libSM libX11 libXext libXft libXi libXrender libXt libXtst libjpeg-turbo libpng libxml2 mesa-libGL mesa-libGLU pango qt qt3 redhat-lsb-graphics redhat-lsb-printing firefox nspluginwrapper gnome-system-monitor mousetweaks system-config-firewall system-config-users vim-X11 system-config-printer system-config-printer-udev firewall-config
  do
	testOSInstalled "${PKG}"
  done
else
  INS_logSubSection "Load required EL6 O/S GUI packages"
  for PKG in xorg-x11-drivers xorg-x11-server-Xorg xorg-x11-xauth xorg-x11-xinit firstboot glx-utils xorg-x11-server-utils xorg-x11-utils NetworkManager alsa-plugins-pulseaudio at-spi control-center dbus gdm gdm-user-switch-applet gnome-panel gnome-power-manager gnome-screensaver gnome-session gnome-terminal gvfs-archive gvfs-fuse gvfs-smb metacity nautilus notification-daemon polkit-gnome xdg-user-dirs-gtk yelp control-center-extra eog gdm-plugin-fingerprint gnome-applets gnome-media gnome-packagekit gnome-vfs2-smb gok openssh-askpass orca pulseaudio-module-gconf pulseaudio-module-x11 vino tigervnc-server atk cairo dbus dbus-libs fontconfig freetype glib2 gtk2 libICE libSM libX11 libXext libXft libXi libXrender libXt libXtst libjpeg-turbo libpng libxml2 mesa-libGL mesa-libGLU pango qt qt3 redhat-lsb-graphics redhat-lsb-printing firefox nspluginwrapper gnome-system-monitor mousetweaks system-config-firewall system-config-users vim-X11
  do
	testOSInstalled "${PKG}"
  done
fi

${ECHO} | tee -a ${LOG}
INS_logSubSection "Load Office packages"
for PKG in libreoffice-calc libreoffice-draw libreoffice-graphicfilter libreoffice-impress libreoffice-math libreoffice-writer libreoffice-xsltfilter libreoffice-base libreoffice-emailmerge libreoffice-headless libreoffice-ogltrans libreoffice-wiki-publisher planner taskjuggler libreoffice-langpack-en
do
	testOSInstalled "${PKG}"
done

${ECHO} | tee -a ${LOG}
TFILE="/etc/group"
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

${ECHO}
INS_logSubSection "Load Database Client Libraries"
#FIREBIRD SuperClassic
${ECHO} "Install Firebird SuperClient" | tee -a ${LOG}

cd ${INSTALLERDIR}
rm -rf ./opt
DBVERS= "2.5.6"
# Check if Firebird is already installed as CS i.e. this system is BOTH DB and APP server
INS_logSubSection "Install 64bit Database Libraries"
rpm2cpio /data/pkgs/FirebirdSS-2*.amd64.rpm | cpio -ivd ./opt/firebird/lib/libfbclient.so.2.5.6
mv ./opt/firebird/lib/libfbclient.so.2.5.6 /lib64/libfbclient.so.2.5.6
ln -s /lib64/libfbclient.so.2.5.6 /lib64/libfbclient.so.2
ln -s /lib64/libfbclient.so.2 /lib64/libfbclient.so
ln -s /lib64/libfbclient.so.2 /lib64/libgds.so
rm -rf ./opt
rpm2cpio /data/pkgs/FirebirdSS-2*.amd64.rpm | cpio -ivd ./opt/firebird/lib/libib_util.so
INS_logSubSection "Install 32bit Database Libraries"
rpm2cpio /data/pkgs/FirebirdSS-2*.i686.rpm | cpio -ivd ./opt/firebird/lib/libfbclient.so.2.5.6
rpm2cpio /data/pkgs/FirebirdSS-2*.i686.rpm | cpio -ivd ./opt/firebird/lib/libib_util.so
mv ./opt/firebird/lib/libfbclient.so.2.5.6 /lib/libfbclient.so.2.5.6
ln -s /lib/libfbclient.so.2.5.6 /lib/libfbclient.so.2
ln -s /lib/libfbclient.so.2 /lib/libfbclient.so

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
for PKG in pbzip2 pigz firebird-libfbclient php-interbase inotify-tools php-pear-DB cups-pdf
do
	testEPELInstalled "${PKG}"
done
${ECHO} "python-fdb" | tee -a ${LOG}
yum install python-fdb --exclude firebird-libfbclient --skip-broken
${ECHO} | tee -a ${LOG}
INS_logSubSection "Load required EPEL packages for SOAP import"
for PKG in php-pear-SOAP 
do
	testEPELInstalled "${PKG}"
done
${ECHO}

# calculate this...
ALL_PACKAGES="OK"

INS_validateResult ${ALL_PACKAGES} "Linux Package Installation"

INS_logSection "Update system configuration files for Minder"
INS_logSubSection "HTTPD Global config file"
if [ "$ELVER" = "el7" ] ; then
  cp /data/pkgs/el7-httpd.conf /etc/httpd/conf/httpd.conf
elif [ "$ELVER" = "el6" -o "$ELVER" = "el6uek" ] ; then
  cp /data/pkgs/el6-httpd.conf /etc/httpd/conf/httpd.conf
fi
INS_logSubSection "Shell Global rc files"
TFILE="/etc/bashrc"
KNOWNSUM=22098
CSUM=`sum -r ${TFILE}`
CALCSUM=`expr "${CSUM}" : '^\(.*\) .*$'`
if [ -f ${TFILE} -a ${CALCSUM} -eq ${KNOWNSUM} ] ; then
  cat <<- 'EOF' >> ${TFILE}
	LS_COLORS=${LS_COLORS}'di=00;36:' ; export LS_COLORS
	alias ls='ls --color=auto'
EOF
	INS_logResult ${Gre} "${TFILE} Updated"
else
	INS_logResult ${BIRed} "${TFILE}: Checksum was: ${CALCSUM} expected ${KNOWNSUM}"
fi
TFILE="/etc/kshrc"
KNOWNSUM=29181
if [ -f ${TFILE} ] ; then
  CSUM=`sum -r ${TFILE}`
  CALCSUM=`expr "${CSUM}" : '^\(.*\) .*$'`
  if [ ${CALCSUM} -eq ${KNOWNSUM} ] ; then
  cat <<- 'EOF' >> ${TFILE}
	LS_COLORS=${LS_COLORS}'di=00;36:' ; export LS_COLORS
	alias ls='ls --color=auto'
EOF
	INS_logResult ${Gre} "${TFILE} Updated"
  else
	INS_logResult ${BIRed} "${TFILE}: Checksum was: ${CALCSUM} expected ${KNOWNSUM}"
  fi
  INS_logResult ${Yel} "NOTE: ${TFILE} is not installed on this server"
fi

${ECHO}
INS_validateResult "OK" "Packages Loaded"
