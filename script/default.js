(function() {
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