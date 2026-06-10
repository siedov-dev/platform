<div class="ntf__list">
    <div class="notifications__no-records">
        <svg viewBox="0 0 50 50">
            <g fill="none" stroke="#fff" stroke-width="1px">
                <path d="m0.5 30 9.5-9.5h30l9.5 9.5v15c0 2.76-1.74 4.5-4.5 4.5h-40c-2.76 0-4.41-1.74-4.5-4.5z" stroke-linecap="round" stroke-linejoin="round"/>
                <g>
                    <path d="m0 30h15v5a5 5 45 0 0 5 5h10a5 5 135 0 0 5-5v-5h15"/>
                    <path d="m25 14v-9"/>
                    <path d="m33 14 4-7"/>
                    <path d="m18 14-5-7"/>
                </g>
            </g>
        </svg>
    </div>
</div>


<script>
    (() => {
        class Notification
        {
            constructor(notify_object, manager) {
                this.element  = null;
                this.id       = notify_object.id;
                this.title    = notify_object.title;
                this.text     = notify_object.message;
                this.status   = notify_object.status;
                this.linkName = notify_object.link_name;
                this.linkUrl  = notify_object.link_url;
                this.manager  = manager;
            }

            createElement() {
                let new_el = document.createElement("div");
                new_el.classList.add('ntf__item');
                if (this.isNew()) new_el.classList.add('unread');

                new_el.innerHTML = `
                    <div class="ntf__item-icon-wrap">
                        <img src="/img/ui/icons/notifications/cup.svg" class="ntf__item-icon">
                    </div>
                    <div class="ntf__item-content">
                        <div class="ntf__item-title text-green">${this.title}</div>
                        <div class="ntf__item-desc">
                            ${this.text}
                        </div>
                        ${this.linkUrl ? '<a href="' + this.linkUrl + '" class="ntf__item-link">' + this.linkName + '</a>' : ''}
                    </div>
                    <div class="ntf__del-button">&times;</div>`;
                this.element = new_el;

                let closeEl = new_el.querySelector('.ntf__del-button');
                closeEl.addEventListener('click', (e) => this.onClose(e));
                this.element.addEventListener('click', (e) => this.onClick(e));
                return this.element;
            }

            onClose(event) {
                event.preventDefault();
                event.stopPropagation();
                this.setStatus(Notification.STATUS_DELETED);
                this.manager.deleteNotification(this);
            }

            onClick(event) {
                this.setStatus(Notification.STATUS_SHOWN);
                this.manager.refreshElements();
            }

            setStatus(status) {
                this.status = status;
                let data = JSON.stringify({ n_id: this.id, status: this.status});
                fetch('/setNotificationStatus',{ method: "POST", cache: "no-cache", body: data});
            }

            isNew() {
                return (this.status == Notification.STATUS_NEW || this.status == Notification.STATUS_SENT);
            }
        }

        Notification.STATUS_NEW = 0;
        Notification.STATUS_SENT = 1;
        Notification.STATUS_SHOWN = 2;
        Notification.STATUS_DELETED = 3;

        class NotificationsManager{

            notifications;
            hasNew = false;
            rootElement;
            notificationBell = 'notifications__icon';
            clearAll = 'ntf__del-btn';

            constructor(el) {
                this.notifications = [];
                this.rootElement = el;
                setTimeout(() => this.load(), 500);
                setInterval(() => this.update(), 3000);

                // for(let el of document.getElementsByClassName(this.clearAll)){
                //     el.addEventListener('click', (e) => this.onClearAll(e));
                // }
                document.addEventListener('click:ntf__del-btn', (e) => {
                    this.onClearAll(e);
                })
            }

            setNotificationBellClass(className) {
                this.notificationBell = className;
            }

            setClearAllClass(className) {
                this.clearAll = className;
            }

            load() {
                fetch('/getNotifications',{ method: "GET", cache: "no-cache"})
                    .then( res => res.json())
                    .then( data => {
                        if(data.length > 0){
                            for(let obj of data){
                                let notif = new Notification(obj, this);
                                this.notifications.push(notif);
                            }
                        }
                        this.refreshElements();
                    });
            }

            update() {
                fetch('/getNewNotifications',{ method: "GET", cache: "no-cache"})
                    .then( res => res.json())
                    .then( data => {
                        if(data.length > 0){
                            for(let obj of data){
                                let notification = new Notification(obj, this);
                                this.notifications.push(notification);
                                this.hasNew = true;
                                this.addNewElement(notification.createElement());
                                this.activateBell();
                                this.showClearAll();
                            }
                        }
                    });
            }

            activateBell() {
                for(let el of document.getElementsByClassName(this.notificationBell)) {
                    if(!el.classList.contains('active')) el.classList.add('active');
                }
            }

            deactivateBell() {
                for(let el of document.getElementsByClassName(this.notificationBell)) {
                    if(el.classList.contains('active')) el.classList.remove('active');
                }
            }

            showClearAll() {
                for(let el of document.getElementsByClassName(this.clearAll)) {
                    el.style.display = null;
                }
            }

            hideClearAll() {
                for(let el of document.getElementsByClassName(this.clearAll)) {
                    el.style.display = "none";
                }
            }

            hasUnread() {
                return this.notifications.some( el => el.isNew() );
            }

            refreshElements() {
                //clear old elements
                this.clearElements();

                this.deactivateBell();

                //sort, unread message first
                this.notifications.sort( (a , b) => !a.isNew() && b.isNew() );

                //add elements
                this.notifications.forEach( el => this.rootElement.appendChild(el.createElement()));

                if(this.hasUnread()) this.activateBell();

                let indicator = document.getElementById('ntf__indicator');

                if(this.notifications.length > 0 ) {
                    this.showClearAll();
                    if (indicator !== null) indicator.style.display = 'flex';
                } else {
                    this.hideClearAll();
                    this.rootElement.innerHTML = `
                    <div class="notifications__no-records">
                        <svg viewBox="0 0 50 50">
                            <g fill="none" stroke="#fff" stroke-width="1px">
                                <path d="m0.5 30 9.5-9.5h30l9.5 9.5v15c0 2.76-1.74 4.5-4.5 4.5h-40c-2.76 0-4.41-1.74-4.5-4.5z" stroke-linecap="round" stroke-linejoin="round"/>
                                <g>
                                    <path d="m0 30h15v5a5 5 45 0 0 5 5h10a5 5 135 0 0 5-5v-5h15"/>
                                    <path d="m25 14v-9"/>
                                    <path d="m33 14 4-7"/>
                                    <path d="m18 14-5-7"/>
                                </g>
                            </g>
                        </svg>
                    </div>
                    `;
                    if (indicator !== null) indicator.style.display = 'none';
                }
            }

            addNewElement(el) {
                this.rootElement.insertBefore(el, this.rootElement.firstElementChild);
            }

            deleteNotification(notification) {
                this.rootElement.removeChild(notification.element);
                this.notifications.splice(this.notifications.indexOf(notification), 1);
                if(this.notifications.length === 0){
                    this.hideClearAll();
                }
                if(!this.hasUnread()){
                    this.deactivateBell();
                }

                if (this.notifications.length === 0) {
                    this.rootElement.innerHTML = `
                    <div class="notifications__no-records">
                        <svg viewBox="0 0 50 50">
                            <g fill="none" stroke="#fff" stroke-width="1px">
                                <path d="m0.5 30 9.5-9.5h30l9.5 9.5v15c0 2.76-1.74 4.5-4.5 4.5h-40c-2.76 0-4.41-1.74-4.5-4.5z" stroke-linecap="round" stroke-linejoin="round"/>
                                <g>
                                    <path d="m0 30h15v5a5 5 45 0 0 5 5h10a5 5 135 0 0 5-5v-5h15"/>
                                    <path d="m25 14v-9"/>
                                    <path d="m33 14 4-7"/>
                                    <path d="m18 14-5-7"/>
                                </g>
                            </g>
                        </svg>
                    </div>
                    `;
                }
            }

            clearElements() {
                //clear all child nodes
                while (this.rootElement.lastElementChild) {
                    this.rootElement.removeChild(this.rootElement.lastElementChild);
                }
            }

            onClearAll(event) {
                event.preventDefault();
                event.stopPropagation();
                for(let n of this.notifications) n.setStatus(Notification.STATUS_DELETED);
                this.notifications = [];
                this.refreshElements();
                let closeBtn = document.querySelector('.ntf__dd-close-btn');
                if (closeBtn !== null) closeBtn.click();
            }
        }

        let notificationsManager = new NotificationsManager(document.querySelector('.ntf__list'));
    })();
</script>
