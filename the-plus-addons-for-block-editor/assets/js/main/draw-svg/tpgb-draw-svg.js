document.addEventListener('DOMContentLoaded', (event) => {
    drawsvgInit(document);
});

function resolveCSSVar(value) {
    if (value && value.startsWith("var(")) {
        const cssVar = value.replace(/var\(|\)/g, "").trim();
        return getComputedStyle(document.documentElement).getPropertyValue(cssVar).trim();
    }
    return value;
}

function drawsvgInit(doc) {
    let drawSVG = doc.querySelectorAll('.tpgb-draw-svg');
    if (drawSVG) {
        drawSVG.forEach((ds) => {
            let data_id = ds.getAttribute("data-id"),
                data_duration =  Math.max(parseInt(ds.getAttribute("data-duration")) || 1, 2),
                data_type = ds.getAttribute("data-type") || 'delayed', 
                data_stroke = ds.getAttribute("data-stroke"),
                fillenable = ds.getAttribute("data-fillenable"),
                fillcolor = ds.getAttribute("data-fillcolor");

            if (data_id) {
                var objectElement = document.getElementById(data_id);

                if (objectElement && objectElement.contentDocument) {
                    var svgDocument = objectElement.contentDocument;

                    if (svgDocument) {
                        var svgElement = svgDocument.querySelector("svg");

                        if (svgElement) {
                            svgElement.setAttribute("width", "100%");
                            svgElement.setAttribute("height", "100%");
                        }
                    }
                }
                let vivusConfig = {
                    type: data_type,
                    duration: data_duration,
                    forceRender: false,
                    start: 'inViewport',
                    delay: 0, 
                    onReady: function (myVivus) {
                        var cAll = myVivus.el.childNodes;
                        var show_id = document.getElementById(data_id);

                        if (fillenable != '' && fillenable == 'yes') {
                            myVivus.el.style.fillOpacity = '0';
                            myVivus.el.style.transition = 'fill-opacity 0s';
                        }

                        show_id.style.opacity = "1";

                        if (data_stroke != '' && cAll != undefined) {
                            const dystoColor = resolveCSSVar(data_stroke);
                            const dyfillColor = resolveCSSVar(fillcolor);

                            for (var i = 0; i < cAll.length; i++) {
                                if (cAll[i].nodeName != '#text') {
                                    cAll[i].setAttribute("fill", dyfillColor);
                                    cAll[i].setAttribute("stroke", dystoColor);
                                    var pchildern = cAll[i].children;
                                    if (pchildern != undefined) {
                                        for (var j = 0; j < pchildern.length; j++) {
                                            pchildern[j].setAttribute("fill", dyfillColor);
                                            pchildern[j].setAttribute("stroke", dystoColor);
                                        }
                                    }
                                }
                            }
                        }
                    }
                };

                new Vivus(data_id, vivusConfig, function (myVivus) {
                    if (myVivus.getStatus() === 'end' && fillenable != '' && fillenable == 'yes') {
                        myVivus.el.style.fillOpacity = '1';
                        myVivus.el.style.transition = 'fill-opacity 1s';
                    }
                });
            }

            if (ds.classList.contains('tpgb-hover-draw-svg')) {
                let svgInner = ds.querySelector(".svg-inner-block");
                if (svgInner) {
                    svgInner.addEventListener('mouseenter', () => {
                        new Vivus(data_id, {
                            type: data_type,
                            duration: data_duration,
                            start: 'manual',
                            delay: 0
                        }).reset().play();
                    })
                }
            }
        });
    }
}