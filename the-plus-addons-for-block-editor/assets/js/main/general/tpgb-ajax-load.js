document.addEventListener("DOMContentLoaded", function() {

    var ajaxClick = document.querySelectorAll('.tpgb-load-template-click');
    if(ajaxClick.length){
        ajaxClick.forEach(function(ele) {
            ele.addEventListener("click", function(e) {
                e.preventDefault()
                tpgbeventCall(e)
            })
        });
    }

    var ajaxHover = document.querySelectorAll('.tpgb-load-template-hover');
    if(ajaxHover.length){
        ajaxHover.forEach(function(ele) {
            ele.addEventListener("mouseenter", function(e) {
                e.preventDefault();
                tpgbeventCall(e)
            })
        });
    }

    const tpgbonView = document.getElementsByClassName("tpgb-load-template-view");
    let tpgbserver = new IntersectionObserver(e => {
        e.forEach(e => {
            if( e.isIntersecting ){
                tpgbeventCall(e)
            }
        })
    });
    for (let tpserve of tpgbonView) tpgbserver.observe(tpserve);
});

// Lazy Render Script
! function(e) {
    "function" == typeof define && define.amd ? define(e) : e()
}(function() {
    ! function() {
        const tpgbonView = document.getElementsByClassName("tpgb-lazy-render");
        let tpgbserver = new IntersectionObserver(e => {
            e.forEach(e => {
                if( e.isIntersecting ){
                    var n = e.target.querySelector("noscript"),
                        blockID = e.target.getAttribute('data-bid');
                    if (n) {
                        var r, o = (new DOMParser).parseFromString(n.textContent, "text/html");
                        (r = e.target).replaceWith.apply(r, o.body.childNodes)
                        
                        var lcnt = '';
                        if( document.querySelector('.tpgb-wrap-'+blockID) != null ){
                            lcnt = document.querySelector('.tpgb-wrap-'+blockID)
                        }else{
                            lcnt = document.querySelector('.tpgb-block-'+blockID)
                        }

                        if(lcnt){
                            tpgb_trigger_hook(lcnt)
                        }
                        
                       
                    }
                    tpgbserver.unobserve(e.target)
                }
            })
        }, {
            root: null,
            rootMargin: "200px 0px"
        });
        for (let tpserve of tpgbonView) tpgbserver.observe(tpserve);
    }()
});



function tpgbeventCall(e){
    let listcls = (e.currentTarget) ? e.currentTarget.getAttribute('class') :  e.target.getAttribute('class') ,
    regex = /tpgb-load-(\d+)/,
    match = regex.exec(listcls);

    if (match &&  match[1]) {
        let id = match[1];
        cntselec = document.querySelector('.tpgb-load-'+id+'-content');
        if(cntselec ){
            if(!cntselec.classList.contains("tpgbtemloaded")){
                tpgb_pattern_ajax( cntselec , id)
            }
        }
    }
}

function tpgb_pattern_ajax(selector = '' , postId){
    selector.classList.add('tpgbonAjax');
    

    var actionSig = (typeof tpgb_config !== 'undefined' && tpgb_config.tpgb_action_sig) ? tpgb_config.tpgb_action_sig : '';
    var body = 'action=tpgb_get_template_content&tpgb_nonce=' + ((typeof tpgb_config !== 'undefined' && tpgb_config.tpgb_nonce) ? tpgb_config.tpgb_nonce : '')
        + '&postid=' + postId
        + '&tpgb_action_sig=' + encodeURIComponent(actionSig);

    tpgbAjax({
        body: body,
        nonceField: 'tpgb_nonce',
        nonceKind: 'addons',
        onSuccess: function (response) {
            if (response && response.success == true) {
                var cntselec = document.querySelectorAll('.tpgb-load-'+postId+'-content');
                if(cntselec.length ){
                    cntselec.forEach(function(cnt) {
                        if(!cnt.classList.contains("tpgbtemloaded")){
                            cnt.innerHTML = response.data
                            cnt.classList.add('tpgbtemloaded');
                            if( cnt.classList.contains('tpgbonAjax')){
                                cnt.classList.remove('tpgbonAjax');
                            }
                            tpgb_trigger_hook(cnt)
                        }
                    })
                }else{
                    selector.innerHTML = response.data
                    selector.classList.add('tpgbtemloaded');
                    selector.classList.remove('tpgbonAjax');
                    
                    tpgb_trigger_hook(selector)
                }
                    
                
            }
        }
    });
}

/**
 * tpgbAjax() -- shared retry-on-stale-nonce AJAX transport (Nexter-Blocks #123).
 *
 * A page served from a page cache / CDN older than the printed nonce's ~12-24h life carries a
 * dead nonce; every public ajax handler answered 403/-1 with no way to recover short of a full
 * reload. This wraps `fetch()` (the transport this codebase's own form-block submit already
 * uses): on a 403 status or a WordPress "invalid nonce" body (`-1` or `0`), it fetches exactly
 * one fresh nonce pair from the nopriv `tpgb_nonce_refresh` endpoint, substitutes it into the
 * SAME request body, and retries once. A second failure is reported as a normal error -- this
 * never loops.
 *
 * Contract, so Pro's handlers can wire to this unchanged:
 *   tpgbAjax({
 *     url:        string, optional, defaults to tpgb_config.ajax_url
 *     method:     string, optional, defaults to 'POST'
 *     body:       string ('key=value&key=value', url-encoded) OR a FormData instance -- either
 *                 way it must already contain `action=...` and the nonce field named by
 *                 `nonceField`.
 *     headers:    object, optional, extra request headers (merged in; a string body always
 *                 gets 'Content-Type: application/x-www-form-urlencoded' added automatically).
 *     nonceField: string, required -- the key in `body` whose value is the nonce to replace on
 *                 retry (e.g. 'tpgb_nonce', 'nonce').
 *     nonceKind:  string, optional, one of 'addons' (default) | 'searchbar' -- selects which
 *                 value tpgb_nonce_refresh's response supplies for the retry: 'tpgb_nonce' for
 *                 'addons', 'searchbar_nonce' for 'searchbar'.
 *     onSuccess:  function(parsedJsonOrRawText, response), called once, on the final response.
 *     onError:    function(responseOrError), called once, only if both the original request and
 *                 the single retry fail (or fail to reach the server).
 *   })
 *
 * tpgb_nonce_refresh response shape (also documented in
 * Tp_Blocks_Helper::tpgb_nonce_refresh_callback()):
 *   { success: true, data: { tpgb_nonce: '<fresh tpgb-addons nonce>', searchbar_nonce: '<fresh tpgb-searchbar nonce>' } }
 */
// This file can legitimately be enqueued under two different script handles on the same page
// (the conditional lazy-load bundle and, now, a hard dependency of the form/search blocks) --
// guarded so a second inclusion never resets an in-flight `_tpgbNonceRefreshPromise`.
if (typeof window.tpgbAjax !== 'function') {
function tpgbAjax(opts) {
    opts = opts || {};
    var url        = opts.url || ((typeof tpgb_config !== 'undefined' && tpgb_config.ajax_url) ? tpgb_config.ajax_url : '');
    var method     = opts.method || 'POST';
    var headers    = opts.headers || {};
    var nonceField = opts.nonceField || 'tpgb_nonce';
    var nonceKind  = opts.nonceKind || 'addons';
    var onSuccess  = (typeof opts.onSuccess === 'function') ? opts.onSuccess : function () {};
    var onError    = (typeof opts.onError === 'function') ? opts.onError : function () {};

    function parse(raw) {
        try {
            return JSON.parse(raw);
        } catch (e) {
            return raw;
        }
    }

    function withNonce(body, value) {
        if (typeof FormData !== 'undefined' && body instanceof FormData) {
            body.set(nonceField, value);
            return body;
        }
        var str = String(body || '');
        var re  = new RegExp('(^|&)' + nonceField + '=([^&]*)');
        return re.test(str)
            ? str.replace(re, function (m, p1) { return p1 + nonceField + '=' + encodeURIComponent(value); })
            : str + (str ? '&' : '') + nonceField + '=' + encodeURIComponent(value);
    }

    function fetchOpts(body) {
        var merged = { method: method, credentials: 'same-origin', headers: {} };
        for (var k in headers) {
            if (Object.prototype.hasOwnProperty.call(headers, k)) {
                merged.headers[k] = headers[k];
            }
        }
        var isFormData = (typeof FormData !== 'undefined') && (body instanceof FormData);
        if (!isFormData) {
            merged.headers['Content-Type'] = 'application/x-www-form-urlencoded;';
        }
        merged.body = body;
        return merged;
    }

    function attempt(body, isRetry) {
        fetch(url, fetchOpts(body)).then(function (response) {
            if (response.status === 403 && !isRetry) {
                return retryAfterRefresh(body);
            }
            return response.text().then(function (raw) {
                var trimmed = (raw || '').trim();
                if (!isRetry && (trimmed === '-1' || trimmed === '0')) {
                    return retryAfterRefresh(body);
                }
                if (response.ok) {
                    onSuccess(parse(raw), response);
                } else {
                    onError(response);
                }
            });
        }).catch(function (err) {
            onError(err);
        });
    }

    function retryAfterRefresh(originalBody) {
        tpgbRefreshNonceAndRetry(nonceKind, function (freshValue) {
            attempt(freshValue ? withNonce(originalBody, freshValue) : originalBody, true);
        });
    }

    attempt(opts.body, false);
}

// One in-flight tpgb_nonce_refresh call is shared by every concurrent caller (e.g. several
// lazy-load triggers going stale at once never fire more than one refresh request).
var _tpgbNonceRefreshPromise = null;
function tpgbRefreshNonceAndRetry(nonceKind, cb) {
    if (!_tpgbNonceRefreshPromise) {
        var url = (typeof tpgb_config !== 'undefined' && tpgb_config.ajax_url) ? tpgb_config.ajax_url : '';
        _tpgbNonceRefreshPromise = fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded;' },
            body: 'action=tpgb_nonce_refresh',
        })
            .then(function (response) { return response.json(); })
            .catch(function () { return null; })
            .then(function (parsed) {
                _tpgbNonceRefreshPromise = null;
                var data = (parsed && parsed.success && parsed.data) ? parsed.data : null;
                if (data && typeof tpgb_config !== 'undefined' && data.tpgb_nonce) {
                    tpgb_config.tpgb_nonce = data.tpgb_nonce;
                }
                return data;
            });
    }
    _tpgbNonceRefreshPromise.then(function (data) {
        cb(data ? (nonceKind === 'searchbar' ? data.searchbar_nonce : data.tpgb_nonce) : null);
    });
}
} // end typeof window.tpgbAjax !== 'function' guard

function tpgb_trigger_hook(ele){

    if( typeof onTpgbJsLoad == 'function' ){
        onTpgbJsLoad()
    }

    if(typeof accordionJS == 'function'){
        accordionJS(ele);
    }
    if(typeof advanceTypo == 'function'){
        advanceTypo(ele);
    }
    if(typeof tpadvBtn == 'function'){
        tpadvBtn(ele);
    }
    if(typeof tpdownBtn == 'function'){
        tpdownBtn(ele);
    }
    if(typeof tpadvChart == 'function'){
        tpadvChart(ele);
    }
    if(typeof tpaniBox == 'function'){
        tpaniBox(ele)
    }
    if(typeof tpaudioPlayer == 'function'){
        tpaudioPly(ele)
    }
    if(typeof tpbeAfimg == 'function'){
        setTimeout(() => {
            tpbeAfimg(ele)
        }, 100);
    }
    if(typeof tpcaroRemote == 'function'){
        tpcaroRemote(ele)
    }
    if(typeof tpcircleMenu == 'function'){
        tpcircleMenu(ele)
    }
    if(typeof tpcountDown == 'function'){
        tpcountDown(ele)
    }
    if(typeof tpstiCol == 'function'){
        tpstiCol(ele)
    }
    if(typeof tpcopCode == 'function'){
        tpcopCode(ele)
    }
    if(typeof tpadvImg == 'function'){
        tpadvImg(ele)
    }
    if(typeof tpgbctaTilt == 'function'){
        tpgbctaTilt(ele)
    }
    if(typeof tpdyDevices == 'function'){
        tpdyDevices(ele)
    }
    if(typeof tpExpand == 'function'){
        tpExpand(ele)
    }
    if(typeof tpgbInitMap == 'function'){
        tpgbInitMap(ele)
    }
    if(typeof tpheAnim == 'function'){
        tpheAnim(ele)
    }
    if(typeof tphospInit == 'function'){
        tphospInit(ele)
    }
    if(typeof tplogin == 'function'){
        tplogin(ele)
    }
    if(typeof tpmailchmp == 'function'){
        tpmailchmp(ele)
    }
    if(typeof tpmediaInit == 'function'){
        tpmediaInit(ele)
    }
    if(typeof tpmouserCur == 'function'){
        tpmouserCur(ele)
    } 
    if(typeof tpNav == 'function'){
        tpNav(ele)
    } 
    if(typeof tpnavmResize == 'function'){
        tpnavmResize(ele)
    } 
    if(typeof tpnavScro == 'function'){
        tpnavScro(ele)
    } 
    if(typeof tpnavAcc == 'function'){
        tpnavAcc(ele)
    } 
    if(typeof tpeqHeight == 'function'){
        tpeqHeight(ele)
    }
    if(typeof tpglTip == 'function'){
        tpglTip(ele)
    }
    if(typeof tpmoPrlx == 'function'){
        tpmoPrlx(ele)
    }
    if(typeof tpevTrack == 'function'){
        tpevTrack(ele)
    }
    if(typeof tpgloTilt == 'function'){
        tpgloTilt(ele)
    }
    if(typeof tppopup == 'function'){
        tppopup(ele)
    }

    // Post Listing Style 1 Hover
    if(typeof tppostList == 'function'){
        tppostList(ele)
    }
    if(typeof tpFilter == 'function'){
        tpFilter(ele)
    }
    if(typeof tppoLay == 'function'){
        tppoLay(ele)
    }
    if(typeof tppoMaso == 'function'){
        tppoMaso(ele)
    }
    if(typeof tppoMetro == 'function'){
        tppoMetro(ele)
    }
    if(typeof tppocaro == 'function'){
        tppocaro(ele)
    }
    if(typeof tppoLoad == 'function'){
        tppoLoad(ele)
    }
    if(typeof tppriceTable == 'function'){
        tppriceTable(ele)
    }
    if(typeof tpproStep == 'function'){
        tpproStep(ele)
    }
    if(typeof tpscrollNav == 'function'){
        tpscrollNav(ele)
    }
    if(typeof tpscrollSeq == 'function'){
        tpscrollSeq(ele)
    }
    if(typeof tpsocialFeed == 'function'){
        tpsocialFeed(ele)
    }
    if(typeof tpsocialReview == 'function'){
        tpsocialReview(ele)
    }
    if(typeof tpsocialShare == 'function'){
        tpsocialShare(ele)
    }
    if(typeof tpstlishList == 'function'){
        tpstlishList(ele)
    }
    if(typeof tpswitcherInit == 'function'){
        tpswitcherInit(ele)
    }
    if(typeof tptablecntInit == 'function'){
        tptablecntInit(ele)
    }
    if(typeof tprowretch == 'function'){
        tprowretch(ele)
    }
    if(typeof tpnorSticky == 'function'){
        tpnorSticky()
    }
    if(typeof tponlyStricky == 'function'){
        tponlyStricky()
    }

    // Free Block Function Call

    if(typeof drawsvgInit == 'function'){
        drawsvgInit(ele)
    }
    if(typeof tpcoForm == 'function'){
        tpcoForm(ele)
    }
    if(typeof tpeverestform == 'function'){
        tpeverestform(ele)
    }
    if(typeof tpgravityForm == 'function'){
        tpgravityForm(ele)
    }
    if(typeof darkmodeInit == 'function'){
        darkmodeInit(ele)
    }
    if(typeof tpdataTable == 'function'){
        tpdataTable(ele)
    }
    if(typeof tpgbwpForm == 'function'){
        tpgbwpForm(ele)
    }
    if(typeof tpheading == 'function'){
        tpheading(ele)
    }
    if(typeof tpinCircle == 'function'){
        tpinCircle(ele)
    }
    if(typeof tpmsgBox == 'function'){
        tpmsgBox(ele)
    }
    if(typeof tpproBar == 'function'){
        tpproBar(ele)
    }
    if(typeof tpproTrack == 'function'){
        tpproTrack(ele)
    }
    if(typeof tpsmScroll == 'function'){
        tpsmScroll(ele)
    }
    if(typeof tpsoIcon == 'function'){
        tpsoIcon(ele)
    }
    if(typeof tabtoursInit == 'function'){
        tabtoursInit(ele)
    }
    if(typeof testimonialInit == 'function'){
        testimonialInit(ele)
    }
    if(typeof postimageInit == 'function'){
        postimageInit(ele)
    }
    if(typeof rowInit == 'function'){
        rowInit(ele)
    }
    if(typeof tpvideoInit == 'function'){
        tpvideoInit(ele)
    }
    if(typeof searchbarInit == 'function'){
        searchbarInit(ele)
    }
    if(typeof tpcodeInit == 'function'){
        tpcodeInit(ele)

        if(typeof Prism.highlightAll == 'function'){
            Prism.highlightAll()
        }
    }
    if(typeof tpgbextraInit == 'function'){
        tpgbextraInit(ele)
    }
    if(typeof tpgbpichart == 'function'){
        tpgbpichart(ele)
    }
    if(typeof tplottieAnim == 'function'){
        tplottieAnim(ele)
    }
    if(typeof tpgnbtnPopup == 'function'){
        tpgnbtnPopup(ele)
    }
    if(typeof tpgbmobileMenu == 'function'){
        tpgbmobileMenu(ele)
    }

    // Row Background Function
    if(typeof tpgbrowimgMove == 'function'){
        tpgbrowimgMove(ele)
    }
    if(typeof tpgbvideoComm == 'function'){
        tpgbvideoComm(ele)
    }
    if(typeof tpgbyouVideo == 'function'){
        tpgbyouVideo(ele)
    }
    if(typeof tpgbselfVideo == 'function'){
        tpgbselfVideo(ele)
    }
    if(typeof tpgbcanvasBg == 'function'){
        tpgbcanvasBg(ele)
    }
    if(typeof tpgbcanvasParti == 'function'){
        tpgbcanvasParti(ele)
    }
    if(typeof tpgbimageParellax == 'function'){
        tpgbimageParellax(ele)
    }
    if(typeof tpgbmodenParellex == 'function'){
        tpgbmodenParellex(ele)
    }
    if(typeof tpgbrowaniBg == 'function'){
        tpgbrowaniBg(ele)
    }
    if(typeof tpgbscrollParallex == 'function'){
        tpgbscrollParallex(ele)
    }
    if(typeof tpgbimgSlide == 'function'){
        tpgbimgSlide(ele)
    }
    if(typeof tpgbbgScroll == 'function'){
        tpgbbgScroll(ele)
    }

    if(typeof numscrollerfun == 'function'){
        numscrollerfun()
    }
}