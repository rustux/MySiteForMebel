<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
	<!-- Добавляем мета-тег для корректного масштабирования -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Форма ввода данных</title>
	<link rel="stylesheet" href="style.css">
</head>
<body>
    <form class="product-form" action="#" method="post">
        <!-- Группа полей для ввода данных -->
        <div class="form-group autocomplete-container">
            <input type="text" id="article" name="article" required>
			<label for="article">Артикул:</label>
			<div class="autocomplete-dropdown" style="display: none;" id="dropdown1">
				<ul class="dropdown-list">
					<!-- Здесь будут динамически добавляться элементы -->
				</ul>
			</div>
        </div>

        <div class="form-group autocomplete-container">
            <input type="text" id="name" name="name" required>
            <label for="name">Наименование:</label>
				<div class="autocomplete-dropdown" id="dropdown2" style="display: none;">
					<ul class="dropdown-list">
						<!-- Здесь будут динамически добавляться элементы -->
					</ul>
				</div>			
        </div>

        <div class="form-group">
            <input type="date" id="date" name="date" required>
            <label for="date">Дата:</label>
        </div>

        <div class="form-group">
            <input type="number" id="quantity" name="quantity" min="1" required>
            <label for="quantity">Количество:</label>
        </div>

        <div class="form-group">
            <input type="text" id="supplier" name="supplier" required>
            <label for="supplier">Название Поставщика / ФИО Заказчика:</label>
        </div>

        <div class="form-group">
            <input type="text" id="document_number" name="document_number" required>
            <label for="document_number">Номер документа:</label>
        </div>

        <div class="form-group">
            <textarea id="comments" name="comments" rows="3" cols="50" placeholder="Введите комментарии"></textarea>
            <label for="comments">Комментарии:</label>
        </div>
		
<!-- Блок с кнопками действий -->
<div class="buttons-container">
    <button type="submit" class="button primary" name="action" value="IN">Внести</button>
    <button type="submit" class="button outline" name="action" value="OUT">Списать</button>
</div>

<div class="output-container" style="margin-top: 2rem; padding: 2rem; background: #f8f9fa; border-radius: 8px;">
    <h3>Результаты операции:</h3>
    <ul class="result-list"></ul>
</div>
<script>
/// Получаем форму
const form = document.querySelector('.product-form');

	let operationType; // Объявляем переменную вне обработчика событий

	document.addEventListener('DOMContentLoaded', () => {
		document.querySelector('.buttons-container').addEventListener('click', function(event) {
			if (event.target.tagName === 'BUTTON') {
				operationType = event.target.getAttribute('value'); // Присваиваем значение переменной
				console.log(operationType); // Для проверки можно вывести значение в консоль
			}
		});
	});

// Обработчик отправки формы
form.addEventListener('submit', async (event) => {
    event.preventDefault();	
	
	// Теперь переменная operationType доступна вне блока
	console.log('Значение после клика:', operationType);


    // Собираем данные формы
    const formData = new FormData(form);
    const data = {
        type_op: operationType,
        article: formData.get('article'),
        date: formData.get('date'),
        quantity: parseInt(formData.get('quantity')),
        supplier: formData.get('supplier'),
        document_number: formData.get('document_number'),
        comments: formData.get('comments')
    };
	displayDataInList(data)

    try {
        // Отправляем данные
        const response = await fetch('/addToStockOperations.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams(data)
        });

        const result = await response.json();

        if (response.ok) {
            alert(result.success);
            form.reset();
        } else {
            throw new Error(result.error || 'Ошибка при обработке запроса');
        }
    } catch (error) {
        console.error('Ошибка:', error.message);
        alert('Произошла ошибка при отправке данных');
    }
});

// Функция для отображения данных из объекта data
function displayDataInList(data) {
    // Получаем контейнер для результатов
    const resultList = document.querySelector('.result-list');
    
    // Очищаем существующий контент
    resultList.innerHTML = '';
    
    // Массив для хранения названий полей на русском
    const fieldLabels = {
        type_op: 'Тип операции',
        article: 'Артикул',
        date: 'Дата',
        quantity: 'Количество',
        supplier: 'Поставщик',
        document_number: 'Номер документа',
        comments: 'Комментарии'
    };
    
    // Проходим по всем полям объекта data
    for (const key in data) {
        if (data.hasOwnProperty(key)) {
            // Создаем элемент списка
            const li = document.createElement('li');
            
            // Формируем текст с названием поля и значением
            li.textContent = `${fieldLabels[key] || key}: ${data[key]}`;
            
            // Добавляем элемент в список
            resultList.appendChild(li);
        }
    }
}



</script>
<script src="autocomplete.js"></script>
    </form>
</body>
</html>
