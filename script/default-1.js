(function() {
    var menu_button = document.querySelector('#menu__icon');
    var menu = document.querySelector("#menu__box");
    menu_button.addEventListener('click', () => {
        menu.classList.toggle('open');
    });

    var sub_menus = document.querySelectorAll('div.menu__link');
    sub_menus.forEach((menu) => {
        menu.addEventListener('click', (e) => {
            menu.classList.toggle('open');
        });
    });
    
    function resizeBanners() {
        var banners = document.querySelectorAll('.banner');
        banners.forEach((banner) => {
            if (window.innerWidth > 768) {
                var ratio = banner.getAttribute('data-ratio');
            }
            else {
                var ratio = banner.getAttribute('data-ratio-m');
            }
            var width = banner.clientWidth;
            var height = ((width / ratio)) + 'px';
            banner.style.backgroundSize = width + 'px ' + height;
            banner.style.height = height;
        });
    }

    resizeBanners();

    window.addEventListener('resize', () => {
        resizeBanners();
    });

    var see_more_box = document.querySelector('#see-more-box');
    var see_more_button = document.querySelector('#see-more-button');
    var see_more_successess = document.querySelector('#see-more-successes');

    if (see_more_button !== null) {
        see_more_button.addEventListener('click', (e) => {
            e.preventDefault();
            see_more_box.style.display = 'none';
            see_more_successess.style.display = 'block';
        });
    }
    if (typeof gsap !== 'undefined') {
        gsap.registerPlugin(ScrollTrigger);

        var elements = document.querySelectorAll('.fade-in');
        elements.forEach((el) => {
            gsap.to(el, {
                opacity: 1,
                scrollTrigger: {
                    trigger: el,
                    start: 'top 90%',
                    end: 'center center',
                    scrub: 2
                }
            });
        });
    }

    var contactForm = document.getElementById('contact-form');
    if (contactForm !== null) {
        contactForm.addEventListener('submit', (event) => {
            event.preventDefault();
            grecaptcha.ready(function() {
                grecaptcha.execute('6LdKX-IpAAAAAFSXsbD8KRl6dGmeBFqC_i7K0njj', {action: 'submit'}).then((token) => {
                    var gtoken = document.getElementById('gtoken');    
                    gtoken.value = token;
                    contactForm.submit();
                });
            });
        });
    }
})();