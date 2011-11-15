<?php
/*  Black Sequoia
 *  bs.php5-fcgi.php
 *  PHP5 as FCGI service
 *  Copyrigth Iraxis llc. '10'11  */

class PHP5_FCGI{
    public function install() {
        $s=new SYS;
        $s->log('PHP 5.x FastCGI (fspawn)', LOG_STEP);
        
        $s->log('download and install', LOG_STR);
        $s->apt('install php5 php5-cgi php5-mysql php5-curl php5-gd php5-json php5-mcrypt php-soap php5-dev spawn-fcgi php-apc apache2 php-pear libyaml-dev');
        $s->run('service apache2 stop');
        $s->run('update-rc.d -f apache2 remove');
        $s->ok();
        
        $s->log('add fspawn php/fcgi service', LOG_STR);
        $s->run('cp '.INSTALLDIR.'dist/php5-fcgi/php5-fcgi.sh /etc/init.d/php5-fcgi');
        $s->run('chmod 755 /etc/init.d/php5-fcgi');
        $s->run('update-rc.d php5-fcgi defaults');
        $s->run('cp '.INSTALLDIR.'dist/php5-fcgi/nginx-template-php /etc/nginx/templates/php');
        $s->ok();
        
        $s->log('run php5-fcgi', LOG_STR);
        $s->run('/etc/init.d/php5-fcgi start');
        $s->ok();
    }
    
    public function add_site($name) {
        $s=new SYS;
        $s->run_raw('echo "server {   server_name  '.$name.'; include /etc/nginx/templates/php; }" > /etc/nginx/sites/'.$name.'.conf');
        $s->run('/etc/nginx/restart');
        $dir="/home/www/$name";
        $s->run('mkdir '.$dir);
        $s->run('chown -R www-data:www-data '.$dir);
        $s->run('chmod 775 '.$dir);
    }
    
    public function cadd_site() {
        global $argv;
        $s=new SYS;
        $s->log('Add '.$argv[2].' as PHP site', LOG_STR);
        if (!isset($argv[2])) {
          $s->log('',LOG_ERROR);
          $s->log('site name not setted'."\n",LOG_STR);
          die;
        }
        if (file_exists('/etc/nginx/sites/'.$argv[2].'.conf')) {
          $s->log('',LOG_ERROR);
          $s->log('site already created'."\n",LOG_STR);
          die;
        }

        $this->add_site($argv[2]);
        $s->ok();               
    }

}
