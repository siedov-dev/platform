window.shortMessage = {
    show: function (message, type = "error") {
        let wrapper = document.getElementById('short-message_wrapper');
        if (wrapper !== null) {

            if (wrapper.firstElementChild !== null) {
                wrapper.firstElementChild.remove();
            }

            let messageEl = document.createElement("DIV")
            messageEl.classList.add('message');
            messageEl.classList.add(type);
            messageEl.innerText = message;
            wrapper.appendChild(messageEl);
            setTimeout(() => {
                messageEl.remove();
            }, 2000)
        }
    }
};
