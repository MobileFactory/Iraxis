<?php
/*  Black Sequoia
 *  bs.svn.php
 *  SVN service
 *  Copyrigth Iraxis llc. '10'11  */

class SVN {
    public function install() {
        $s=new SYS;
        $s->log('SVN/Apache2', LOG_STEP);
        
        $s->log('install apache2 SVN module', LOG_STR);
        $s->run('service apache2 stop');
        $s->apt('install libapache2-svn python-subversion'); //svn - tcp:3690
        $s->run('groupadd svn');
        $s->run('usermod -a -G svn www-data');
        $s->run('rm -rf /etc/apache2');
        $s->run('cp -r '.INSTALLDIR.'dist/apache2 /etc/apache2');
        $s->run('update-rc.d php5-fcgi defaults');
        $s->run('service apache2 start');
        $s->ok();
    }
}