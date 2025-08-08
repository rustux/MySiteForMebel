<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Форма учета товаров</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <form class="product-form">
        <!-- Список категорий -->
        <div class="category-tree">
			<div class="category-header">Категории товаров ▼</div>
				<?php include 'category_tree.php'; ?>
				<?php echo $treeHtml; ?>
		</div>


        <!-- Основные поля формы -->
        <div class="form-group">
            <input type="text" id="article" required>
            <label for="article">Артикул</label>
        </div>

        <div class="form-group">
            <input type="text" id="name" required>
            <label for="name">Наименование</label>
        </div>

        <div class="form-group">
            <select id="unit" required>
                <option value=""></option>
                <option meas_id="1">шт</option>
                <option meas_id="2">кв.м</option>
                <option meas_id="3">м</option>
				<option meas_id="4">куб.м</option>
				<option meas_id="5">кг</option>
				<option meas_id="6">компл</option>
				<option meas_id="201">л</option>
            </select>
            <label for="unit">Единица измерения</label>
        </div>

        <div class="form-group">
            <input type="number" id="price" step="0.01" required>
            <label for="price">Цена</label>
        </div>

        <div class="form-group">
            <textarea id="notes" rows="3"></textarea>
            <label for="notes">Примечание</label>
        </div>

        <!-- Блок дополнительных параметров -->
        <div class="toggle-block">
            <div class="toggle-header">Дополнительные параметры ▼</div>
            
            <div class="additional-params">
                <div class="form-group">
                    <input type="number" id="thickness" step="0.1">
                    <label for="thickness">Толщина</label>
                </div>

                <div class="form-group">
                    <input type="number" id="length" step="0.1">
                    <label for="length">Длина</label>
                </div>

                <div class="form-group">
                    <input type="number" id="width" step="0.1">
                    <label for="width">Ширина</label>
                </div>

                <div class="form-group">
                    <input type="number" id="coefficient" step="0.01">
                    <label for="coefficient">Коэффициент избытка</label>
                </div>

                <div class="form-group">
                    <input type="text" id="abbreviation">
                    <label for="abbreviation">Краткое обозначение</label>
                </div>
            </div>
        </div>
		<input type="hidden" name="selected_category" id="selectedCategory">

        <button type="submit">Сохранить</button>
    </form>

    <!-- Скрипт для взаимодействия -->
    <script>
document.addEventListener('DOMContentLoaded', () => {
  // Инициализация раскрытия на старте (как было)
  const initNested = (container, maxLevel = 0) => {
    const nested = container.querySelector('.nested');
    if (!nested) return;
    nested.classList.add('active');
    if (maxLevel > 0) {
      nested.querySelectorAll('.nested').forEach((child, idx) => {
        if (idx < maxLevel) child.classList.add('active');
      });
    }
  };

  // Показ/скрытие главного списка
  document.querySelector('.category-header').addEventListener('click', function() {
    const mainList = this.nextElementSibling;
    mainList.classList.toggle('active');
    if (mainList.classList.contains('active')) {
      initNested(mainList, 0);
    }
  });

  // Раскрытие/сокрытие подпунктов
  document.querySelectorAll('.caret').forEach(caret => {
    caret.addEventListener('click', function(e) {
      const nested = this.closest('li').querySelector('.nested');
      nested.classList.toggle('active');
      this.classList.toggle('open');
      e.stopPropagation();
    });
  });

  // Новый блок: выбор категории (только одна)
  const categoryRoot = document.querySelector('.category-tree');
  const hiddenInput = document.getElementById('selectedCategory');

categoryRoot.addEventListener('click', function(e) {
  // ищем span.label или span.caret.label
  const label = e.target.closest('span.label, span.caret');
  if (!label) return;

  const li = label.closest('li[data-id]');

  // Убираем selected со всех меток
  categoryRoot.querySelectorAll('span.label.selected, span.caret.selected')
    .forEach(el => el.classList.remove('selected'));

  // Добавляем selected только на кликнутый label
  label.classList.add('selected');

  // Записываем ID группы из родительского li
  hiddenInput.value = li.getAttribute('data-id');

  e.stopPropagation();
});


  // Переключатель доп. параметров (как было)
  document.querySelector('.toggle-header').addEventListener('click', function() {
    this.nextElementSibling.classList.toggle('active');
    this.classList.toggle('open');
  });
});

document.querySelector('.product-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Получаем выбранную категорию
    const selectedElement = document.querySelector('.selected');
    const parentWithDataId = selectedElement ? selectedElement.closest('[data-id]') : null;
    const selectedCategory = parentWithDataId ? parentWithDataId.getAttribute('data-id') : null;

    if (!selectedCategory) {
        console.error('Ошибка: Не выбрана категория товара');
        return;
    }

    // Получаем единицу измерения
    const unitSelect = document.getElementById('unit');
    const measId = unitSelect.options[unitSelect.selectedIndex].getAttribute('meas_id');

    // Собираем основные данные
    const mainData = {
        id_grm: selectedCategory,
        id_ms: parseInt(measId),
        name_mat: document.getElementById('name').value.trim(),
        article: document.getElementById('article').value.trim(),
        coef_exc: parseFloat(document.getElementById('coefficient').value) || 0,
        price: parseFloat(document.getElementById('price').value),
        comment: document.getElementById('notes').value.trim()
    };

    // Собираем дополнительные параметры
    const advanceData = {
        thickness: parseFloat(document.getElementById('thickness').value) || 0,
        length: parseFloat(document.getElementById('length').value) || 0,
        width: parseFloat(document.getElementById('width').value) || 0
    };

    // Валидация обязательных полей
    if (!mainData.name_mat || !mainData.article || isNaN(mainData.price)) {
        console.error('Ошибка: Не заполнены обязательные поля');
        return;
    }

    // Выводим данные для проверки
    console.log('Отправленные данные:');
    console.log('Основные параметры:', mainData);
    console.log('Дополнительные параметры:', advanceData);

    try {
        // Отправляем данные на сервер
        const response = await fetch('save_material.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                main: mainData,
                advance: advanceData
            })
        });

        // Проверяем статус ответа
        if (!response.ok) {
            throw new Error(`Ошибка сервера: ${response.statusText}`);
        }

        // Получаем ответ в виде текста
        const serverResponse = await response.text();
        
        // Выводим ответ сервера в консоль
        console.log('Ответ сервера:', serverResponse);

        // Проверяем успешность операции по содержимому ответа
        if (serverResponse.includes('успешно')) {
            console.log('Операция выполнена успешно');
            this.reset();
        } else {
            console.error('Ошибка:', serverResponse);
        }
    } catch (error) {
        console.error('Произошла ошибка:', error.message);
    }
});



</script>


</body>
</html>
