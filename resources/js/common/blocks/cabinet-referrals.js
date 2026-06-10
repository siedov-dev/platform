document.addEventListener('click:ref-history__pagination', async function (e) {
    e.detail.originalEvent.preventDefault();
    const page = e.detail.originalEvent.target;
    if (page.tagName !== 'A') return;
    const tableWrap = document.getElementById("cabinet__ref-history");
    try {
        Spinner.show(tableWrap);
        const url = page.getAttribute('href');
        resp = await network.get(url);
        const html = await resp.text();
        setInnerHtml(tableWrap, html);
    } catch (e) {
        console.log(e.toString())
    }
    Spinner.hide(tableWrap);
});

document.addEventListener('click:ref-users__pagination', async function (e) {
    e.detail.originalEvent.preventDefault();
    const page = e.detail.originalEvent.target;
    if (page.tagName !== 'A') return;
    const tableWrap = document.getElementById("cabinet__ref-list");
    try {
        Spinner.show(tableWrap);
        const url = page.getAttribute('href');
        resp = await network.get(url);
        const html = await resp.text();
        setInnerHtml(tableWrap, html);
    } catch (e) {
        console.log(e.toString())
    }
    Spinner.hide(tableWrap);
});
