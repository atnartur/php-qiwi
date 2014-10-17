<?
/**
 * Qiwi - класс для работы с платежным API от QIWI
 * @package Qiwi
 * @author atnartur (Атнагулов Артур) <i@atnartur.ru>
 * @copyright 2014 atnartur (Атнагулов Артур)
 */


class Qiwi{
    /**
    * ID магазина
    * @var int
    */
    public $shop_id = 000000;
    
    
    /**
    * API ID (REST ID) для BASIC авторизации
    * @var int
    */
    public $rest_id = 00000000;
    
    
    /**
    * пароль API
    * @var string
    */
    public $rest_pass = 'PASSWORD';
    
    
    /**
    * валюта
    * @var string
    */
    public $currency = 'RUB';
    
    
    /**
    * Источник оплаты: mobile - оплата с мобильного телефона пользователя, qw - с любых источников оплаты Visa Qiwi Wallet
    * @var string
    */
    public $pay_source = 'qw';
    
    
    /**
    * название провайдера
    * @var string
    */
    public $prv_name = 'My store';
    
    
    
    /**
    * Конструктор класса. Проверяет наличие CURL
    */
    function __construct(){
        if(!function_exists('curl_init')){
            throw new Exception('CURL library not found on this server');
        }
    }
    
    
    /**
	* Создает новый CURL запрос и выставляет таймаут соединения 30 секунд
	*
	* @returns {resource} CURL resourse
	*/
    private function __curl_start($url){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        return $ch;
    }
    
    
    /**
	* Выставление счета
	* 
	* @param {string} tel Телефон пользователя, на которого выставляется счет
	* @param {int} amount Сумма счета
	* @param {string} date Срок годности счета (в формате ISO 8601)
	* @param {string} bill_id Уникальный номер счета
	* @param {string} comment Комментарий к платежу (не обязательно)
	* @returns {object} Объект ответа от сервера QIWI
	*/
    function create($tel, $amount, $date, $bill_id, $comment = null){
        $parameters = array(
            'user' => 'tel:+'.$tel, // телефон начинается с +
            'amount' => $amount, 
            'ccy' => $this->currency, 
            'comment' => $comment,
            'pay_source' => $this->pay_source,
            'lifetime' => $date,
            'prv_name' => $this->prv_name,
        );
                
        
        $ch = $this->__curl_start('https://w.qiwi.com/api/v2/prv/'.$this->shop_id.'/bills/'.$bill_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Accept: text/json",
            "Content-Type: application/x-www-form-urlencoded; charset=utf-8"
        ));
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $this->rest_id . ':' . $this->rest_pass);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $httpResponse = curl_exec($ch);
        
        if (!$httpResponse) {
            // Описание ошибки, к примеру 
            throw new Exception(curl_error($ch).'('.curl_errno($ch).')');
            return false;
        }
        $httpResponseAr = @json_decode($httpResponse);
        return $httpResponseAr->response;
    }
    
    
    /**
	* Переадресация на страницу оплаты счета
	* 
	* @param {string} bill_id Уникальный номер счета
	* @param {string} success_url URL, на который пользователь будет переброшен в случае успешного проведения операции (не обязательно)
	* @param {string} fail_url URL, на который пользователь будет переброшен в случае неудачного завершения операции (не обязательно)
	*/
    function redir($bill_id, $success_url = '', $fail_url = ''){
        header("Location: https://w.qiwi.com/order/external/main.action?shop=" . $this->shop_id . "&transaction=" . $bill_id .
               "&successUrl=" . $success_url . "&failUrl=" . $fail_url);
    }
    
    
    /**
	* Информация о счете
	* 
	* @param {string} bill_id Уникальный номер счета
	* @returns {object} Объект ответа от сервера QIWI
	*/
    function info($bill_id){
        $ch = $this->__curl_start('https://w.qiwi.com/api/v2/prv/'.$this->shop_id.'/bills/'.$bill_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Accept: text/json",
            "Content-Type: application/x-www-form-urlencoded; charset=utf-8"
        ));
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $this->rest_id . ':' . $this->rest_pass);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $httpResponse = curl_exec($ch);
        
        if (!$httpResponse) {
            // Описание ошибки, к примеру 
            throw new Exception(curl_error($ch).'('.curl_errno($ch).')');
            return false;
        }
        $httpResponseAr = @json_decode($httpResponse);
        return $httpResponseAr->response;
    }
    
    
    /**
	* Отмена платежа
	* 
	* @param {string} bill_id Уникальный номер счета
	* @returns {object} Объект ответа от сервера QIWI
	*/
    function reject($bill_id){
    	$parameters = array(
		'status' => 'rejected'
		); 
        $ch = $this->__curl_start('https://w.qiwi.com/api/v2/prv/'.$this->shop_id.'/bills/'.$bill_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Accept: text/json",
            "Content-Type: application/x-www-form-urlencoded; charset=utf-8"
        ));
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $this->rest_id . ':' . $this->rest_pass);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $httpResponse = curl_exec($ch);
        
        if (!$httpResponse) {
            // Описание ошибки, к примеру 
            throw new Exception(curl_error($ch).'('.curl_errno($ch).')');
            return false;
        }
        $httpResponseAr = @json_decode($httpResponse);
        return $httpResponseAr->response;
    }
}
?>
