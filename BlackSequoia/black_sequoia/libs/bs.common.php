<?php
/*  Black Sequoia
 *  bs.common.php
 *  Common php lib
 *  Copyrigth Iraxis llc. '10'11  */

define('BS_VER','0.2.5-DEV-ALPHA');
define('LOG_STEP','step');
define('LOG_STR','str');
define('LOG_OK','ok');
define('LOG_ERROR','error');
define('LOG_CUSTOM','custom');
define('LOG_VER','ver');

define('LOG_SILENT',false);


//BSCF Encode
function BSCF_Encode($obj){
    return base64_encode(json_encode($obj));
}

//BSCF Decode
function BSCF_Decode($str){
    return json_decode(base64_decode($str));
}

class SYS {
    public $color;
    function __construct() {
        $this->color['std']="\033[0;39m";
        $this->color['std_h']="\033[1;37m";
        $this->color['pink']="\033[0;35m";
        $this->color['pink_h']="\033[1;35m";
        $this->color['red']="\033[0;31m";
        $this->color['red_h']="\033[1;31m";
        $this->color['cayan']="\033[0;36m";
        $this->color['cayan_h']="\033[1;36m";
        $this->color['yellow']="\033[0;33m";
        $this->color['yellow_h']="\033[1;33m";
        $this->color['green']="\033[0;32m";
        $this->color['green_h']="\033[1;32m";
        $this->color['blue']="\033[0;34m";
        $this->color['blue_h']="\033[1;34m";
        global $sys_run_counter;
        if ((!isset($sys_run_counter))||($sys_run_counter==0)){
           $sys_run_counter=  file_get_contents(INSTALLDIR.'log/bslog.count');
        }
    }
    
    public function getNodeName() {  //nodename.nodes.clustername.tld
        if (!file_exists('/etc/hostname')){ return 'unknown'; }
        $arr=explode('.', file_get_contents('/etc/hostname'));
        if (!isset ($arr[3])){ return 'unknown'; }
        return $arr[0];
    }    
        
    public function getClusterName() {
        if (!file_exists('/etc/hostname')){ return 'unknown'; }
        $arr=explode('.', file_get_contents('/etc/hostname'));
        if (!isset ($arr[3])){ return 'unknown'; }
        return $arr[2].'.'.$arr[3]; //clustername.tld
    }    

    public function setClusterKey($key) {
        file_put_contents('/root/.bs-ckey',$key);
        $this->run('chmod 700 /root/.bs-ckey');
        $this->run('chown root /root/.bs-ckey');
    }    
    public function getClusterKey() {
        if (!file_exists('/root/.bs-ckey')){ 
            $this->log('Cluster key not found!',LOG_ERROR);
            die(5588);
        }
        $res=file_get_contents('/root/.bs-ckey');
        if (strlen($res)<4) {
            $this->log('There is no root rights or key corrupt!',LOG_ERROR);
            die(5578);
        }
        return $res;
    }
    
    public function getParams() {
        global $argv; $out=false;
        foreach ($argv as $key => $value) {
            if ($key>1) { $out[]=$value; }
        }
        return $out;
    }
    
    public function setPermission($path,$files='644',$dirs='755',$user=false) {
        $s->log('Fix permissions for '.$path, LOG_STR);
        if (!$user) {
            $s->run("chown -R `id -un`:www-data $path");
        } else {
            $s->run("chown -R $user:www-data $path");            
        }
        $s->run("find $path. -type f -exec chmod $files {} \;");
        $s->run("find $path/ -type d -exec chmod $dirs {} \;");
        $s->ok();
    }
    
    public function run($cmd,$pass_error=false) {
        global $sys_run_counter;
        $sys_run_counter++;
        file_put_contents(INSTALLDIR.'log/bslog.count',$sys_run_counter);
        $date_time=date("d.m.y H:i:s");
        $data="RUN/$sys_run_counter [$date_time]: $cmd\n";
        file_put_contents(INSTALLDIR.'log/main.log', $data,FILE_APPEND);
        $log_file=INSTALLDIR."log/runlog/run-$sys_run_counter.log";
        system('bash -c "'.$cmd.'" >> '.$log_file.' 2>&1',$ret);
        if ($pass_error) {return $ret;}
        if ($ret!=0) {
            $tolog=$this->color['red_h']." [ERROR] \n    $data"."     Wrong exitcode: $ret\n     Error log:\n".$this->color['std_h'];
            $tolog.=file_get_contents($log_file);
            $tolog.=$this->color['red_h']."    End error log\n".$this->color['std'];
            if (!LOG_SILENT) {
                echo $tolog;
            }
            file_put_contents(INSTALLDIR.'log/screen.log', $tolog,FILE_APPEND);
            $this->last_log_err=true;
        } 
        return $ret;
    }
    
    public function srun($cmd,$pass_error=false) {
        return $this->run('sudo '.$cmd,$pass_error,$cmd);
    }
    
    public function run_raw($cmd) {
        system($cmd,$ret);
        return $ret;
    }
    
    public function apt($cmd,$pass_error=false) {
        return $this->srun("DEBIAN_FRONTEND='noninteractive' aptitude -o Dpkg::Options::='--force-confnew' -y -q $cmd",$pass_error);
    }
    public $last_log_str;
    public $last_log_err;
      
    public function log($log,$kind='unknown') {
        switch ($kind){
            case LOG_STR:  
                $str="                - ".$log;
                $this->last_log_str=$str;
                $str=$this->color['yellow_h'].$str;
                break;
            
            case LOG_OK:
                $str='';
                $strl=strlen($this->last_log_str);
                for ($index = 1; $index < 80-$strl; $index++) {
                    $str .= '.';
                }
                $str.=$this->color['green_h']."[OK]\n";
                break;    
                
            case LOG_ERROR:
                $str='';
                $strl=strlen($this->last_log_str);
                for ($index = 1; $index < 80-$strl; $index++) {
                    $str .= '.';
                }
                $str.=$this->color['red_h']."[ERROR]\n        ".$log;
                break;    
                
            case LOG_CUSTOM:
                $str = $log;
                break;
            
            case LOG_STEP:  
                $str=$this->color['green_h'].'    [>]    '.$this->color['green'].$log."\n";
                break;

            case LOG_VER:  
                $str=$this->color['std_h'].'Black Sequoia. Administrative tool, ver: '.BS_VER.$log."\n";
                break;
            
            case 'unknown':
                $str='    '.$this->color['green_h'].$log."\n";
                break;
            
            default :
                //$str = $log;
                break;
        }
        $this->last_log_err=false;
        echo($str.$this->color['std']);
        file_put_contents(INSTALLDIR.'log/screen.log', $str.$this->color['std'],FILE_APPEND);
    }   
    
    public function ok() {
        if ($this->last_log_err) {$this->last_log_err=false; return false;}
        $this->log('', LOG_OK);
        return true;
    }

    public function ver() {
        $this->log('', LOG_VER);
        return true;
    }


    public function dl($addr,$stop_if_fail=true){
        $result=$this->run("wget -q '$addr'");
        if ($result!=0){
            $this->log("STOP: can't download '$addr'", LOG_ERROR);
            if ($stop_if_fail) {die(9699);}
        }
    }
    public function cd($path){
        return chdir($path);
    }
}
