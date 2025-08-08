<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <!-- Добавляем мета-тег для корректного масштабирования -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Формы учета</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="tabs">
        <button class="tab-button active" data-tab="tab1">Операции со складом</button>
        <button class="tab-button" data-tab="tab2">Учет товаров</button>
    </div>

    <div id="tab1" class="tab-content active">
        <form class="product-form stock-form" action="#" method="post">
            <div class="form-group autocomplete-container">
                <input type="text" id="article" name="article" required>
                <label for="article">Артикул:</label>
                <div class="autocomplete-dropdown" style="display: none;" id="dropdown1">
                    <ul class="dropdown-list"></ul>
                </div>
            </div>

            <div class="form-group autocomplete-container">
                <input type="text" id="name" name="name" required>
                <label for="name">Наименование:</label>
                <div class="autocomplete-dropdown" id="dropdown2" style="display: none;">
                    <ul class="dropdown-list"></ul>
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

            <div class="buttons-container">
                <button type="submit" class="button primary" name="action" value="IN">Внести</button>
                <button type="submit" class="button outline" name="action" value="OUT">Списать</button>
            </div>

            <div class="output-container" style="margin-top: 2rem; padding: 2rem; background: #f8f9fa; border-radius: 8px;">
                <h3>Результаты операции:</h3>
                <ul class="result-list"></ul>
            </div>
        </form>
    </div>

    <div id="tab2" class="tab-content">
        <form class="product-form material-form">
            <div class="category-tree">
                <div class="category-header">Категории товаров ▼</div>
                <?php include 'category_tree.php'; ?>
                <?php echo $treeHtml; ?>
            </div>

            <div class="form-group">
                <input type="text" id="mat_article" required>
                <label for="mat_article">Артикул</label>
            </div>

            <div class="form-group">
                <input type="text" id="mat_name" required>
                <label for="mat_name">Наименование</label>
            </div>

            <div class="form-group">
                <select id="mat_unit" required>
                    <option value=""></option>
                    <option meas_id="1">шт</option>
                    <option meas_id="2">кв.м</option>
                    <option meas_id="3">м</option>
                    <option meas_id="4">куб.м</option>
                    <option meas_id="5">кг</option>
                    <option meas_id="6">компл</option>
                    <option meas_id="201">л</option>
                </select>
                <label for="mat_unit">Единица измерения</label>
            </div>

            <div class="form-group">
                <input type="number" id="mat_price" step="0.01" required>
                <label for="mat_price">Цена</label>
            </div>

            <div class="form-group">
                <textarea id="mat_notes" rows="3"></textarea>
                <label for="mat_notes">Примечание</label>
            </div>

            <div class="toggle-block">
                <div class="toggle-header">Дополнительные параметры ▼</div>

                <div class="additional-params">
                    <div class="form-group">
                        <input type="number" id="mat_thickness" step="0.1">
                        <label for="mat_thickness">Толщина</label>
                    </div>

                    <div class="form-group">
                        <input type="number" id="mat_length" step="0.1">
                        <label for="mat_length">Длина</label>
                    </div>

                    <div class="form-group">
                        <input type="number" id="mat_width" step="0.1">
                        <label for="mat_width">Ширина</label>
                    </div>

                    <div class="form-group">
                        <input type="number" id="mat_coefficient" step="0.01">
                        <label for="mat_coefficient">Коэффициент избытка</label>
                    </div>

                    <div class="form-group">
                        <input type="text" id="mat_abbreviation">
                        <label for="mat_abbreviation">Краткое обозначение</label>
                    </div>
                </div>
            </div>

            <input type="hidden" name="selected_category" id="selectedCategory">

            <button type="submit">Сохранить</button>
        </form>
    </div>

    <script src="autocomplete.js"></script>
    <script>
    document.querySelectorAll('.tab-button').forEach(button => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            button.classList.add('active');
            document.getElementById(button.dataset.tab).classList.add('active');
        });
    });

    const stockForm = document.querySelector('.stock-form');
    let operationType;
    document.querySelector('.buttons-container').addEventListener('click', (event) => {
        if (event.target.tagName === 'BUTTON') {
            operationType = event.target.getAttribute('value');
        }
    });

    stockForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        const formData = new FormData(stockForm);
        const data = {
            type_op: operationType,
            article: formData.get('article'),
            date: formData.get('date'),
            quantity: parseInt(formData.get('quantity')),
            supplier: formData.get('supplier'),
            document_number: formData.get('document_number'),
            comments: formData.get('comments')
        };
        displayDataInList(data);

        try {
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
                stockForm.reset();
            } else {
                throw new Error(result.error || 'Ошибка при обработке запроса');
            }
        } catch (error) {
            console.error('Ошибка:', error.message);
            alert('Произошла ошибка при отправке данных');
        }
    });

    function displayDataInList(data) {
        const resultList = document.querySelector('.result-list');
        resultList.innerHTML = '';

        const fieldLabels = {
            type_op: 'Тип операции',
            article: 'Артикул',
            date: 'Дата',
            quantity: 'Количество',
            supplier: 'Поставщик',
            document_number: 'Номер документа',
            comments: 'Комментарии'
        };

        for (const key in data) {
            if (data.hasOwnProperty(key)) {
                const li = document.createElement('li');
                li.textContent = `${fieldLabels[key] || key}: ${data[key]}`;
                resultList.appendChild(li);
            }
        }
    }

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

    document.querySelector('.category-header').addEventListener('click', function() {
        const mainList = this.nextElementSibling;
        mainList.classList.toggle('active');
        if (mainList.classList.contains('active')) {
            initNested(mainList, 0);
        }
    });

    document.querySelectorAll('.caret').forEach(caret => {
        caret.addEventListener('click', function(e) {
            const nested = this.closest('li').querySelector('.nested');
            nested.classList.toggle('active');
            this.classList.toggle('open');
            e.stopPropagation();
        });
    });

    const categoryRoot = document.querySelector('.category-tree');
    const hiddenInput = document.getElementById('selectedCategory');

    categoryRoot.addEventListener('click', function(e) {
        const label = e.target.closest('span.label, span.caret');
        if (!label) return;

        const li = label.closest('li[data-id]');
        categoryRoot.querySelectorAll('span.label.selected, span.caret.selected')
            .forEach(el => el.classList.remove('selected'));

        label.classList.add('selected');
        hiddenInput.value = li.getAttribute('data-id');

        e.stopPropagation();
    });

    document.querySelector('.toggle-header').addEventListener('click', function() {
        this.nextElementSibling.classList.toggle('active');
        this.classList.toggle('open');
    });

    const materialForm = document.querySelector('.material-form');
    materialForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const selectedElement = document.querySelector('.selected');
        const parentWithDataId = selectedElement ? selectedElement.closest('[data-id]') : null;
        const selectedCategory = parentWithDataId ? parentWithDataId.getAttribute('data-id') : null;

        if (!selectedCategory) {
            console.error('Ошибка: Не выбрана категория товара');
            return;
        }

        const unitSelect = document.getElementById('mat_unit');
        const measId = unitSelect.options[unitSelect.selectedIndex].getAttribute('meas_id');

        const mainData = {
            id_grm: selectedCategory,
            id_ms: parseInt(measId),
            name_mat: document.getElementById('mat_name').value.trim(),
            article: document.getElementById('mat_article').value.trim(),
            coef_exc: parseFloat(document.getElementById('mat_coefficient').value) || 0,
            price: parseFloat(document.getElementById('mat_price').value),
            comment: document.getElementById('mat_notes').value.trim()
        };

        const advanceData = {
            thickness: parseFloat(document.getElementById('mat_thickness').value) || 0,
            length: parseFloat(document.getElementById('mat_length').value) || 0,
            width: parseFloat(document.getElementById('mat_width').value) || 0
        };

        if (!mainData.name_mat || !mainData.article || isNaN(mainData.price)) {
            console.error('Ошибка: Не заполнены обязательные поля');
            return;
        }

        console.log('Отправленные данные:');
        console.log('Основные параметры:', mainData);
        console.log('Дополнительные параметры:', advanceData);

        try {
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

            if (!response.ok) {
                throw new Error(`Ошибка сервера: ${response.statusText}`);
            }

            const serverResponse = await response.text();
            console.log('Ответ сервера:', serverResponse);

            if (serverResponse.includes('успешно')) {
                console.log('Операция выполнена успешно');
                materialForm.reset();
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

