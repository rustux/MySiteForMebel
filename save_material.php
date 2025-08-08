<?php
// Подключение к базе данных
$dsn = "DRIVER=Firebird/InterBase(r) driver;DBNAME=C:\Users\MatBase.FDB;CHARSET=UTF8";
$user = "SYSDBA";
$password = "masterkey";

// Получаем данные из запроса
$data = json_decode(file_get_contents('php://input'), true);

// Проверяем наличие данных
if (!isset($data['main']) || !isset($data['advance'])) {
    echo "Ошибка: неверные данные";
    exit;
}

// Выводим полученные данные для проверки
echo "Полученные данные:\n";
echo "Основные параметры: " . print_r($data['main'], true) . "\n";
echo "Дополнительные параметры: " . print_r($data['advance'], true) . "\n";

try {
    // Устанавливаем соединение
    $conn = odbc_connect($dsn, $user, $password);
    if (!$conn) {
        echo "Ошибка подключения к базе данных";
        exit;
    }

// Получаем следующее значение последовательности
$sequenceQuery = "SELECT GEN_ID(GEN_MATERIAL_ID, 1) AS NEW_ID FROM RDB\$DATABASE";
$result = odbc_exec($conn, $sequenceQuery);
if (!$result) {
    throw new Exception("Ошибка получения значения последовательности: " . odbc_errormsg());
}
$row = odbc_fetch_array($result);
$newId = $row['NEW_ID'];
    
    // Добавляем вывод полученного ID
	echo "Полученный MaxID". $row . "\n";
    echo "Полученный ID для новой записи: " . $newId . "\n";

    // Формируем SQL запрос для основной таблицы
    $sqlMain = "INSERT INTO MATERIAL (
        ID_M, ID_GRM, ID_MS, NAME_MAT, ARTICLE, COEF_EXC, PRICE, COMMENT
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?
    )";

    // Формируем SQL запрос для дополнительной таблицы
    $sqlAdvance = "INSERT INTO MATERIAL_ADVANCE (
        ID_M, THICKNESS, LENGTH, WIDTH
    ) VALUES (
        ?, ?, ?, ?
    )";

    // Подготавливаем и выполняем запросы
    $stmtMain = odbc_prepare($conn, $sqlMain);
    $stmtAdvance = odbc_prepare($conn, $sqlAdvance);

    // Значения для основной таблицы
    $paramsMain = [
        $newId,
        $data['main']['id_grm'],
        $data['main']['id_ms'],
        $data['main']['name_mat'],
        $data['main']['article'],
        $data['main']['coef_exc'],
        $data['main']['price'],
        $data['main']['comment']
    ];

    // Значения для дополнительной таблицы
    $paramsAdvance = [
        $newId,
        $data['advance']['thickness'],
        $data['advance']['length'],
        $data['advance']['width']
    ];

    // Выполняем запросы
    if (
        odbc_execute($stmtMain, $paramsMain) &&
        odbc_execute($stmtAdvance, $paramsAdvance)
    ) {
        echo "Запись успешно добавлена. Новый ID: " . $newId;
    } else {
        echo "Ошибка при добавлении записи";
    }

    // Закрываем соединение
    odbc_close($conn);

} catch (Exception $e) {
    echo "Произошла ошибка: " . $e->getMessage();
}
?>
