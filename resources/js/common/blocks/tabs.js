document.addEventListener('click:tabs__tab', async e => {
    let tab = e.detail.target;
    let wrap = tab.parentNode;

    let activeTab = wrap.querySelector('.tabs__tab_active');
    if (activeTab !== null) activeTab.classList.remove('tabs__tab_active');

    tab.classList.add('tabs__tab_active');

    let contents = [];
    let prevContent = null;
    let newContent = null;
    let allTabs = wrap.getElementsByClassName('tabs__tab');
    for (let i = 0; i < allTabs.length; i++) {
        let c = document.querySelector(allTabs[i].getAttribute('data-target'));
        if (c !== null) {
            if (allTabs[i] === tab) {
                newContent = c;
                continue;
            }
            if (c.style.display !== 'none') {
                prevContent = c;
            }
        }
    }

    if (prevContent !== null) {
        prevContent.style.display = 'none';
    }

    if (newContent !== null) {
        newContent.style.display = null;
    }
});
