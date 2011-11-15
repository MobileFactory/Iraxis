<?php
/*  Black Sequoia
 *  bs.common.php
 *  Common php lib
 *  Copyrigth Iraxis llc. '10'11  */

define('BS_VER','0.2.0-DEV-ALPHA');
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
    }
    
    public function getNodeName() {
        $cont=file_get_contents('/etc/hostname');
        $arr=explode('.', $cont);
        if (!isset ($arr[3])){
            return false;
        }
        return $arr[0];
    }    
        
    public function getClusterName() {
        $cont=file_get_contents('/etc/hostname');
        $arr=explode('.', $cont);
        if (!isset ($arr[3])){
            return false;
        }
        return $arr[2].'.'.$arr[3];
    }    

    public function getClusterKey() {
        global $argv;        
        if (!isset($argv[2])){
            $s= new SYS;
            $s->log('Cluster key not setted!',LOG_ERROR);
            die(5588);
        }
        return $argv[2];
    }
    
    public function getAfterClusterKeyParams() {
        global $argv;      
        $out=false;
        foreach ($argv as $key => $value) {
            if ($key>2) {
                $out[]=$value;
            }
        }
        return $out;
    }
    

    public function run($cmd,$pass_error=false) {
        global $sys_run_counter;
        $sys_run_counter++;
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
                $str=$this->color['std_h'].'Black Sequoia administrative tool, ver: '.BS_VER.$log."\n";
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


    public function dl($addr){
        $result=$this->run("wget -q '$addr'");
        if ($result!=0){
            $this->log("STOP: can't download '$addr'", LOG_ERROR);
            die(9699);
        }
    }
    public function cd($path){
        return chdir($path);
    }
}
