<?php 
final class Log implements LogInterface
{
    /*
     * Логирование в файл
     * 
     * @param $data - Что нужно залогировать
     * @param string $name - Название файла
     */
    final public static function logToFile($data, string $fileName)
    {
        $timeZone = "Asia/Vladivostok";
        $timestamp = time();
        $dateTime = new DateTime("now", new DateTimeZone($timeZone));
        $dateTime->setTimestamp($timestamp);
        $dateNow = $dateTime->format("d.m.Y H:i:s");
        $yearNow = $dateTime->format("Y");
        $monthNow = $dateTime->format("m");
        
        $path = __DIR__ . "/../log/" . $fileName . "/" . $fileName . "_" . $yearNow . "_" . $monthNow . ".log";
        
        $log = $dateNow . " " . print_r($data, true);
        //if ($fileName == "error") echo ($log);
        
        file_put_contents($path, $log . PHP_EOL, FILE_APPEND);
    }
}
?>