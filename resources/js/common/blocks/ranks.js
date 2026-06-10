(() => {
    let modalContent = null;

    document.addEventListener('click:rank', e => {
        let t = e.detail.target;

        if (modalContent === null) {
            modalContent = document.getElementById('rank-info-modal');
        }
        modalContent.style.display = 'flex';

        modalContent.querySelector('.rank-preview').innerHTML = t.outerHTML;
        let modal = Modal.show(modalContent);
        modal.querySelector('.close-button').style.backgroundColor = 'transparent';
    });
})();
