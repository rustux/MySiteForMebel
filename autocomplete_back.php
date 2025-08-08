<?php
// Параметры подключения к базе данных
$host = '10.8.0.5';
$port = '14357';
$dbPath = 'C:\Users\MatBase.FDB';
$username = 'SYSDBA';
$password = 'masterkey';

header('Content-Type: application/json; charset=utf-8');

try {
    // Формируем DSN строку подключения
    $dsn = "firebird:dbname={$host}/{$port}:{$dbPath};charset=utf8";
    
    // Создаем подключение
    $dbh = new PDO($dsn, $username, $password);
    
    // Устанавливаем режим обработки ошибок
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Получаем данные из POST запроса
    $data = json_decode(file_get_contents('php://input'), true);
    $query = $data['query'];
    $field = $data['field'];
    
    // Проверяем корректность входных данных
    if (empty($query) || empty($field)) {
        http_response_code(400);
        echo json_encode(['error' => 'Неверные параметры запроса']);
        exit;
    }
    
    // Формируем SQL запрос в зависимости от поля поиска
    if ($field === 'article') {
        $sql = "
            SELECT FIRST 10 
                ARTICLE, 
                NAME_MAT 
            FROM MATERIAL 
            WHERE ARTICLE LIKE :query 
            ORDER BY ARTICLE
        ";
    } elseif ($field === 'name') {
        $sql = "
            SELECT FIRST 10 
                ARTICLE, 
                NAME_MAT 
            FROM MATERIAL 
            WHERE NAME_MAT LIKE :query 
            ORDER BY NAME_MAT
        ";
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Неверное поле поиска']);
        exit;
    }
    
    // Подготавливаем и выполняем запрос
    $stmt = $dbh->prepare($sql);
    $stmt->execute(['query' => '%' . $query . '%']);
    
    // Получаем результаты
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Формируем ответ
    $response = array_map(function($row) {
        return [
            'article' => $row['ARTICLE'],
            'name' => $row['NAME_MAT']
        ];
    }, $results);
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка базы данных: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Внутренняя ошибка сервера']);
}
?>
