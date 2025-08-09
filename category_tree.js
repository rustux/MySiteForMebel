document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('categoryTree');
    const categoryRoot = document.querySelector('.category-tree');
    const hiddenInput = document.getElementById('selectedCategory');

    fetch('category_tree.php')
        .then(response => response.text())
        .then(html => {
            container.innerHTML = html;

            const initNested = (el, maxLevel = 0) => {
                const nested = el.querySelector('.nested');
                if (!nested) return;
                nested.classList.add('active');
                if (maxLevel > 0) {
                    nested.querySelectorAll('.nested').forEach((child, idx) => {
                        if (idx < maxLevel) child.classList.add('active');
                    });
                }
            };

            document.querySelector('.category-header').addEventListener('click', function() {
                const mainList = container.querySelector('.nested');
                if (!mainList) return;
                mainList.classList.toggle('active');
                if (mainList.classList.contains('active')) {
                    initNested(mainList, 0);
                }
            });

            container.querySelectorAll('.caret').forEach(caret => {
                caret.addEventListener('click', function(e) {
                    const nested = this.closest('li').querySelector('.nested');
                    if (nested) {
                        nested.classList.toggle('active');
                    }
                    this.classList.toggle('open');
                    e.stopPropagation();
                });
            });

            categoryRoot.addEventListener('click', function(e) {
                const label = e.target.closest('span.label, span.caret');
                if (!label) return;

                const li = label.closest('li[data-id]');
                categoryRoot.querySelectorAll('span.label.selected, span.caret.selected')
                    .forEach(el => el.classList.remove('selected'));

                label.classList.add('selected');
                if (li && hiddenInput) {
                    hiddenInput.value = li.getAttribute('data-id');
                }
                e.stopPropagation();
            });
        })
        .catch(err => console.error('Ошибка загрузки дерева категорий:', err));
});

