/*post Masonry*/
document.addEventListener('DOMContentLoaded', function () {
    tppoMetro(document)
});

window.addEventListener("resize", function () {
    tppoMetro(document);
});

document.body.addEventListener("post-load", function () {
    setTimeout(function () {
        tppoMetro(document);
    }, 800);
});

document.body.addEventListener("resort-isotope", function () {
    setTimeout(function () {
        tppoMetro(document);
    }, 800);
});

document.body.addEventListener("tabs-reinited", function () {
    setTimeout(function () {
        tppoMetro(document);
    }, 800);
});
document.body.addEventListener("isotope-sorted", function () {
    setTimeout(function () {
        tppoMetro(document);
    }, 500);
});

function tppoMetro(doc) {
    var tpgbMetroElements = doc.querySelectorAll('.tpgb-metro');
    if (tpgbMetroElements.length > 0) {
        tpgbMetroElements.forEach(function (element) {
            tpgbMetroLayout(element);
        });
    }

    if (typeof tpFilter == 'function') {
        tpFilter(doc);
    }
}

function tpgbMetroLayout(element) {
    var Id = element.getAttribute('data-id'),
        metroAttr = JSON.parse(element.getAttribute('data-metroAttr')),
        innerWidth = window.innerWidth,
        decWidth = innerWidth >= 1024,
        tabWidth = innerWidth >= 768 && innerWidth < 1024,
        mobileWidth = innerWidth < 768,
        metroCOl = '',
        setPad = 0,
        myWindow = window;

    if (decWidth && metroAttr.metro_col) {
        metroCOl = metroAttr.metro_col;
    }
    if (tabWidth && metroAttr.tab_metro_col) {
        metroCOl = metroAttr.tab_metro_col;
    }
    if (mobileWidth && metroAttr.mobile_metro_col) {
        metroCOl = metroAttr.mobile_metro_col;
    }

    if (metroCOl == '3') {
        var norm_size = Math.floor((element.offsetWidth - setPad * 2) / 3),
            double_size = norm_size * 2;
        element.querySelectorAll('.grid-item').forEach(function (item) {
            var set_w = norm_size,
                set_h = norm_size;
            if ((decWidth && metroAttr.metro_style == 'style-1') || (tabWidth && metroAttr.tab_metro_style == 'style-1') || (mobileWidth && metroAttr.mobile_metro_style == 'style-1')) {
                if ((decWidth && (item.classList.contains('tpgb-metro-1') || item.classList.contains('tpgb-metro-7'))) || (tabWidth && (item.classList.contains('tpgb-tab-metro-1') || item.classList.contains('tpgb-tab-metro-7'))) || (mobileWidth && (item.classList.contains('tpgb-mobile-metro-1') || item.classList.contains('tpgb-mobile-metro-7')))) {
                    set_w = double_size, set_h = double_size;
                }
                if ((decWidth && (item.classList.contains('tpgb-metro-4') || item.classList.contains('tpgb-metro-9'))) || (tabWidth && (item.classList.contains('tpgb-tab-metro-4') || item.classList.contains('tpgb-tab-metro-9'))) || (mobileWidth && (item.classList.contains('tpgb-mobile-metro-4') || item.classList.contains('tpgb-mobile-metro-9')))) {
                    set_w = double_size, set_h = norm_size;
                }
            }

            if (innerWidth < 768) {
                item.style.width = '100%';
                item.style.height = double_size * 2 + 'px';
            } else {
                item.style.width = set_w + 'px';
                item.style.height = set_h + 'px';
            }
        });
    }

    if (element.classList.contains('tpgb-metro')) {
        var block = document.querySelector('.tpgb-block-' + Id),
            postLoopInner = block.querySelector(".post-loop-inner");

        // Initialize Isotope
        let isotopeOptions = {
            itemSelector: '.grid-item',
            layoutMode: 'masonry',
            masonry: {
                columnWidth: norm_size
            }
        };

        if (element) {
            if (myWindow.innerWidth > 767) {
                new Isotope(postLoopInner, isotopeOptions);
            } else {
                new Isotope(postLoopInner, isotopeOptions);
            }
        } else {
            new Isotope(postLoopInner, {
                layoutMode: 'masonry',
                masonry: {
                    columnWidth: norm_size
                }
            });
        }

        if (typeof postLoopInner.isotope === 'function') {
            postLoopInner.isotope('layout');
        }


    }
}
