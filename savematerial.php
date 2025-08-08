<?php
// Параметры подключения к БД
$dsn = "DRIVER=Firebird/InterBase(r) driver;DBNAME=C:\\Users\\MatBase.FDB;CHARSET=UTF8";
$user = "SYSDBA";
$password = "masterkey";

// Получаем данные из POST запроса
$idGroup = $_POST['id_group'] ?? null;
$idMeasure = $_POST['id_measure'] ?? null;
$article = $_POST['article'] ?? '';
$name = $_POST['name'] ?? '';
$price = $_POST['price'] ?? 0;
$notes = $_POST['notes'] ?? '';
$coefficient = $_POST['coefficient'] ?? 1;
$thickness = $_POST['thickness'] ?? 0;
$length = $_POST['length'] ?? 0;
$width = $_POST['width'] ?? 0;

// Подключаемся к БД
$conn = odbc_connect($dsn, $user, $password);
if (!$conn) {
    die(json_encode(['success' => false, 'error' => 'Ошибка подключения к базе данных']));
}

try {
    // Начинаем транзакцию
    odbc_autocommit($conn, false);
    
    // Вставляем данные в таблицу MATERIAL
    $sqlMaterial = "
        INSERT INTO MATERIAL (
            ID_GRM, ID_MS, NAME_MAT, ARTICLE, COEF_EXC, PRICE, COMMENT, COEF_EXC_CUTTING
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?
        )";
    
    $stmt = odbc_prepare($conn, $sqlMaterial);
    $success = odbc_execute($stmt, [
        $idGroup,
        $idMeasure,
        $name,
        $article,
        $coefficient,
        $price,
        $notes,
        1
    ]);
    
    if (!$success) {
        throw new Exception('Ошибка вставки в таблицу MATERIAL');
    }
    
    // Получаем ID новой записи
    $idMaterial = odbc_lastinsertid($conn);
    
    // Вставляем данные в таблицу MATERIAL_ADVANCE
    $sqlAdvance = "
        INSERT INTO MATERIAL_ADVANCE (
            ID_M, THICKNESS, LENGTH, WIDTH
        ) VALUES (
            ?, ?, ?, ?
        )";
    
    $stmt = odbc_prepare($conn, $sqlAdvance);
    $success = odbc_execute($stmt, [
        $idMaterial,
        $thickness,
        $length,
        $width
    ]);
    
    if (!$success) {
        throw new Exception('Ошибка вставки в таблицу MATERIAL_ADVANCE');
    }
    
    // Фиксируем изменения
    odbc_commit($conn);
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {