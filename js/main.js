/***********************************************************************
*                         MyLittleJavaScript                           *
************************************************************************
* Created by Michael Loesler <https://github.com/loesler>              *
*                                                                      *
* This script is part of my little forum <https://mylittleforum.net>   *
*                                                                      *
* This program is free software; you can redistribute it and/or modify *
* it under the terms of the GNU General Public License as published by *
* the Free Software Foundation; either version 3 of the License, or    *
* (at your option) any later version.                                  *
*                                                                      *
* This program is distributed in the hope that it will be useful,      *
* but WITHOUT ANY WARRANTY; without even the implied warranty of       *
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        *
* GNU General Public License for more details.                         *
*                                                                      *
* You should have received a copy of the GNU General Public License    *
* along with this program; if not, write to the                        *
* Free Software Foundation, Inc.,                                      *
* 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.            *
***********************************************************************/

/***********************************************************************
* NOTICE: In order to reduce bandwidth usage, a minimized version of   *
* this script is used by default (main.min.js). Changes in this file   *
* do not have any effect unless it is loaded by the template           *
* (themes/[THEME FOLDER]/main.tpl).                                    *
* The minimized version was created with the YUI Compressor            *
***********************************************************************/

/**
 * Liefert die CSS-Eigenschaften eines Elements
 *
 * @param el
 * @param cssProp
 * @return cssValue
 */
document.getStyle = function(el,styleProp) {
	if (el.currentStyle)
		return el.currentStyle[styleProp];
	else if (window.getComputedStyle)
		return document.defaultView.getComputedStyle(el,null).getPropertyValue(styleProp);
	return false;
};

/**
 * Liefert eine Liste mit Elementen, die die
 * selbe CSS-Klasse haben
 *
 * @param class_name
 * @return node_list
 */
if(typeof document.getElementsByClassName != 'function') {  
	document.getElementsByClassName = function (class_name) {
	var all_obj,ret_obj=new Array(),j=0,teststr;
	if(this.all)
		all_obj=this.all;
	else if(this.getElementsByTagName && !this.all)
		all_obj=this.getElementsByTagName("*");
	var len=all_obj.length;
	for(var i=0;i<len;i++) {
		if(all_obj[i].className.indexOf(class_name)!=-1) {
			teststr=","+all_obj[i].className.split(" ").join(",")+",";
			if(teststr.indexOf(","+class_name+",")!=-1) {
				ret_obj[j]=all_obj[i];
				j++;
			}
		} 
	}
	return ret_obj;
	};
}

/**
 * Funktion zum Vorladen von Bildern
 * Sollte am Ende eines ONLOAD aufgerufen werden,
 * sodass das Bildladen das Script nicht blockiert
 *
 * @param images
 * @param path
 */
document.preloadImages = function(images, path) {
	if (typeof images != "object")
		images = [images];
	path = path || "";
	var img = [];
	for(var i = 0; i<images.length; i++) {
		img[i] = new Image();
		img[i].src = path + images[i];
	}
};

/**
 * Liefert das Element, auf dem das Event ausgeloest wurde
 * @return tar
 */
document.getTarget = function(e) {
	e = e || window.event;
	return e.target || e.srcElement || false;
};

/**
 * Prueft, ob ein Element in einem anderen
 * enthalten ist
 * @return conatinsElement
 * @see http://forum.de.selfhtml.org/archiv/2010/2/t195270/#m1306879
 */
if (window.Node && Node.prototype && !Node.prototype.contains) {
	Node.prototype.contains = function (arg) {
		try {
			return !!(this.compareDocumentPosition(arg) & 16);
		}
		catch(e) {
			return false;
		}
	};
}

/**
 * Erzeugt ein INPUT/BUTTON-Element mit zusaetzlichen Attributen
 * Attribute werden als einfaches Objekt uebergeben
 * {"type": "text", "class": "foo"}
 * Optional kann das Elternelement angegeben werden,
 * um das neue Element einzuhaengen
 * Funktion ist notwendig, da der IE (korrekterweise) 
 * das TYPE-Attribut bei diesen Elementen nicht setzt,
 * wenn das Element bereits im DOM ist
 *
 * @param tagName
 * @param att
 * @param par
 * @return el 
 * @see http://forum.de.selfhtml.org/archiv/2007/4/t151146/#m982711
 * @see http://forum.de.selfhtml.org/archiv/2011/3/t204212/#m1382727
 */
document.createInputElementWithAttributes = function(tagName, attributes, parentElement) {
	if (tagName.toLowerCase() != "input" && tagName.toLowerCase() != "button") 
		return document.createElementWithAttributes(tagName, attributes, parentElement);
 
	var type = attributes["type"] || false;
	var name = attributes["name"] || false;
	var el   = false;
 
	if (type) {
		try {
			el = document.createElement(tagName);
			el.type = type;
			if (name)
				el.name = name;
		}
		catch(err) {
			var attr = " type=" + type +(name?" name=" + name : "");
			//el = document.createElement('<'+tagName+' type="'+type+'">');
			el = document.createElement("<" + tagName + attr + ">");
		}
	}
	el = el || document.createElement(tagName);
 
	for (var attribute in attributes)  
		if (attribute.toLowerCase() != "type" && attribute.toLowerCase() != "name")
			el[attribute] = attributes[attribute];
 
	if (parentElement) 
		parentElement.appendChild(el);
 
	return el;
};

/**
 * Erzeugt ein Element mit zusaetzlichen Attributen
 * Attribute werden als einfache Objekte uebergeben
 * {"class": "foo", "href": "#"}
 * Optional kann das Elternelement angegeben werden,
 * um das neue Element einzuhaengen
 *
 * @param tagName
 * @param attributes
 * @param parentElement
 * @return el 
 * @see http://forum.de.selfhtml.org/archiv/2011/3/t204212/#m1382727
 */
document.createElementWithAttributes = function(tagName, attributes, parentElement) {
	if (tagName.toLowerCase() == "input" || tagName.toLowerCase() == "button") 
		return document.createInputElementWithAttributes(tagName, attributes, parentElement);
	
	var el = document.createElement(tagName);
	for (var attribute in attributes) 
		el[attribute] = attributes[attribute];

	if (parentElement) 
		parentElement.appendChild(el);

	return el;
};

/**
 * Liefert die Scroll-Position des aktuellen
 * Fensters
 * @return scrollPos
 * @see http://forum.de.selfhtml.org/archiv/2005/4/t106392/#m659379
 */
document.getScrollPosition = function() {
	var l = 0, t = 0;
	if( typeof window.pageYOffset == "number" ) {
		t = window.pageYOffset;
		l = window.pageXOffset;
	} 
	// else if( document.documentElement && typeof document.documentElement.scrollLeft == "number" && typeof document.documentElement.scrollTop  == "number" ) 
	else if (document.compatMode && document.compatMode == "CSS1Compat") {
		t = document.documentElement.scrollTop;
		l = document.documentElement.scrollLeft;
	} 
	else if( document.body && typeof document.body.scrollLeft == "number" && typeof document.body.scrollTop == "number" ) {
		t = document.body.scrollTop;
		l = document.body.scrollLeft;
	}
	return {
		left: l,
		top: t
	};
};

/**
 * Liefert die Groesse des Dokuments
 * @return docSize
 * @see http://forum.de.selfhtml.org/archiv/2009/1/t181640/
 */
document.getWindowSize = function() {
	var l, t, windowWidth, windowHeight;
	if (window.innerHeight && window.scrollMaxY) {
		l = document.body.scrollWidth;
		t = window.innerHeight + window.scrollMaxY;
	} 
	else if (document.body.scrollHeight > document.body.offsetHeight){
		l = document.body.scrollWidth;
		t = document.body.scrollHeight;
	} 
	else {
		l = document.getElementsByTagName("html").item(0).offsetWidth;
		t = document.getElementsByTagName("html").item(0).offsetHeight;
		l = (l < document.body.offsetWidth) ? document.body.offsetWidth : l;
		t = (t < document.body.offsetHeight) ? document.body.offsetHeight : t;
	}
	if (window.innerHeight) {
		windowWidth  = window.innerWidth;
		windowHeight = window.innerHeight;
	} 
	//else if (document.documentElement && document.documentElement.clientHeight) {
	else if (document.compatMode && document.compatMode == "CSS1Compat") {
		windowWidth  = document.documentElement.clientWidth;
		windowHeight = document.documentElement.clientHeight;
	} 
	else if (document.body) {
		windowWidth  = document.getElementsByTagName("html").item(0).clientWidth;
		windowHeight = document.getElementsByTagName("html").item(0).clientHeight;
		windowWidth  = (windowWidth == 0) ? document.body.clientWidth : windowWidth;
		windowHeight = (windowHeight == 0) ? document.body.clientHeight : windowHeight;
	}
	var pageHeight = (t < windowHeight) ? windowHeight : t;
	var pageWidth = (l < windowWidth) ? windowWidth : l;
	
	return {
		pageWidth: pageWidth,
		pageHeight: pageHeight,
		windowWidth: windowWidth,
		windowHeight: windowHeight
	};
};

/**
 * Liefert den zum Tastendruck gehoerenden Event-Key
 * return keyCode
 */
document.getKeyCode = function(ev) {
	ev = ev || window.event;
	if ((typeof ev.which == "undefined" || (typeof ev.which == "number" && ev.which == 0)) && typeof ev.keyCode  == "number")
		return ev.keyCode;
	else	
		return ev.which;
};

/**
 * Liefert die Position und Groesse eines Elements im Dokument
 * @param el
 * @return elPositionAndSize
 * @see http://www.quirksmode.org/js/findpos.html
 */
document.getElementPoSi = function(el){
    var r = { top:0, left:0, width:0, height:0 };
 
    if(!el || typeof(el) != 'object') 
		return r;
 
    if(typeof(el.offsetTop) != 'undefined')    {
         r.height = el.offsetHeight;
         r.width  = el.offsetWidth;
         r.left   = r.top = 0;
         while(el && el.tagName != 'BODY') {
            r.top  += parseInt( el.offsetTop );
            r.left += parseInt( el.offsetLeft );
			
            el = el.offsetParent;
         }
    }
    return r;
};

/**
 * Liefert das erste direkte Kindelement eines Elternknotens,
 * welches optionale eine bestimmte CSS-Klasse haben muss
 *
 * @param par
 * @param tagName
 * @param cssClasses
 * return el
 */
document.getFirstChildByElement = function(par, tagName, cssClasses) {
	if (cssClasses && typeof(cssClasses) != "object")
		cssClasses = [cssClasses];	
	if (par && par.hasChildNodes()) {
		var childNodeFromPar = par.firstChild;
		while (childNodeFromPar != null) {
			if (childNodeFromPar.nodeName.toLowerCase() == tagName) {
				if (!cssClasses)
					return childNodeFromPar;
				else {
					var teststr = ","+childNodeFromPar.className.split(" ").join(",")+",";
					for (var i=0; i<cssClasses.length; i++)
						if (teststr.indexOf(","+cssClasses[i]+",") != -1)
							return childNodeFromPar;
				}
			}
			childNodeFromPar = childNodeFromPar.nextSibling;
		}
	}
	return null;
};

/**
 * Liefert die Koordinaten 
 * des letzten Maus-Klicks
 * @param e
 * @return pos
 * @see http://forum.de.selfhtml.org/archiv/2006/1/t121722/#m782727
 */
document.getMousePos = function(e) {
	e =  e || window.event;
	var body = (window.document.compatMode && window.document.compatMode == "CSS1Compat") ? 
	window.document.documentElement : window.document.body;
	return {
		top: e.pageY ? e.pageY : e.clientY + body.scrollTop - body.clientTop,
		left: e.pageX ? e.pageX : e.clientX + body.scrollLeft  - body.clientLeft
	};
};

/**
 * Entfernt White-Spaces am Anfang und Ende eines Strings
 * (ist im FF schon drin, daher die Bedingung)
 */
if(typeof String.prototype.trim != "function") { 
	String.prototype.trim = function() {
		return this.replace(/^\s+|\s+$/g,"");
	};
}

/**
 * Liefert true, wenn der String mind. einen Zeilenumbruch enthaelt
 * @return lineBreak
 */
String.prototype.containsLineBreak = function() {
	var newLineRegExp = new RegExp(/(\n|\r|\r\n)./);
	return newLineRegExp.test(this);
}

/**
 * Entfernt Slashes in eimem String vgl. gleichnamige PHP-Funktion
 * @return str
 */
String.prototype.stripslashes = function() {
	var str = this;
	str=str.replace(/\\'/g,'\'');
	str=str.replace(/\\"/g,'"');
	str=str.replace(/\\0/g,'\0');
	str=str.replace(/\\\\/g,'\\');
	return str;
};

/**
 * DragAndDropTable ermoeglicht das Tauschen von 
 * Zeilen (TR) innerhalb von TBODY
 *
 * @param table
 * @see http://www.isocra.com/2007/07/dragging-and-dropping-table-rows-in-javascript/
 */
function DragAndDropTable(table,mode,queryKey) {
	if (!table)
		return;
	var isChanged = false;
	var rows = table.tBodies[0].rows;
	var dragObject = null;
	var oldOnMouseUpFunc   = window.document.onmouseup;
	var oldOnMouseMoveFunc = window.document.onmousemove;
	var tableTop = 0;
	var rowList = [];
	var getLocationQueryByParameter = function(par) {
		var q = window.document.location.search.substring(1).split('&');
		if(!q.length) 
			return false;
		for(var i=0; i<q.length; i++){
			var v = q[i].split('=');
			if (decodeURIComponent(v[0]) == par)
				return v.length>1?decodeURIComponent(v[1]):"";
		}
	};
	
	var saveNewOrder = function() {
		if (!isChanged)
			return;
		var page  = getLocationQueryByParameter(queryKey);
		var order = getRowOrder();
		if (!page || !order)
			return;
		var querys = [
				new Query("mode", mode),
				new Query("action", "reorder"),
				new Query(page,   order)
		];
		new Request("index.php", "POST", querys);
	};
	
	var updateClasses = function() {
		for (var i=0; i<rows.length; i++)
			rows[i].className = (i%2==0)?"a":"b";
	};
	
	var getRowOrder = function() {
		var order = "";
		for (var i=0; i<rows.length; i++)
			if (rows[i].id.length > 3)
				order += rows[i].id.substring(3) + ",";
		return order.substr(0, order.length-1);
	};
	
	var ondrag = function(row) {
		if (!row)
			return;
    };
	
	var ondrop = function(row) {
		if (!row)
			return;
		updateClasses();
		saveNewOrder();
    };
	
	var start = function() {
		window.document.onmousemove = function(e) {
			if (typeof oldOnMouseMoveFunc == "function")
				oldOnMouseMoveFunc(e);
			if (!dragObject)
				return;
			var mPos = document.getMousePos(e);
			var currentTop = mPos.top - dragObject.handlePos.top + dragObject.elementPos.top;
            var currentRow = findDropTargetRow( currentTop );
            if (tableTop != currentTop && currentRow && dragObject != currentRow) {
				var movingDown = currentTop > tableTop;
				tableTop = currentTop;
                
				if (movingDown)
					currentRow = currentRow.nextSibling;
				dragObject.parentNode.insertBefore(dragObject, currentRow);
				isChanged = true;
				ondrag(dragObject);
            }
			
			if(e && e.preventDefault) 
				e.preventDefault();
			return false;
		};
		
		window.document.onmouseup = function (e) {
			window.document.onmouseup = window.document.onmousemove = null;
			if (typeof oldOnMouseUpFunc == "function")
				oldOnMouseUpFunc(e);
			if (typeof oldOnMouseMoveFunc == "function")
				window.document.onmousemove = oldOnMouseMoveFunc;
			ondrop(dragObject);
			dragObject = null;
			isChanged = false;
			return false;
		};
	};
	
	var findDropTargetRow = function(top) {
        for (var i=0; i<rows.length; i++) {
			var rowPoSi = document.getElementPoSi(rows[i]);
			var h = rowPoSi.height;
			if (h == 0 && row[i].firstChild) {
				rowPoSi = document.getElementPoSi(row[i].firstChild);
				h = row[i].firstChild.offsetHeight;
			}
			h /= 2;
			if ((top >= (rowPoSi.top - h)) && (top < (rowPoSi.top + h))) {
				return rows[i];
			}
		}
		return null;
	};
		
	var add = function(row) {
		row.classList.add("js-cursor-move");
		row.title = lang["drag_and_drop_title"];
		row.onmousedown = function(e){
			isChanged = false;
			var obj = document.getTarget(e);
			if (obj && obj.className.search(/control/) != -1)
				return false;
			this.className = "drag";
			this.elementPos = document.getElementPoSi(this);
			this.handlePos  = document.getMousePos(e);
			dragObject = this; 
			start();
			return false;  
		};	
		
		var links = row.cells[row.cells.length-1].getElementsByTagName("a");
		if (links && links.length > 0) {
			for (var i=0; i<links.length; i++) {
				if (links[i].href.search(/move_up/) != -1) 
					links[i].onclick = function(e) {
						row.parentNode.insertBefore(row, rows[Math.max(row.rowIndex-2,0)]);
						isChanged = true;
						updateClasses();
						saveNewOrder();
						return false;
					};
				else if (links[i].href.search(/move_down/) != -1)
					links[i].onclick = function(e) {
						row.parentNode.insertBefore(row, rows[Math.min(row.rowIndex+1, rows.length)]);
						updateClasses();
						isChanged = true;
						saveNewOrder();
						return false;
					};
			}
		}
	};

	(function() {
		for (var i=0; i<rows.length; i++){
			add(rows[i]);
		}
	}());
};

/**
 *
 * Author: Torben Brodt
 * Summary: Cross-browser wrapper for DOMContentLoaded
 * Updated: 07/09/2009
 * License: MIT / GPL
 * Version: 1.1
 *
 * URL:
 * @see http://www.easy-coding.de
 * @see http://jquery.com/dev/svn/trunk/jquery/MIT-LICENSE.txt
 * @see http://jquery.com/dev/svn/trunk/jquery/GPL-LICENSE.txt
 *
 * A page has loaded after all external resources like images have been loaded.
 * Should all scripts wait for that? a better bevaviour is to wait for the dom content being ready.
 *
 * This script has workarounds for all the big browsers meaning the major versions of firefox, internet explorer, opera, safari and chrome.
 * You can use it without risk, since the normal "onload" behavior is the fallback solution.
 *
 * Most of the source is lended from jquery
 */
var ready = new (function () {
	var readyBound = 0, d = document, w = window, t = this, x;
	t.isReady = 0;
	t.readyList = [];
 
	function bindReady() {
		if ( readyBound ) return;
		readyBound = 1;
 
		// Mozilla, Opera and webkit nightlies currently support this event
		if ( d.addEventListener ) {
			// Use the handy event callback
			x = "DOMContentLoaded";
			d.addEventListener( x, function(){
				d.removeEventListener( x, arguments.callee, false );
				ready.ready();
			}, false );
 
		// If IE event model is used
		} else if ( d.attachEvent ) {
			// ensure firing before onload,
			// maybe late but safe also for iframes
			x = "onreadystatechange";
			d.attachEvent(x, function(){
				if ( d.readyState === "complete" ) {
					d.detachEvent( x, arguments.callee );
					ready.ready();
				}
			});
 
			// If IE and not an iframe
			// continually check to see if the document is ready
			if ( d.documentElement.doScroll && w == w.top ) (function(){
				if ( t.isReady ) return;
 
				try {
					// If IE is used, use the trick by Diego Perini
					// [url]http://javascript.nwbox.com/IEContentLoaded/[/url]
					d.documentElement.doScroll("left");
				} catch( error ) {
					setTimeout( arguments.callee, 0 );
					return;
				}
 
				// and execute any waiting functions
				ready.ready();
			})();
		}
 
		// A fallback to window.onload, that will always work
		w.onload = ready.ready; // TODO: compliant? t.event.add( window, "load", t.ready );
	};
 
	// Handle when the DOM is ready
	t.ready = function() {
		// Make sure that the DOM is not already loaded
		if ( !t.isReady ) {
			// Remember that the DOM is ready
			t.isReady = 1;
 
			// If there are functions bound, to execute
			if ( t.readyList ) {
				// Execute all of them
				for(var i=0; i<t.readyList.length; i++) {
					t.readyList[i].call( w, t );
				};
 
				// Reset the list of functions
				t.readyList = null;
			}
 
			// Trigger any bound ready events
			d.loaded = true; // TODO: compliant? this(document).triggerHandler("ready");
		}
	};
 
	// adds funtion to readyList if not ready yet, otherwise call immediately
	t.push = function(fn) {
		// Attach the listeners
		bindReady();
 
		// If the DOM is already ready
		if ( t.isReady )
			// Execute the function immediately
			fn.call( w, t );
 
		// Otherwise, remember the function for later
		else
			// Add the function to the wait list
			t.readyList.push( fn );
 
		return t;
	};
})();	

/************************ MyLittleForum-Objekte *************************************/

	/**
	 *	Erzeugt einen Query als Schluessel-Wert-Paar
	 *	@param k
	 *	@param v
	 */
	function Query(k, v){
		v = v || "";
		var key = k.trim();
		var value = encodeURIComponent(v.toString().trim());
		
		this.toString = function(){
			return key + "=" + value + "&";
		};
	};

	/**
	 * Erzeugt eine Anfrage und uebergibt die Antwort an eine Funktion
	 * als XML oder String
	 *
	 * @param uri
	 * @param m
	 * @param obj
	 * @param func
	 * @param resXML
	 * @param mimeType
	 *
	 */
	function Request(uri,m,q,obj,func,args,resXML,mimeType){
		args = args?(typeof args == "object"||typeof args == "function"?args:[args]):[];
		resXML  = resXML || false;
		mimeType = mimeType?mimeType:resXML?"text/xml":"text/plain";
		obj = obj || null;
		var httpRequest = false;
		try{
			if (window.XMLHttpRequest) 
				httpRequest = new XMLHttpRequest();
				if (httpRequest.overrideMimeType)
					httpRequest.overrideMimeType(mimeType);
			else if (window.ActiveXObject) {
				try {
					httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
				} catch (e) {
					try {
						httpRequest = new ActiveXObject("Microsoft.XMLHTTP");
					} catch (er) {
						httpRequest = false;
					}
				}
			}
		}
		catch(err) {
			httpRequest = false;
		}	
		if (!httpRequest) {
			if (obj && typeof obj[func] == "function") 
				obj[func](false, args);
			return;
		}
		var qStr = "";	
		if (q instanceof Query)
			qStr = q.toString();
		else if((typeof q == "object"||typeof q == "function") && q.length > 0)
			for (var i=0; i<q.length; i++)
				qStr += q[i].toString();
		qStr +=	new Date().getTime();
		
		httpRequest.abort();
		httpRequest.onreadystatechange = function() {
			if (httpRequest.readyState == 4) { 
				if (obj && typeof obj[func] == "function") {
					obj[func]( (resXML?httpRequest.responseXML:httpRequest.responseText), args);				
				}
				httpRequest = false;
			}
		};
		if (m.toLowerCase() == "post"){
			httpRequest.open("POST", uri, true);
			httpRequest.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			httpRequest.send( qStr );
		}
		else {
			httpRequest.open("GET", uri+"?"+qStr, true);
			httpRequest.send(null);
		}
	};
	
	/**
	 * Sidebar-Objekt
	 * @param templatePath
	 */
	function Sidebar(templatePath) {
		templatePath = templatePath || "";
		var main    = document.getElementById("sidebar") || document.getElementById("bottombar") || false;
		var icon    = document.getElementById("sidebartoggle");
		var self    = this;
		if (!main || !icon)
			return;
		this.setVisible = function(visible) {
			if (visible) {
				main.classList.remove("js-display-fold");
				icon.src = templatePath + settings["hide_sidebar_image"];
				icon.classList.remove("show-sidebar");
				icon.classList.add("hide-sidebar");
			}
			else {
				main.classList.add("js-display-fold");
				icon.src = templatePath + settings["show_sidebar_image"];
				icon.classList.remove("hide-sidebar");
				icon.classList.add("show-sidebar");
			}
		};
		this.isVisible = function() {
			return !main.classList.contains("js-display-fold");
		};
		var links = main.getElementsByTagName("a");
		for (var i=0; i<links.length; i++) {
			if (links[i].href.search(/toggle_sidebar/) != -1) {
				links[i].onclick = function(e) {
					self.setVisible(!self.isVisible());
					new Request("index.php", "POST", new Query("toggle_sidebar", true));
					return false;
				}
			}
		}
	};
		
	/**
	 * Erzeugt aus einem UL oder dessen ID ein Thread-Objekt,
	 * welches zum Ein- und Ausklappen des Baums
	 * aufgerufen werden kann
	 * @param ul (UL-Element oder UL-ID)
	 * @param templatePath
	 */
	function Thread(ul, templatePath) {
		var tid = false;
		if (!isNaN( parseInt(ul) )) {
			tid = ul;
			ul = document.getElementById("thread-"+tid);
		}
		else {
			var tidRegExp = new RegExp(/thread-([0-9])+/);
			var q = tidRegExp.exec(ul.id);
			if (!q)
				return;
			tid = q&&q.length>1?q[1]:0;
		}
		var lis = ul.getElementsByTagName("li");
		var uls = ul.getElementsByTagName("ul");
		var self = this;
		var icon = new Image();
		var repliesInfo = null;

		if (ul.parentNode.nodeName != "TD") {
			var tail = document.getFirstChildByElement(lis[0], "span", ["tail"]);
			if (tail && lis.length > 1) {
				repliesInfo = document.getFirstChildByElement(tail, "span", ["replies"]);
				if (!repliesInfo) {
					repliesInfo = document.createElementWithAttributes("span", {"className": "replies"}, tail);
					repliesInfo.appendChild( document.createTextNode( " (" + (lis.length-1) + ")" ) );
				}
			}
		}
		
		this.isFold = function() {
			return uls.length > 0 && uls[0].classList.contains("js-display-none");
		};
		
		this.setFold = function(fold, changeCSS) {
			changeCSS = changeCSS || false;
			
			if (fold) {
				icon.src = templatePath + settings["expand_thread_image"];
				icon.classList.remove("fold-thread");
				icon.classList.add("expand-thread");
				icon.alt = "";
				icon.onerror = function(e) { this.alt = "[+]"; };
				icon.title = lang["expand_fold_thread_linktitle"]; 
				
				if (repliesInfo)
					repliesInfo.classList.remove("js-display-none");
				
				if (changeCSS) {
					ul.classList.remove("expanded");
					ul.classList.add("folded");
				}
			}
			else {
				icon.src = templatePath + settings["fold_thread_image"];
				icon.classList.remove("expand-thread");
				icon.classList.add("fold-thread");
				icon.alt = "";
				icon.onerror = function(e) { this.alt = "[-]"; };
				icon.title = lang["expand_fold_thread_linktitle"]; 
				
				if (repliesInfo)
					repliesInfo.classList.add("js-display-none");
				
				if (changeCSS) {
					ul.classList.remove("folded");
					ul.classList.add("expanded");
				}
			}
			for (var i=0; i<uls.length; i++) {
				if (fold)
					uls[i].classList.add("js-display-none");
				else
					uls[i].classList.remove("js-display-none");
			}
		};
		
		var setIcon = function(el) {
			if (!el)
				return;
			if (lis.length > 0 && lis[0].firstChild) 
				lis[0].insertBefore(el, lis[0].firstChild);
			else
				lis[0].appendChild(el);
		};
		
		var foldExpandWrapper = document.createElementWithAttributes("span", {"className": "fold-expand"}, null);
		
		if (lis.length == 1) {
			var inactiveFoldExpandImg = document.createElementWithAttributes("img", {"src": templatePath + settings["expand_thread_inactive_image"], "className": "expand-thread-inactive", "alt": "", "onerror": function(e) { this.alt = "[]"; } }, foldExpandWrapper)
			setIcon(foldExpandWrapper);  
		}
		else {
			var link = document.createElementWithAttributes("a", {"href": "#", "onclick": function(e) {self.setFold(!self.isFold()); this.blur(); return false;} }, foldExpandWrapper);
			this.setFold(this.isFold());
			link.appendChild(icon);
			setIcon(foldExpandWrapper);
		}
	};
	
	/**
	 * Erzeugt aus einer ID ein Posting, welches ein- und ausgeklappt werden kann
	 * @param pid
	 */
	function Posting(pid) {
		if (!pid)
			return;
		var pWrapper  = document.getElementById("p" + pid);
		var pHeadline = document.getElementById("headline-" + pid);
		if (!pWrapper || !pHeadline)
			return;
		var self = this;
		pHeadline.classList.add("js-cursor-pointer");
		pHeadline.title = lang["fold_posting_title"];
		pHeadline.onclick = function(e) {
			self.setFold(!self.isFold());
		};
		this.isFold = function() {
			return pWrapper.classList.contains("js-display-fold");
		};
		this.setFold = function(fold) {
			if (fold) {
				pWrapper.classList.add("js-display-fold");
			}
			else {
				pWrapper.classList.remove("js-display-fold");
			}
		};
		this.setFold(this.isFold());
	};
	
	
	function FullSizeImage(els) {
		if (!els) return;
		els = (typeof els == "object" || typeof els == "function") && typeof els.length == "number"?els:[els];
		var hashTrigger = null;
		var body = document.body;
		// http://aktuell.de.selfhtml.org/weblog/kompatibilitaetsmodus-im-internet-explorer-8
		var isIELower8 = /*@cc_on!@*/false && !(document.documentMode && document.documentMode >= 8);
   		var imageCanvas = document.getElementById("image-canvas") || document.createElementWithAttributes("div", {"id": "image-canvas"}, body);	
		imageCanvas.setVisible = function(visible) {
			if (visible)
				this.classList.remove("js-display-none");
			else
				this.classList.add("js-display-none");
		};
		var stopTrigger = function() {
			if (hashTrigger) {
				window.clearInterval(hashTrigger);
				var scrollPos = document.getScrollPosition();
				if (!isIELower8)
					window.history.back();
				else
					window.location.hash="GET_OPERA";
				// Fuer den Fall, dass man bei eingeblendeten Bild gescrollt hat
				window.scrollTo(scrollPos.left, scrollPos.top);	
			}
		};
		
		var oldOnKeyPressFunc = window.document.onkeypress;
		window.document.onkeypress = function(e) { 
			var keyCode = document.getKeyCode(e);
			if (keyCode == 27) {
				imageCanvas.setVisible(false);
				stopTrigger();
			}
			if (typeof oldOnKeyPressFunc == "function")
				oldOnKeyPressFunc(e);
		};
		imageCanvas.onclick = function(e) {
			imageCanvas.setVisible(false);
			stopTrigger();
		}; 
		imageCanvas.setVisible(false);			
		var fullSizeImage = document.getElementById("fullSizeImage") || document.createElementWithAttributes("img", {"id": "fullSizeImage"}, imageCanvas);
		for (var i=0; i<els.length; i++) {
			var links = els[i].getElementsByTagName("a");
			for (var j=0; j<links.length; j++) {
				if(links[j].rel.search(/thumbnail/) != -1) {
					links[j].onclick = function(e) {
						window.location.hash="image";
   						var currentHash = window.location.hash;
						fullSizeImage.src = this.href;
						imageCanvas.setVisible(true);
						var imgPoSi = document.getElementPoSi(fullSizeImage);
						var scrollPos = document.getScrollPosition();
						var winSize = document.getWindowSize();							
						imageCanvas.style.height=winSize.pageHeight+"px";
						fullSizeImage.style.marginTop = (scrollPos.top+(winSize.windowHeight-imgPoSi.height)/2) + "px"; 
						
						hashTrigger = window.setInterval( 
							function() {
								if ( this.location.hash != currentHash ) {
									imageCanvas.setVisible(false);
								}
							},50 
						);
						return false;
					};				
				}
			}
		}
	}
		
	/**
	 * Erzeugt anhand eines HTML-Strings ein Geruest fuer
	 * ein Vorschaufenster und haengt dieses im Dokument
	 * ein
	 * @param structure
	 * @param templatePath
	 */
	function AjaxPreviewWindow(structure, templatePath) { 
		templatePath = templatePath?templatePath:"";
		var hideURI = false;
		var pinned  = false;
		var win = document.getElementById('ajax-preview');
		var self = this;
		if (!win) {
			win = document.createElementWithAttributes("div", {"id": "ajax-preview", "className": "js-display-none"}, null);	
			document.body.appendChild( win );
		}
		win.innerHTML = structure.stripslashes().trim();
		var opEl = null;
		var xShift = 0;
		
		var closeEl   = document.getElementById("ajax-preview-close");
		var contentEl = document.getElementById("ajax-preview-content");
		var mainEl    = document.getElementById("ajax-preview-main");	
		
		if (!closeEl || !contentEl || !mainEl)
			console.log("main.js: Fail to init ajax-Elements!");
		
		var oldOnMouseDownFunc = window.document.onmousedown;
		window.document.onmousedown = function(e) { 
			self.closeByOutSideClick(e);	
			if (typeof oldOnMouseDownFunc == "function")
				oldOnMouseDownFunc(e);
		};

		var oldOnKeyPressFunc = window.document.onkeypress;
		window.document.onkeypress = function(e) { 
			var keyCode = document.getKeyCode(e);
			if (keyCode == 27) {
				self.setVisible(false);	
			}
			if (typeof oldOnKeyPressFunc == "function")
				oldOnKeyPressFunc(e);
		};
		
		if (settings["ajax_preview_onmouseover"]) {
			var oldOnMouseOver = window.document.onmouseover;
			window.document.onmouseover = function(e) {
				if (!self.isPinned())
					self.closeByOutSideClick(e);
				if (typeof oldOnMouseOver == "function")
					oldOnMouseOver(e);
			};
		}
		
		closeEl.onclick = function() { self.setVisible(false); return false; };
		var throbberIcon = document.createElementWithAttributes("img", {"id": "ajax-preview-throbber", "src": templatePath + settings["ajax_preview_throbber_image"], "alt": "[*]"}, contentEl);
		var replylinkWrapper = document.createElementWithAttributes("p", {"id": "ajax-preview-replylink-wrapper", "className": "js-display-none"}, contentEl);
		var replylinkLink = document.createElementWithAttributes("a", {"id": "ajax-preview-replylink", "href": "#"}, null);
		replylinkLink.appendChild( document.createTextNode( lang["reply_link"] ));
		
		this.closeByOutSideClick = function(e) {
			var imgCanvas = document.getElementById("image-canvas");
			if (self.isVisible() && imgCanvas && imgCanvas.classList.contains("js-display-none")) {
				var obj = document.getTarget(e);
				if (obj && obj != self.getOpener().firstChild && obj != self.getContentElement() && obj != self.getMainElement()) {
					var evtPos = document.getMousePos(e);
					var posX = evtPos.left;
					var posY = evtPos.top;
					var boxX = self.getDocumentPosition().left;
					var boxY = self.getDocumentPosition().top;
					var boxWidth  = self.getWidth();
					var boxHeight = self.getHeight();
					if ((posX < boxX || posX > (boxX+boxWidth) || posY < boxY || posY > (boxY+boxHeight)) && obj.className != 'ap') {
						self.setVisible(false);
					}
				}
			}
		};
		
		this.pin = function() {
			pinned = !pinned;
		};
		
		this.isPinned = function() {
			return pinned;
		};
		
		this.getContentElement = function() {
			return contentEl;
		};
		
		this.getMainElement = function() {
			return mainEl;
		};
		
		this.hideURI = function(hide) {
			hideURI = hide;
		};
		
		this.setPosition = function(x, y) {
			win.style.left = x + "px";
			win.style.top  = y + "px";
			var winWidth = this.getWidth();	
			var documentWidth = document.getWindowSize().windowWidth;
			if ((x+winWidth) >= documentWidth) {
				this.moveHorizontal( documentWidth-25-(x+winWidth) );			
			}
			else {
				this.moveHorizontal( 0 );	
			}
		};
		
		this.getWidth = function() {
			return mainEl.offsetWidth;
		};
		
		this.getHeight = function() {
			return win.offsetHeight + mainEl.offsetHeight;
		};
		
		this.setOpener = function(op) {
			opEl = op;
		};
		
		this.getOpener = function() {
			return opEl;
		};
		
		this.isVisible = function() {
			return !win.classList.contains("js-display-none");
		};
		
		this.getDocumentPosition = function() {
			var left = win.offsetLeft;
			var top  = win.offsetTop;
			return {
				top: top,
				left: left + xShift
			};
		};
		
		this.moveHorizontal = function(val) {
			xShift = val;
			mainEl.style.left = val + "px";
		};

		this.setVisible = function(visible) {
			if (visible) {
				win.classList.remove("js-display-none");
				win.classList.add("js-display-block");
			}
			else {
				win.classList.remove("js-display-block");
				win.classList.add("js-display-none");
				pinned = false;
			}
		};
		
		this.setText = function(str) {
			contentEl.innerHTML = str;
			if (str != "") {
				if (!replylinkLink.firstChild)
					replylinkLink.appendChild( document.createTextNode( lang["reply_link"] ));
				if (!hideURI) {
					replylinkWrapper.appendChild( replylinkLink );
					contentEl.appendChild( replylinkWrapper );
				}
				new FullSizeImage(contentEl);
			}
			else {
				contentEl.appendChild( throbberIcon );
			}
		};
		
		this.setURI = function(uri) {
			if (!uri) {
				replylinkLink.href = "#";
				replylinkWrapper.classList.remove("js-display-block");
				replylinkWrapper.classList.add("js-display-none");
			}
			else {
				replylinkWrapper.classList.remove("js-display-none");
				replylinkWrapper.classList.add("js-display-block");
				replylinkLink.href = uri;
			}
		};
	}	
	
	/**
	 * Hauptfunktion des Forums
	 */
	function MyLittleJavaScript() {
		var templatePath      = null;
		var ajaxPreviewWindow = null;
		var sidebar           = null;
		var strURL = 'index.php';
		var threads = [];
		var postings = [];
		var regExpFID = new RegExp(/[?|&]id=([0-9]+)(#p([0-9]+))?/);
		var self = this;
		
		/**
		 * Ermittelt die Posting ID aus einer URI
		 * @param link
		 * @return id
		 */
		var getPostingId = function(link) {
			if (link && regExpFID.test(link.href)) {
				var q = regExpFID.exec(link.href);
				return q[3]?q[3]:q[1];
			}
			return false;
		}
		
		/**
		 * Liefert den Pfad zum gewaehlten Template,
		 * welcher aus einem LINK-Element ermittelt wird.
		 * @return path
		 */
		this.getTemplatePath = function() {
			if (templatePath != null)
				return templatePath;
			var el = document.getElementsByTagName("link");
			for (var i=0; i<el.length; i++) {
				if (el[i].rel == "stylesheet") {
					return el[i].href.substring(0, el[i].href.lastIndexOf("/")+1);
				}
			}
			return "";
		};
		
		/**
		 * Erstellt einen Link (mit Bild), der, wenn er geklickt wird,
		 * oeffnet das Vorschaufenster
		 * @param id
		 * @return link
		 */
		var createAjaxPreviewLink = function(id) {
			var link = document.createElementWithAttributes("a", {"pid": id, "title": lang["ajax_preview_title"], "href": strURL+"?id="+id, "onclick": function(e) {self.showAjaxPreviewWindow(this, true); this.blur(); return false; }, "onmouseover": function(e) { if (settings["ajax_preview_onmouseover"]) {self.showAjaxPreviewWindow(this, false); this.blur(); } return false; }, "tabIndex": -1 }, null);
			var img  = document.createElementWithAttributes("img", {"src": templatePath + settings["ajax_preview_image"], "title": lang["ajax_preview_title"], "alt": "", "onload": function(e) { this.alt = "[…]"; }, "onerror": function(e) { this.alt = "[…]"; } }, link);
			return link;
		};
		
		/**
		 * Erzeugt den Link zum Vorschaufenster
		 * im Nutzerprofil, welches einem Element el hinzugefuegt wird
		 * @param el
		 */
		var setPreviewBoxToProfil = function(el) {
			if (!el || !ajaxPreviewWindow)
				return;		
			var pid = getPostingId(el);
			if (pid && el.parentNode) {
				el.parentNode.appendChild( document.createTextNode( String.fromCharCode(160) ) );
				el.parentNode.appendChild( createAjaxPreviewLink(pid) );	
			}
		};
		
		/**
		 * Erzeugt den Link zum Vorschaufenster
		 * auf der Antwortseite, welches einem Element el hinzugefuegt wird
		 * @param el
		 */
		var setPreviewBoxToReplyPage = function(el) {
			if (!el || !ajaxPreviewWindow)
				return;
			ajaxPreviewWindow.hideURI( true );
			var f = document.getElementById("postingform");
			var pid = false;
			if (f && f.elements["id"]) {
				pid = parseInt(f.elements["id"].value);
			}
			if (pid) {
				el.appendChild( document.createTextNode( String.fromCharCode(160) ) );
				el.appendChild( createAjaxPreviewLink(pid)  );	
			}
		};
		
		/**
		 * Markiert ein Posting ADMIN-Funktion
		 * @param param1 id || xml
		 * @param param2 null || args
		 */
		this.selectPosting = function(par1, par2) {
			var isResponse = par2 && (typeof par2 == "object" || typeof par2 == "function") && par2.length > 0;
			var pid = isResponse?par2[0]:par1;
			var xml = isResponse?par1:false;
			var imgEl = null;
			if (!pid || !(imgEl = document.getElementById('markimg_'+pid)))
				return;
				
			imgEl.src = templatePath + settings["mark_process_image"];
			imgEl.alt = '[ ]';

			var querys = [
				new Query("mode", "posting"),
				new Query("mark", pid),
				new Query("method", "ajax")
			];
			if (!isResponse)
				new Request(strURL, "POST", querys, this, "selectPosting", pid, true);
			else if (isResponse && xml && document.getElementById('marklink_'+pid)) {
				var linkEl = document.getElementById('marklink_'+pid);
				var selectPosting = xml.getElementsByTagName('action') && xml.getElementsByTagName('action')[0].firstChild.data == "1";
				if(selectPosting) {
					imgEl.src = templatePath + settings["marked_image"];
					imgEl.alt = '[●]';
					linkEl.title = lang["unmark_linktitle"];
					imgEl.title  = lang["unmark_linktitle"];
				}
				else {
					imgEl.src = templatePath + settings["unmarked_image"];
					imgEl.alt = '[○]';
					linkEl.title = lang["mark_linktitle"];
					imgEl.title  = lang["mark_linktitle"];
				}
			}
		};

		/**
		 * Oeffnet/Schliesst alle Threads
		 * @param expand
		 */
		var expandAllThreads = function(expand) {
			expand = expand || false;
			for (var i=0; i<threads.length; i++) {
				threads[i].setFold(!expand, true);
			}
 
			var querys = [
					new Query("fold_threads", expand ? "0" : "1"),
					new Query("ajax", "true")
			];

			new Request(strURL, "GET", querys);
		};
		
		var initThreadFoldingInSubMenu = function() {
			if (!document.getElementById("subnavmenu"))
				return;
			var menuLinks = document.getElementById("subnavmenu").getElementsByTagName("a");
			var foldingLink = null;
			var foldRegExp = new RegExp(/fold-([0-9])+/);
			for (var i=0; i<menuLinks.length; i++) {
				if (menuLinks[i].className.search( foldRegExp ) != -1) {
					foldingLink = menuLinks[i];
					break;
				}
			}
			
			if (foldingLink) {
				var q = foldRegExp.exec(foldingLink.className);
				var isExpand = q.length>1&&q[1]=="1";

				foldingLink.onclick = function(e) {
					expandAllThreads( !isExpand );
					this.className = this.className.replace(foldRegExp, "fold-" + (isExpand ? 2 : 1) );
					this.firstChild.replaceData(0, this.firstChild.nodeValue.length, (isExpand?lang["expand_threads"]:lang["fold_threads"]) );
					this.title = isExpand?lang["expand_threads_linktitle"]:lang["fold_threads_linktitle"];
					isExpand = !isExpand;
					this.blur();
					return false;
				}
			}
		};
		
		/**
		 * Erzeugt die Links (Sprechblase) zum Vorschaufenster
		 * auf der Forenhauptseite an den gewuenschten Elementen,
		 * sofern das Posting Inhalt besitzt.
		 * @param els
		 */
		var setPreviewBoxToMainPage = function(els) {
			if (!els)
				return;
			
			initThreadFoldingInSubMenu();
				
			for (var i=0; i<els.length; i++) {
				var el = els[i];
				var li = el.parentNode;
				var pLink = document.getFirstChildByElement(li, "a", ["ap", "reply", "thread", "replynew", "threadnew", "thread-sticky", "threadnew-sticky", "reply-search", "thread-search", "thread-locked"]);
				var pEmpty = !!document.getFirstChildByElement(li, "img", ["no-text"]);
				var pid = parseInt( el.id.substring(1) );
				if (!pid) 
					continue;
				
				var links = el.getElementsByTagName("a");
				if (links.length >= 2) {
					for (var j=0; j<links.length; j++) {
						if (links[j].href.search(/mark/) != -1) {
							links[j].pid = pid;
							links[j].onclick = function(e) {
								self.selectPosting( this.pid );
								this.blur();
								return false;
							};							
						}
						else if (links[j].href.search(/delete_posting/) != -1) {
							links[j].onclick = function(e) {
								var confirmed = window.confirm( lang["delete_posting_confirm"] );
								if (confirmed) 
									this.href += '&delete_posting_confirm=true';
								this.blur();
								return confirmed;	
							};
						}				
					}			
				}
				if (!pEmpty && pLink && ajaxPreviewWindow) {
					if (links.length >= 1) {
						var link = links[0];
						el.insertBefore(createAjaxPreviewLink(pid), link);
						el.insertBefore(document.createTextNode( String.fromCharCode(160) ), link);
					}
					else {
						el.appendChild(document.createTextNode( String.fromCharCode(160) ));
						el.appendChild(createAjaxPreviewLink(pid));
					}
				}
				// thread, folded oder expanded - Reicht eigentlich die Suche nach thread?
				if (li.parentNode.className.search(/thread/) != -1 && li.parentNode.className.search(/[folded|expanded]/) != -1) {
					threads.push( new Thread( li.parentNode, templatePath) );
				}
			}
			
			var editAreas = document.getElementsByClassName("options");
			if (editAreas.length > 0) {
				for (var i=0; i<editAreas.length; i++) {
					var links = editAreas[i].getElementsByTagName("a");
					if (links.length > 0) {
						for (var j=0; j<links.length; j++) {
							if (links[j].href.search(/delete_posting/) != -1) {
								links[j].onclick = function(e) {
									var confirmed = window.confirm( lang["delete_posting_confirm"] );
									if (confirmed) 
										this.href += '&delete_posting_confirm=true';
									return confirmed;	
								};
								break;
							}				
						}			
					}
				}
			}

			var pEls = document.getElementsByClassName("posting");
			pEls = pEls.length>0?pEls:document.getElementsByClassName("thread-posting");
			new FullSizeImage(pEls);
		};

		var setDefaultInputValue = function(id) {
			var inp = document.getElementById(id);
			if (!inp) 
				return;
			//var value = inp.value;
			var value = (inp.alt) ? inp.alt : inp.value;
			inp.onfocus = function(e) {
				if (this.value == value) 
					this.value="";
			};
			inp.onblur = function(e) {
				if(this.value.trim() == "") 
					this.value = value;
			};
		};

		/**
		 * Durchsucht Seite nach einem Formular innerhalb von 
		 * >CONTENT< und setzt den Fokus auf das erste INPUT
		 * Trift auf Anmeldung und Antworten zu
		 */
		var setFocusToContentForm = function() {
			if (document.getElementById("content")) {
				var f = document.getElementById("content").getElementsByTagName("form");
				if (f && f.length>0) {
					for (var i=0; i<f[0].elements.length; i++) {
						if (f[0].elements[i].type == "text" && f[0].elements[i].name != "search_user" && f[0].elements[i].name != "smiley_code" && f[0].elements[i].name != "new_category") {
							f[0].elements[i].focus();
							break;
						}
					}
				}
			}
		};
		
		/**
		 * Durchsucht Seite nach Formularen mit Passwort-Feldern im
		 * >CONTENT< Bereich und fuegt eine Checkbox hinter dem 
		 * jeweiligen Passwort-Feld hinzu. Ist die Checkbox ausgewaehlt,
		 * so wird das Passwort im Klartext angezeigt ansonsten nicht.
		 */
		var togglePasswordVisibility = function() {
			if (document.getElementById("content")) {
				var f = document.getElementById("content").getElementsByTagName("form");
				if (f && f.length>0) {
					var passwordFields = [];
					for (var i=0; i<f.length; i++) {
						var fields = f[i].getElementsByTagName("input");
						for (var j=0; j<fields.length; j++) {
							if (fields[j].type == "password") {
								var passwordField = fields[j];
								// lang["fold_postings_title"]
								var cb = document.createElementWithAttributes("input", {"type": "checkbox", "checked": false, "value": false, "field": passwordField, "title": lang["show_password_title"]}, passwordField.parentNode);
								cb.onclick = function(e) {
									var isShown = this.field.type == "text";
									this.value = isShown;
									this.title = isShown ? lang["show_password_title"] : lang["hide_password_title"];
									this.field.type  = isShown ? "password" : "text";
								};
							}
						}
					}
				}
			}
		};
		
		/**
		 * Oeffnet/Schliesst alle Antworten in einem Thread
		 * @param expand
		 */
		var expandAllPostings = function(expand) {
			expand = expand || false;
			for (var i=0; i<postings.length; i++) {
				postings[i].setFold(!expand);
			}
		};
		
		/**
		 * Initialisiert die Option zum Ein/Ausklappen der einzelnen Threads
		 * @param els
		 */
		var initPostingFolding = function(els) {
			// Postings suchen
			if (!els)
				return;
			for (var i=0; i<els.length; i++) {
				var el  = els[i];
				var pid = parseInt( el.id.substring(1) );
				if (!pid) 
					continue;
				postings.push( new Posting(pid) );
			}
			// Menü anpassen
			var menu = null;
			if (postings.length == 0 || !(menu=document.getElementById("subnavmenu")))
				return;
			var listEntry = document.createElementWithAttributes("li", {}, menu);
			var link = document.createElementWithAttributes("a", {"isExpand": true, "title": lang["fold_postings_title"],"href": "#", "className": "fold-postings"}, listEntry);
			link.appendChild( document.createTextNode( lang["fold_postings"] ) );
			link.onclick = function(e) {
				this.isExpand = !this.isExpand;
				expandAllPostings(this.isExpand);  
				this.blur();
				return false;
			}
		};
		
		/**
		 * Initialisiert PopUp-Aufrufe 
		 * bei einem Link
		 */
		var initPopUpLinks = function() {
			var els = [[document.getElementById("terms_of_use") || false, settings["terms_of_use_popup_width"], settings["terms_of_use_popup_height"]],
						[document.getElementById("data_privacy_statement") || false, settings["terms_of_use_popup_width"], settings["terms_of_use_popup_height"]],
						[document.getElementById("edit_avatar") || false, settings["avatar_popup_width"], settings["avatar_popup_height"]]];
						
			for (var i=0; i<els.length; i++) {
				if (els[i][0]) {
					var docSize = document.getWindowSize();
					var w = els[i][1];
					var h = els[i][2];
					var l = parseInt(0.5*(docSize.windowWidth-w));
					var t = parseInt(0.25*(docSize.windowHeight-h));
					els[i][0].onclick = function(e) {
						window.open(this.href,"MyLittleForum","width="+w+",height="+h+",left="+l+",top="+t+",scrollbars,resizable");
						return false;
					};
				}
			}
		};
		
		/**
		 * Tauscht bzw. aktualisiert den Inhalt des Vorschaufensters
		 * @param xml
		 */
		this.updateAjaxPreviewWindow = function(xml) {
			if (xml === false || !ajaxPreviewWindow)
				return;
			var content = xml.getElementsByTagName('content');
			var isLocked = xml.getElementsByTagName('locked');
			
			isLocked = !isLocked?true:isLocked[0].firstChild.data == "1";
			content = !content?"":content[0].firstChild.data;
				
			if (isLocked) 
				ajaxPreviewWindow.setURI(false);

			else if (ajaxPreviewWindow.getOpener() && ajaxPreviewWindow.getOpener().pid)
				ajaxPreviewWindow.setURI("index.php?mode=posting&id=" + ajaxPreviewWindow.getOpener().pid);
  
			if (content.trim() == "") 
				content = "<p>"+lang["no_text"]+"</p>";
				
			ajaxPreviewWindow.setText( content );	
		};
		
		/**
		 * Zeigt das Vorschaufenster an. Erwartet das Objekt, 
		 * welches den Aufruf hervorgerufen hat (Opener), und 
		 * ob das Fesnster geoffnet bleiben soll (pin).
		 * Schliesst das Fenster, wenn auf den selben Opener
		 * erneut geklickt wird.
		 * @param obj
		 * @param pin
		 */
		this.showAjaxPreviewWindow = function(obj, pin) {
			if (!obj || !ajaxPreviewWindow)
				return;

			if (obj == ajaxPreviewWindow.getOpener() && ajaxPreviewWindow.isVisible() && pin) {
				ajaxPreviewWindow.pin();
				if (!ajaxPreviewWindow.isPinned()) {
					ajaxPreviewWindow.setVisible(false);
					ajaxPreviewWindow.setOpener(null);					
				}
			}
			else if (!ajaxPreviewWindow.isPinned()) {
				if (pin && !ajaxPreviewWindow.isPinned())
					ajaxPreviewWindow.pin();
				
				var elPos = document.getElementPoSi(obj);
				ajaxPreviewWindow.setOpener(obj);
				ajaxPreviewWindow.setText("");
				ajaxPreviewWindow.setVisible(true);	
				ajaxPreviewWindow.setPosition( elPos.left, elPos.top );
				var querys = [
								new Query("mode", "entry"),
								new Query("ajax_preview", "true"),
								new Query("id", obj.pid)
				];
				new Request(strURL, "POST", querys, this, "updateAjaxPreviewWindow", null, true);
			}
		};
		
		/**
		 * Liefert das Vorschaufenster
		 * @return win
		 */
		this.getAjaxPreviewWindow = function() {
			return ajaxPreviewWindow;
		}

		/**
		 * Sendet das Formular im Submenue ab, wenn sich der
		 * Wert im Drop-Down-Menue aendert
		 */
		var setAutoSubmitSubNaviForms = function() {
			var subNav = document.getElementById("subnav-2");
			if (subNav) {
				var f = subNav.getElementsByTagName("form");
				for (var i=0; i<f.length; i++) {
					var els = f[i].getElementsByTagName("select");
					for (var j=0; j<els.length; j++) {
						els[j].f = f[i];
						els[j].onchange = function(e) { this.f.submit(); return false; };
					}
				}
			}
		};
		
		/**
		 * Initialisiert MyLittelJavaScript
		 * @param ajaxPreviewStructure
		 */
		this.init = function( ajaxPreviewStructure ) {
			ajaxPreviewStructure = ajaxPreviewStructure || false;
			setFocusToContentForm();
			setDefaultInputValue("search-input");
			setDefaultInputValue("search-user");
			templatePath = this.getTemplatePath();
			if (ajaxPreviewStructure)
				ajaxPreviewWindow = new AjaxPreviewWindow( ajaxPreviewStructure, templatePath );
			setPreviewBoxToProfil( document.getElementById("user-last-posting") );
			setPreviewBoxToReplyPage( document.getElementById("reply-to") );
			setPreviewBoxToMainPage( document.getElementsByClassName("tail") );
			
			initPostingFolding( document.getElementsByClassName("thread-posting") );
			initPopUpLinks();
			setAutoSubmitSubNaviForms();
			sidebar = new Sidebar(templatePath);
			
			togglePasswordVisibility();
			
			if (typeof preload == "object") 
				document.preloadImages(preload, templatePath);		
		};
	
	}
	
	var mlf = null;
	window.ready.push(function() {
		mlf = new MyLittleJavaScript();
		var ajaxPreviewStructure = typeof settings != "undefined" && typeof settings["ajaxPreviewStructure"] == "string"?settings["ajaxPreviewStructure"]:false;
		if (mlf && typeof lang == "object") 
			mlf.init(ajaxPreviewStructure);
		new DragAndDropTable(document.getElementById("sortable"), "bookmarks", "mode");
	});
