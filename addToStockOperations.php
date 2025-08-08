<?php
// Устанавливаем заголовок ответа
header('Content-Type: application/json; charset=utf-8');

// Данные для подключения к базе
$host = '10.8.0.5';
$port = '14357';
$dbPath = 'C:\Users\MatBase.FDB';
$username = 'SYSDBA';
$password = 'masterkey';

// Формируем DSN строку подключения
$dsn = "firebird:dbname={$host}/{$port}:{$dbPath};charset=utf8";

try {
    // Создаем подключение
    $dbh = new PDO($dsn, $username, $password);
    
    // Устанавливаем режим обработки ошибок
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Получаем параметры из POST-запроса и проверяем их
    $type_op = trim($_POST['type_op']) ?: null;
    $article = trim($_POST['article']) ?: null;
    $date = trim($_POST['date']) ?: null;
    $quantity = (float)trim($_POST['quantity']) ?: null;
    $supplier = trim($_POST['supplier']) ?: null;
    $document_number = trim($_POST['document_number']) ?: null;
    $comments = trim($_POST['comments']) ?: null;
	
	// Получаем следующее значение генератора и вставляем запись
    $sql = "SELECT GEN_ID(GEN_STOCK_OPERATIONS, 1) AS new_id FROM RDB\$DATABASE";
    $stmt = $dbh->query($sql);
	$result = $stmt->fetchColumn();
	$idOP = (int)$result;
	
    
    // Подготавливаем SQL-запрос с параметрами
    $sql = "INSERT INTO STOCK_OPERATIONS (
		ID_OP,
        ARTICLE, 
        OPERATION_DATE, 
        QUANTITY, 
        ID_SUP, 
        DOC_NUMBER, 
        COMMENT, 
        TYPE_OP
    ) VALUES (
		:id_op,
        :article, 
        :date, 
        :quantity, 
        (SELECT ID_SUP FROM SUPPLIERS WHERE NAME_SUP = :supplier),
        :document_number, 
        :comments, 
        :type_op
    )";
    
    // Создаем подготовленный запрос
    $stmt = $dbh->prepare($sql);
    
    // Привязываем параметры
	$stmt->bindParam(':id_op', $idOP, PDO::PARAM_INT);
    $stmt->bindParam(':article', $article);
    $stmt->bindParam(':date', $date);
    $stmt->bindParam(':quantity', $quantity);
    $stmt->bindParam(':supplier', $supplier);
    $stmt->bindParam(':document_number', $document_number);
    $stmt->bindParam(':comments', $comments);
    $stmt->bindParam(':type_op', $type_op);
	
	// Формируем массив с данными
$responseData = [
    'new_id' => $idOP,
    'article' => $article,
    'date' => $date,
    'quantity' => $quantity,
    'supplier' => $supplier,
    'document_number' => $document_number,
    'comments' => $comments,
    'type_op' => $type_op
];

    $response = []; // Инициализируем массив для ответа

    if ($stmt->execute()) {
        http_response_code(200); // Успешный статус
        $response['status'] = 'success';
        $response['message'] = 'Запись успешно добавлена';
        $response['data'] = $responseData;
        $response['data2'] = $idOP;
    } else {
        http_response_code(400); // Ошибка клиента
        $response['status'] = 'error';
        $response['message'] = 'Ошибка при добавлении записи';
        $response['details'] = $stmt->errorInfo(); // Для отладки
    }
} catch (PDOException $e) {
    http_response_code(500); // Серверная ошибка
    $response['status'] = 'error';
    $response['message'] = 'Ошибка подключения к базе данных: ' . $e->getMessage();
} finally {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    $dbh = null;
}
?>
