    <?php
    /*  Black Sequoia
     *  bs.percona_mysql.php
     *  Perocna Server 5.5 (mysql) service
     *  Copyrigth Iraxis llc. '10'11  */
    define('SQL_PASS_KEY','balcksequoia-9-cx8592jf8ma8402');


    class PERCONA_MYSQL{
        public function getPass($user='root') {
            $s = new SYS;
            $mdpass=md5($s->getClusterKey().'Su!зУкИ'.$user.md5($s->getNodeName()).SQL_PASS_KEY);       
            $pass=substr($mdpass, 1, 16);
            return $pass;
        }
        public function cgetPass($user='root') {
            $s = new SYS;
            $arr=$s->getAfterClusterKeyParams();
            if (isset($arr[0])) {
                $user=$arr[0];
            }
            return $this->getPass($user);
        }



        public function rootQuery($sql) {
            $db=mysql_connect('localhost', 'root', $this->getPass());
            return mysql_query($sql, $db);
        }

        public function addUser($user,$pass) {
            $sql="CREATE USER '$user'@'%' IDENTIFIED BY  '$pass'; ";
            $this->rootQuery($sql);
            $sql="GRANT USAGE ON *.* TO '$user'@'%' IDENTIFIED BY '$pass' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0 ;";
            return $this->rootQuery($sql);
        }

        public function grantAccess($db,$user) {
            if ($user==false){$user=$dbname;}
            $sql="GRANT ALL PRIVILEGES ON  `$db` . * TO  '$user'@'%';";
            return $this->rootQuery($sql);        
        }

        public function addDB($dbname){
            $sql="CREATE DATABASE IF NOT EXISTS  `$dbname` ;";
            $this->rootQuery($sql);
            $sql="ALTER DATABASE `$dbname` DEFAULT CHARACTER SET utf8; ";
            $this->rootQuery($sql);
            $sql="ALTER DATABASE `$dbname` DEFAULT COLLATE utf8_unicode_ci; ";
            return $this->rootQuery($sql);
        }

        public function addUserDB($dbname,$user=false,$pass=false) {
            if ($user==false){$user=$dbname;}
            if ($pass==false){$pass=$this->getPass($user);}
            $this->addUser($user, $pass);
            $this->addDB($dbname);
            $this->grantAccess($dbname, $user);
            return $pass;
        }

        public function c_addUserDB() {
            $s=new SYS;
            $array=$s->getAfterClusterKeyParams();
            if (isset($array[0])) {$dbname=$array[0];} else {$dbname=false;}
            if (isset($array[1])) {$user=$array[1];} else {$user=false;}        
            if (isset($array[2])) {$pass=$array[2];} else {$pass=false;}        
            return $this->addUserDB($dbname,$user,$pass);
        }


        /*

    $sql="CREATE DATABASE IF NOT EXISTS  `$db` ;";
    $sql="GRANT ALL PRIVILEGES ON  `$db` . * TO  '$user'@'%';";

    */


        public function install($sql_root_pass) {
            $s=new SYS;
            $s->log('Install Percona Server 5.5', LOG_STEP);

            $s->log('add apt-repositories and keys', LOG_STR); 
            $s->run('gpg --keyserver  hkp://keys.gnupg.net --recv-keys 1C4CBDCDCD2EFD2A');
            $s->run('gpg -a --export CD2EFD2A | apt-key add -');
            $s->run_raw('echo " " >> /etc/apt/sources.list');
            $s->run_raw('echo "deb http://black-sequoia.s3.amazonaws.com/percona-repo maverick main" >> /etc/apt/sources.list');
            $s->run_raw('echo "deb-src http://black-sequoia.s3.amazonaws.com/percona-repo maverick main"  >> /etc/apt/sources.list');
            $s->apt('update');
            $s->run_raw('echo percona-server-server-5.5 percona-server-server/root_password password '.$sql_root_pass.' | debconf-set-selections');
            $s->run_raw('echo percona-server-server-5.5 percona-server-server/root_password_again password '.$sql_root_pass.' | debconf-set-selections');
            $s->ok();

            $s->log('download', LOG_STR);
            $s->apt('-d install percona-server-server percona-server-client libmysqlclient-dev');
            $s->ok();

            $s->log('install', LOG_STR);
            $s->apt('install percona-server-server percona-server-client');
            $s->ok();
        }
    }
    /*  DEBMIRROR
    debmirror  --progress --verbose --nocleanup --source --md5sums --host=repo.percona.com --root=apt-rc --dist=maverick --ignore-missing-release --ignore-release-gpg --method=http --section=main --arch=i386,amd64 /mirror
    s3cmd --recursive --acl-public put /mirror/ s3://black-sequoia/percona-repo/
    rm -rf /mirror/ 
     * 
     * bs-admin mysql_add_db msaterkey mydb
     * 
    */