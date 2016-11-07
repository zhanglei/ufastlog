<?php
class ufastlog
{
    public static $logprefix;
    public static $logrequestid;
    public static $upstreamAddr;
    public static $upstreamPort;
    public static function setConfig($config = [])
    {
        static::$upstreamAddr = (array_key_exists('upstreamaddr', $config)) ? $config['upstreamaddr'] : 'udp://127.0.0.1';
        static::$upstreamPort = (array_key_exists('upstreamport', $config)) ? $config['upstreamport'] : '1043';
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
        $fp = null;
        try {
            $fp = fsockopen(static::$upstreamAddr, static::$upstreamPort, $errno, $errmsg);
            if (!$fp) {
                echo "ERROR: " . $errno . ": " . $errmsg . PHP_EOL;
                return "";
            }
            var_dump($fp);
            echo "Connect upstream success";
            fwrite($fp, $msg . "\n");
            fwrite($fp, PHP_EOL);
            $return  = fread($fp, 32);
            var_dump($return);
            echo $return;
            return $return;
        } catch (\Exception $e) {
            echo "Something err";
        } finally {
            if (null !== $fp) {
                fclose($fp);
            }
        }

        return '';
        //var_dump($msg);
        //var_dump($msgArr = unpack("a64logprefix/a64logrequestid/a*msg", $msg));
    }
}

ufastlog::setConfig();
ufastlog::info('hello');
ufastlog::setConfig(['prefix' => 'log_2016_11_03', 'requestid' => 'DK023dxidfjadkfjasdfisafj']);
ufastlog::info('A large amount scale log');
