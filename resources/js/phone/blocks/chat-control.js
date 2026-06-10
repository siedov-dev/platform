function chatMenuClick(elNom)
{
    let trigger = document.getElementById('supportTrigger');

    trigger.click()

    let c = 0;
    let waitId = setInterval(() => {
        c++;
        if (document.querySelector(".wrapper.mobile-trigger-menu-wrapper") !== null || c > 10) {
            clearInterval(waitId);
            window.supportTriggerClick = true;
            document.querySelector(".wrapper.mobile-trigger-menu-wrapper li:nth-child(" + elNom + ") a").click();
        }
    }, 200);
}
