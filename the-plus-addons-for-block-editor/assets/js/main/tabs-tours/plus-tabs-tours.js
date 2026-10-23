document.addEventListener("DOMContentLoaded", function() {
    tabtoursInit(document)
});

function tabtoursInit(doc){
	let tabsWrappers = doc.querySelectorAll('.tpgb-tabs-wrapper');
    
    if(tabsWrappers){
        tabsWrappers.forEach(function(currentTab) {
            var tabHover = currentTab.dataset.tabHover;
            var tabHeaders = currentTab.querySelectorAll('.tpgb-tab-header');
            if(tabHeaders){
                tabHeaders.forEach(function(tabHeader) {
                    if(tabHover === 'no') {
                        tabHeader.addEventListener('click', function() {
                            tabstoursclickhover(this)
                        });
                    }
                });
            }

            /* Responsive Mobile Accordion */
            if(currentTab.classList.contains("mobile-accordion")) {
                window.addEventListener('resize', function() {
                    if(window.innerWidth <= 600) {
                        currentTab.classList.add("mobile-accordion-tab");
                    }
                });
        
                var mobileAccordionTabs = currentTab.querySelectorAll('.tpgb-tabs-content-wrapper .tab-mobile-title');
                if(mobileAccordionTabs){
                    mobileAccordionTabs.forEach(function(tab) {
                        tab.addEventListener('click', function() {
                            var currentTabIndex = this.dataset.tab;
                            var tabsContainer = this.closest('.tpgb-tabs-wrapper');
                            var tabsNav = tabsContainer.querySelectorAll('.tpgb-tabs-content-wrapper .tab-mobile-title');
                            var tabsContent = tabsContainer.querySelectorAll('.tpgb-tabs-content-wrapper .tpgb-tab-content');
                            
                            tabsNav.forEach(function(nav) {
                                nav.classList.remove('active', 'default-active');
                                nav.classList.add('inactive');
                            });
                            this.classList.add('active');
                            this.classList.remove('inactive');
                            
                            tabsContent.forEach(function(content) {
                                content.classList.remove('active');
                                content.classList.add('inactive');
                            });
                            tabsContainer.querySelector(".tpgb-tabs-content-wrapper .tpgb-tab-content[data-tab='" + currentTabIndex + "']").classList.add('active');
                            tabsContainer.querySelector(".tpgb-tabs-content-wrapper .tpgb-tab-content[data-tab='" + currentTabIndex + "']").classList.remove('inactive');
                        });
                    });
                }
            }
        });
    }
}

function tabstoursclickhover(e){
    var currentTabIndex = e.dataset.tab;
    var tabsContainer = e.closest('.tpgb-tabs-wrapper');
    var tabsNav = tabsContainer.querySelector('.tpgb-tabs-nav').querySelectorAll('.tpgb-tab-li .tpgb-tab-header');
    var tabsContent = tabsContainer.querySelector('.tpgb-tabs-content-wrapper').querySelectorAll('.tpgb-tab-content');
    
    tabsNav.forEach(function(nav) {
        nav.classList.remove('active', 'default-active');
        nav.classList.add('inactive');
    });
    e.classList.add('active');
    e.classList.remove('inactive');
    
    tabsContent.forEach(function(content) {
        content.classList.remove('active');
        content.classList.add('inactive');
    });
    tabsContainer.querySelector(".tpgb-tabs-content-wrapper .tpgb-tab-content[data-tab='" + currentTabIndex + "']").classList.add('active');
    tabsContainer.querySelector(".tpgb-tabs-content-wrapper .tpgb-tab-content[data-tab='" + currentTabIndex + "']").classList.remove('inactive');
    
    //Init Splide Slider
    var splideCarousels = tabsContainer.querySelectorAll(".tpgb-tab-content[data-tab='" + currentTabIndex + "'] .tpgb-carousel");
    splideCarousels.forEach(function(carousel) {
        var splideInit = slideStore.get(carousel);
        splideInit.refresh();
    });
    
    // Handle isotope
    var isotopeElements = tabsContainer.querySelectorAll(".tpgb-tab-content[data-tab='" + currentTabIndex + "'] .tpgb-isotope .post-loop-inner");
    if(isotopeElements.length) {
        if(document.body.clientHeight <= window.innerHeight) {
            isotopeElements.forEach(function(element) {
                if(element.querySelector(".post-lazy-load") && typeof tpgb_lazy_load_ajax === "function") {
                    tpgb_lazy_load_ajax(element);
                }
            });
        }
        
        isotopeElements.forEach(function(element) {
            var curr = element;
            var isotope = new Isotope(curr, {
                itemSelector: ".grid-item",
                resizable: true,
                sortBy: "original-order",
                resizesContainer: true,
                initLayout: false
            });
            isotope.layout();
            setTimeout(function() {
                isotope.layout();
            }, 50);
            
            if(curr.dataset.anim === 'no') {
                isotope.options.transitionDuration = 0;
            }
            
            if(curr.clientHeight === 0) {
                curr.style.opacity = 0;
                isotope.once('layoutComplete', function(isoInstance, laidOutItems) {
                    curr.style.opacity = 1;
                });
            }
        });
    }
    
    // Handle before-after
    if(tabsContainer.querySelector(".tpgb-tabs-content-wrapper .tpgb-tab-content[data-tab='" + currentTabIndex + "'] .pt_tpgb_before_after")) {
        size_Elements();
    }
}

/**
 * Tabs / Tours accessibility layer.
 *
 * TT-1 (WCAG 2.1.1, Level A) - tab headers had no tabindex and the script
 *   registered no keyboard listener, so no tab could be selected by keyboard.
 * TT-2 - aria-selected was never emitted, aria-controls pointed at the header's
 *   own id instead of the panel, and role="tab" sat inside a plain div wrapper
 *   rather than being a child of the role="tablist".
 *
 * Applied to the rendered DOM on purpose: tabs saved by any earlier version are
 * upgraded in place, with no change to saved block markup, so no block
 * deprecation and no content invalidation.
 *
 * Selection is delegated to the existing click / mouseover handler, so keyboard
 * behaviour is identical to a pointer interaction.
 */
function tpgbTabsA11y( scope ) {
	var root = scope || document;
	if ( ! root || ! root.querySelectorAll ) {
		return;
	}

	Array.prototype.forEach.call( root.querySelectorAll( '.tpgb-tabs-wrapper' ), function ( wrapper ) {
		var nav = wrapper.querySelector( '.tpgb-tabs-nav' );
		if ( ! nav ) {
			return;
		}

		var headers = Array.prototype.slice.call( nav.querySelectorAll( '.tpgb-tab-header' ) );
		if ( ! headers.length ) {
			return;
		}

		var isHover  = 'yes' === wrapper.dataset.tabHover;
		var vertical = wrapper.classList.contains( 'tpgb-tabs-vertical' ) ||
			( wrapper.getAttribute( 'data-tab-layout' ) === 'vertical' );

		// role="tab" must be a child of role="tablist"; the .tpgb-tab-li wrapper
		// broke that, which is what axe reported as aria-required-children.
		Array.prototype.forEach.call( nav.querySelectorAll( '.tpgb-tab-li' ), function ( li ) {
			if ( ! li.getAttribute( 'role' ) ) {
				li.setAttribute( 'role', 'presentation' );
			}
		} );

		var selectedSeen = false;

		headers.forEach( function ( header ) {
			var index = header.getAttribute( 'data-tab' );
			var panel = null;
			if ( null !== index ) {
				panel = wrapper.querySelector( '.tpgb-tab-content[data-tab="' + index + '"]' );
			}

			if ( panel ) {
				if ( ! panel.id ) {
					panel.id = 'tpgb-tab-panel-' + Math.random().toString( 36 ).slice( 2, 9 );
				}
				header.setAttribute( 'aria-controls', panel.id );
				if ( header.id ) {
					panel.setAttribute( 'aria-labelledby', header.id );
				}
				// a tabpanel with no focusable content still needs to be reachable
				if ( ! panel.hasAttribute( 'tabindex' ) ) {
					panel.setAttribute( 'tabindex', '0' );
				}
			}

			var isActive = header.classList.contains( 'active' ) || header.classList.contains( 'default-active' );
			if ( isActive ) {
				selectedSeen = true;
			}

			if ( '1' === header.getAttribute( 'data-tpgb-a11y' ) ) {
				return;
			}

			var sync = function () {
				var on = header.classList.contains( 'active' ) || header.classList.contains( 'default-active' );
				header.setAttribute( 'aria-selected', on ? 'true' : 'false' );
				// Every tab sits in the Tab order so the Tab key steps tab-to-tab
				// (tab 1 -> tab 2 -> panel); arrow keys still move + select as well.
				header.setAttribute( 'tabindex', '0' );
				if ( panel ) {
					panel.setAttribute( 'aria-hidden', on ? 'false' : 'true' );
				}
			};
			sync();

			if ( window.MutationObserver ) {
				new window.MutationObserver( sync ).observe( header, {
					attributes: true,
					attributeFilter: [ 'class' ],
				} );
			}

			var select = function () {
				if ( isHover ) {
					header.dispatchEvent( new Event( 'mouseover', { bubbles: true } ) );
				} else {
					header.click();
				}
			};

			header.addEventListener( 'keydown', function ( e ) {
				var key = e.key;

				if ( 'Enter' === key || ' ' === key || 'Spacebar' === key ) {
					e.preventDefault();
					select();
					return;
				}

				var move = 0;
				var fwd  = vertical ? 'ArrowDown' : 'ArrowRight';
				var back = vertical ? 'ArrowUp' : 'ArrowLeft';
				if ( key === fwd || key === ( vertical ? 'Down' : 'Right' ) ) {
					move = 1;
				} else if ( key === back || key === ( vertical ? 'Up' : 'Left' ) ) {
					move = -1;
				}

				if ( move || 'Home' === key || 'End' === key ) {
					e.preventDefault();
					var i = headers.indexOf( header );
					var next;
					if ( 'Home' === key ) {
						next = headers[ 0 ];
					} else if ( 'End' === key ) {
						next = headers[ headers.length - 1 ];
					} else {
						next = headers[ ( i + move + headers.length ) % headers.length ];
					}
					if ( next ) {
						next.focus();
						// WAI-ARIA tabs: arrow keys select as they move
						if ( isHover ) {
							next.dispatchEvent( new Event( 'mouseover', { bubbles: true } ) );
						} else {
							next.click();
						}
					}
				}
			} );

			header.setAttribute( 'data-tpgb-a11y', '1' );
		} );

		// never leave the whole tablist out of the tab order
		if ( ! selectedSeen && headers[ 0 ] ) {
			headers[ 0 ].setAttribute( 'tabindex', '0' );
		}
	} );
}

document.addEventListener( 'DOMContentLoaded', function () {
	tpgbTabsA11y( document );
} );
