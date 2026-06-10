// document.getElementById('mob-menu-btn').addEventListener("click", function(){
// 	document.getElementById('mob-menu-btn').classList.toggle("active");
// });



$(document).ready(function() {

    $('.game-img-block').mouseover(function(){
        $(this).find('.game-hover-link').removeClass('hidden');
    });
    $('.game-hover-link').mouseleave(function(){
        $(this).addClass('hidden');
    });


    $('.main-slider-wrapper').removeClass('placeholder');


    $('.main-slider-wrapper').slick({
        dots: true,
        infinite: true,
        speed: 500,
        cssEase: 'linear',
        arrows: true,
        autoplay: false,
        autoplaySpeed: 4000,
        prevArrow: '<button type="button" class="slick-prev"><img src="/images/arrow-1-right.svg" alt="" /></button>',
        nextArrow: '<button type="button" class="slick-next"><img src="/images/arrow-1-right.svg" alt="" /></button>',
    });
    $('.games-slider-1').slick({
        slidesToShow: 5,
        slidesToScroll: 1,
        dots: false,
        appendArrows: $('.games-slider-arrows-1'),
        prevArrow: '<button type="button" class="slick-prev"><img src="/images/arrow-1-right.svg" alt="" /></button>',
        nextArrow: '<button type="button" class="slick-next"><img src="/images/arrow-1-right.svg" alt="" /></button>',
        autoplay: false,
        autoplaySpeed: 4000,
        draggable: false,
        infinite: false,
        responsive: [
            {
                breakpoint: 992,
                settings: {
                    slidesToShow: 3
                }
            },
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 2
                }
            },
            {
                breakpoint: 500,
                settings: {
                    slidesToShow: 2
                }
            }
        ]
    });
    $('.games-slider-3').slick({
        slidesToShow: 5,
        slidesToScroll: 1,
        dots: false,
        appendArrows: $('.games-slider-arrows-3'),
        prevArrow: '<button type="button" class="slick-prev"><img src="/images/arrow-1-right.svg" alt="" /></button>',
        nextArrow: '<button type="button" class="slick-next"><img src="/images/arrow-1-right.svg" alt="" /></button>',
        autoplay: false,
        autoplaySpeed: 4000,
        draggable: false,
        infinite: false,
        responsive: [
            {
                breakpoint: 992,
                settings: {
                    slidesToShow: 3
                }
            },
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 2
                }
            },
            {
                breakpoint: 500,
                settings: {
                    slidesToShow: 2
                }
            }
        ]
    });
    $('.games-slider-2').slick({
        slidesToShow: 5,
        slidesToScroll: 1,
        dots: false,
        appendArrows: $('.games-slider-arrows-2'),
        prevArrow: '<button type="button" class="slick-prev"><img src="/images/arrow-1-right.svg" alt="" /></button>',
        nextArrow: '<button type="button" class="slick-next"><img src="/images/arrow-1-right.svg" alt="" /></button>',
        autoplay: false,
        autoplaySpeed: 4000,
        draggable: false,
        infinite: false,
        responsive: [
            {
                breakpoint: 992,
                settings: {
                    slidesToShow: 3
                }
            },
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 2
                }
            },
            {
                breakpoint: 500,
                settings: {
                    slidesToShow: 2
                }
            }
        ]
    });
    $('.news-slider').slick({
        slidesToShow: 3,
        slidesToScroll: 1,
        dots: false,
        appendArrows: $('.news-slider-arrows'),
        prevArrow: '<button type="button" class="slick-prev"><img src="/images/arrow-1-right.svg" alt="" /></button>',
        nextArrow: '<button type="button" class="slick-next"><img src="/images/arrow-1-right.svg" alt="" /></button>',
        autoplay: false,
        autoplaySpeed: 4000,
        draggable: false,
        infinite: false,
        responsive: [
            {
                breakpoint: 992,
                settings: {
                    slidesToShow: 3
                }
            },
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 2
                }
            },
            {
                breakpoint: 500,
                settings: {
                    slidesToShow: 1
                }
            }
        ]
    });





    $('#datatable-1').DataTable();

    $('#datatable-history').DataTable({
        "language": {
            "decimal":        "",
            "emptyTable":     "",
            "info":           "Показано _START_ - _END_ из _TOTAL_",
            "infoEmpty":      "",
            "infoFiltered":   "",
            "infoPostFix":    "",
            "thousands":      ",",
            "lengthMenu":     "",
            "loadingRecords": "Loading...",
            "processing":     "Processing...",
            "search":         "Поиск:",
            "zeroRecords":    "Не найдено",
            "paginate": {
                "first":      "<<",
                "last":       ">>",
                "next":       "след.",
                "previous":   "пред."
            },
            "aria": {
                "sortAscending":  "",
                "sortDescending": ""
            }
        },
        "info": true,
    });

    for(let f = 1; f <= 12; f++) {

        $('#datatable-glory-' + f).DataTable({
            "language": {
                "decimal": "",
                "emptyTable": "",
                "info": "Показано _START_ - _END_ из _TOTAL_",
                "infoEmpty": "",
                "infoFiltered": "",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "",
                "loadingRecords": "Loading...",
                "processing": "Processing...",
                "search": "Поиск:",
                "zeroRecords": "",
                "paginate": {
                    "first": "<<",
                    "last": ">>",
                    "next": "след.",
                    "previous": "пред."
                },
                "aria": {
                    "sortAscending": "",
                    "sortDescending": ""
                }
            },
            "info": true,
        });
    }


    $('.status-link-1').click(function() {
        $('.status-wrapper').hide();
        $('.status-1-wrapper').show();
        $('.status-link').removeClass('active');
        $(this).addClass('active');
    });
    $('.status-link-2').click(function() {
        $('.status-wrapper').hide();
        $('.status-2-wrapper').show();
        $('.status-link').removeClass('active');
        $(this).addClass('active');
    });
    $('.status-link-3').click(function() {
        $('.status-wrapper').hide();
        $('.status-3-wrapper').show();
        $('.status-link').removeClass('active');
        $(this).addClass('active');
    });
    $('.status-link-4').click(function() {
        $('.status-wrapper').hide();
        $('.status-4-wrapper').show();
        $('.status-link').removeClass('active');
        $(this).addClass('active');
    });




    $('.notification-bell').click(function(e){
        e.preventDefault();
        $('.notifications-top').removeClass('hidden');
        $('.dark-full-bg').removeClass('hidden');
        $(this).addClass('clicked');
    });

    $('.header-left .navbar-toggler').click(function(e){
        e.preventDefault();
        $('.dark-full-bg').removeClass('hidden');
        $('.left-menu').removeClass('hidden');
    });
    $('.auth-modal-link').click(function(e){
        e.preventDefault();
        e.stopPropagation();
        hideModals();
        $('.auth-modal').removeClass('hidden');
    });
    $('.register-modal-link').click(function(e){
        e.preventDefault();
        e.stopPropagation();
        hideModals();
        $('.register-modal').removeClass('hidden');
    });
    $('.wallets-modal-link').click(function(e){
        e.preventDefault();
        e.stopPropagation();
        $('.wallets-modal').removeClass('hidden');
    });
    $('.forgot-modal-link').click(function(e){
        e.preventDefault();
        e.stopPropagation();
        hideModals();
        $('.forgot-modal').removeClass('hidden');
    });
    $('.deposit-modal-link').click(function(e){
        e.preventDefault();
        $('.deposit-modal').removeClass('hidden');
    });

    $('.phone-verify-link').click(function(e){
        e.preventDefault();
        $('.phone-verify-modal').removeClass('hidden');
    });

    $('.email-verify-link').click(function(e){
        e.preventDefault();
        $('.email-verify-modal').removeClass('hidden');
    });
    $('.close-modal').click(function(){
        $('.modal').addClass('hidden');
    });

    $(window).click(function() {
        hideModals();
    });

    $('.notifications-top, .notification-bell, .left-menu, .header-left .navbar-toggler, .modal-block, .modal-link').click(function(event){
        event.stopPropagation();
    });

    $('.close-left-menu').click(function(e){
        e.preventDefault();
        $('.dark-full-bg').addClass('hidden');
        $('.left-menu').addClass('hidden');
    });




    $('.custom-input-1 .input-img-2.input-eye').click(function(){
        if (!$(this).hasClass('visible')) {
            $(this).parent().find('input').attr('type', 'text');
            $(this).addClass('visible');
        } else {
            $(this).parent().find('input').attr('type', 'password');
            $(this).removeClass('visible');
        }
    });


    $('.toggle-heading-1').click(function(){
        $('.toggle-heading').removeClass('active');
        $(this).addClass('active');
        $('.modal-heading').removeClass('display-none');
        $('.modal-heading-2').addClass('display-none');
        $('.content-modal-1').removeClass('display-none');
        $('.content-modal-2').addClass('display-none');
    });

    $('.toggle-heading-2').click(function(){
        $('.toggle-heading').removeClass('active');
        $(this).addClass('active');
        $('.modal-heading').removeClass('display-none');
        $('.modal-heading-1').addClass('display-none');
        $('.content-modal-2').removeClass('display-none');
        $('.content-modal-1').addClass('display-none');
    });

    $('.modal').click(function () {
        this.classList.add('hidden');
    });

    $('.redirect-link').click( function () {
        window.location.href = $(this).attr('data-href');
    });

    $('.toggle-link-1').click(function(e){
        e.preventDefault();
        $(this).addClass('active');
        $('.toggle-link-2').removeClass('active');
        $('.content-1').removeClass('display-none');
        $('.content-2').addClass('display-none');
    });
    $('.toggle-link-2').click(function(e){
        e.preventDefault();
        $(this).addClass('active');
        $('.toggle-link-1').removeClass('active');
        $('.content-2').removeClass('display-none');
        $('.content-1').addClass('display-none');
    });

    if ($('body').hasClass('white-theme')) {
        $('.theme-change-wrapper input').prop("checked", true);
    }
    $('.theme-change-wrapper input').change(function() {
        $('body').toggleClass('white-theme');
        if(document.body.classList.contains('white-theme')){
            setCookie('white-theme', 'true', {'max-age':2147483647})
        } else {
            deleteCookie('white-theme');
        }
    });

    for(let el of document.querySelectorAll('.choose-balance')) {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            let modal = document.querySelector('.balance-mode-modal');
            let balance = document.querySelector('.user-wallet-amount').innerHTML.split(" ")[0];
             modal.querySelector('.main-balance .main-value').innerHTML = balance.split(".")[0];
             modal.querySelector('.main-balance .cents-value').innerHTML = "." + balance.split(".")[1];
            for(let el of modal.querySelectorAll('.game-link-url')) el.setAttribute('action', this.getAttribute('href'));
            if(this.getAttribute('data-active-bonus') === "true"){
                let balance_el = modal.querySelector('.bonus-balance-plate');
                if(balance_el) balance_el.style.display="flex";
            } else {
                let balance_el = modal.querySelector('.bonus-balance-plate');
                if(balance_el) balance_el.style.display="none";
            }
            if(this.getAttribute('data-active-tournament') === "true"){
                let tnt_el = modal.querySelector('.tournament-balance-plate');
                if(tnt_el) tnt_el.style.display="flex";
            } else {
                let tnt_el = modal.querySelector('.tournament-balance-plate');
                if(tnt_el) tnt_el.style.display="none";
            }
            modal.classList.remove('hidden');
        })
    }

    document.linkClicked = false;

    for(let el of document.querySelectorAll('.one-click-link')){
        el.addEventListener('click', function (e) {
            if(document.linkClicked) e.preventDefault();
            document.linkClicked = true;
        })
    }

    document.formsClicked = false;

    for(let el of document.querySelectorAll('.one-submit-forms')){
        el.addEventListener('submit', function (event) {
            if(document.formsClicked) event.preventDefault();
            document.formsClicked = true;
            return true;
        })
    }

    $('.modal-window').click(function (e) {
        e.stopPropagation();
    });
});

function hideModals(){
    if (!$('.notification-bell').hasClass('hidden')) {
        $('.notifications-top').addClass('hidden');
        $('.dark-full-bg').addClass('hidden');
        $('.notification-bell').removeClass('clicked');
    }
    if (!($('.left-menu').hasClass('hidden'))) {
        $('.dark-full-bg').addClass('hidden');
        $('.left-menu').addClass('hidden');
    }
    if (!($('.auth-modal').hasClass('hidden'))) {
        $('.auth-modal').addClass('hidden');
    }
    if (!($('.register-modal').hasClass('hidden'))) {
        $('.register-modal').addClass('hidden');
    }
    if (!($('.deposit-modal').hasClass('hidden'))) {
        $('.deposit-modal').addClass('hidden');
    }
    if (!($('.modal').hasClass('hidden'))) {
        $('.modal').addClass('hidden');
    }
    if ($('.lang-select').hasClass('active')) {
        $('.lang-select').removeClass('active');
    }

}

function modalInfo(header, text){
    document.getElementById('info-modal-header').innerText = header;
    document.getElementById('info-modal-text').innerText = text;
    document.getElementById('info-form').classList.remove('hidden');
}


function toggleFullscreen(elem)
{
    if (elem.requestFullScreen) {
        elem.requestFullScreen();
    } else if (elem.mozRequestFullScreen) {
        elem.mozRequestFullScreen();
    } else if (elem.webkitRequestFullScreen) {
        elem.webkitRequestFullScreen();
    } else {
        //iphone here )
        elem.style.position = "absolute";
        elem.style.top = "0";
        elem.style.left = "0";
        elem.style.width = "100%";
        elem.style.height = "100vh";
        elem.style.zIndex = "10";
        elem.style.borderRadius = "0";
        document.querySelector('.games-block').style.display = "none";
        document.querySelector('.footer').style.display = "none";
    }

    Tawk_API.hideWidget();
}




if (!$('.lang-select').hasClass('active')) {
    $('.lang-select').click(function(e){
        e.stopPropagation();
        $(this).addClass('active');
    });
}
$('.lang-select-inner label').click(function(e){
    $('.lang-select-inner label').removeClass('current-lang');
    $(this).addClass('current-lang');
    $('.lang-select').removeClass('active');
});
