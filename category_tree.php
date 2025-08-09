<?php
// Подключение к базе данных через ODBC
$dsn = "DRIVER=Firebird/InterBase(r) driver;DBNAME=C:\Users\MatBase.FDB;CHARSET=UTF8";
$user = "SYSDBA";
$password = "masterkey";

$conn = odbc_connect($dsn, $user, $password);
if (!$conn) {
    die("Ошибка подключения: " . odbc_errormsg());
}

// Получаем данные из таблицы GROUP_MATERIAL
$sql = "SELECT ID_GRM, NAME_GROUP, ENTRY FROM GROUP_MATERIAL ORDER BY ENTRY";
$result = odbc_exec($conn, $sql);

$categories = [];
while ($row = odbc_fetch_array($result)) {
    $categories[$row['ENTRY'] ?? 0][] = $row;
}

// Рекурсивная функция для построения дерева
function buildTree($categories, $parentId = 0) {
 if (!isset($categories[$parentId])) return '';
 
 $html = '<ul class="nested">';
 foreach ($categories[$parentId] as $category) {
 $hasChildren = isset($categories[$category['ID_GRM']]);
 $level = $parentId == 0 ? 1 : (isset($category['ENTRY']) && $category['ENTRY'] == 0 ? 1 : 2);
 
 $html .= '<li data-level="'.$level.'" data-id="'.$category['ID_GRM'].'">'; // Добавлен data-id
 if ($hasChildren) {
    $html .= '<span class="caret">'. htmlspecialchars($category['NAME_GROUP']). '</span>';
	$html .= buildTree($categories, $category['ID_GRM']);
} else {
    // оборачиваем в span.label
    $html .= '<span class="label">'. htmlspecialchars($category['NAME_GROUP']). '</span>';
}
// После этого, для унификации, оборачиваем также все caret в label
// Делаем так:
$html = str_replace('<span class="caret">', '<span class="caret label">', $html);

 $html .= '</li>';
 }
 $html .= '</ul>';
 
 return $html;
}


$treeHtml = buildTree($categories);
echo $treeHtml;
odbc_close($conn);
?>
