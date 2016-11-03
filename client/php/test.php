<?php
class ufastlog
{
    public static $logprefix;
    public static $logrequestid;
    public static function setConfig($config = [])
    {
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
        var_dump($msg);
        var_dump($msgArr = unpack("a64logprefix/a64logrequestid/a*msg", $msg));
    }
}

ufastlog::setConfig();
ufastlog::info('hello');
ufastlog::setConfig(['prefix' => 'log_2016_11_03', 'requestid' => 'DK023dxidfjadkfjasdfisafj']);
ufastlog::info('A large amount scale log');
