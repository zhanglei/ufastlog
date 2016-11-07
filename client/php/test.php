<?php
class ufastlog
{
    public static $logprefix;
    public static $logrequestid;
    public static $upstreamAddr;
    public static $upstreamPort;
    public static function setConfig($config = [])
    {
        static::$upstreamAddr = (array_keys('upstreamaddr', $config)) ? $config['upstreamaddr'] : 'udp://127.0.0.1';
        static::$upstreamPort = (array_keys('upstreamport', $config)) ? $config['upstreamport'] : '1043';
        static::$logprefix = (array_key_exists('prefix', $config)) ? $config['prefix'] : 'log_2016_11';
        static::$logrequestid = (array_key_exists('requestid', $config)) ? $config['requestid'] : str_replace(".", "", microtime(true)) . rand(1000, 9999);
    }
    public static function debug($msg)
    {
    }
    public static function info($msg)
    {
        $msg = pack("a64a64a*", static::$logprefix, static::$logrequestid, $msg);
        static::send($msg);
    }
    public static function error($msg)
    {
    }
    public static function send($msg)
    {
        $fp = fsockopen(static::$config['upstreamaddr'], static::$config['upstreamport'], $errno, $errmsg);
        if (!$fp) {
            echo "ERROR: " . $errno . ": " . $errmsg . PHP_EOL;
            return ;
        } else {
            fwrite($fp, $msg);
            return fread($fp, 64);
        }
        fclose($fp);
        //var_dump($msg);
        //var_dump($msgArr = unpack("a64logprefix/a64logrequestid/a*msg", $msg));
    }
}

ufastlog::setConfig();
ufastlog::info('hello');
ufastlog::setConfig(['prefix' => 'log_2016_11_03', 'requestid' => 'DK023dxidfjadkfjasdfisafj']);
ufastlog::info('A large amount scale log');
