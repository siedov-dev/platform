(() => {
    let activeSelect = null;

    document.addEventListener('click:custom-select', e => {
        let t = e.detail.target;

        // Don't do anything if user clicks on the active select.
        if (activeSelect === t) return;

        // Collapse active select.
        if (activeSelect !== null) {
            activeSelect.classList.remove('custom-select_expanded');
        }

        // Make clicked select new active.
        t.classList.add('custom-select_expanded');
        activeSelect = t;

        let dropdown = t.querySelector('.custom-select__dropdown');
        if (dropdown === null) {
            let selector = activeSelect.dataset.dropdown;
            if (selector !== undefined) dropdown = document.querySelector(selector);
        }
        if (dropdown !== null) {
            let rect = t.getBoundingClientRect();
            if (dropdown.classList.contains('custom-select__dropdown_wide')) {
                // let pRect = t.parentNode.getBoundingClientRect();
                // let w = t.parentNode.offsetWidth;
                // dropdown.style.width = w + 'px'
                // dropdown.style.left = -(rect.left - pRect.left) + 'px';
            } else {
                let w = dropdown.offsetWidth;
                let h = dropdown.offsetHeight;
                let restW = document.body.clientWidth - rect.left;
                let restH = document.body.clientWidth - rect.left;
                if (restW < w) {
                    dropdown.style.left = 'initial';
                    dropdown.style.right = '0';
                } else {
                    dropdown.style.right = null;
                    dropdown.style.left = '0';
                }
            }

            dropdown.classList.add('custom-select__dropdown_visible');
        }

        // Prevent select collapsing handler call.
        e.detail.originalEvent.stopPropagation();
    });

    // Don't collapse select if user clicks on it's dropdown.
    document.addEventListener('click:custom-select__dropdown', e => {
        e.detail.originalEvent.stopPropagation();
    });

    document.addEventListener('click:custom-select__option', e => {
        let t = e.detail.target;
        let customSelect = t.closest('.custom-select');
        if (customSelect === null) customSelect = t.closest('.custom-select__dropdown');
        let singleSelection = false;
        if (customSelect !== null && customSelect.classList.contains('single-select')) singleSelection = true;
        let wasSelected = t.classList.contains('custom-select__option_selected');

        if (singleSelection) {
            customSelect.querySelectorAll('.custom-select__option')
                .forEach((e) => e.classList.remove('custom-select__option_selected'));
        }

        if (wasSelected) {
            t.classList.remove('custom-select__option_selected');
        } else {
            t.classList.add('custom-select__option_selected');
            if (t.dataset.unselect !== undefined) {
                t.parentNode.querySelectorAll('.custom-select__option_selected[data-value=' + t.dataset.unselect + ']')
                    .forEach((e) => e.classList.remove('custom-select__option_selected'));
            }
        }

        let counter = customSelect.querySelector('.custom-select__counter');
        if (counter === null) {
            counter = t.parentNode.querySelector('.custom-select__counter')
        }
        if (counter !== null) {
            counter.textContent = t.parentNode.getElementsByClassName('custom-select__option_selected').length;
            counter.style.visibility = counter.textContent === '0' ? 'hidden' : 'visible';
        }

        // Update input's value.
        let input = activeSelect.querySelector('input');
        if (input !== null) {
            if (singleSelection) {
                input.value = wasSelected? '' : t.dataset.value;
                activeSelect.dispatchEvent(new CustomEvent('change', {detail: input.value}));
            } else {
                // let value = t.dataset.value;
                // let values = input.value === '' ? [] : input.value.split(',');
                let values = [];
                t.parentNode.querySelectorAll('.custom-select__option_selected')
                    .forEach((e) => values.push(e.dataset.value));
                // let index = values.indexOf(value);
                // if (t.classList.contains('custom-select__option_selected')) {
                //     if (index < 0) {
                //         values.push(value);
                //         input.value = values.join(',');
                //     }
                // } else {
                //     if (index >= 0) {
                //         values.splice(index, 1);
                //         input.value = values.join(',');
                //     }
                // }
                input.value = values.join(',');
                activeSelect.dispatchEvent(new CustomEvent('change', {detail: values.join(',')}));
            }
        }
    });

    function close() {
        if (activeSelect !== null) {
            let dropdown = activeSelect.querySelector('.custom-select__dropdown');
            if (dropdown === null) {
                let selector = activeSelect.dataset.dropdown;
                if (selector !== undefined) dropdown = document.querySelector(selector);
            }
            if (dropdown !== null) {
                dropdown.classList.remove('custom-select__dropdown_visible');
            }

            activeSelect.classList.remove('custom-select_expanded');
            activeSelect = null;
        }
    }

    // Handler that collapses active select on any click.
    window.addEventListener('click', close);
    document.addEventListener('closeActiveSelect', close);
    document.addEventListener('click:custom-select__dd-close-btn', close)
})();
