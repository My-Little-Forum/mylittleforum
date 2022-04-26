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
 * Returns the CSS properties, e.g. the text color, of the element
 *
 * @param el
 * @param propertyName
 * @return propertyValue
 */
document.getStyle = function(el,propertyName) {
	return document.defaultView.getComputedStyle(el,null).getPropertyValue(propertyName);
};

/**
 * Pre-load images of a given file path. 
 * Please note: This fuction needs runtime and should, thus, 
 * be called at the end of ONLOAD
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
 * Returns the element, which fires the event
 * @return target
 */
document.getTarget = function(e) {
	e = e || window.event;
	return e.target || e.srcElement || false;
};

/**
 * Checks, if an element contains an element
 * @return contains
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
 * Creates and returns an element with further attributes
 * attributes are specified by an object-list like e.g.
 * {"type": "text", "class": "foo", "name": "bar", "href": "#"}
 * Optionally the parent element can be specified.
 *
 * @param tagName
 * @param attributes
 * @param parentElement
 * @return element
 */
document.createElementWithAttributes = function(tagName, attributes, parentElement) {
	var el = document.createElement(tagName);
	
	for (var attribute in attributes) 
		el[attribute] = attributes[attribute]; //el.setAttribute(attribute, attributes[attribute]); cannot used because function are evaluated in attributes e.g. {"onclick": function(e) { return false; }}

	if (parentElement) 
		parentElement.appendChild(el);

	return el;
};

/**
 * Returns the current scroll position of the window
 * @return [left top]
 * @see https://developer.mozilla.org/en-US/docs/Web/API/Window/scrollY
 */
document.getScrollPosition = function() {
	var l = 0, t = 0;
	if( typeof window.pageYOffset == "number" ) {
		t = window.pageYOffset; // window.scrollY
		l = window.pageXOffset; // window.scrollX
	} 
	return {
		left: l,
		top: t
	};
};

/**
 * Returns an array containing the window size as well as the page size
 * @return [pageWidth, pageHeight, windowWidth, windowHeight]
 */
document.getWindowSize = function() {
	var pageWidth  = document.body.scrollWidth;
	var pageHeight = document.body.scrollHeight;

	var windowWidth  = window.innerWidth;
	var windowHeight = window.innerHeight;

	pageHeight = pageHeight < windowHeight ? windowHeight : pageHeight;
	pageWidth  = pageWidth  < windowWidth  ? windowWidth  : pageWidth;
	
	return {
		pageWidth:    pageWidth,
		pageHeight:   pageHeight,
		windowWidth:  windowWidth,
		windowHeight: windowHeight
	};
};

/**
 * Returns the position and the dimension of an element
 * @param el
 * @return elemPosDim
 * @see http://www.quirksmode.org/js/findpos.html
 */
document.getElementPoSi = function(el){
    var r = { top:0, left:0, width:0, height:0 };
 
    if(!el || typeof(el) != 'object') 
		return r;
 
    if(typeof(el.offsetTop) != 'undefined') {
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
 * Returns the first child element of a parent (optionally: having a css class).
 * If no child exists, the function returns null.
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
 * Returns the coordinates of the mouse event
 * @param e
 * @return position
 * @see http://forum.de.selfhtml.org/archiv/2006/1/t121722/#m782727
 */
document.getMousePosition = function(e) {
	return {
		top: e.pageY,
		left: e.pageX
	};
};

/**
 * Checks, if string A contains string B
 * @param str
 * @return includes
 */
if (typeof String.prototype.includes != "function") {
	String.prototype.includes = function(str) {
		return this.indexOf(str) !== -1;
	};
}

/**
 * Returns true, if the string contains a line break
 * @return lineBreak
 */
String.prototype.containsLineBreak = function() {
	var newLineRegExp = new RegExp(/(\n|\r|\r\n)./);
	return newLineRegExp.test(this);
}

/**
 * Removes slashes of a string like the PHP function stripslashes()
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
 * Drag and drop table, which allows for changing the order of the rows (TR elements) of the TBODY.
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
			var mPos = document.getMousePosition(e);
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
			this.handlePos  = document.getMousePosition(e);
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

/************************ MyLittleForum-Objekte *************************************/

	/**
	 *	Query-Object having a key and a value
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
	 * Create a HTTP request the returned values are handeled by calling a handling function (func)
	 *
	 * @param uri
	 * @param method
	 * @param query
	 * @param obj
	 * @param func
	 * @param resXML
	 * @param mimeType
	 *
	 */
	function Request(uri,method,q,obj,func,args,resXML,mimeType){
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
		if (method.toLowerCase() == "post"){
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
	 * The sidbar object
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
	 * Thread object, which is created by an UL element or the numerical ID of the UL element,
	 * which is used to collapse the tree
	 *
	 * @param ul or id
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
	 * Posting object with collapse property, which is created by a nummeric posting id
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
	
	/**
	 * Create a full-size image object within an element 
	 * @param el
	 */
	function FullSizeImage(els) {
		if (!els) return;
		els = (typeof els == "object" || typeof els == "function") && typeof els.length == "number"?els:[els];
		var hashTrigger = null;
		var body = document.body;
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
				window.history.back();
				// Fuer den Fall, dass man bei eingeblendeten Bild gescrollt hat
				window.scrollTo(scrollPos.left, scrollPos.top);	
			}
		};
		
		var oldOnKeyPressFunc = window.document.onkeypress;
		window.document.onkeypress = function(e) { 
			if (e.key == "Esc") {
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
	 * Preview window for ajax requests to e.g. postings. The window contains the given HTML structure
	 * 
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
			if (e.key == "Esc") {
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
					var evtPos = document.getMousePosition(e);
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
	 * Entry object, to handle link targets of an entry depending on user preferences/forum settings
	 * @param el
	 */
	function Entry(el) {
		if (!el) 
			return;
 
		this.setLinkTarget = function(trg, defaultTarget) {
			var entryBodies = el.getElementsByClassName("body");
			for (var i=0; i<entryBodies.length; i++) {
				var links = entryBodies[i].getElementsByTagName("a");
				for (var j=0; j<links.length; j++) {

					if (trg.toUpperCase() == "NONE") {
						links[j].target = ""; // this is not the default case, because the global forum settings may set _blank
					}
					else if (trg.toUpperCase() == "EXTERNAL" || trg.toUpperCase() == "ALL") {
						// skip internal links
						if (trg.toUpperCase() == "EXTERNAL" && links[j].href.includes(window.document.location.origin))
							continue;
						links[j].target = "_blank";
					}
					else {
						links[j].target = defaultTarget; // default case - forum settings
					}
				}
			}
		};
	}
	
	/**
	 * Main object of the forum
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
		 * Returns the posting id given by the URI
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
		 * Returns the path to the template,
		 * which was extracted by a LINK element.
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
		 * Create a link to open the ajax preview window
		 * @param id
		 * @return link
		 */
		var createAjaxPreviewLink = function(id) {
			var link = document.createElementWithAttributes("a", {"pid": id, "title": lang["ajax_preview_title"], "href": strURL+"?id="+id, "onclick": function(e) {self.showAjaxPreviewWindow(this, true); this.blur(); return false; }, "onmouseover": function(e) { if (settings["ajax_preview_onmouseover"]) {self.showAjaxPreviewWindow(this, false); this.blur(); } return false; }, "tabIndex": -1 }, null);
			var img  = document.createElementWithAttributes("img", {"src": templatePath + settings["ajax_preview_image"], "title": lang["ajax_preview_title"], "alt": "", "onload": function(e) { this.alt = "[…]"; }, "onerror": function(e) { this.alt = "[…]"; } }, link);
			return link;
		};
		
		/**
		 * Set the preview window to the user profile. The link is added to the specified element 
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
		 * Set the preview window to the posting page of an entry. The link is added to the specified element 
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
		 * Marked an posting. Please note: This is an admin function.
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
		 * Collape/expand all threads
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
		 * Add a links to the preview window of the main page, if and only if, the posting contains content.
		 * Links are added to els
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

		/**
		 * Set the default value to an INPUT element
		 */
		var setDefaultInputValue = function(id) {
			var inp = document.getElementById(id);
			if (!inp) 
				return;

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
		 * Set focus to first INPUT element on the page, if exists
		 */
		var setFocusToContentForm = function() {
			var par = document.getElementById("content");
			if (par) {
				var f = par.getElementsByTagName("form");
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
		 * Add a checkbox to a INPUT element of type PASSWORD to show/hid the entered password
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
		 * Collape/expand all replies of a posting
		 * @param expand
		 */
		var expandAllPostings = function(expand) {
			expand = expand || false;
			for (var i=0; i<postings.length; i++) {
				postings[i].setFold(!expand);
			}
		};
		
		/**
		 * Init. option to collape/expand threads
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
		 * Init. pop-up window for e.g. terms of use
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
		 * Refresh content of preview window
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
		 * Set the preview window visible and pin the window, if desired
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
		 * Returns the current preview window
		 * @return win
		 */
		this.getAjaxPreviewWindow = function() {
			return ajaxPreviewWindow;
		}

		/**
		 * Submit the form, if the value of a drop down menu is changed
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
		 * Add target to links in entries depending on user preferences
		 */
		var addUserDefinedLinkTargetInEntries = function() {
			var cEl = document.getElementById("content");
			if (!cEl)
				return;
			
			var trg    = 'DEFAULT';
			var dflTrg = '';
			
			if (typeof user_settings == "object" && typeof user_settings["open_links_in_new_window"] == "string")
				trg = user_settings["open_links_in_new_window"];
			if (typeof settings == "object" && typeof settings["forum_based_link_target"] == "string")
				dflTrg = settings["forum_based_link_target"];
			
			if (trg != 'DEFAULT' || dflTrg != '') {
				var pEls = cEl.getElementsByClassName("posting");
				pEls = pEls.length > 0 ? pEls : cEl.getElementsByClassName("thread-posting");
				pEls = (typeof pEls == "object" || typeof pEls == "function") && typeof pEls.length == "number"?pEls:[pEls];
				for (var i=0; i<pEls.length; i++) {
					var entry = new Entry(pEls[i]);
					entry.setLinkTarget(trg, dflTrg); 
				}
			}
		};
		
		/**
		 * Init. MyLittelJavaScript
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

			addUserDefinedLinkTargetInEntries();
			
			initPostingFolding( document.getElementsByClassName("thread-posting") );
			initPopUpLinks();
			setAutoSubmitSubNaviForms();
			sidebar = new Sidebar(templatePath);
			
			togglePasswordVisibility();
			
			if (typeof preload == "object") 
				document.preloadImages(preload, templatePath);		
		};
	
	}

	document.addEventListener("DOMContentLoaded", function(e) {
		var mlf = new MyLittleJavaScript();
		var ajaxPreviewStructure = typeof settings != "undefined" && typeof settings["ajaxPreviewStructure"] == "string"?settings["ajaxPreviewStructure"]:false;
		if (mlf && typeof lang == "object") 
			mlf.init(ajaxPreviewStructure);
		new DragAndDropTable(document.getElementById("sortable"), "bookmarks", "mode", "admin", "action");
	});
