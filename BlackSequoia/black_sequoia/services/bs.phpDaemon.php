<?php
/*  Black Sequoia
 *  bs.phpDaemon.php
 *  PHP5 as daemon service
 *  Copyrigth Iraxis llc. '10'11  */

class PHP_DAEMON {
    public function install() {
        $s=new SYS;
        $s->log('phpDaemon', LOG_STEP);
        $s->log('install libevent', LOG_STR);
        $s->apt('install libevent-dev');
        $s->ok();
        
        $s->log('install libevent-pecl', LOG_STR);
        $s->run('echo autodetect| pecl install libevent channel://pecl.php.net/libevent-0.0.4');
        $s->ok();
        
        $s->log('install proctitle-pecl', LOG_STR);
        $s->run('pecl install proctitle channel://pecl.php.net/proctitle-0.1.1');
        $s->ok();

        $s->log('install runkit', LOG_STR);
        $s->run('git clone git://github.com/zenovich/runkit.git /tmp/runkit');
        $s->cd('/tmp/runkit');
        $s->run('phpize');
        $s->run('./configure --enable-runkit --enable-runkit-modify');
        $s->run('make');
        $s->run('make install');
        $s->ok();
                
        $s->log('install php5-cli config', LOG_STR);
        $s->cd(INSTALLDIR);
        $s->run('cp -r '.INSTALLDIR.'dist/php5-daemon/phpdaemon.ini /etc/php5/cli/conf.d/');
        $s->ok();
        
        $s->log('install phpDaemon', LOG_STR);
        $s->run('git clone git://github.com/kakserpom/phpdaemon.git /usr/local/phpdaemon');
        $s->run('ln -s /usr/local/phpdaemon/bin/phpd /usr/bin/phpd');
        $s->run('chmod 775 /usr/local/phpdaemon/bin/phpd');
        $s->ok();
    }
}