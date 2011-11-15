#! /bin/sh
/etc/init.d/nginx stop
sleep 3
/etc/init.d/nginx start
exit 0