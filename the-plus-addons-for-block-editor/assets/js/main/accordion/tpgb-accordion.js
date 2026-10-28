document.addEventListener('DOMContentLoaded', (event) => {
  accordionJS(document)
});

function accordionJS(doc){
  let allAccordion = doc.querySelectorAll('.tpgb-accor-wrap');
  if(allAccordion){
        allAccordion.forEach((el)=>{
          let accType = el.getAttribute('data-type'),
              accrodionList = el.querySelectorAll('.tpgb-accor-item');

          accrodionList.forEach((al)=>{
              let acBtn = al.querySelector('.tpgb-accordion-header');

              if(accType == 'accordion'){
                if(/iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream){
                  acBtn.addEventListener('touchstart', (btn)=>{
                      var currBtn = btn.currentTarget;
                      toggleFun(currBtn, accrodionList);
                      changeEventAccordion(currBtn)
                  })
                }else{
                    acBtn.addEventListener('click', (btn)=>{
                            var currBtn = btn.currentTarget;
                            toggleFun(currBtn, accrodionList);
                            changeEventAccordion(currBtn)
                        })
                    }
                  }
              });
          })

      function toggleFun(currBtn, accrodionList){
          let content = currBtn.nextSibling;
          let getParent = currBtn.parentElement;
          if(currBtn.classList.contains('active')){
              getParent.classList.remove('active');
              currBtn.classList.remove('active');
			  currBtn.setAttribute('aria-expanded', 'false');
              content.classList.remove('active');
              slideUpP(content, 500);
          }else{
            accrodionList.forEach((ell) => {
              let actCon = ell.querySelector('.tpgb-accordion-header');

              if(actCon.classList.contains('active')){
                actCon.parentElement.classList.remove('active');
                actCon.classList.remove('active');
				actCon.setAttribute('aria-expanded', 'false');
                actCon.nextSibling.classList.remove('active');
                slideUpP(actCon.nextSibling, 500);
              }
            })
            getParent.classList.add('active');
            currBtn.classList.add('active');
			currBtn.setAttribute('aria-expanded', 'true');
            content.classList.add('active');
            slideDownP(content, 500);
          }
      }

      var hash = window.location.hash;
      document.addEventListener("DOMContentLoaded", () =>{
        if(hash!='' && hash!=undefined){
          let getHash = document.querySelector(hash);

          if(!getHash.classList.contains('active') && getHash){
            var hashOffset = getHash.getBoundingClientRect();
            window.scrollTo({top : hashOffset.top, behavior : "smooth"});

            let mainAc = getHash.closest('.tpgb-accor-wrap');

            if(mainAc){
              let acList = mainAc.querySelectorAll('.tpgb-accor-item');
              toggleFun(getHash, acList);
            }
          }
          
        }
      })
  }
}
function changeEventAccordion(el){
  var isotope_class = " .tpgb-isotope .post-loop-inner";
  if(el && el.nextSibling.querySelector(isotope_class) && typeof tppoMaso == 'function'){
    setTimeout(function(){
      tppoMaso(el.nextSibling);
    }, 50);
  }
}

/**
 * Accordion accessibility layer.
 *
 * AC-1 (WCAG 2.1.1, Level A) - headers were not focusable and there was no
 *   keyboard handler, so the whole widget was mouse-only.
 * AC-2 - aria-controls pointed at the header's own id instead of the panel,
 *   aria-expanded was never emitted, and an accordion was marked up with the
 *   tabs pattern (tablist/tab/tabpanel) instead of a disclosure.
 *
 * Applied to the rendered DOM on purpose: accordions saved by any earlier
 * version are upgraded in place, with no change to saved block markup and
 * therefore no block deprecation and no content invalidation.
 *
 * Activation is delegated to the existing click/mouseenter handlers, so
 * keyboard behaviour is identical to a mouse interaction.
 */
function tpgbAccordionA11y( scope ) {
	var root = scope || document;
	if ( ! root || ! root.querySelectorAll ) {
		return;
	}

	Array.prototype.forEach.call( root.querySelectorAll( '.tpgb-accor-wrap' ), function ( wrap ) {
		// A set of disclosures is not a tablist; the role made axe flag
		// aria-required-children and made screen readers announce tabs.
		if ( 'tablist' === wrap.getAttribute( 'role' ) ) {
			wrap.removeAttribute( 'role' );
		}

		var isHover = 'accordion' !== wrap.getAttribute( 'data-type' );
		var headers = [];

		Array.prototype.forEach.call( wrap.querySelectorAll( '.tpgb-accor-item' ), function ( item ) {
			var header = item.querySelector( '.tpgb-accordion-header' );
			if ( ! header ) {
				return;
			}
			headers.push( header );

			if ( '1' === header.getAttribute( 'data-tpgb-a11y' ) ) {
				return;
			}

			var panel = header.nextElementSibling;
			if ( ! panel || ! panel.classList || ! panel.classList.contains( 'tpgb-accordion-content' ) ) {
				panel = item.querySelector( '.tpgb-accordion-content' );
			}

			if ( ! header.hasAttribute( 'tabindex' ) ) {
				header.setAttribute( 'tabindex', '0' );
			}
			if ( 'tab' === header.getAttribute( 'role' ) ) {
				header.setAttribute( 'role', 'button' );
			}

			if ( panel ) {
				if ( ! panel.id ) {
					panel.id = 'tpgb-acc-panel-' + Math.random().toString( 36 ).slice( 2, 9 );
				}
				header.setAttribute( 'aria-controls', panel.id );
				if ( 'tabpanel' === panel.getAttribute( 'role' ) ) {
					panel.setAttribute( 'role', 'region' );
				}
				if ( header.id ) {
					panel.setAttribute( 'aria-labelledby', header.id );
				}
			}

			// The existing toggle only flips the .active class, so mirror it
			// rather than reaching into that logic.
			var syncExpanded = function () {
				header.setAttribute( 'aria-expanded', header.classList.contains( 'active' ) ? 'true' : 'false' );
			};
			syncExpanded();

			if ( window.MutationObserver ) {
				new window.MutationObserver( syncExpanded ).observe( header, {
					attributes: true,
					attributeFilter: [ 'class' ],
				} );
			}

			header.addEventListener( 'keydown', function ( e ) {
				var key = e.key;

				if ( 'Enter' === key || ' ' === key || 'Spacebar' === key ) {
					e.preventDefault();
					if ( isHover ) {
						// Hover-driven variant listens on mouseenter.
						header.dispatchEvent( new Event( 'mouseenter', { bubbles: false } ) );
					} else {
						header.click();
					}
					return;
				}

				var move = 0;
				if ( 'ArrowDown' === key || 'Down' === key ) {
					move = 1;
				} else if ( 'ArrowUp' === key || 'Up' === key ) {
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
					}
				}
			} );

			header.setAttribute( 'data-tpgb-a11y', '1' );
		} );
	} );
}

document.addEventListener( 'DOMContentLoaded', function () {
	tpgbAccordionA11y( document );
} );
