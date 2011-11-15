<?php
//Defines
define('LOG_FILE','/dev/null');
define('INSTALLDIR','/usr/local/black_sequoia/');

//Libs
require_once 'libs/bs.common.php';
require_once 'libs/bs.aws.route53.php';

//Services
require_once 'services/bs.basic_platform.php'; // basic platform
require_once 'services/bs.nginx.php';          // nginx webserver
require_once 'services/bs.percona_mysql.php';  // percona server - mysql
require_once 'services/bs.php5-fcgi.php';      // php5 fcgi
require_once 'services/bs.phpDaemon.php';      // phpDaemon
require_once 'services/bs.svn.php';            // SVN
require_once 'services/bs.trac.php';           // Trac
require_once 'services/bs.admin_tools.php';    // admin-tools

//Inits
$s           = new SYS;
$b_platform  = new BASIC_PLATFORM;
$nginx       = new NGINX;
$percona     = new PERCONA_MYSQL;
$php5_fcgi   = new PHP5_FCGI;
$svn         = new SVN;
$php_daemon  = new PHP_DAEMON;
$trac        = new TRAC;
$dns         = new AWS_Route53;
//$s3          = new AWS_S3;

if (!isset($argv[1])){
    $argv[1]='unknown';
}

switch (trim(strtolower($argv[1]))) {
    case 'install_node':
        $s          -> ver();
        $b_platform -> install();
        $nginx      -> install_ngx_ctpp2('2.7.1','0.5');
        $nginx      -> install('1.1.2');
        $nginx      -> init();
        $percona    -> install($percona->getPass());
        $php5_fcgi  -> install();
        $svn        -> install();
        $php_daemon -> install();
        $trac       -> install();
        break;
    case 'update':
        $s          -> dl('http://');
        break;    
    case 'update_nginx':
        $nginx      -> install_ngx_ctpp2('2.7.1','0.5');
        $nginx      -> install('1.1.2');
        break;
    case 'create_cluster':
        //$s3         -> setAuth($accessKey, $secretKey);
        //$s3         -> putObject($input, $bucket, $uri);
        $dns        -> setAuth('', '');
        break;
    case 'mysql_pass':
        echo "\nCurrent SQL pass: ".$percona->cgetPass()."\n\n";
        break; 
    case 'mysql_add':
        echo "\nCurrent SQL pass: ".$percona->c_addUserDB()."\n\n";
        break;  
    case 'php_add':
        $php5_fcgi->cadd_site();
        break;      
    case 'trac_add':
        $trac->cadd_trac();
        break;      
    case 'trac_add_user':
        $trac->ctrac_add_user();
        break;
    case 'help':
        $s          -> ver();
        echo "bs-admin [CMD] {params}\n\n";
        echo "  Command list:\n";
        echo "          install_node [cluster_name] [cluster_key] - Initial install node\n";
        echo "          get_sql_pass [cluster_name] [cluster_key] - Get SQL password for this node\n";
        echo "\n";
    default:
        $s          -> ver();        
        echo "Use bs-admin help\n\n";
        break;
}


die('');
//Administrative tool
$s->log('Install NMI Administrative tool'."\n", 'proc');
$s->run('cp -r '.INSTALLDIR.'nmi-admin.sh /usr/sbin/nmi-admin');
$s->run('mkdir /usr/lib/nmi');
$s->run('cp -r '.INSTALLDIR.'nmi-admin.php /usr/lib/nmi/nmi-admin.php');
$s->run('cp -r '.INSTALLDIR.'nmi.lib.php /usr/lib/nmi/nmi.lib.php');
$s->run('chmod 755 /usr/local/sbin/nmi-admin');
$s->log('Install NMI Administrative tool', 'proc-str');


if (isset($argv[3])){
    $cluster_name=$argv[1];
    $node_name=$argv[2];
    $key_name=$argv[3];
    
    $s->log('3', 'step');
    $s->log("Starting node '$node_name' deploy on '$cluster_name'", 'proc');

    $NMI_CALL[1]='deploy';
    $NMI_CALL[2]=$cluster_name;
    $NMI_CALL[3]=$node_name;
    $NMI_CALL[4]=$key_name;
    require_once '/usr/lib/nmi/nmi-admin.php';
    
} else {
    $s->log('Installed successfully', 'proc');
}

