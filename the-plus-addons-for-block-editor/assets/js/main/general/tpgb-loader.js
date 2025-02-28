var tpgbJsLoaded = false;
var onTpgbJsLoad = function onTpgbJsLoad() {
  if (tpgbJsLoaded === true) {
    return;
  }
  tpgbJsLoaded = true;

  if(tpgbLoadScripts && tpgbLoadScripts.google_api){
	var tpgbGoogle = document.createElement("script");
	tpgbGoogle.src = '//maps.googleapis.com/maps/api/js?key='+tpgbLoadScripts.google_api+'&sensor=false&callback=tpgbInitMap';
	tpgbGoogle.id = 'gmaps-js';
	document.body.appendChild(tpgbGoogle);
  }
  if(tpgbLoadScripts && tpgbLoadScripts.font_pro){
	var tpgbFont = document.createElement("script");
	tpgbFont.src = 'https://kit.fontawesome.com/'+tpgbLoadScripts.font_pro+'.js';
	tpgbFont.id = 'tpgb-font-awesome-pro';
	document.body.appendChild(tpgbFont);
  }
  if(tpgbLoadScripts && tpgbLoadScripts.lottie){
    var tpgblottie = document.createElement("script");
    tpgblottie.src = tpgbLoadScripts.lottie;
    tpgblottie.id = 'tpgb-lottie-extra-js';
    document.body.appendChild(tpgblottie);
  }
  if(tpgbLoadScripts && tpgbLoadScripts.tpgbLottie){
    setTimeout(() => {
      var tpgbLottieJs = document.createElement("script");
      tpgbLottieJs.src = tpgbLoadScripts.tpgbLottie;
      tpgbLottieJs.id = 'tpgb-lottie-js';
      document.body.appendChild(tpgbLottieJs); 
      setTimeout(() => {
        if( typeof tplottieAnim == 'function' ){
          tplottieAnim(document)
        }
      }, 20);
    }, 150);
  }
  if(tpgbLoadScripts && tpgbLoadScripts.splineviewer){
    setTimeout(() => {
      var tpgbsplineviewerJs = document.createElement("script");
      tpgbsplineviewerJs.src = tpgbLoadScripts.splineviewer;
      tpgbsplineviewerJs.id = 'tpgb-spline3d-js';
      tpgbsplineviewerJs.type="module",
      document.body.appendChild(tpgbsplineviewerJs),
      tpgbsplineviewerJs.onload=()=>{
            let svLoad = document.querySelectorAll('.tpgb-sv-loading');
            if(svLoad){
                svLoad.forEach((sv)=>{
                    sv.style.display = 'none';
                })
            }
        }
    }, 150);
  }
};
document.body.addEventListener("mouseover", onTpgbJsLoad, {once: true});
document.body.addEventListener("touchmove", onTpgbJsLoad, {once: true});
window.addEventListener("scroll", onTpgbJsLoad, {once: true});
document.body.addEventListener("keydown", onTpgbJsLoad, {once: true});