/**
 * Is the DOM ready?
 *
 * This implementation is coming from https://gomakethings.com/a-native-javascript-equivalent-of-jquerys-ready-method/
 *
 * @param {Function} fn Callback function to run.
 */
function domReady(fn) {
    if (typeof fn !== 'function') {
        return;
    }

    if (document.readyState === 'interactive' || document.readyState === 'complete') {
        return fn();
    }

    document.addEventListener('DOMContentLoaded', fn, false);
}

domReady(function () {
    //Blog Ajax Pagination in the frontpage
    function blogPaginationAjax(maxpageNum, next_num) {
        fetch(yummy_bites_data.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=yummy_bites_blog_section_pagination&paged=${next_num}`,
        })
            .then(response => response.text())
            .then(response => {
                document.querySelector('.blog-sec__content-wrapper .blog-sec__inner-wrapper').insertAdjacentHTML('beforeend', response);
            });

        if (maxpageNum == next_num) {
            const loadMoreBtn = document.querySelector('.blog-load-more');
            loadMoreBtn.classList.add('inactive');
            loadMoreBtn.textContent = 'No More Posts';
        }
    }

    let blogBtn = document.querySelector('.blog-load-more');
    if (null !== blogBtn) {
        blogBtn.addEventListener('click', function (e) {
            let pageNum = 1;
            pageNum = Number(this.getAttribute('data-pagenum')) + 1;
            let maxpageNum = this.getAttribute('data-pages');
            this.setAttribute('data-pagenum', pageNum.toString());
            e.preventDefault(); // don't trigger page reload
            blogPaginationAjax(maxpageNum, pageNum);
        });
    }

    //Sticky Widget height adjustment when sticky header is enabled
    var stickyHeader = document.querySelector('.sticky-header');
    function widgetSticky() {
        var sh_offsetHeight = stickyHeader.offsetHeight;
        var lastWidget = document.querySelector('.widget-area .widget:last-child');
        if (null !== lastWidget) {
            document.querySelector('.widget-area .widget:last-child').style.top = sh_offsetHeight + 50 + "px";
        }
    }

    if (null !== stickyHeader) {
        stickyHeader.length !== 0 && widgetSticky();
    }

    // Animation for FadeIn and FadeOut
    function fadeIn(element, duration) {
        element.style.opacity = 0;
        element.style.display = "block";

        (function fade() {
            var val = parseFloat(element.style.opacity);
            if (!((val += 0.1) > 1)) {
                element.style.opacity = val;
                requestAnimationFrame(fade);
            }
        })(duration);
    }

    function fadeOut(element, duration) {
        element.style.opacity = 1;
        (function fade() {
            if ((element.style.opacity -= 0.1) <= 0) {
                element.style.display = "none";
            } else {
                requestAnimationFrame(fade);
            }
        })(duration);
    }

    // back to top
    const backToTop = document.querySelector('.back-to-top');
    if (null !== backToTop) {
        document.addEventListener('scroll', () => {
            window.scrollY > 200
                ? backToTop.classList.add('active')
                : backToTop.classList.remove('active');
        });

        backToTop.addEventListener('click', () => {
            setTimeout(() => {
                window.scrollTo({
                    top: 0,
                    behavior: "smooth"
                });
            }, 300);

        });
    }

    // Header Search toggle
    const headerSearch = {
        init: function () {
            const searchOpenBtns = document.querySelectorAll('.header-search .search-toggle');
            searchOpenBtns.forEach(searchOpenBtn => {
                searchOpenBtn.addEventListener('click', () => {
                    this.showSearch(searchOpenBtn);
                });
            });

            const searchCloseBtns = document.querySelectorAll('.header-search-wrap .header-search-inner .close');
            searchCloseBtns.forEach(searchCloseBtn => {
                searchCloseBtn.addEventListener('click', () => {
                    this.closeSearch(searchCloseBtn);
                });
            });

            const searchModels = document.querySelectorAll('.header-search .header-search-wrap');
            searchModels.forEach(searchModel => {
                searchModel.addEventListener('keydown', (e) => {
                    if (e.key === "Escape") {
                        fadeOut(searchModel, 600)
                    }
                })

            })

            const stopPropagation = document.querySelector('.header-search .header-search-inner .search-form');
            if (stopPropagation !== null) {
                stopPropagation.addEventListener('click', function (e) {
                    e.stopPropagation();
                });
            }

            const closeSearchModels = document.querySelectorAll('.header-search .header-search-wrap');
            const inputForm = document.querySelector('.header-search .header-search-wrap .header-search-inner .search-field');
            closeSearchModels.forEach(closeSearchModel => {
                closeSearchModel.addEventListener('click', function (e) {
                    if (!e.target.isEqualNode(inputForm)) {
                        fadeOut(closeSearchModel, 300);
                    }
                })
            })
        },
        showSearch: function (searchOpenBtn) {
            const toggle_elem = searchOpenBtn.nextElementSibling;
            fadeIn(toggle_elem, 300);
        },
        closeSearch: function (searchCloseBtn) {
            const toggle_elem = searchCloseBtn.parentNode.closest('.header-search-wrap');
            fadeOut(toggle_elem, 300);
        }
    }
    headerSearch.init();

    // mobile navigation
    const adminbar = document.getElementById('wpadminbar');
    let adminbarHeight;

    if (adminbar) {
        adminbarHeight = document.getElementById('wpadminbar').offsetHeight;
    }

    const headerElement = document.querySelector('.site-header .mobile-header .header-bottom-slide .header-bottom-slide-inner');

    if (headerElement) {
        if (adminbarHeight) {
            headerElement.style.top = adminbarHeight + "px";
        } else {
            headerElement.style.top = "0";
        }
    }

    const mobButtons = document.querySelectorAll('.sticky-header .toggle-btn,.site-header .mobile-header .toggle-btn-wrap .toggle-btn');
    mobButtons.forEach(function (mobButton) {
        mobButton.addEventListener('click', () => {
            document.body.classList.add('mobile-menu-active');
            document.querySelector('.site-header .mobile-header .header-bottom-slide .header-bottom-slide-inner').style.transform = "translate(0,0)";
        });
    })

    document.querySelector('.site-header .mobile-header .header-bottom-slide .header-bottom-slide-inner .container .mobile-header-wrap  > .close')?.addEventListener('click', function () {
        document.body.classList.remove('mobile-menu-active');
        document.querySelector('.site-header .mobile-header .header-bottom-slide .header-bottom-slide-inner').style.transform = "translate(-100%,0)";
    })

    // navigation accessibility
    document.body.addEventListener('keydown', function (e) {
        if (e.key === 'Tab') {
            document.body.classList.add('keyboard-nav-on');
        }
    });

    document.body.addEventListener('mousemove', function (e) {
        if (document.body.classList.contains('keyboard-nav-on')) {
            document.body.classList.remove('keyboard-nav-on');
        }
    });


    const navitems = document.querySelectorAll('.nav-menu li')
    if (null !== navitems) {
        navitems.forEach(function (navitem) {
            navitem.addEventListener('focusin', function () {
                this.classList.add("focus");
            });
            navitem.addEventListener('focusout', function () {
                this.classList.remove("focus");
            });
        })
    }


    // desktop navigation 

    // adding icon 
    let menuItems = document.querySelectorAll('.site-header .menu-item-has-children:not(.mobile-header .menu-item-has-children), .footer-navigation .menu-item-has-children:not(.mobile-header .menu-item-has-children');
    if (null !== menuItems) {
        menuItems.forEach(menuItem => {
            let iconAdd = menuItem.querySelector('a');

            let icon = '<span tabindex="-1" class="submenu-toggle-btn"><svg width="12" height="6" viewBox="0 0 12 6" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0.978478 0.313439C1.15599 0.135928 1.43376 0.11979 1.62951 0.265027L1.68558 0.313439L5.9987 4.62632L10.3118 0.313439C10.4893 0.135928 10.7671 0.11979 10.9628 0.265027L11.0189 0.313439C11.1964 0.49095 11.2126 0.768726 11.0673 0.964466L11.0189 1.02055L6.35225 5.68721C6.17474 5.86472 5.89697 5.88086 5.70122 5.73562L5.64514 5.68721L0.978478 1.02055C0.783216 0.825283 0.783216 0.508701 0.978478 0.313439Z" fill="currentColor"/></svg></span>'
            iconAdd.insertAdjacentHTML("afterend", icon)

        })
    }

    let mobileMenuItems = document.querySelectorAll('.mobile-header .menu-item-has-children');
    if (null !== mobileMenuItems) {
        mobileMenuItems.forEach(mobileMenuItem => {
            let iconAdd = mobileMenuItem.querySelector('a');

            let icon = '<button class="submenu-toggle-btn"><svg width="12" height="6" viewBox="0 0 12 6" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0.978478 0.313439C1.15599 0.135928 1.43376 0.11979 1.62951 0.265027L1.68558 0.313439L5.9987 4.62632L10.3118 0.313439C10.4893 0.135928 10.7671 0.11979 10.9628 0.265027L11.0189 0.313439C11.1964 0.49095 11.2126 0.768726 11.0673 0.964466L11.0189 1.02055L6.35225 5.68721C6.17474 5.86472 5.89697 5.88086 5.70122 5.73562L5.64514 5.68721L0.978478 1.02055C0.783216 0.825283 0.783216 0.508701 0.978478 0.313439Z" fill="currentColor"/></svg></button>'
            iconAdd.insertAdjacentHTML('afterend', icon)
        })
    }

    // menu close button
    let menuCloseBtns = document.querySelectorAll('.main-navigation');
    if (null !== menuCloseBtns) {
        menuCloseBtns.forEach(menuCloseBtn => {
            menuCloseBtn.insertAdjacentHTML('afterbegin', '<button class="close-btn"></button>');
        })
    }

    // mobile submenu toggle
    const mobileHeader = document.querySelector('.site-header .mobile-header .header-bottom-slide .header-bottom-slide-inner');

    if (null !== mobileHeader) {
        mobileHeader.addEventListener('click', function (e) {
            if (!document.body.classList.contains('mobile-menu-active')) return;

            const toggleBtn = e.target.closest('.submenu-toggle-btn');
            if (toggleBtn) {
                e.preventDefault();
                e.stopPropagation();
                submenuToggle(toggleBtn.closest('.menu-item-has-children'));
            }
        })
    }

    function submenuToggle(parentLi) {

        function handleParent(parentLi) {
            if (!parentLi) return;

            const toggleBtn = parentLi.querySelector('.submenu-toggle-btn');
            const submenu = parentLi.querySelector('ul.sub-menu');

            if (parentLi.classList.contains('active')) {
                toggleBtn.setAttribute('aria-expanded', 'false');

                closeSubmenu(submenu, () => {
                    parentLi.classList.toggle('active');
                })
            } else {
                toggleBtn.setAttribute('aria-expanded', 'true');
                [...parentLi.parentNode.children].map((el) => {
                    el.classList.contains('active') && handleParent(el);
                })

                parentLi.classList.toggle('active');
                openSubmenu(submenu);
            }
        }

        function openSubmenu(submenu) {
            handleTransitionEnd(submenu)

            requestAnimationFrame(() => {
                const submenuHeight = submenu.getBoundingClientRect().height
                submenu.style.height = '0px';

                requestAnimationFrame(() => {
                    submenu.style.height = submenuHeight + 'px';
                })
            })
        }

        function closeSubmenu(submenu, cb) {

            handleTransitionEnd(submenu, cb)

            requestAnimationFrame(() => {
                const submenuHeight = submenu.getBoundingClientRect().height
                submenu.style.height = `${submenuHeight}px`

                requestAnimationFrame(() => {
                    submenu.style.height = '0px'
                })
            })
        }

        function handleTransitionEnd(submenu, cb) {
            submenu.addEventListener('transitionend', function onTransitionEnd() {
                submenu.removeAttribute('style')
                submenu.removeEventListener('transitionend', onTransitionEnd)
                if (cb) cb()
            })
        }
        handleParent(parentLi);
    }

    function animateOnScroll() {
        const animationClasses = ['[class*="animation"]', '[class*="clipIn"]'];
    
        function scrollTrigger(selector, options = {}) {
            const elements = [];
            selector.forEach((selector) => {
                const els = document.querySelectorAll(selector);
                elements.push(...Array.from(els));
            });
    
            elements.forEach((el) => {
                addObserver(el, options);
            });
        }
    
        function addObserver(el, options) {
            if (!("IntersectionObserver" in window)) {
                entry.target.classList.add("animate");
                return;
            }
            let observer = new IntersectionObserver((entries, observer) => {
                entries.forEach((entry) => {
                    entry.target.classList.toggle("animate", entry.isIntersecting);
                });
            }, options);
            observer.observe(el);
        }
    
        scrollTrigger(animationClasses, {
            rootMargin: "0px"
        });
    }
    
    animateOnScroll();
});