document.addEventListener('userEvent', (e) => {
    document.dispatchEvent(new CustomEvent(e.detail.name, {
        detail: e.detail,
    }));
});

document.addEventListener('chest:open-common', async function (e) {
    let res = await network.get('/treasures/tab?key_types=common');
    let html = await res.text();
    setInnerHtml('#treasures-common-tab > div', html);
});
document.addEventListener('chest:open-legendary', async function (e) {
    let res = await network.get('/treasures/tab?key_types=legendary');
    let html = await res.text();
    setInnerHtml('#treasures-legendary-tab > div', html);
});

document.addEventListener('inventory:item-changed', async function (e) {
    const inventoryPage = document.getElementById('user-inventory');
    if (inventoryPage === null) return;
    let res = await network.get('/slc/items');
    let html = await res.text();
    setInnerHtml('#layout-content', html);
});

document.addEventListener('update_menu', async function (e) {
    document.dispatchEvent(new CustomEvent('updateSlcMenu'));
});
