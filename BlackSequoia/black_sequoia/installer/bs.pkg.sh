#!/bin/bash
#NMI Logic installer / nmi.pkg.sh / ver 1.2-b
sudo DEBIAN_FRONTEND='noninteractive' aptitude -o Dpkg::Options::='--force-confnew' -y -q $1 $2 >> /dev/null 2>&1