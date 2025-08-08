document.addEventListener('DOMContentLoaded', () => {
    const articleInput = document.getElementById('article');
    const nameInput = document.getElementById('name');
    const dropdown1 = document.getElementById('dropdown1');
    const dropdown2 = document.getElementById('dropdown2');
    
    let timeoutId;
    let currentActiveInput = null;
    let dataCache = [];

    // Функция для получения данных с сервера
    async function fetchData(query, fieldId) {
        try {
            const response = await fetch('autocomplete_back.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ 
					query: query,
					field: fieldId
					})
            });
            
            if (!response.ok) {
                throw new Error('Ошибка при получении данных');
            }
            
            return await response.json();
        } catch (error) {
            console.error('Ошибка:', error);
			console.log(query)
			console.log(fieldId)
            return [];
        }
    }

    // Обработчик ввода
    function handleInput(input, dropdown) {
		const fieldId = input.id
        clearTimeout(timeoutId);
        timeoutId = setTimeout(async () => {
            const query = input.value;
            
            if (query.trim() === '') {
                hideDropdown(dropdown);
                return;
            }
            
            dataCache = await fetchData(query, fieldId);
            showSuggestions(dataCache, dropdown, input);
        }, 300);
    }

    // Показываем варианты
    function showSuggestions(data, dropdown, activeInput) {
        const list = dropdown.querySelector('ul');
        list.innerHTML = '';
        
        data.slice(0, 10).forEach(item => {
            const li = document.createElement('li');
            li.dataset.article = item.article;
            li.dataset.name = item.name;
            
            if (activeInput === articleInput) {
                li.textContent = item.article;
            } else {
                li.textContent = item.name;
            }
            
            li.addEventListener('click', () => {
				nameInput.value = li.dataset.name;
                articleInput.value = li.dataset.article;
                hideDropdown(dropdown);
            });
            
            list.appendChild(li);
        });
        
        dropdown.style.display = 'block';
    }

    // Скрываем dropdown
    function hideDropdown(dropdown) {
        dropdown.style.display = 'none';
    }

    // Обработчики событий
    articleInput.addEventListener('input', () => handleInput(articleInput, dropdown1));
    nameInput.addEventListener('input', () => handleInput(nameInput, dropdown2));
    
    articleInput.addEventListener('focus', () => {
        currentActiveInput = articleInput;
        hideDropdown(dropdown2);
    });
    
    nameInput.addEventListener('focus', () => {
        currentActiveInput = nameInput;
        hideDropdown(dropdown1);
    });
    
    // Скрываем при клике вне полей
    document.addEventListener('click', (e) => {
        if (!articleInput.contains(e.target) && !dropdown1.contains(e.target)) {
            hideDropdown(dropdown1);
        }
        if (!nameInput.contains(e.target) && !dropdown2.contains(e.target)) {
            hideDropdown(dropdown2);
        }
    });
});
