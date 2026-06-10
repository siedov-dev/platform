function chatMenuClick(elNom)
{
    let trigger = document.getElementById('supportTrigger');
    trigger.click();
    fixTalkChatPosition();
    return;
    // when chat menu has more than one element
    let element = document.getElementById('supportTrigger');

    let event = new MouseEvent('mouseover', {
        'view': window,
        'bubbles': true,
        'cancelable': true
    });

    element.dispatchEvent(event);

    let c = 0;
    let waitId = setInterval(() => {
        c++;
        if (document.querySelector("#supportTrigger .trigger-menu") !== null || c > 10) {
            clearInterval(waitId);
            document.querySelector("#supportTrigger .trigger-menu li:nth-child(" + elNom + ") a").click();
            setTimeout(() => {
                let eventLeave = new MouseEvent('mouseout', {
                    'view': window,
                    'bubbles': true,
                    'cancelable': true
                });

                element.dispatchEvent(eventLeave);
            }, 100)
            if (elNom === 1) fixTalkChatPosition();
        }
    }, 200);
}

function fixTalkChatPosition()
{
    let c = 0;
    let waitChatWindow = setInterval(() => {
        c++;
        if (document.querySelector('.online-chat-root .draggable') !== null || c > 20) {
            clearInterval(waitChatWindow);
            let chatWindow = document.querySelector('.online-chat-root .draggable');
            console.log(chatWindow);
            let left = window.innerWidth - 380 + "px !important; ";
            let top = window.innerHeight - 520 + "px !important; ";
            chatWindow.setAttribute('style', 'left: '+ left + 'top: ' + top + "width: 360px !important; height: 500px !important");
        }

    }, 400);

}
