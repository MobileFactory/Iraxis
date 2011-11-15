<?php
/*  Black Sequoia
 *  bs.basic_platform.php
 *  Basic platform service
 *  Copyrigth Iraxis llc. '10'11  */

class BASIC_PLATFORM {
    public function install($cluster_key=false) {
        $s=new SYS;
        $s->log('Upgrade OS to [Ubuntu/Natty 11.04]', LOG_STEP);

        $s->log('save cluster key', LOG_STR);
        global $argv;
        if ((!isset($argv[1]))&&(!$cluster_key)){
            $this->log('Cluster key not setted in command string',LOG_ERROR);
            die(5578);
        }
        if (!$cluster_key){$cluster_key=$argv[1];}
        $s->setClusterKey($cluster_key);
        $s->ok();
        
        $s->log('install new apt-repo [Ubuntu/Natty 11.04] and update', LOG_STR);
        $s->run('mv '.INSTALLDIR.'dist/apt/sources.list /etc/apt/');
        $s->apt('update');
        $s->ok();

        $s->log('download packets for dist-upgrade', LOG_STR);
        $s->apt('-d dist-upgrade');
        $s->ok();

        $s->log('install packets for dist-upgrade', LOG_STR);
        $s->apt('dist-upgrade');
        $s->ok();

        $s->log('install locales', LOG_STR);
        $s->apt('reinstall locales');
        $s->apt('install language-pack-en');
        $s->ok();
        
        $s->log('install basic software', LOG_STR);
        $s->apt("install tcsh s3cmd dar mc nano psmisc build-essential openssl pwgen gpg subversion git-core cmake traceroute rdiff");
        $s->ok();

    }
}