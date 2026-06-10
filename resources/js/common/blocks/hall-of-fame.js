document.addEventListener('click:hof__button', async e => {
     let btn = e.detail.target;
     let date = btn.dataset.date;

     let wrap = document.getElementById('hof__table-wrap');

     btn.parentNode.querySelector('.hof__button_active').classList.remove('hof__button_active');
     btn.classList.add('hof__button_active');

     Spinner.show(wrap);

     try {
         let res = await network.get('/hall-of-fame?date=' + date + '&table=1');
         let html = await res.text();
         setInnerHtml(wrap, html);
     } catch (errorMsg) {
         console.error(errorMsg);
     }

     Spinner.hide(wrap);
});
