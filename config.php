<?php 

interface CurlInterface {
    public static function curlRequest(string $action, array $postData, string $token);
}

interface OrderApiInterface {
    function getAuthToken(string $apiLogin);
    function getOrgIds(): array;
    function getTerminalId(): array;
    function getCityId(): object;
    function getStreets(): object;
    function sendOrder(array $orderArray): object;
}
 
interface OrderCreateRequestArrayInterface {
    public static function create(array $orderData, object $orderApi): array;
}

interface LogInterface {
    public static function logToFile($data, string $name);
}

/*
constants.php не передаём в Git. Содержание:

const IIKO_URL = "https://api-ru.iiko.services/api/1/";
const API_LOGIN;
const ORGANIZATION;
const TERMINAL;
const CITY;
*/

require_once("constants.php");

require_once("class/Exceptions.php");
require_once("class/Curl.php");
require_once("class/OrderApi.php");
require_once("class/OrderCreateRequestArray.php");
require_once("class/Log.php");

?>