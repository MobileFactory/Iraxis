#! /bin/sh
PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin
NAME=tarc-fcgi
DAEMON=/usr/bin/trac-fcgi
TRACINSTLIST=/etc/trac/instances


. /lib/lsb/init-functions

test -x $DAEMON || exit 
 
set -e
 
case "$1" in
start)
    log_daemon_msg "spawn-fcgi starting"
    cat $TRACINSTLIST| while read line; do
        echo "\n" $line
        start-stop-daemon --start --pidfile /var/run/trac-$line.pid --exec $DAEMON -- "$line &"
    done
echo "done."
;;
stop)
    log_daemon_msg "spawn-fcgi stopping"
    cat $TRACINSTLIST| while read line; do
        echo "\n" $line
        start-stop-daemon --stop --pidfile /var/run/trac-$line.pid --retry 5
        rm -f /var/run/trac-$line.pid
    done
    echo "done."
;;
restart)
echo "Stopping $NAME: "
    cat $TRACINSTLIST| while read line; do
        echo "\n" $line
        start-stop-daemon --stop --pidfile /var/run/trac-$line.pid --retry 5
        rm -f /var/run/trac-$line.pid
    done
echo "done..."
sleep 1
echo "Starting $NAME: "
    cat $TRACINSTLIST| while read line; do
        echo "\n" $line
        start-stop-daemon --start --pidfile /var/run/trac-$line.pid --exec $DAEMON -- $"$line &"
    done
echo "done."
;;
*)
echo "Usage: /etc/init.d/$NAME {start|stop|restart}" >&2
exit 1
;;
esac
 
exit 
