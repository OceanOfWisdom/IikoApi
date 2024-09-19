<?php 

final class Curl 
{
    /*
     * Метод отправки запросов в Iiko 
     * 
     * @param string $action - Действие
     * @param array $postData - Передаваемые значения
     * @param string $token - Токен
     * 
     * @return $response - Ответ
     */
    final public static function curlRequest(
        string $action, 
        array  $postData = ["" => ""], 
        string $token = null
    ) {
        $retries = 3;
        
        $actionUrl = IIKO_URL . $action;
        $httpHeaders = ["Content-Type: application/json"];
        
        if (!empty($token)) {
            array_push($httpHeaders, "Authorization: Bearer " . $token);
        }
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL,            $actionUrl);
        curl_setopt($curl, CURLOPT_HTTPHEADER,     $httpHeaders);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST,  "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS,     json_encode($postData));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
        for ($i = 0; $i < $retries; $i++) {
            $response = json_decode(curl_exec($curl));
            
            if (!$response) {
                Log::logToFile ("Error: " . curl_error($curl) . " - Code: " . curl_errno($curl), "error");
                throw new NoCurlResponseReceivedExpcetion("Не получен ответ сервера от IIKO API.");
            }
            
            $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            
            if (in_array($status_code, array(200, 404))) {
                break;
            } else {
                sleep(1);
            }
        }
        
        curl_close($curl);
        return $response;
    }
}

?>