(() => {
    window.preloaderProgress = 0;

    // Функция для обновления ширины прелоадера в зависимости от процента загрузки
    window.updateLoaderWidth = (percentage) => {
        const preloaderOverlay = document.querySelector(".preloader__overlay");
        preloaderOverlay.style.width = (100 - percentage).toString() + "%";
    }

    window.showPreloader = (progress = 0, autoprogress = 3, autohide = true) => {
        window.preloaderProgress = progress;
        updateLoaderWidth(progress);

        const preloaderOverlay = document.querySelector(".preloader__overlay");
        const preloader = document.querySelector(".preloader");
        const element = document.querySelector(".preloader .element");


        if (preloader !== null) {
            preloader.classList.remove('remove');
            // window.addEventListener('load', function (e)  {
            //     fakeProgress = 100;
            //     updateLoaderWidth(fakeProgress);
            // });

            if (autoprogress > 0) {
                let progressInterval = setInterval(function () {
                    window.preloaderProgress += autoprogress; // Замените на ваш реальный прогресс
                    updateLoaderWidth(preloaderProgress);

                    if (window.preloaderProgress >= 100) {
                        clearInterval(progressInterval);

                        if (autohide) {
                            setTimeout(function () {
                                completePreloader();
                            }, 200); // Измените на 3000, если нужно 3 секунды
                        }
                    }
                }, 50); // Изменено на 30 миллисекунд для более плавной анимации изменения ширины
            }


            // Событие загрузки страницы для смены цвета элемента
            // document.addEventListener("DOMContentLoaded", function() {
            //     // Задержка 1 секунда перед сменой цвета элемента
            //     setTimeout(function() {
            //         changeElementColor();
            //     }, 1000);
            //
            //     // Восстанавливаем цвет элемента из localStorage
            //     var savedColor = localStorage.getItem('elementColor');
            //     if (savedColor) {
            //         element.setAttribute('fill', savedColor);
            //     }
            // });

        }
    };

    // Функция для смены цвета элемента
    function changeSvgElementColor(el) {
        const colors = [
            "#ff7900",
            "#ff0000",
            "#ffe000",
            "#00e83c",
            "#00e1ff",
            "#0000ff",
            "#ff00ff",
            "#6183ff",
            "#8cc63f",
            "#d4145a"
        ];
        let randomColor = colors[Math.floor(Math.random() * colors.length)];
        el.setAttribute('fill', randomColor);

        // Сохраняем текущий цвет в localStorage
        localStorage.setItem('elementColor', randomColor);
    }

    window.completePreloader = () => {
        setTimeout(function () {
            document.querySelector(".preloader").classList.add('remove');
        }, 200); // Измените на 3000, если нужно 3 секунды
        window.preloaderProgress = 100;
        updateLoaderWidth(window.preloaderProgress);
    };

    // при загрузке страницы 100%
    // window.addEventListener('load', function (ev) {
    //     setTimeout(completePreloader, 0);
    // })
    // document.addEventListener("DOMContentLoaded", function () {
    //     setTimeout(completePreloader, 0);
    // });

})()
