#! /bin/sh
PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin
NAME=php5-fcgi
PID=/var/run/php5-fcgi.pid
DAEMON=/usr/bin/spawn-fcgi
DAEMON_OPTS="-a 127.0.0.1 -p 5300 -C 5 -u www-data -g www-data -f /usr/bin/php5-cgi -P $PID"

. /lib/lsb/init-functions

test -x $DAEMON || exit 
 
set -e
 
case "$1" in
start)
log_daemon_msg "spawn-fcgi starting"
start-stop-daemon --start --pidfile $PID --exec $DAEMON -- $DAEMON_OPTS
echo "done."
;;
stop)
log_daemon_msg "spawn-fcgi stopping"
start-stop-daemon --stop --pidfile $PID --retry 5
rm -f $PID
echo "done."
;;
restart)
echo "Stopping $NAME: "
start-stop-daemon --stop --pidfile $PID --retry 5
rm -f $PID
echo "done..."
sleep 1
echo "Starting $NAME: "
start-stop-daemon --start --pidfile $PID --exec $DAEMON -- $DAEMON_OPTS
echo "done."
;;
*)
echo "Usage: /etc/init.d/$NAME {start|stop|restart}" >&2
exit 1
;;
esac
 
exit 
