<?
require 'qiwi.class.php';

$Qiwi = new Qiwi(); // Создаем экземпляр класса


// CОЗДАНИЕ СЧЕТА

$bill_id = rand(10000000, 99999999);

$create_result = $Qiwi->create(
    '79001234567', // телефон
    100, // сумма
    date('Y-m-d', strtotime(date('Y-m-d') . " + 1 DAY")) . "T00:00:00", // Дата в формате ISO 8601. Здесь генерируется дата на день позже, чем текущая
    $bill_id, // ID платежа
    'Тестовая оплата' // комментарий
);

if($create_result->result_code !== 0){
    echo 'Ошибка в создании счета';
}
else{
    echo 'Счет выставлен';
}

// ПЕРЕАДРЕСАЦИЯ НА СТРАНИЦУ ОПЛАТЫ

$Qiwi->redir(              
    $bill_id, // ID счета
    'http://' . $_SERVER['SERVER_NAME'] . '/success_url', // URL, на который пользователь будет переброшен в случае успешного проведения операции (не обязательно)
    'http://' . $_SERVER['SERVER_NAME'] . '/fail_url' // URL, на который пользователь будет переброшен в случае неудачного завершения операции (не обязательно)
);


// ПОЛУЧЕНИЕ ИНФОРМАЦИИ О СЧЕТЕ

$info_result = $Qiwi->info($bill_id);

if($info_result->result_code !== 0){
    echo 'Ошибка в получении информации о счете';
}
else{
    echo 'Статус счета: ' . $info_result->bill->status;
}


// ОТМЕНА СЧЕТА

$reject_result = $Qiwi->reject($bill_id);

if($reject_result->bill->status === 'rejected'){
    echo 'Не удалось отменить счет';
}
else{
    echo 'Счет отменен';
}
?>
