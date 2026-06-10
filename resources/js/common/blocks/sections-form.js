(() => {

    document.addEventListener('click:section-next-btn',  async function (e) {
        e.detail.originalEvent.preventDefault();
        let btn = e.detail.target;
        let wrapper = btn.closest('.sections-wrapper');
        let currSection = btn.closest('.sections-wrapper').querySelector('.section-wrapper.active');

        if (! await canGoNext(wrapper, currSection)) return;

        let prevBtn = btn.closest('.sections-form').querySelector('.section-prev-btn');
        prevBtn.classList.add('active');

        //show next section
        currSection.classList.remove('active');
        currSection = currSection.nextElementSibling;
        currSection.classList.add('active');

        //change control buttons
        if (isLastSection(currSection)) {
            btn.classList.remove('active');
            let doneBtn = btn.closest('.sections-form').querySelector('.section-done-btn');
            doneBtn.classList.add('active');
        }

        setActiveNames(wrapper, currSection);
    })

    document.addEventListener('click:section-prev-btn', function (e) {
        e.detail.originalEvent.preventDefault();
        let btn = e.detail.target;
        let doneBtn = btn.closest('.sections-form').querySelector('.section-done-btn');
        let nextBtn = btn.closest('.sections-form').querySelector('.section-next-btn');
        let wrapper = btn.closest('.sections-form').querySelector('.sections-wrapper');

        //show prev section
        let currSection = btn.closest('.sections-wrapper').querySelector('.section-wrapper.active');
        currSection.classList.remove('active');
        currSection = currSection.previousElementSibling;
        currSection.classList.add('active');

        //change control buttons
        if (isFirstSection(currSection)) {
            btn.classList.remove('active');
        }

        doneBtn.classList.remove('active');
        nextBtn.classList.add('active');

        setActiveNames(wrapper, currSection);
    })

    document.addEventListener('click:section-done-btn',  async function (e) {
        let btn = e.detail.target;
        let wrapper = btn.closest('.sections-wrapper');
        let currSection = btn.closest('.sections-wrapper').querySelector('.section-wrapper.active');
        if (! await canGoNext(wrapper, currSection)) {
            e.preventDefault();
            e.detail.originalEvent.preventDefault();
        }
    })

    function isLastSection(section)
    {
        return !(section.nextElementSibling && section.nextElementSibling.classList.contains('section-wrapper'));
    }

    function isFirstSection(section)
    {
        return !(section.previousElementSibling && section.previousElementSibling.classList.contains('section-wrapper'));
    }

    function setActiveNames(wrapper, section)
    {
        //clear all names
        for (let sName of wrapper.querySelectorAll('.sections-names *')) {
            sName.classList.remove('active');
            sName.classList.remove('done');
        }

        //set all previous done and current active
        if (section.dataset.sectionName !== undefined) {
            for(let sName of wrapper.querySelectorAll('.sections-names *')) {
                if (sName.dataset.sectionName === section.dataset.sectionName) {
                    sName.classList.add('active');
                    break;
                }
                sName.classList.add('done');
            }
        }
    }

    async function canGoNext(wrapper, section)
    {
        let form = wrapper.querySelector('.sections-form');
        Spinner.show(section);
        //clear errors
        for(let el of section.querySelectorAll('.input__error')) el.style.display = 'none';
        for(let el of section.querySelectorAll('.has-error')) el.classList.remove('has-error');
        //send data to validate
        let data = new FormData(form);
        //mark only validate
        data.set('onlyValidate', 'true');
        let result = await network.post(form.getAttribute('action'), data);
        await Spinner.hide(section);
        //if validation error
        if (result.status === 422) {
            let resp = await result.json();
            if (resp.errors !== undefined) {
                //mark error elements
                for(let error in resp.errors) {
                    if (error === 'captcha') {
                        let captchaEl = form.querySelector('.reg-form__captcha');
                        let catchaSection = captchaEl.closest('.section-wrapper');
                        let currSection = form.querySelector('.section-wrapper.active');
                        currSection.classList.remove('active');
                        catchaSection.classList.add('active');
                    }
                    let el = section.querySelector('.reg-form__' + error);
                    if (el) el.classList.add('has-error');
                    el = section.querySelector('.reg-form__' + error + ' .input__error');
                    if (el) {
                        el.innerHTML = resp.errors[error][0];
                        el.style.display = 'block';
                    }
                }
                //if there are no errors in the section can go next
                return section.querySelector('.has-error') === null;
            }
        }

        return true;
    }

})();
