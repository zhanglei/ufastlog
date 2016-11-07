<?php
$startime = microtime(true);
class ufastlog
{
    public static $logprefix;
    public static $logrequestid;
    public static $upstreamAddr;
    public static function setConfig($config = [])
    {
        static::$upstreamAddr = (array_key_exists('upstreamaddr', $config)) ? $config['upstreamaddr'] : 'udp://127.0.0.1:1043';
        static::$logprefix = (array_key_exists('prefix', $config)) ? $config['prefix'] : 'log_2016_11';
        static::$logrequestid = (array_key_exists('requestid', $config)) ? $config['requestid'] : str_replace(".", "", microtime(true)) . rand(1000, 9999);
    }
    public static function debug($msg)
    {
        return static::send('debug', $msg);
    }
    public static function info($msg)
    {
        return static::send('info', $msg);
    }
    public static function error($msg)
    {
        return static::send('error', $msg);
    }
    public static function send($msgtype, $msg)
    {
        $time = date('c');
        $msg = pack("a32a64a32a5a*", static::$logprefix, static::$logrequestid, $time, $msgtype, $msg);
        $fp = null;
        try {
            $fp = stream_socket_client(static::$upstreamAddr, $errno, $errmsg, 1, STREAM_CLIENT_CONNECT | STREAM_CLIENT_ASYNC_CONNECT);
            if (!$fp) {
                echo "ERROR: " . $errno . ": " . $errmsg . PHP_EOL;
                return "";
            }
            fwrite($fp, $msg . "\n");
            return 'ok';
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
var_dump(ufastlog::info('hello'));
ufastlog::setConfig(['prefix' => 'log_2016_11_03', 'requestid' => 'DK023dxidfjadkfjasdfisafj']);
var_dump(ufastlog::info('A large amount scale log'));
$endtime = microtime(true);
$difftime = $endtime - $startime;
echo $difftime*1000 . "ms";
