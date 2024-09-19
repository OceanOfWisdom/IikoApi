<?php 

class OrderApi implements OrderApiInterface
{
    private string $token;
    public array $organizations;
    public array $terminalGroups;
    public object $cities;
    public object $streets;
    
    /*
     * Получаем токен, используя логин. Токен будет использоваться при каждом следующем обращении к Iiko Api.
     * Также получаем ID организации и терминала, без них невозможны дальнейшие действия.
     */
    public function __construct()
    {
        $this->token = $this->getAuthToken(API_LOGIN);
        $this->organizations = $this->getOrgIds();
        $this->terminalGroups = $this->getTerminalId();
    }
    
    /*
     * Получение токена
     * 
     * @param string $apiLogin - Константа из config.php
     * 
     * @return string Token
     */
    function getAuthToken(string $apiLogin): string
    {
        $postData = [
            "apiLogin" => $apiLogin,
        ];

        $tokenResponse = Curl::curlRequest(
            "access_token", 
            $postData
        );
        
    if (!isset($tokenResponse->token)) {
        Log::logToFile ($tokenResponse, "error");
        throw new NotFoundTokenExpcetion("Не удалось получить токен авторизации по API-ключу.");
    }
    
    return $tokenResponse->token;
    }
    
    /*
     * Получение ID организации на основании константы ORGANIZATION
     * 
     * @return array Organizations
     */
    function getOrgIds(): array
    {
        $postData = [
            "returnAdditionalInfo" => true,
        ];
        $organizationsResponse = Curl::curlRequest(
            "organizations", 
            $postData, 
            $this->token
        );
        
        if (!isset($organizationsResponse->organizations)) {
            Log::logToFile ($organizationsResponse, "error");
            throw new NotFoundOrganizationExpcetion("Не удалось получить список организаций.");
        }
        
        foreach ($organizationsResponse->organizations as $organization) {
            if ($organization->name === ORGANIZATION) {
                $organizationsResult[] = $organization->id;
            }
        }
        
        if (!isset($organizationsResult)) {
            $error_text = "Организация `" . ORGANIZATION . "` не найдена";
            Log::logToFile ($error_text, "error");
            throw new NotFoundOrganizationExpcetion($error_text);
        }
        
        return $organizationsResult;
    }
    
    /*
     * Получение ID терминала на основании константы TERMINAL
     * 
     * @return array TerminalGroups
     */
    function getTerminalId(): array
    {
        $postData = [
            "organizationIds" => $this->organizations,
        ];
        
        $terminalsResponse = Curl::curlRequest(
            "terminal_groups", 
            $postData, 
            $this->token
        );
        
        if (!isset($terminalsResponse->terminalGroups)) {
            Log::logToFile ($terminalsResponse, "error");
            throw new NotFoundTerminalExpcetion("Не удалось получить список терминалов.");
        }
        
        foreach ($terminalsResponse->terminalGroups as $terminalGroup) {
            foreach ($terminalGroup->items as $item) {
                if ($item->name === TERMINAL) {
                    $terminalGroupsResult[] = $item->id;
                }
            }
        }
        
        if (!isset($terminalGroupsResult)) {
            $error_text = "Терминал `" . TERMINAL . "` не найден";
            Log::logToFile ($error_text, "error");
            throw new NotFoundTerminalExpcetion($error_text);
        }
        
        return $terminalGroupsResult;
    }
    
    /*
     * Получение ID города на основании константы CITY
     * 
     * @return object City
     */
    function getCityId(): object
    {
        $postData = [
            "organizationIds" => $this->organizations,
        ];
        
        $citiesResponse = Curl::curlRequest(
            "cities",
            $postData,
            $this->token
        );
        
        if (!isset($citiesResponse->cities[0]->items)) {
            Log::logToFile ($citiesResponse, "error");
            throw new NotFoundCityExpcetion("Не удалось получить список городов.");
        }

        foreach ($citiesResponse->cities[0]->items as $city) {
            if ($city->name === CITY) {
                $cityResult = $city;
            }
        }
        
        if (!isset($cityResult)) {
            $error_text = "Город `" . CITY . "` не найден";
            Log::logToFile ($error_text, "error");
            throw new NotFoundCityExpcetion($error_text);
        }
        
        return $cityResult;
    }
    
    /*
     * Получение улиц из КЛАДРа Iiko на основании города
     * 
     * @return object Streets
     */
    function getStreets(): object
    {
        $postData = [
            "organizationId" => $this->organizations[0],
            "cityId" => $this->cities->id,
        ];
        
        $streets = Curl::curlRequest(
            "streets/by_city", 
            $postData, 
            $this->token
        );
        
        if (!isset($streets->streets)) {
            Log::logToFile ($streets, "error");
            throw new NotFoundStreetExpcetion("Не удалось получить список улиц.");
        }
        
        return $streets;
    }
    
    /*
     * Отправка заказа в Iiko
     * 
     * @param array $orderArray - Массив заказа
     * 
     * @return object - Ответ на формирование заказа
     */
    function sendOrder(array $orderArray): object
    {
        return Curl::curlRequest(
            "deliveries/create", 
            $orderArray, 
            $this->token
        );
    }
    
}
?>