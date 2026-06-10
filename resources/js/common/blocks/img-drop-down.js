(() => {
    document.addEventListener('click:img-drop-down', function (e) {
        let target = e.detail.target;
        if (target.classList.contains('img-drop-down')) {
            target.classList.toggle('shown');
        }
    });

    document.addEventListener('click:img-drop-down__value', function (e) {
        let target = e.detail.target;
        let dropDown = target.closest('.img-drop-down');
        if (dropDown !== null) {
            dropDown.querySelector('input').value = target.dataset.value;
            dropDown.querySelector('.img-drop-down__current-value').innerHTML = target.innerHTML;
        }
        if (dropDown.id !== null) {
            let event = new CustomEvent('select:' + dropDown.id, {
                detail: {
                    target: target,
                    dropDown: dropDown,
                    originalEvent: e,
                    value: target.dataset.value,
                }
            });
            document.dispatchEvent(event);
        }
    });

    document.addEventListener('click', function (e) {
        if (e.target.closest('.drop-down') === null) {
            document.querySelectorAll('.img-drop-down').forEach(el => el.classList.remove('shown'));
        }
    });

})();
