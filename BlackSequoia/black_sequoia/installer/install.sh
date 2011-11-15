#!/bin/bash
#rm install.sh &&  wget http://iridium2.s3.amazonaws.com/install.sh && bash install.sh masterkey
BS_VER="0.9.9-9 RC5"
INSTALLDIR="/usr/local/black_sequoia"
DWL_PATH="https://iridium2.s3.amazonaws.com"

# Constants
c_std="\E[0;39m"
c_h_std="\E[1;37m"
c_pink="\E[0;35m"
c_h_pink="\E[1;35m"
c_red="\E[0;31m"
c_h_red="\E[1;31m"
c_cayan="\E[0;36m"
c_h_cayan="\E[1;36m"
c_yellow="\E[1;33m"
c_green="\E[0;32m"
c_h_green="\E[1;32m"
c_blue="\E[0;34m"
c_h_blue="\E[1;34m"

# Welcome screen
clear
echo -e ${c_h_std}"Black Sequoia installer / Ubuntu 11.04 scheme. Ver: "$BS_VER
echo -e ${c_h_green}"	Download site   - " ${c_yellow} $DWL_PATH
echo -e ${c_h_green}"	Node name       - " ${c_yellow} `cat /etc/hostname`
echo -e ${c_h_green}"	Cluster key     - " ${c_yellow} "$1"

echo -e ${c_h_green}""
echo -e ${c_h_green}"Starting install program"${c_std}

echo -e ${c_h_green}"    [>]"${c_green}"    Make Black Sequoia directory"${c_std}
rm -rf $INSTALLDIR
mkdir $INSTALLDIR
mkdir $INSTALLDIR/log
mkdir $INSTALLDIR/log/runlog
cd $INSTALLDIR
echo -e ${c_h_green}"    [>]"${c_green}"    Install pre-required software"${c_std}
apt-get -y -q update >> /dev/null 2>&1
apt-get -y -q install wget aptitude sudo unzip > /dev/null 2>&1

echo -e ${c_h_green}"    [>]"${c_green}"    Download Black Sequoia installer"${c_std}
wget "$DWL_PATH/bs.zip" --no-check-certificate >> /dev/null 2>&1
if [ ! -f bs.zip ]
then
    echo -e ${c_h_red}"        [FAIL]"${c_red}"    STOP: can't download Black Sequoia installation package"${c_std}
    exit 1998
fi

unzip ./bs.zip >> /dev/null 2>&1

chmod 755 $INSTALLDIR/installer/bs.pkg.sh >> /dev/null 2>&1
chmod 755 $INSTALLDIR/bs-admin >> /dev/null 2>&1
ln -s $INSTALLDIR/bs-admin /sbin/bs-admin >> /dev/null 2>&1
ln -s $INSTALLDIR/installer/bs.pkg.sh /sbin/bs.pkg.sh >> /dev/null 2>&1

echo -e ${c_h_green}"    [>]"${c_green}"    Install PHP 5.x CLI"${c_std}
bs.pkg.sh install php5-cli
if ! [[ `php $INSTALLDIR/installer/php_check.php` = "8d30155575962e80a4359c02ac5eba58" ]] 
then 
    echo -e ${c_h_red}"        [FAIL]"${c_red}"    STOP: PHP 5.x CLI test result fail"${c_std}
    exit 2999
fi
echo -e ${c_h_green}"    [>]"${c_green}"    Start-up Black Sequoia"${c_std}
php $INSTALLDIR/bs-admin.php install_node $1
exit 0
;;