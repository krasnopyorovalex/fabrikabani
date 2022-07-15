"use strict";
(function() {
    var userAgent = navigator.userAgent.toLowerCase(),
        $document = $(document),
        $window = $(window),
        $html = $("html"),
        $body = $("body"),
        isDesktop = $html.hasClass("desktop"),
        isIE = userAgent.indexOf("msie") !== -1 ? parseInt(userAgent.split("msie")[1], 10) : userAgent.indexOf("trident") !== -1 ? 11 : userAgent.indexOf("edge") !== -1 ? 12 : false,
        isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent),
        windowReady = false,
        isNoviBuilder = false,
        livedemo = false,
        plugins = {
            bootstrapTooltip: $("[data-toggle='tooltip']"),
            bootstrapModalDialog: $('.modal'),
            bootstrapTabs: $(".tabs-custom"),
            customToggle: $("[data-custom-toggle]"),
            counter: $(".counter"),
            circleProgress: $(".progress-bar-circle"),
            countDown: $('[data-circle-countdown]'),
            checkbox: $("input[type='checkbox']"),
            lightGallery: $("[data-lightgallery='group']"),
            lightGalleryItem: $("[data-lightgallery='item']"),
            lightDynamicGalleryItem: $("[data-lightgallery='dynamic']"),
            materialParallax: $(".parallax-container"),
            owl: $(".owl-carousel"),
            progressLinear: $(".progress-linear"),
            //preloader: $(".preloader"),
            rdNavbar: $(".rd-navbar"),
            radio: $("input[type='radio']"),
            swiper: document.querySelectorAll('.swiper-container'),
            statefulButton: $('.btn-stateful'),
            viewAnimate: $('.view-animate'),
            wow: $(".wow"),
            rdRange: $('.rd-range'),
            stepper: $("input[type='number']"),
            radioPanel: $('.radio-panel .radio-inline')
        };

    function isScrolledIntoView(elem) {
        if (isNoviBuilder) return true;
        return elem.offset().top + elem.outerHeight() >= $window.scrollTop() && elem.offset().top <= $window.scrollTop() + $window.height();
    }

    $window.on('load', function() {
        // if (plugins.preloader.length && !isNoviBuilder) {
        //     pageTransition({
        //         target: document.querySelector('.page'),
        //         delay: 0,
        //         duration: 150,
        //         classIn: 'fadeIn',
        //         classOut: 'fadeOut',
        //         classActive: 'animated',
        //         conditions: function(event, link) {
        //             return !/(\#|callto:|tel:|mailto:|:\/\/)/.test(link) && !event.currentTarget.hasAttribute('data-lightgallery');
        //         },
        //         onTransitionStart: function(options) {
        //             setTimeout(function() {
        //                 plugins.preloader.removeClass('loaded');
        //             }, options.duration * .75);
        //         },
        //         onReady: function() {
        //             plugins.preloader.addClass('loaded');
        //             windowReady = true;
        //         }
        //     });
        // }
        if (plugins.counter.length) {
            for (var i = 0; i < plugins.counter.length; i++) {
                var
                    counter = $(plugins.counter[i]),
                    initCount = function() {
                        var counter = $(this);
                        if (!counter.hasClass("animated-first") && isScrolledIntoView(counter)) {
                            counter.countTo({
                                refreshInterval: 40,
                                speed: counter.attr("data-speed") || 1000,
                                from: 0,
                                to: parseInt(counter.text(), 10)
                            });
                            counter.addClass('animated-first');
                        }
                    };
                $.proxy(initCount, counter)();
                $window.on("scroll", $.proxy(initCount, counter));
            }
        }
        if (plugins.progressLinear.length) {
            for (var i = 0; i < plugins.progressLinear.length; i++) {
                var
                    bar = $(plugins.progressLinear[i]),
                    initProgress = function() {
                        var
                            bar = $(this),
                            end = parseInt($(this).find('.progress-value').text(), 10);
                        if (!bar.hasClass("animated-first") && isScrolledIntoView(bar)) {
                            bar.find('.progress-bar-linear').css({
                                width: end + '%'
                            });
                            bar.find('.progress-value').countTo({
                                refreshInterval: 40,
                                from: 0,
                                to: end,
                                speed: 1000
                            });
                            bar.addClass('animated-first');
                        }
                    };
                $.proxy(initProgress, bar)();
                $window.on("scroll", $.proxy(initProgress, bar));
            }
        }
        if (plugins.countDown.length) {
            svgCountDown({
                tickInterval: 100,
                counterSelector: '.countdown-counter'
            });
        }
        if (plugins.circleProgress.length) {
            for (var i = 0; i < plugins.circleProgress.length; i++) {
                var circle = $(plugins.circleProgress[i]);
                circle.circleProgress({
                    value: circle.attr('data-value'),
                    size: circle.attr('data-size') ? circle.attr('data-size') : 175,
                    fill: {
                        gradient: circle.attr('data-gradient').split(","),
                        gradientAngle: Math.PI / 4
                    },
                    startAngle: -Math.PI / 4 * 2,
                    emptyFill: circle.attr('data-empty-fill') ? circle.attr('data-empty-fill') : "rgb(245,245,245)"
                }).on('circle-animation-progress', function(event, progress, stepValue) {
                    $(this).find('span').text(String(stepValue.toFixed(2)).replace('0.', '').replace('1.', '1'));
                });
                if (isScrolledIntoView(circle)) circle.addClass('animated-first');
                $window.on('scroll', $.proxy(function() {
                    var circle = $(this);
                    if (!circle.hasClass("animated-first") && isScrolledIntoView(circle)) {
                        circle.circleProgress('redraw');
                        circle.addClass('animated-first');
                    }
                }, circle));
            }
        }
        if (plugins.materialParallax.length) {
            if (!isNoviBuilder && !isIE && !isMobile) {
                plugins.materialParallax.parallax();
            } else {
                for (var i = 0; i < plugins.materialParallax.length; i++) {
                    var $parallax = $(plugins.materialParallax[i]);
                    $parallax.addClass('parallax-disabled');
                    $parallax.css({
                        "background-image": 'url(' + $parallax.data("parallax-img") + ')'
                    });
                }
            }
        }
    });
    $(function() {
        isNoviBuilder = window.xMode;

        function initOwlCarousel(c) {
            var aliaces = ["-", "-sm-", "-md-", "-lg-", "-xl-", "-xxl-"],
                values = [0, 576, 768, 992, 1200, 1600],
                responsive = {};
            for (var j = 0; j < values.length; j++) {
                responsive[values[j]] = {};
                for (var k = j; k >= -1; k--) {
                    if (!responsive[values[j]]["items"] && c.attr("data" + aliaces[k] + "items")) {
                        responsive[values[j]]["items"] = k < 0 ? 1 : parseInt(c.attr("data" + aliaces[k] + "items"), 10);
                    }
                    if (!responsive[values[j]]["stagePadding"] && responsive[values[j]]["stagePadding"] !== 0 && c.attr("data" + aliaces[k] + "stage-padding")) {
                        responsive[values[j]]["stagePadding"] = k < 0 ? 0 : parseInt(c.attr("data" + aliaces[k] + "stage-padding"), 10);
                    }
                    if (!responsive[values[j]]["margin"] && responsive[values[j]]["margin"] !== 0 && c.attr("data" + aliaces[k] + "margin")) {
                        responsive[values[j]]["margin"] = k < 0 ? 30 : parseInt(c.attr("data" + aliaces[k] + "margin"), 10);
                    }
                }
            }
            if (c.attr('data-dots-custom')) {
                c.on("initialized.owl.carousel", function(event) {
                    var carousel = $(event.currentTarget),
                        customPag = $(carousel.attr("data-dots-custom")),
                        active = 0;
                    if (carousel.attr('data-active')) {
                        active = parseInt(carousel.attr('data-active'), 10);
                    }
                    carousel.trigger('to.owl.carousel', [active, 300, true]);
                    customPag.find("[data-owl-item='" + active + "']").addClass("active");
                    customPag.find("[data-owl-item]").on('click', function(e) {
                        e.preventDefault();
                        carousel.trigger('to.owl.carousel', [parseInt(this.getAttribute("data-owl-item"), 10), 300, true]);
                    });
                    carousel.on("translate.owl.carousel", function(event) {
                        customPag.find(".active").removeClass("active");
                        customPag.find("[data-owl-item='" + event.item.index + "']").addClass("active")
                    });
                });
            }
            c.on("initialized.owl.carousel", function() {
                initLightGalleryItem(c.find('[data-lightgallery="item"]'), 'lightGallery-in-carousel');
            });
            c.owlCarousel({
                autoplay: isNoviBuilder ? false : c.attr("data-autoplay") === "true",
                loop: isNoviBuilder ? false : c.attr("data-loop") !== "false",
                items: 1,
                center: c.attr("data-center") === "true",
                dotsContainer: c.attr("data-pagination-class") || false,
                navContainer: c.attr("data-navigation-class") || false,
                mouseDrag: isNoviBuilder ? false : c.attr("data-mouse-drag") !== "false",
                nav: c.attr("data-nav") === "true",
                dots: c.attr("data-dots") === "true",
                dotsEach: c.attr("data-dots-each") ? parseInt(c.attr("data-dots-each"), 10) : false,
                animateIn: c.attr('data-animation-in') ? c.attr('data-animation-in') : false,
                animateOut: c.attr('data-animation-out') ? c.attr('data-animation-out') : false,
                responsive: responsive,
                smartSpeed: c.attr('data-smart-speed') ? c.attr('data-smart-speed') : 250,
                navText: c.attr("data-nav-text") ? $.parseJSON(c.attr("data-nav-text")) : [],
                navClass: c.attr("data-nav-class") ? $.parseJSON(c.attr("data-nav-class")) : ['owl-prev', 'owl-next']
            });
        }

        function initBootstrapTooltip(tooltipPlacement) {
            plugins.bootstrapTooltip.tooltip('dispose');
            if (window.innerWidth < 576) {
                plugins.bootstrapTooltip.tooltip({
                    placement: 'bottom'
                });
            } else {
                plugins.bootstrapTooltip.tooltip({
                    placement: tooltipPlacement
                });
            }
        }

        function initLightGallery(itemsToInit, addClass) {
            if (!isNoviBuilder) {
                $(itemsToInit).lightGallery({
                    thumbnail: $(itemsToInit).attr("data-lg-thumbnail") !== "false",
                    selector: "[data-lightgallery='item']",
                    autoplay: $(itemsToInit).attr("data-lg-autoplay") === "true",
                    pause: parseInt($(itemsToInit).attr("data-lg-autoplay-delay")) || 5000,
                    addClass: addClass,
                    mode: $(itemsToInit).attr("data-lg-animation") || "lg-slide",
                    loop: $(itemsToInit).attr("data-lg-loop") !== "false"
                });
            }
        }

        function initDynamicLightGallery(itemsToInit, addClass) {
            if (!isNoviBuilder) {
                $(itemsToInit).on("click", function() {
                    $(itemsToInit).lightGallery({
                        thumbnail: $(itemsToInit).attr("data-lg-thumbnail") !== "false",
                        selector: "[data-lightgallery='item']",
                        autoplay: $(itemsToInit).attr("data-lg-autoplay") === "true",
                        pause: parseInt($(itemsToInit).attr("data-lg-autoplay-delay")) || 5000,
                        addClass: addClass,
                        mode: $(itemsToInit).attr("data-lg-animation") || "lg-slide",
                        loop: $(itemsToInit).attr("data-lg-loop") !== "false",
                        dynamic: true,
                        dynamicEl: JSON.parse($(itemsToInit).attr("data-lg-dynamic-elements")) || []
                    });
                });
            }
        }

        function initLightGalleryItem(itemToInit, addClass) {
            if (!isNoviBuilder) {
                $(itemToInit).lightGallery({
                    selector: "this",
                    addClass: addClass,
                    counter: false,
                    youtubePlayerParams: {
                        modestbranding: 1,
                        showinfo: 0,
                        rel: 0,
                        controls: 0
                    },
                    vimeoPlayerParams: {
                        byline: 0,
                        portrait: 0
                    }
                });
            }
        }

        function setRealPrevious(swiper) {
            var element = swiper.$wrapperEl[0].children[swiper.activeIndex];
            swiper.realPrevious = Array.prototype.indexOf.call(element.parentNode.children, element);
        }

        function initSwiper(sliderMarkup) {
            var
                autoplayAttr = sliderMarkup.getAttribute('data-autoplay') || 5000,
                slides = sliderMarkup.querySelectorAll('.swiper-slide'),
                swiper, options = {
                    loop: sliderMarkup.getAttribute('data-loop') === 'true' || false,
                    effect: sliderMarkup.getAttribute('data-effect') || 'slide',
                    direction: sliderMarkup.getAttribute('data-direction') || 'horizontal',
                    speed: sliderMarkup.getAttribute('data-speed') ? Number(sliderMarkup.getAttribute('data-speed')) : 600,
                    simulateTouch: sliderMarkup.getAttribute('data-simulate-touch') === 'true' && !isNoviBuilder || false,
                    slidesPerView: sliderMarkup.getAttribute('data-slides') || 1,
                    spaceBetween: Number(sliderMarkup.getAttribute('data-margin')) || 0
                };
            if (Number(autoplayAttr)) {
                options.autoplay = {
                    delay: Number(autoplayAttr),
                    stopOnLastSlide: false,
                    disableOnInteraction: true,
                    reverseDirection: false,
                };
            }
            if (sliderMarkup.getAttribute('data-keyboard') === 'true') {
                options.keyboard = {
                    enabled: sliderMarkup.getAttribute('data-keyboard') === 'true',
                    onlyInViewport: true
                };
            }
            if (sliderMarkup.getAttribute('data-mousewheel') === 'true') {
                options.mousewheel = {
                    sensitivity: 1
                };
            }
            if (sliderMarkup.querySelector('.swiper-button-next, .swiper-button-prev')) {
                options.navigation = {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev'
                };
            }
            if (sliderMarkup.querySelector('.swiper-pagination')) {
                options.pagination = {
                    el: '.swiper-pagination',
                    type: 'bullets',
                    clickable: true
                };
            }
            if (sliderMarkup.querySelector('.swiper-scrollbar')) {
                options.scrollbar = {
                    el: '.swiper-scrollbar',
                    hide: false
                };
            }
            for (var s = 0; s < slides.length; s++) {
                var
                    slide = slides[s],
                    url = slide.getAttribute('data-slide-bg');
                if (url) slide.style.backgroundImage = 'url(' + url + ')';
            }
            options.on = {
                init: function() {
                    setRealPrevious(this);
                    initCaptionAnimate(this);
                    this.on('slideChangeTransitionEnd', function() {
                        setRealPrevious(this);
                    });
                }
            };
            swiper = new Swiper(sliderMarkup, options);
            return swiper;
        }
        if (navigator.platform.match(/(Mac)/i)) {
            $html.addClass("mac-os");
        }
        if (isIE) {
            if (isIE === 12) $html.addClass("ie-edge");
            if (isIE === 11) $html.addClass("ie-11");
            if (isIE < 10) $html.addClass("lt-ie-10");
            if (isIE < 11) $html.addClass("ie-10");
        }
        if (plugins.bootstrapTooltip.length) {
            var tooltipPlacement = plugins.bootstrapTooltip.attr('data-placement');
            initBootstrapTooltip(tooltipPlacement);
            $window.on('resize orientationchange', function() {
                initBootstrapTooltip(tooltipPlacement);
            })
        }
        if (plugins.bootstrapModalDialog.length) {
            for (var i = 0; i < plugins.bootstrapModalDialog.length; i++) {
                var modalItem = $(plugins.bootstrapModalDialog[i]);
                modalItem.on('hidden.bs.modal', $.proxy(function() {
                    var activeModal = $(this),
                        rdVideoInside = activeModal.find('video'),
                        youTubeVideoInside = activeModal.find('iframe');
                    if (rdVideoInside.length) {
                        rdVideoInside[0].pause();
                    }
                    if (youTubeVideoInside.length) {
                        var videoUrl = youTubeVideoInside.attr('src');
                        youTubeVideoInside.attr('src', '').attr('src', videoUrl);
                    }
                }, modalItem))
            }
        }
        if (plugins.statefulButton.length) {
            $(plugins.statefulButton).on('click', function() {
                var statefulButtonLoading = $(this).button('loading');
                setTimeout(function() {
                    statefulButtonLoading.button('reset')
                }, 2000);
            })
        }
        if (plugins.bootstrapTabs.length) {
            for (var i = 0; i < plugins.bootstrapTabs.length; i++) {
                var bootstrapTabsItem = $(plugins.bootstrapTabs[i]);
                if (bootstrapTabsItem.find('.slick-slider').length) {
                    bootstrapTabsItem.find('.tabs-custom-list > li > a').on('click', $.proxy(function() {
                        var $this = $(this);
                        var setTimeOutTime = isNoviBuilder ? 1500 : 300;
                        setTimeout(function() {
                            $this.find('.tab-content .tab-pane.active .slick-slider').slick('setPosition');
                        }, setTimeOutTime);
                    }, bootstrapTabsItem));
                }
            }
        }
        if (plugins.radio.length) {
            for (var i = 0; i < plugins.radio.length; i++) {
                $(plugins.radio[i]).addClass("radio-custom").after("<span class='radio-custom-dummy'></span>")
            }
        }
        if (plugins.checkbox.length) {
            for (var i = 0; i < plugins.checkbox.length; i++) {
                $(plugins.checkbox[i]).addClass("checkbox-custom").after("<span class='checkbox-custom-dummy'></span>")
            }
        }
        if (isDesktop && !isNoviBuilder) {
            $().UItoTop({
                easingType: 'easeOutQuad',
                containerClass: 'ui-to-top mdi mdi-arrow-up'
            });
        }

        if (plugins.rdNavbar.length) {
            var aliaces, i, j, len, value, values, responsiveNavbar;
            aliaces = ["-", "-sm-", "-md-", "-lg-", "-xl-", "-xxl-"];
            values = [0, 576, 768, 992, 1200, 1600];
            responsiveNavbar = {};
            for (i = j = 0, len = values.length; j < len; i = ++j) {
                value = values[i];
                if (!responsiveNavbar[values[i]]) {
                    responsiveNavbar[values[i]] = {};
                }
                if (plugins.rdNavbar.attr('data' + aliaces[i] + 'layout')) {
                    responsiveNavbar[values[i]].layout = plugins.rdNavbar.attr('data' + aliaces[i] + 'layout');
                }
                if (plugins.rdNavbar.attr('data' + aliaces[i] + 'device-layout')) {
                    responsiveNavbar[values[i]]['deviceLayout'] = plugins.rdNavbar.attr('data' + aliaces[i] + 'device-layout');
                }
                if (plugins.rdNavbar.attr('data' + aliaces[i] + 'hover-on')) {
                    responsiveNavbar[values[i]]['focusOnHover'] = plugins.rdNavbar.attr('data' + aliaces[i] + 'hover-on') === 'true';
                }
                if (plugins.rdNavbar.attr('data' + aliaces[i] + 'auto-height')) {
                    responsiveNavbar[values[i]]['autoHeight'] = plugins.rdNavbar.attr('data' + aliaces[i] + 'auto-height') === 'true';
                }
                if (isNoviBuilder) {
                    responsiveNavbar[values[i]]['stickUp'] = false;
                } else if (plugins.rdNavbar.attr('data' + aliaces[i] + 'stick-up')) {
                    responsiveNavbar[values[i]]['stickUp'] = plugins.rdNavbar.attr('data' + aliaces[i] + 'stick-up') === 'true';
                }
                if (plugins.rdNavbar.attr('data' + aliaces[i] + 'stick-up-offset')) {
                    responsiveNavbar[values[i]]['stickUpOffset'] = plugins.rdNavbar.attr('data' + aliaces[i] + 'stick-up-offset');
                }
            }
            plugins.rdNavbar.RDNavbar({
                anchorNav: !isNoviBuilder,
                stickUpClone: (plugins.rdNavbar.attr("data-stick-up-clone") && !isNoviBuilder) ? plugins.rdNavbar.attr("data-stick-up-clone") === 'true' : false,
                responsive: responsiveNavbar,
                callbacks: {
                    onStuck: function() {
                        var navbarSearch = this.$element.find('.rd-search input');
                        if (navbarSearch) {
                            navbarSearch.val('').trigger('propertychange');
                        }
                    },
                    onDropdownOver: function() {
                        return !isNoviBuilder;
                    },
                    onUnstuck: function() {
                        if (this.$clone === null)
                            return;
                        var navbarSearch = this.$clone.find('.rd-search input');
                        if (navbarSearch) {
                            navbarSearch.val('').trigger('propertychange');
                            navbarSearch.trigger('blur');
                        }
                    }
                }
            });
            if (plugins.rdNavbar.attr("data-body-class")) {
                document.body.className += ' ' + plugins.rdNavbar.attr("data-body-class");
            }
        }
        if (plugins.owl.length) {
            for (var i = 0; i < plugins.owl.length; i++) {
                var c = $(plugins.owl[i]);
                plugins.owl[i].owl = c;
                initOwlCarousel(c);
            }
            if (!isIE || isIE >= 12) {
                setTimeout(function() {
                    window.dispatchEvent(new Event('resize'));
                }, 500)
            }
        }
        if (plugins.viewAnimate.length) {
            for (var i = 0; i < plugins.viewAnimate.length; i++) {
                var $view = $(plugins.viewAnimate[i]).not('.active');
                $document.on("scroll", $.proxy(function() {
                    if (isScrolledIntoView(this)) {
                        this.addClass("active");
                    }
                }, $view)).trigger("scroll");
            }
        }

        function initCaptionAnimate(swiper) {
            var
                animate = function(caption) {
                    return function() {
                        var duration;
                        if (duration = caption.getAttribute('data-caption-duration')) caption.style.animationDuration = duration + 'ms';
                        caption.classList.remove('not-animated');
                        caption.classList.add(caption.getAttribute('data-caption-animate'));
                        caption.classList.add('animated');
                    };
                },
                initializeAnimation = function(captions) {
                    for (var i = 0; i < captions.length; i++) {
                        var caption = captions[i];
                        caption.classList.remove('animated');
                        caption.classList.remove(caption.getAttribute('data-caption-animate'));
                        caption.classList.add('not-animated');
                    }
                },
                finalizeAnimation = function(captions) {
                    for (var i = 0; i < captions.length; i++) {
                        var caption = captions[i];
                        if (caption.getAttribute('data-caption-delay')) {
                            setTimeout(animate(caption), Number(caption.getAttribute('data-caption-delay')));
                        } else {
                            animate(caption)();
                        }
                    }
                };
            swiper.params.caption = {
                animationEvent: 'slideChangeTransitionEnd'
            };
            initializeAnimation(swiper.$wrapperEl[0].querySelectorAll('[data-caption-animate]'));
            finalizeAnimation(swiper.$wrapperEl[0].children[swiper.activeIndex].querySelectorAll('[data-caption-animate]'));
            if (swiper.params.caption.animationEvent === 'slideChangeTransitionEnd') {
                swiper.on(swiper.params.caption.animationEvent, function() {
                    initializeAnimation(swiper.$wrapperEl[0].children[swiper.previousIndex].querySelectorAll('[data-caption-animate]'));
                    finalizeAnimation(swiper.$wrapperEl[0].children[swiper.activeIndex].querySelectorAll('[data-caption-animate]'));
                });
            } else {
                swiper.on('slideChangeTransitionEnd', function() {
                    initializeAnimation(swiper.$wrapperEl[0].children[swiper.previousIndex].querySelectorAll('[data-caption-animate]'));
                });
                swiper.on(swiper.params.caption.animationEvent, function() {
                    finalizeAnimation(swiper.$wrapperEl[0].children[swiper.activeIndex].querySelectorAll('[data-caption-animate]'));
                });
            }
        }
        if (plugins.swiper) {
            for (var i = 0; i < plugins.swiper.length; i++) {
                plugins.swiper[i].swiper = initSwiper(plugins.swiper[i]);
            }
            var dynamicSwipers = $('.swiper-slider-custom');
            if (dynamicSwipers.length) {
                $window.on('resize orientationchange', function() {
                    for (var i = 0; i < dynamicSwipers.length; i++) {
                        if (window.innerWidth < 576 && dynamicSwipers[i].swiper.params.direction === 'vertical') {
                            dynamicSwipers[i].setAttribute('data-direction', 'horizontal');
                            dynamicSwipers[i].swiper.destroy();
                            initSwiper(dynamicSwipers[i]);
                        } else if (window.innerWidth >= 576 && dynamicSwipers[i].swiper.params.direction === 'horizontal') {
                            dynamicSwipers[i].setAttribute('data-direction', 'vertical');
                            dynamicSwipers[i].swiper.destroy();
                            initSwiper(dynamicSwipers[i]);
                        }
                    }
                });
            }
        }
        if ($html.hasClass("wow-animation") && plugins.wow.length && !isNoviBuilder && isDesktop) {
            new WOW().init();
        }
        if (plugins.lightGallery.length) {
            for (var i = 0; i < plugins.lightGallery.length; i++) {
                initLightGallery(plugins.lightGallery[i]);
            }
        }
        if (plugins.lightGalleryItem.length) {
            var notCarouselItems = [];
            for (var z = 0; z < plugins.lightGalleryItem.length; z++) {
                if (!$(plugins.lightGalleryItem[z]).parents('.owl-carousel').length && !$(plugins.lightGalleryItem[z]).parents('.swiper-slider').length && !$(plugins.lightGalleryItem[z]).parents('.slick-slider').length) {
                    notCarouselItems.push(plugins.lightGalleryItem[z]);
                }
            }
            plugins.lightGalleryItem = notCarouselItems;
            for (var i = 0; i < plugins.lightGalleryItem.length; i++) {
                initLightGalleryItem(plugins.lightGalleryItem[i]);
            }
        }
        if (plugins.lightDynamicGalleryItem.length) {
            for (var i = 0; i < plugins.lightDynamicGalleryItem.length; i++) {
                initDynamicLightGallery(plugins.lightDynamicGalleryItem[i]);
            }
        }
        if (plugins.customToggle.length) {
            for (var i = 0; i < plugins.customToggle.length; i++) {
                var $this = $(plugins.customToggle[i]);
                $this.on('click', $.proxy(function(event) {
                    event.preventDefault();
                    var $ctx = $(this);
                    $($ctx.attr('data-custom-toggle')).add(this).toggleClass('active');
                }, $this));
                if ($this.attr("data-custom-toggle-hide-on-blur") === "true") {
                    $body.on("click", $this, function(e) {
                        if (e.target !== e.data[0] && $(e.data.attr('data-custom-toggle')).find($(e.target)).length && e.data.find($(e.target)).length === 0) {
                            $(e.data.attr('data-custom-toggle')).add(e.data[0]).removeClass('active');
                        }
                    })
                }
                if ($this.attr("data-custom-toggle-disable-on-blur") === "true") {
                    $body.on("click", $this, function(e) {
                        if (e.target !== e.data[0] && $(e.data.attr('data-custom-toggle')).find($(e.target)).length === 0 && e.data.find($(e.target)).length === 0) {
                            $(e.data.attr('data-custom-toggle')).add(e.data[0]).removeClass('active');
                        }
                    })
                }
            }
        }
        if (plugins.stepper.length) {
            plugins.stepper.stepper({
                labels: {
                    up: "",
                    down: ""
                }
            });
        }
        if (plugins.radioPanel) {
            for (var i = 0; i < plugins.radioPanel.length; i++) {
                var $element = $(plugins.radioPanel[i]);
                $element.on('click', function() {
                    plugins.radioPanel.removeClass('active');
                    $(this).addClass('active');
                })
            }
        }
    });

    var btnCallOrder = $(".btn__call-order");
    if (btnCallOrder.length) {
        btnCallOrder.on("click", function () {
            var val = $(this).attr("data-product");
            return $("#order .modal-title .product_name").text(val) && $("#order input[name=product]").val(val);
        });
    }

    var btnCallOrderService = $(".btn__call-order-service");
    if (btnCallOrderService.length) {
        btnCallOrderService.on("click", function () {
            var val = $(this).attr("data-service");
            return $("#order_service .modal-title .product_name").text(val) && $("#order_service input[name=service]").val(val);
        });
    }

    var Notification = {
        element: false,
        setElement: function (element) {
            return this.element = element;
        },
        notify: function (message) {
            if( ! this.element) {
                this.setElement(jQuery(".notify"));
            }
            return this.element.html('<div>' + message + '</div>') && this.element.fadeIn().delay(7000).fadeOut();
        }
    };

    formHandler("#form__subscribe", Notification);
    formHandler("#form__order", Notification);
    formHandler("#form__order-service", Notification);
    formHandler("#form__order-cart", Notification);
    formHandler("#form__callback-popup", Notification);

    [].forEach.call(document.querySelectorAll('img[data-src]'), function(img) {
        img.setAttribute('src', img.getAttribute('data-src'));
        img.onload = function() {
            img.removeAttribute('data-src');
        };
    });

}());

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

function formHandler(selector, Notification) {
    return $(document).on("submit", selector, function(e){
        e.preventDefault();
        var _this = $(this),
            url = _this.attr('action'),
            data = _this.serialize(),
            submitBlock = _this.find(".submit"),
            orderForm = $(".modal"),
            agree = _this.find(".agree__block input[type=checkbox]");
        if (agree.length && ! agree.prop("checked")) {
            agree.closest(".agree__block").find(".error").fadeIn().delay(3000).fadeOut();
            return false;
        }
        return $.ajax({
            type: "POST",
            dataType: "json",
            url: url,
            data: data,
            beforeSend: function() {
                return submitBlock.addClass("is__sent");
            },
            success: function (data) {
                Notification.notify(data.message);
                if (orderForm.length) {
                    orderForm.modal("hide");
                }
                if (data.clear) {
                    $(".cart_table").remove();
                    $("button.rd-navbar-basket span").text(0);
                    $("html, body").animate({ scrollTop: 0 }, "slow");
                }
                return submitBlock.removeClass("is__sent") && _this.trigger("reset");
            }
        });
    });
}
jQuery(document).ajaxError(function () {
    return jQuery("form .submit").removeClass("is__sent") && jQuery('.notify').html('<div>Произошла ошибка =(</div>').fadeIn().delay(3000).fadeOut();
});
(function() {

    var youtube = document.querySelectorAll(".youtube-box");

    var yLength = youtube.length;
    if(yLength) {
        for (var i = 0; i < yLength; i++) {

            var source = "https://img.youtube.com/vi/"+ youtube[i].dataset.embed +"/sddefault.jpg";

            var image = new Image();
            image.src = source;
            image.addEventListener( "load", function() {
                youtube[ i ].appendChild( image );
            }(i));

            youtube[i].addEventListener( "click", function() {

                var iframe = document.createElement( "iframe" );

                iframe.setAttribute("frameborder", "0");
                iframe.setAttribute("allowfullscreen", "");
                iframe.setAttribute("allow", "accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture");
                iframe.setAttribute("src", "https://www.youtube.com/embed/"+ this.dataset.embed +"?rel=0&showinfo=0&autoplay=1");

                this.innerHTML = "";
                this.appendChild(iframe);
            });
        }
    }

    var check_if_load = false;
    var myMapTemp, myPlacemarkTemp;
    var halfHeightDocument = $(document).height() / 3;

    var ymap = function() {
        jQuery(window).scroll(function(){
                if (!check_if_load && window.pageYOffset >= halfHeightDocument) {
                    check_if_load = true;
                    loadScript("https://api-maps.yandex.ru/2.1/?lang=ru_RU&amp;loadByRequire=1", function(){
                        ymaps.load(init);
                    });
                }
            }
        );
    };

    function init () {
        if(!$("#map-yandex").length) {
            return false;
        }
        var myMapTemp = new ymaps.Map("map-yandex", {
            center: [44.581759, 33.484082],
            zoom: 17,
            controls: ['zoomControl', 'fullscreenControl']
        });
        var myPlacemarkTemp = new ymaps.GeoObject({
            geometry: {
                type: "Point",
                coordinates: [44.581759, 33.484082]
            }
        });
        myMapTemp.geoObjects.add(myPlacemarkTemp);
        myMapTemp.layers.get(0).get(0);
    }

    function loadScript(url, callback){
        var script = document.createElement("script");

        if (script.readyState){
            script.onreadystatechange = function(){
                if (script.readyState === "loaded" ||
                    script.readyState === "complete"){
                    script.onreadystatechange = null;
                    callback();
                }
            };
        } else {
            script.onload = function(){
                callback();
            };
        }

        script.src = url;
        document.getElementsByTagName("head")[0].appendChild(script);
    }

    ymap();

    /*
    |-----------------------------------------------------------
    |   cart actions
    |-----------------------------------------------------------
    */

    var cartIcon = $(".rd-navbar-basket");

    // add to cart
    $("section").on("click", ".add_to-cart", function () {
        var _this = jQuery(this);

        return jQuery.ajax({
            type: "POST",
            dataType: "json",
            url: _this.attr("data-cart"),
            beforeSend: function() {
                return true;
            },
            success: function (response) {
                var notify = jQuery(".notify");
                cartIcon.find("span").text(response.quantity);
                return notify.html('<div>' + response.message + '</div>') &&  notify.fadeIn().delay(2000).fadeOut();
            }
        });
    });

    var table = jQuery(".table-cart");
    table.on("click", ".remove_item", function () {
        var _this = jQuery(this),
            table = _this.closest("table");

        return jQuery.ajax({
            type: "POST",
            dataType: "json",
            url: _this.attr("data-link"),
            success: function (response) {
                if (response.status) {
                    cartIcon.find("span").text(response.quantity);
                    return _this.closest("tr").remove() && table.find(".total_price").text(response.total);
                }
                return false;
            }
        });
    });

    table.on("click touchend", ".stepper-arrow", function () {
        var _this = jQuery(this),
            productId = parseInt(_this.closest("tr").attr("data-product")),
            table = _this.closest("table"),
            quantity = 1;

        if (_this.hasClass("down")) {
            quantity = quantity * -1;
        }

        return jQuery.ajax({
            type: "POST",
            dataType: "json",
            url: "cart/update/" + productId + "/" + quantity,
            success: function (response) {
                cartIcon.find("span").text(response.quantity);
                return table.find(".total_price").text(response.total) && _this.closest("tr").find(".product_price > span").text(response.productPrice);
            }
        });
    });

    $(".to-form__subscribe").click(function () {
        const elementClick = $(this).attr("href");
        const destination = $(elementClick).offset().top - 200;
        $('html, body').animate({ scrollTop: destination }, 900);

        return false;
    });

    $('.options-title').on('click', function () {
        var t = $(this),
            e = t.next();

        if (e.hasClass("d-lg-block")) {
            e.addClass("d-none").removeClass("d-lg-block");
            t.find(".fa-chevron-down").addClass("d-none");
            t.find(".fa-angle-right").removeClass("d-none");
        } else {
            e.removeClass("d-none").addClass("d-lg-block");
            t.find(".fa-chevron-down").removeClass("d-none");
            t.find(".fa-angle-right").addClass("d-none");
        }
    });

    const players = Array.from(document.querySelectorAll('.js-player')).map((p) => new Plyr(p, {
        'settings': [],
        'controls': ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'captions', 'settings', 'airplay', 'fullscreen']
    }));
})();
