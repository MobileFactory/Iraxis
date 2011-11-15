<?php
/*  Black Sequoia
 *  bs.trac.php
 *  Trac service
 *  Copyrigth Iraxis llc. '10'11  */

class TRAC {
    public function install() {
        $s=new SYS;
        $s->log('Trac 0.13', LOG_STEP);
        
        $s->log('install pre-required software', LOG_STR);
        $s->apt('install python-setuptools python-mysqldb python-svn python-subversion');
        $s->ok();
        
        $s->log('install Genshi', LOG_STR);
        $s->run('easy_install Genshi');
        $s->ok();
        
        $s->log('install Bable', LOG_STR);
        $s->run('easy_install Babel');
        $s->ok();
        
        $s->log('install latest Trac', LOG_STR);
        $s->run('easy_install Trac==dev');
        $s->ok();

        $s->log('install Trac plug-in: accessmacro', LOG_STR);
        $s->run('easy_install https://trac-hacks.org/svn/accessmacro/0.11');
        $s->ok();
        
        $s->log('install Trac plug-in: accountmanager', LOG_STR);
        $s->run('easy_install https://trac-hacks.org/svn/accountmanagerplugin/trunk');
        $s->ok();
        
        $s->log('install Trac plug-in: newsflashmacro', LOG_STR);
        $s->run('easy_install https://trac-hacks.org/svn/newsflashmacro/0.11');
        $s->ok();
        
        $s->log('install Trac plug-in: moviemacro', LOG_STR);
        $s->run('easy_install https://trac-hacks.org/svn/moviemacro/0.11');
        $s->ok();
        
        $s->log('install Trac plug-in: permredirect', LOG_STR);
        $s->run('easy_install https://trac-hacks.org/svn/permredirectplugin/0.11');
        $s->ok();
        
        $s->log('install Trac plug-in: privatewiki', LOG_STR);
        $s->run('easy_install https://trac-hacks.org/svn/privatewikiplugin/0.11');
        $s->ok();
        
        $s->log('install Trac plug-in: xmlrpc', LOG_STR);
        $s->run('easy_install -Z -U http://trac-hacks.org/svn/xmlrpcplugin/trunk');
        $s->ok();
        
        $s->log('install Trac plug-in: googlemapmacro', LOG_STR);
        $s->run('easy_install https://trac-hacks.org/svn/googlemapmacro/0.11');
        $s->ok();
        
        $s->log('install TracFCGI/nginx', LOG_STR);
        $s->run('mkdir /etc/trac');
        $s->run('touch /etc/trac/instances');        
        $s->run('cp '.INSTALLDIR.'dist/trac/nginx-template-trac /etc/nginx/templates/trac');
        $s->run('cp '.INSTALLDIR.'dist/trac/nginx-tracfcgi.conf /usr/local/nginx/conf/');
        $s->run('cp '.INSTALLDIR.'dist/trac/trac-fcgi /usr/bin/');
        $s->run('chmod 755 /usr/bin/trac-fcgi');
        $s->run('cp '.INSTALLDIR.'dist/trac/trac-fcgi.py /usr/bin/');
        $s->run('chmod 755 /usr/bin/trac-fcgi.py');
        $s->run('cp '.INSTALLDIR.'dist/trac/trac-fcgi.sh /etc/init.d/trac-fcgi');                
        $s->run('chmod 755 /etc/init.d/trac-fcgi');
        $s->run('update-rc.d trac-fcgi defaults');
        $s->run('mkdir /usr/share/trac-static/');
        $s->run('ln -s "/usr/local/lib/python2.7/dist-packages/`ls /usr/local/lib/python2.7/dist-packages/ |grep Trac-0.1`/trac/web/" /usr/share/trac-static/');
        
        $s->ok();
        
    }
    
    public function add_trac($name) {
        // trac-admin /home/www/mobile.iraxis.ru/ initenv mobile.iraxis.ru mysql://trac_mobile:0b28f578e782a7cb@localhost/trac_mobile
        $s = new SYS;
        $percona = new PERCONA_MYSQL;
        $bd=explode('.', $name);
        $bdname=$bd[1].$bd[0];
        $pass=$percona->addUserDB($bdname, $bdname);
        $s->run("trac-admin /home/www/$name/ initenv $name mysql://$bdname:$pass@localhost/$bdname",true);
        $s->run("echo $name >> /etc/trac/instances");
//        $s->run("killall -9 python");
        $s->run_raw('echo "server {   server_name  '.$name.'; include /etc/nginx/templates/trac; }" > /etc/nginx/sites/'.$name.'.conf');
        $s->run("/etc/nginx/restart");
        $s->run("/etc/init.d/trac-fcgi start");
        return $pass;
    }
    
    public function cadd_trac() {
        global $argv;
        $s=new SYS;
        $s->log('Create trac site', LOG_STR);
        if (!isset($argv[3])) {
            $s->log('',LOG_ERROR);
            $s->log('site name not setted'."\n",LOG_STR);
            die;
        }
        $resu=$this->add_trac($argv[3]);
        $s->ok();
        $s->log('Create trac admin user, pass: '.$resu.' ', LOG_STR);
        $this->trac_add_user($argv[3], 'admin', $resu);
        $s->ok();
        return $resu;
    }
    
    public function trac_add_user($env,$username,$password) {
        $s=new SYS;
        $path="/etc/nginx/htpasswds/$env.htpasswd";
        if (file_exists($path)){
            $s->run("htpasswd -b $path $username $password");            
        } else {
            $s->run("htpasswd -bc $path $username $password");            
        }
        $s->run("trac-admin /home/www/$env/ permission add $username TRAC_ADMIN");
    }
    
    public function ctrac_add_user() {
        global $argv;
        $s=new SYS;
        $s->log('Create trac user', LOG_STR);
        if (!isset($argv[4])) {
            $s->log('',LOG_ERROR);
            $s->log('incorrect param count'."\n",LOG_STR);
            die;
        }
        $resu=$this->trac_add_user($argv[2],$argv[3],$argv[4]);
        $s->ok();
        return $resu;
    }
    
    //bs-admin trac_add masterkey mobile.iraxis.ru
}