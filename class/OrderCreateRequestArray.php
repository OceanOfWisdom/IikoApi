<?php 
final class OrderCreateRequestArray implements OrderCreateRequestArrayInterface
{
    /*
     * Метод формирования массива значений заказа под стандарт Iiko Api
     * https://api-ru.iiko.services/
     * 
     * @param array $orderData - Массив значений из $_POST
     * @param object $orderApi - Экземпляр основного класса
     */
    final public static function create(array $orderData, object $orderApi): array
    {
        $order["organizationId"] = $orderApi->organizations[0];
        $order["terminalGroupId"] = $orderApi->terminalGroups[0];
        
        $order["order"]["phone"] = preg_replace("/[^0-9+]/", "", $orderData["Телефон"]);
        
        if (($orderData["payment"]["delivery"] === "Самовывоз")) {
            $order["order"]["orderServiceType"] = "DeliveryByClient";
        } else {
            $order["order"]["orderServiceType"] = "DeliveryByCourier";
            
            $orderApi->cities = $orderApi->getCityId();
            $orderApi->streets = $orderApi->getStreets();
            
            $arAddress = explode(",", $orderData["payment"]["delivery_address"]);
            $address["zip"] = $orderData["payment"]["delivery_zip"];
            $address["city"] = $arAddress[1];
            $address["street"] = explode(" ", $arAddress[2])[2];
            $address["house"] = $arAddress[3];
            $address["flat"] = ($arAddress[4]) ? preg_replace("/[^0-9+]/", "", $arAddress[4]) : "";
            $address["entrance"] = ($arAddress[5]) ? preg_replace("/[^0-9+]/", "", $arAddress[5]) : "";
            $address["floor"] = ($arAddress[6]) ? preg_replace("/[^0-9+]/", "", $arAddress[6]) : "";

            foreach ($orderApi->streets->streets as $street) {
                if (strpos($street->name, $address["street"]) !== false) {
                    $arStreet["id"] = $street->id;
                    break;
                }
            }
            
            if (!isset($arStreet)) {
                Log::logToFile ("Улица `" . $address["street"] . "` не найдена", "error");
                die();
            }
            
            $order["order"]["deliveryPoint"]["address"]["street"]["id"] = $arStreet["id"];
            $order["order"]["deliveryPoint"]["address"]["street"]["city"] = CITY;
            
            $order["order"]["deliveryPoint"]["address"]["house"] = $address["house"];
            $order["order"]["deliveryPoint"]["address"]["flat"] = $address["flat"];
            $order["order"]["deliveryPoint"]["address"]["entrance"] = $address["entrance"];
            $order["order"]["deliveryPoint"]["address"]["floor"] = $address["floor"];
            
            $order["order"]["deliveryPoint"]["type"] = "legacy";
        }
        
        $order["order"]["comment"] = $orderData["payment"]["delivery_comment"];
        
        $order["order"]["customer"]["name"] = $orderData["Имя"];
        $order["order"]["customer"]["email"] = $orderData["почта"];
        $order["order"]["customer"]["type"] = "regular";
        
        foreach ($orderData["payment"]["products"] as $key => $product) {
            $order["order"]["items"][$key]["productId"] = $product["externalid"];
            $order["order"]["items"][$key]["price"] = $product["price"];
            $order["order"]["items"][$key]["type"] = "Product";
            $order["order"]["items"][$key]["amount"] = $product["quantity"];
        }
        
        return $order;
    }
}
?>