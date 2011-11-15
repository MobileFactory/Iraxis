<?php
/*  Black Sequoia
 *  os.nginx.php
 *  Web-server nginx service
 *  Copyrigth Iraxis llc. '10'11  */

class NGINX {
    public $configure;
    public function install_ngx_ctpp2($cttp2_ver,$ngx_cttp2_ver) {
        $s = new SYS;
        //install CT++
        $s->log('Install CT++ '.$cttp2_ver, LOG_STEP);
        //download
        $s->cd(INSTALLDIR.'install/');
        $s->log('download', LOG_STR);
        $s->dl("http://ctpp.havoc.ru/download/ctpp2-$cttp2_ver.tar.gz");
        $s->ok();
        //unpack
        $s->log('unpack', LOG_STR);
        $s->run("tar xfz ctpp2-$cttp2_ver.tar.gz");
        $s->cd(INSTALLDIR."install/ctpp2-$cttp2_ver/");
        $s->ok();
        //compile
        $s->log('compile and install', LOG_STR);
        $s->run('cmake -DCMAKE_INSTALL_PREFIX=build');
        $s->run('make install');
        $s->run('cp -rf libctpp2.* /usr/lib64/');        
        $s->ok();
        
        //install CT++ ngx
        $s->log('Install CT++ nginx module '.$ngx_cttp2_ver, LOG_STEP);        
        //download
        $s->cd(INSTALLDIR.'install/');
        $s->log('download', LOG_STR);
        $s->dl("http://dl.vbart.ru/ngx-ctpp/ngx_ctpp2-$ngx_cttp2_ver.tar.gz");
        $s->ok();
        //unpack
        $s->log('unpack', LOG_STR);
        $s->run("tar xfz ngx_ctpp2-$ngx_cttp2_ver.tar.gz");
        $s->ok();
        $this->configure=$this->configure."--add-module=../ngx_ctpp2-$ngx_cttp2_ver --with-cc-opt='-I ../ctpp2-$cttp2_ver/build/include' --with-ld-opt='-L ../ctpp2-$cttp2_ver/build/lib -Wl,-rpath,".'$PWD'."/../ctpp2-$cttp2_ver/build/lib' ";
    }
    
    public function install($nginx_ver) {
        $s = new SYS;
        //install nginx
        $s->log("Install nginx $nginx_ver", LOG_STEP);
        //install pre-requires soft
        $s->log('install pre-required software', LOG_STR);
        $s->apt('install libpcre3-dev libcurl4-openssl-dev libxslt-dev libxml2-dev libgeoip-dev psmisc');
        $path_name="nginx-$nginx_ver";
        $s->cd(INSTALLDIR.'install/');
        $s->ok();        
        
        $s->log('download', LOG_STR);
        $s->dl("http://nginx.org/download/nginx-$nginx_ver.tar.gz");
        $s->ok();
        
        $s->log('unpacking', LOG_STR);
        $s->run("tar xfz nginx-$nginx_ver.tar.gz");
        $s->ok();
        
        $s->log('apply patch "Black Sequoia"', LOG_STR);
        $fname= INSTALLDIR."install/$path_name/src/core/nginx.h";
        file_put_contents($fname,str_replace('"'.$nginx_ver.'"','"3.4 Black Sequoia"', file_get_contents($fname)));
        file_put_contents($fname,str_replace('"nginx/"','"Iridium::CoreServer/"', file_get_contents($fname)));
        $s->ok();
        
        $s->log('configure', LOG_STR);
        $s->cd(INSTALLDIR."install/$path_name/");
        $s->run("./configure --user=www-data --group=www-data --with-http_ssl_module --with-http_realip_module --with-ipv6 --with-http_addition_module --with-http_xslt_module --with-http_geoip_module --with-http_sub_module --with-http_flv_module --with-http_gzip_static_module --with-http_stub_status_module --with-pcre ".$this->configure);
        $s->ok();
        
        $s->log('compile', LOG_STR);
        $s->run('make');
        $s->ok();
        
        $s->log('install', LOG_STR);       
        $s->run('make install');
        $s->cd(INSTALLDIR);
        $s->ok();
    }
    
    public function init() {
        $s = new SYS;
        $s->log('Nginx initial configure', LOG_STEP);
        
        $s->log('install main config', LOG_STR);
        $s->run('rm -rf /usr/local/nginx/conf/*.default');
        $s->run('cp '.INSTALLDIR.'dist/nginx/nginx.conf /usr/local/nginx/conf');
        $s->ok();

        $s->log('create file structures', LOG_STR);        
        $s->run('mkdir /etc/nginx');
        $s->run('mkdir /etc/nginx/sites');
        $s->run('mkdir /etc/nginx/templates');
        $s->run('mkdir /etc/nginx/svn');
        $s->run('mkdir /etc/nginx/htpasswds');
        $s->run('mkdir /home/www');
        $s->run('chown -R www-data:www-data /home/www');
        $s->run('chmod 770 /home/www');
        $s->run('cp '.INSTALLDIR.'dist/nginx/nginx.sh /etc/nginx/nginx');
        $s->run('chmod 755 /etc/nginx/nginx');
        $s->run('cp /etc/nginx/nginx /etc/init.d/');        
        $s->run('cp '.INSTALLDIR.'dist/nginx/nginx-start.sh /etc/nginx/start');
        $s->run('chmod 755 /etc/nginx/start');
        $s->run('cp '.INSTALLDIR.'dist/nginx/nginx-restart.sh /etc/nginx/restart');
        $s->run('chmod 755 /etc/nginx/restart');
        $s->run('cp '.INSTALLDIR.'dist/nginx/nginx-stop.sh /etc/nginx/stop');
        $s->run('chmod 755 /etc/nginx/stop');
        $s->ok();
        
        $s->log('set nginx to autrun', LOG_STR);                
        $s->run('update-rc.d nginx defaults');
        $s->ok();
    }
}
