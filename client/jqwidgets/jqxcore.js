/*
jQWidgets v2.8.1 (2013-Apr-12)
Copyright (c) 2011-2013 jQWidgets.
License: http://jqwidgets.com/license/
*/

(function(a){a.jqx=a.jqx||{};a.jqx.define=function(b,c,d){b[c]=function(){if(this.baseType){this.base=new b[this.baseType]();this.base.defineInstance()}this.defineInstance()};b[c].prototype.defineInstance=function(){};b[c].prototype.base=null;b[c].prototype.baseType=undefined;if(d&&b[d]){b[c].prototype.baseType=d}};a.jqx.invoke=function(e,d){if(d.length==0){return}var f=typeof(d)==Array||d.length>0?d[0]:d;var c=typeof(d)==Array||d.length>1?Array.prototype.slice.call(d,1):a({}).toArray();while(e[f]==undefined&&e.base!=null){e=e.base}if(e[f]!=undefined&&a.isFunction(e[f])){return e[f].apply(e,c)}if(typeof f=="string"){var b=f.toLowerCase();return e[b].apply(e,c)}return};a.jqx.hasProperty=function(c,b){if(typeof(b)=="object"){for(var e in b){var d=c;while(d){if(d.hasOwnProperty(e)){return true}d=d.base}return false}}else{while(c){if(c.hasOwnProperty(b)){return true}c=c.base}}return false};a.jqx.hasFunction=function(e,d){if(d.length==0){return false}if(e==undefined){return false}var f=typeof(d)==Array||d.length>0?d[0]:d;var c=typeof(d)==Array||d.length>1?Array.prototype.slice.call(d,1):{};while(e[f]==undefined&&e.base!=null){e=e.base}if(e[f]&&a.isFunction(e[f])){return true}if(typeof f=="string"){var b=f.toLowerCase();if(e[b]&&a.isFunction(e[b])){return true}}return false};a.jqx.isPropertySetter=function(c,b){if(b.length==1&&typeof(b[0])=="object"){return true}if(b.length==2&&typeof(b[0])=="string"&&!a.jqx.hasFunction(c,b)){return true}return false};a.jqx.validatePropertySetter=function(e,c,b){if(!a.jqx.propertySetterValidation){return true}if(c.length==1&&typeof(c[0])=="object"){for(var d in c[0]){var f=e;while(!f.hasOwnProperty(d)&&f.base){f=f.base}if(!f||!f.hasOwnProperty(d)){if(!b){throw"Invalid property: "+d}return false}}return true}if(c.length!=2){if(!b){throw"Invalid property: "+c.length>=0?c[0]:""}return false}while(!e.hasOwnProperty(c[0])&&e.base){e=e.base}if(!e||!e.hasOwnProperty(c[0])){if(!b){throw"Invalid property: "+c[0]}return false}return true};a.jqx.set=function(c,b){if(b.length==1&&typeof(b[0])=="object"){a.each(b[0],function(d,e){var f=c;while(!f.hasOwnProperty(d)&&f.base!=null){f=f.base}if(f.hasOwnProperty(d)){a.jqx.setvalueraiseevent(f,d,e)}else{if(a.jqx.propertySetterValidation){throw"jqxCore: invalid property '"+d+"'"}}})}else{if(b.length==2){while(!c.hasOwnProperty(b[0])&&c.base){c=c.base}if(c.hasOwnProperty(b[0])){a.jqx.setvalueraiseevent(c,b[0],b[1])}else{if(a.jqx.propertySetterValidation){throw"jqxCore: invalid property '"+b[0]+"'"}}}}};a.jqx.setvalueraiseevent=function(c,d,e){var b=c[d];c[d]=e;if(!c.isInitialized){return}if(c.propertyChangedHandler!=undefined){c.propertyChangedHandler(c,d,b,e)}if(c.propertyChangeMap!=undefined&&c.propertyChangeMap[d]!=undefined){c.propertyChangeMap[d](c,d,b,e)}};a.jqx.get=function(e,d){if(d==undefined||d==null){return undefined}if(e.propertyMap){var c=e.propertyMap(d);if(c!=null){return c}}if(e.hasOwnProperty(d)){return e[d]}var b=undefined;if(typeof(d)==Array){if(d.length!=1){return undefined}b=d[0]}else{if(typeof(d)=="string"){b=d}}while(!e.hasOwnProperty(b)&&e.base){e=e.base}if(e){return e[b]}return undefined};a.jqx.serialize=function(e){var b="";if(a.isArray(e)){b="[";for(var d=0;d<e.length;d++){if(d>0){b+=", "}b+=a.jqx.serialize(e[d])}b+="]"}else{if(typeof(e)=="object"){b="{";var c=0;for(var d in e){if(c++>0){b+=", "}b+=d+": "+a.jqx.serialize(e[d])}b+="}"}else{b=e.toString()}}return b};a.jqx.propertySetterValidation=true;a.jqx.jqxWidgetProxy=function(g,c,b){var d=a(c);var f=a.data(c,g);if(f==undefined){return undefined}var e=f.instance;if(a.jqx.hasFunction(e,b)){return a.jqx.invoke(e,b)}if(a.jqx.isPropertySetter(e,b)){if(a.jqx.validatePropertySetter(e,b)){a.jqx.set(e,b);return undefined}}else{if(typeof(b)=="object"&&b.length==0){return}else{if(typeof(b)=="object"&&b.length==1&&a.jqx.hasProperty(e,b[0])){return a.jqx.get(e,b[0])}else{if(typeof(b)=="string"&&a.jqx.hasProperty(e,b[0])){return a.jqx.get(e,b)}}}}throw"jqxCore: Invalid parameter '"+a.jqx.serialize(b)+"' does not exist.";return undefined};a.jqx.jqxWidget=function(b,d,k){var c=false;try{jqxArgs=Array.prototype.slice.call(k,0)}catch(h){jqxArgs=""}try{c=window.MSApp!=undefined}catch(h){}var g=b;var f="";if(d){f="_"+d}a.jqx.define(a.jqx,"_"+g,f);a.fn[g]=function(){var e=Array.prototype.slice.call(arguments,0);var l=null;if(e.length==0||(e.length==1&&typeof(e[0])=="object")){return this.each(function(){var p=a(this);var o=this;var r=a.data(o,g);if(r==null){r={};r.element=o;r.host=p;r.instance=new a.jqx["_"+g]();if(o.id==""){o.id=a.jqx.utilities.createId()}r.instance.get=r.instance.set=r.instance.call=function(){var s=Array.prototype.slice.call(arguments,0);return a.jqx.jqxWidgetProxy(g,o,s)};a.data(o,g,r);a.data(o,"jqxWidget",r.instance);var q=new Array();var m=r.instance;while(m){m.isInitialized=false;q.push(m);m=m.base}q.reverse();q[0].theme="";a.jqx.jqxWidgetProxy(g,this,e);for(var n in q){m=q[n];if(n==0){m.host=p;m.element=o;m.WinJS=c}if(m!=undefined){if(m.createInstance!=null){if(c){MSApp.execUnsafeLocalFunction(function(){m.createInstance(e)})}else{m.createInstance(e)}}}}for(var n in q){if(q[n]!=undefined){q[n].isInitialized=true}}if(c){MSApp.execUnsafeLocalFunction(function(){r.instance.refresh(true)})}else{r.instance.refresh(true)}l=this}else{a.jqx.jqxWidgetProxy(g,this,e)}})}else{this.each(function(){var m=a.jqx.jqxWidgetProxy(g,this,e);if(l==null){l=m}})}return l};try{a.extend(a.jqx["_"+g].prototype,Array.prototype.slice.call(k,0)[0])}catch(h){}a.extend(a.jqx["_"+g].prototype,{toThemeProperty:function(e,l){if(this.theme==""){return e}if(l!=null&&l){return e+"-"+this.theme}return e+" "+e+"-"+this.theme}});a.jqx["_"+g].prototype.refresh=function(){if(this.base){this.base.refresh()}};a.jqx["_"+g].prototype.createInstance=function(){};a.jqx["_"+g].prototype.propertyChangeMap={};a.jqx["_"+g].prototype.addHandler=function(n,l,e,m){switch(l){case"mousewheel":if(window.addEventListener){if(a.jqx.browser.mozilla){n[0].addEventListener("DOMMouseScroll",e,false)}else{n[0].addEventListener("mousewheel",e,false)}return false}break;case"mousemove":if(window.addEventListener&&!m){n[0].addEventListener("mousemove",e,false);return false}break}if(m==undefined||m==null){if(n.on){n.on(l,e)}else{n.bind(l,e)}}else{if(n.on){n.on(l,m,e)}else{n.bind(l,m,e)}}};a.jqx["_"+g].prototype.removeHandler=function(m,l,e){switch(l){case"mousewheel":if(window.removeEventListener){if(a.jqx.browser.mozilla){m[0].removeEventListener("DOMMouseScroll",e,false)}else{m[0].removeEventListener("mousewheel",e,false)}return false}break;case"mousemove":if(a.jqx.browser.msie&&a.jqx.browser.version>=9){if(window.removeEventListener){m[0].removeEventListener("mousemove",e,false)}}break}if(l==undefined){if(m.off){m.off()}else{m.unbind()}return}if(e==undefined){if(m.off){m.off(l)}else{m.unbind(l)}}else{if(m.off){m.off(l,e)}else{m.unbind(l,e)}}}};a.jqx.utilities=a.jqx.utilities||{};a.extend(a.jqx.utilities,{createId:function(){var b=function(){return(((1+Math.random())*65536)|0).toString(16).substring(1)};return"jqxWidget"+b()+b()+b()},setTheme:function(f,g,e){if(typeof e==="undefined"){return}var h=e[0].className.split(" "),b=[],k=[],d=e.children();for(var c=0;c<h.length;c+=1){if(h[c].indexOf(f)>=0){if(f.length>0){b.push(h[c]);k.push(h[c].replace(f,g))}else{k.push(h[c]+"-"+g)}}}this._removeOldClasses(b,e);this._addNewClasses(k,e);for(var c=0;c<d.length;c+=1){this.setTheme(f,g,a(d[c]))}},_removeOldClasses:function(d,c){for(var b=0;b<d.length;b+=1){c.removeClass(d[b])}},_addNewClasses:function(d,c){for(var b=0;b<d.length;b+=1){c.addClass(d[b])}},getOffset:function(b){var d=a.jqx.mobile.getLeftPos(b[0]);var c=a.jqx.mobile.getTopPos(b[0]);return{top:c,left:d}},html:function(c,d){if(!a(c).on){return a(c).html(d)}try{return jQuery.access(c,function(t){var f=c[0]||{},n=0,k=c.length;if(t===undefined){return f.nodeType===1?f.innerHTML.replace(rinlinejQuery,""):undefined}var s=/<(?:script|style|link)/i,o="abbr|article|aside|audio|bdi|canvas|data|datalist|details|figcaption|figure|footer|header|hgroup|mark|meter|nav|output|progress|section|summary|time|video",h=/<(?!area|br|col|embed|hr|img|input|link|meta|param)(([\w:]+)[^>]*)\/>/gi,q=/<([\w:]+)/,g=/<(?:script|object|embed|option|style)/i,m=new RegExp("<(?:"+o+")[\\s/>]","i"),r=/^\s+/,u={option:[1,"<select multiple='multiple'>","</select>"],legend:[1,"<fieldset>","</fieldset>"],thead:[1,"<table>","</table>"],tr:[2,"<table><tbody>","</tbody></table>"],td:[3,"<table><tbody><tr>","</tr></tbody></table>"],col:[2,"<table><tbody></tbody><colgroup>","</colgroup></table>"],area:[1,"<map>","</map>"],_default:[0,"",""]};if(typeof t==="string"&&!s.test(t)&&(jQuery.support.htmlSerialize||!m.test(t))&&(jQuery.support.leadingWhitespace||!r.test(t))&&!u[(q.exec(t)||["",""])[1].toLowerCase()]){t=t.replace(h,"<$1></$2>");try{for(;n<k;n++){f=this[n]||{};if(f.nodeType===1){jQuery.cleanData(f.getElementsByTagName("*"));f.innerHTML=t}}f=0}catch(p){}}if(f){c.empty().append(t)}},null,d,arguments.length)}catch(b){return a(c).html(d)}},hasTransform:function(d){var c="";c=d.css("transform");if(c==""||c=="none"){c=d.parents().css("transform");if(c==""||c=="none"){var b=a.jqx.utilities.getBrowser();if(b.browser=="msie"){c=d.css("-ms-transform");if(c==""||c=="none"){c=d.parents().css("-ms-transform")}}else{if(b.browser=="chrome"){c=d.css("-webkit-transform");if(c==""||c=="none"){c=d.parents().css("-webkit-transform")}}else{if(b.browser=="opera"){c=d.css("-o-transform");if(c==""||c=="none"){c=d.parents().css("-o-transform")}}else{if(b.browser=="mozilla"){c=d.css("-moz-transform");if(c==""||c=="none"){c=d.parents().css("-moz-transform")}}}}}}else{return c!=""&&c!="none"}}if(c==""||c=="none"){c=a(document.body).css("transform")}return c!=""&&c!="none"&&c!=null},getBrowser:function(){var c=navigator.userAgent.toLowerCase();var b=/(chrome)[ \/]([\w.]+)/.exec(c)||/(webkit)[ \/]([\w.]+)/.exec(c)||/(opera)(?:.*version|)[ \/]([\w.]+)/.exec(c)||/(msie) ([\w.]+)/.exec(c)||c.indexOf("compatible")<0&&/(mozilla)(?:.*? rv:([\w.]+)|)/.exec(c)||[];var d={browser:b[1]||"",version:b[2]||"0"};d[b[1]]=b[1];return d}});a.jqx.browser=a.jqx.utilities.getBrowser();a.jqx.isHidden=function(d){try{if(d.css("display")=="none"){return true}var e=false;var c=d.parents();a.each(c,function(){if(a(this).css("display")=="none"){e=true;return false}});return e}catch(b){return false}};a.jqx.ariaEnabled=true;a.jqx.aria=function(c,e,d){if(!a.jqx.ariaEnabled){return}if(e==undefined){a.each(c.aria,function(g,h){var l=!c.base?c.host.attr(g):c.base.host.attr(g);if(l!=undefined&&!a.isFunction(l)){var k=l;switch(h.type){case"number":k=new Number(l);if(isNaN(k)){k=l}break;case"boolean":k=l=="true"?true:false;break;case"date":k=new Date(l);if(k=="Invalid Date"||isNaN(k)){k=l}break}c[h.name]=k}else{var l=c[h.name];if(a.isFunction(l)){l=c[h.name]()}if(l==undefined){l=""}try{!c.base?c.host.attr(g,l.toString()):c.base.host.attr(g,l.toString())}catch(f){}}})}else{try{if(!c.base){if(c.host){c.host.attr(e,d.toString())}else{c.attr(e,d.toString())}}else{if(c.base.host){c.base.host.attr(e,d.toString())}else{c.attr(e,d.toString())}}}catch(b){}}};if(!Array.prototype.indexOf){Array.prototype.indexOf=function(c){var b=this.length;var d=Number(arguments[1])||0;d=(d<0)?Math.ceil(d):Math.floor(d);if(d<0){d+=b}for(;d<b;d++){if(d in this&&this[d]===c){return d}}return -1}}a.jqx.mobile=a.jqx.mobile||{};a.jqx.position=function(b){var e=parseInt(b.pageX);var d=parseInt(b.pageY);if(a.jqx.mobile.isTouchDevice()){var c=a.jqx.mobile.getTouches(b);var f=c[0];e=parseInt(f.pageX);d=parseInt(f.pageY)}return{left:e,top:d}};a.extend(a.jqx.mobile,{_touchListener:function(h,f){var b=function(k,m){var l=document.createEvent("MouseEvents");l.initMouseEvent(k,m.bubbles,m.cancelable,m.view,m.detail,m.screenX,m.screenY,m.clientX,m.clientY,m.ctrlKey,m.altKey,m.shiftKey,m.metaKey,m.button,m.relatedTarget);l._pageX=m.pageX;l._pageY=m.pageY;return l};var g={mousedown:"touchstart",mouseup:"touchend",mousemove:"touchmove"};var d=b(g[h.type],h);h.target.dispatchEvent(d);var c=h.target["on"+g[h.type]];if(typeof c==="function"){c(h)}},setMobileSimulator:function(c,e){if(this.isTouchDevice()){return}this.simulatetouches=true;if(e==false){this.simulatetouches=false}var d={mousedown:"touchstart",mouseup:"touchend",mousemove:"touchmove"};var b=this;if(window.addEventListener){var f=function(){for(var g in d){if(c.addEventListener){c.removeEventListener(g,b._touchListener);c.addEventListener(g,b._touchListener,false)}}};if(a.jqx.browser.msie){f()}else{window.addEventListener("load",function(){f()},false)}}},isTouchDevice:function(){if(this.touchDevice!=undefined){return this.touchDevice}var b="Browser CodeName: "+navigator.appCodeName+"";b+="Browser Name: "+navigator.appName+"";b+="Browser Version: "+navigator.appVersion+"";b+="Platform: "+navigator.platform+"";b+="User-agent header: "+navigator.userAgent+"";if(b.indexOf("Android")!=-1){return true}if(b.indexOf("IEMobile")!=-1){return true}if(b.indexOf("Windows Phone OS")!=-1){return true}if(b.indexOf("Windows Phone 6.5")!=-1){return true}if(b.indexOf("BlackBerry")!=-1&&b.indexOf("Mobile Safari")!=-1){return true}if(b.indexOf("ipod")!=-1){return true}if(b.indexOf("nokia")!=-1||b.indexOf("Nokia")!=-1){return true}if(b.indexOf("Chrome/17")!=-1){return false}if(b.indexOf("Opera")!=-1&&b.indexOf("Mobi")==-1&&b.indexOf("Mini")==-1&&b.indexOf("Platform: Win")!=-1){return false}if(b.indexOf("Opera")!=-1&&b.indexOf("Mobi")!=-1&&b.indexOf("Opera Mobi")!=-1){return true}var c={ios:"i(?:Pad|Phone|Pod)(?:.*)CPU(?: iPhone)? OS ",android:"(Android |HTC_|Silk/)",blackberry:"BlackBerry(?:.*)Version/",rimTablet:"RIM Tablet OS ",webos:"(?:webOS|hpwOS)/",bada:"Bada/"};try{if(this.touchDevice!=undefined){return this.touchDevice}this.touchDevice=false;for(i in c){if(c.hasOwnProperty(i)){prefix=c[i];match=b.match(new RegExp("(?:"+prefix+")([^\\s;]+)"));if(match){this.touchDevice=true;return true}}}if(navigator.platform.toLowerCase().indexOf("win")!=-1){this.touchDevice=false;return false}document.createEvent("TouchEvent");this.touchDevice=true;return this.touchDevice}catch(d){this.touchDevice=false;return false}},getLeftPos:function(b){var c=b.offsetLeft;while((b=b.offsetParent)!=null){if(b.tagName!="HTML"){c+=b.offsetLeft;if(document.all){c+=b.clientLeft}}}return c},getTopPos:function(b){var c=b.offsetTop;while((b=b.offsetParent)!=null){if(b.tagName!="HTML"){c+=(b.offsetTop-b.scrollTop);if(document.all){c+=b.clientTop}}}if(this.isSafariMobileBrowser()){if(this.isSafari4MobileBrowser()&&this.isIPadSafariMobileBrowser()){return c}c=c+a(window).scrollTop()}return c},isChromeMobileBrowser:function(){var c=navigator.userAgent.toLowerCase();var b=c.indexOf("android")!=-1;return b},isOperaMiniMobileBrowser:function(){var c=navigator.userAgent.toLowerCase();var b=c.indexOf("opera mini")!=-1||c.indexOf("opera mobi")!=-1;return b},isOperaMiniBrowser:function(){var c=navigator.userAgent.toLowerCase();var b=c.indexOf("opera mini")!=-1;return b},isNewSafariMobileBrowser:function(){var c=navigator.userAgent.toLowerCase();var b=c.indexOf("ipad")!=-1||c.indexOf("iphone")!=-1||c.indexOf("ipod")!=-1;b=b&&(c.indexOf("version/5")!=-1);return b},isSafari4MobileBrowser:function(){var c=navigator.userAgent.toLowerCase();var b=c.indexOf("ipad")!=-1||c.indexOf("iphone")!=-1||c.indexOf("ipod")!=-1;b=b&&(c.indexOf("version/4")!=-1);return b},isWindowsPhone:function(){var c=navigator.userAgent.toLowerCase();var b=(c.indexOf("msie 11")!=-1||c.indexOf("msie 10")!=-1)&&c.indexOf("touch")!=-1;return b},isSafariMobileBrowser:function(){var c=navigator.userAgent.toLowerCase();var b=c.indexOf("ipad")!=-1||c.indexOf("iphone")!=-1||c.indexOf("ipod")!=-1;return b},isIPadSafariMobileBrowser:function(){var c=navigator.userAgent.toLowerCase();var b=c.indexOf("ipad")!=-1;return b},isMobileBrowser:function(){var c=navigator.userAgent.toLowerCase();var b=c.indexOf("ipad")!=-1||c.indexOf("iphone")!=-1||c.indexOf("android")!=-1;return b},getTouches:function(b){if(b.originalEvent){if(b.originalEvent.touches&&b.originalEvent.touches.length){return b.originalEvent.touches}else{if(b.originalEvent.changedTouches&&b.originalEvent.changedTouches.length){return b.originalEvent.changedTouches}}}if(!b.touches){b.touches=new Array();b.touches[0]=b.originalEvent!=undefined?b.originalEvent:b;if(b.originalEvent!=undefined&&b.pageX){b.touches[0]=b}if(b.type=="mousemove"){b.touches[0]=b}}return b.touches},getTouchEventName:function(b){if(this.isWindowsPhone()){if(b.toLowerCase().indexOf("start")!=-1){return"MSPointerDown"}if(b.toLowerCase().indexOf("move")!=-1){return"MSPointerMove"}if(b.toLowerCase().indexOf("end")!=-1){return"MSPointerUp"}}else{return b}},dispatchMouseEvent:function(b,f,d){if(this.simulatetouches){return}var c=document.createEvent("MouseEvent");c.initMouseEvent(b,true,true,f.view,1,f.screenX,f.screenY,f.clientX,f.clientY,false,false,false,false,0,null);if(d!=null){d.dispatchEvent(c)}},getRootNode:function(b){while(b.nodeType!==1){b=b.parentNode}return b},setTouchScroll:function(b,c){if(!this.enableScrolling){this.enableScrolling=[]}this.enableScrolling[c]=b},touchScroll:function(c,x,f,C){if(c==null){return}var A=this;var s=0;var h=0;var k=0;var t=0;var l=0;var m=0;if(!this.scrolling){this.scrolling=[]}this.scrolling[C]=false;var g=false;var p=a(c);var u=["select","input","textarea"];var b=0;var d=0;if(!this.enableScrolling){this.enableScrolling=[]}this.enableScrolling[C]=true;var C=C;var B=this.getTouchEventName("touchstart")+".touchScroll";var o=this.getTouchEventName("touchend")+".touchScroll";var z=this.getTouchEventName("touchmove")+".touchScroll";var b=function(D){if(!A.enableScrolling[C]){return true}if(a.inArray(D.target.tagName.toLowerCase(),u)!==-1){return}var E=A.getTouches(D);var F=E[0];if(E.length==1){A.dispatchMouseEvent("mousedown",F,A.getRootNode(F.target))}g=false;h=F.pageY;l=F.pageX;if(A.simulatetouches){h=F._pageY;l=F._pageX}A.scrolling[C]=true;s=0;t=0;return true};if(p.on){p.on(B,b)}else{p.bind(B,b)}var w=function(H){if(!A.enableScrolling[C]){return true}if(!A.scrolling[C]){return true}var I=A.getTouches(H);if(I.length>1){return true}var F=I[0].pageY;var G=I[0].pageX;if(A.simulatetouches){F=I[0]._pageY;G=I[0]._pageX}var D=F-h;var E=G-l;d=F;touchHorizontalEnd=G;k=D-s;m=E-t;g=true;s=D;t=E;f(-m*3,-k*3,E,D,H);H.preventDefault();H.stopPropagation();if(H.preventManipulation){H.preventManipulation()}return false};if(p.on){p.on(z,w)}else{p.bind(z,w)}if(this.simulatetouches){var n=a(window).on!=undefined||a(window).bind;var y=function(D){A.scrolling[C]=false};a(window).on!=undefined?a(window).on("mouseup.touchScroll",y):a(window).bind("mouseup.touchScroll",y);if(window.frameElement){if(window.top!=null){var q=function(D){A.scrolling[C]=false};if(window.top.document){a(window.top.document).on?a(window.top.document).on("mouseup",q):a(window.top.document).bind("mouseup",q)}}}var r=a(document).on!=undefined||a(document).bind;var v=function(D){if(!A.scrolling[C]){return true}A.scrolling[C]=false;var F=A.getTouches(D)[0],E=A.getRootNode(F.target);A.dispatchMouseEvent("mouseup",F,E);A.dispatchMouseEvent("click",F,E)};a(document).on!=undefined?a(document).on("touchend",v):a(document).bind("touchend",v)}var e=function(D){if(!A.enableScrolling[C]){return true}var F=A.getTouches(D)[0];if(!A.scrolling[C]){return true}A.scrolling[C]=false;if(g){A.dispatchMouseEvent("mouseup",F,E)}else{var F=A.getTouches(D)[0],E=A.getRootNode(F.target);A.dispatchMouseEvent("mouseup",F,E);A.dispatchMouseEvent("click",F,E);return true}};p.on?p.on(o+" touchcancel.touchScroll",e):p.bind(o+" touchcancel.touchScroll",e)}});a.jqx.cookie=a.jqx.cookie||{};a.extend(a.jqx.cookie,{cookie:function(e,f,c){if(arguments.length>1&&String(f)!=="[object Object]"){c=jQuery.extend({},c);if(f===null||f===undefined){c.expires=-1}if(typeof c.expires==="number"){var h=c.expires,d=c.expires=new Date();d.setDate(d.getDate()+h)}f=String(f);return(document.cookie=[encodeURIComponent(e),"=",c.raw?f:encodeURIComponent(f),c.expires?"; expires="+c.expires.toUTCString():"",c.path?"; path="+c.path:"",c.domain?"; domain="+c.domain:"",c.secure?"; secure":""].join(""))}c=f||{};var b,g=c.raw?function(k){return k}:decodeURIComponent;return(b=new RegExp("(?:^|; )"+encodeURIComponent(e)+"=([^;]*)").exec(document.cookie))?g(b[1]):null}});a.jqx.string=a.jqx.string||{};a.extend(a.jqx.string,{contains:function(b,c){if(b==null||c==null){return false}return b.indexOf(c)!=-1},containsIgnoreCase:function(b,c){if(b==null||c==null){return false}return b.toUpperCase().indexOf(c.toUpperCase())!=-1},equals:function(b,c){if(b==null||c==null){return false}b=this.normalize(b);if(c.length==b.length){return b.slice(0,c.length)==c}return false},equalsIgnoreCase:function(b,c){if(b==null||c==null){return false}b=this.normalize(b);if(c.length==b.length){return b.toUpperCase().slice(0,c.length)==c.toUpperCase()}return false},startsWith:function(b,c){if(b==null||c==null){return false}return b.slice(0,c.length)==c},startsWithIgnoreCase:function(b,c){if(b==null||c==null){return false}return b.toUpperCase().slice(0,c.length)==c.toUpperCase()},normalize:function(b){if(b.charCodeAt(b.length-1)==65279){b=b.substring(0,b.length-1)}return b},endsWith:function(b,c){if(b==null||c==null){return false}b=this.normalize(b);return b.slice(-c.length)==c},endsWithIgnoreCase:function(b,c){if(b==null||c==null){return false}b=this.normalize(b);return b.toUpperCase().slice(-c.length)==c.toUpperCase()}});a.extend(jQuery.easing,{easeOutBack:function(f,g,e,l,k,h){if(h==undefined){h=1.70158}return l*((g=g/k-1)*g*((h+1)*g+h)+1)+e},easeInQuad:function(f,g,e,k,h){return k*(g/=h)*g+e},easeInOutCirc:function(f,g,e,k,h){if((g/=h/2)<1){return -k/2*(Math.sqrt(1-g*g)-1)+e}return k/2*(Math.sqrt(1-(g-=2)*g)+1)+e},easeInOutSine:function(f,g,e,k,h){return -k/2*(Math.cos(Math.PI*g/h)-1)+e},easeInCubic:function(f,g,e,k,h){return k*(g/=h)*g*g+e},easeOutCubic:function(f,g,e,k,h){return k*((g=g/h-1)*g*g+1)+e},easeInOutCubic:function(f,g,e,k,h){if((g/=h/2)<1){return k/2*g*g*g+e}return k/2*((g-=2)*g*g+2)+e},easeInSine:function(f,g,e,k,h){return -k*Math.cos(g/h*(Math.PI/2))+k+e},easeOutSine:function(f,g,e,k,h){return k*Math.sin(g/h*(Math.PI/2))+e},easeInOutSine:function(f,g,e,k,h){return -k/2*(Math.cos(Math.PI*g/h)-1)+e}})})(jQuery);(function(b){b.fn.extend({ischildof:function(d){var c=b(this).parents().get();for(j=0;j<c.length;j++){if(b(c[j]).is(d)){return true}}return false}});var a=this.originalVal=b.fn.val;b.fn.val=function(d){if(typeof d=="undefined"){if(b(this).hasClass("jqx-widget")){var c=b(this).data().jqxWidget;if(c&&c.val){return c.val()}}return a.call(this)}else{if(b(this).hasClass("jqx-widget")){var c=b(this).data().jqxWidget;if(c&&c.val){if(arguments.length!=2){return c.val(d)}else{return c.val(d,arguments[1])}}}return a.call(this,d)}};b.fn.coord=function(d){var c,k,g={top:0,left:0},f=this[0],h=f&&f.ownerDocument;if(!h){return}c=h.documentElement;if(!jQuery.contains(c,f)){return g}if(typeof f.getBoundingClientRect!==undefined){g=f.getBoundingClientRect()}var e=function(l){return jQuery.isWindow(l)?l:l.nodeType===9?l.defaultView||l.parentWindow:false};k=e(h);return{top:g.top+(k.pageYOffset||c.scrollTop)-(c.clientTop||0),left:g.left+(k.pageXOffset||c.scrollLeft)-(c.clientLeft||0)}}})(jQuery);