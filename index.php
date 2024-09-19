<?php
/*
 * Модуль передачи данных с сайта на Tilda, из $_POST (вебхука) в Iiko через API
 * PHP Version 8.2.17
 * 
 * @author Леонид Селезнёв <leonid.seleznev.27@gmail.com>
 */

ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

$webhook = ($_POST);

// Test
//$fileData = file_get_contents("testOrder/laguna_request.txt");
//$webhook = unserialize($fileData);

if (!empty ($webhook["formname"])) {
    if ($webhook["formname"] == "Cart") {
        
        require_once("config.php");
        Log::logToFile ($webhook, "webhook");
        
        // Подключаемся к API, получаем токен, другие необходимые данные
        $orderApi = new OrderApi();
        
        // Формируем массив данных заказа для передачи а Iiko
        $orderArray = OrderCreateRequestArray::create($webhook, $orderApi);
        Log::logToFile ($orderArray, "orderArrayToIiko");
        
        // Отправляем заказ
        $response = $orderApi->sendOrder($orderArray);
        Log::logToFile ($response, "IikoResponse");
    }
}


?>