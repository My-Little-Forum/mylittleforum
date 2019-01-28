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
* this script is used by default (posting.min.js). Changes in this     *
* file do not have any effect unless it is loaded by the template      *
* (themes/[THEME FOLDER]/main.tpl).                                    *
* The minimized version was created with the YUI Compressor            *
***********************************************************************/

/**
 * Klasse fuer BB-Code Schaltflaechen
 * @param el
 */
function BBCodeButton(el) {
	if (!el) return;
	var buttonGroup = null;
	var self = this;
	var htmlEl = null;

	this.canInsert = function() {
		return buttonGroup && buttonGroup.getTextArea();
	};
	
	this.getCode = function() {
		return htmlEl.name;
	};
	
	this.getButtonGroup = function() {
		return buttonGroup;
	};
	
	this.addButtonGroup = function(group) {
		buttonGroup = group;
	};
	
	this.insertCode = function(obj) {
		if (!this.canInsert()) 
			return;
		var txtarea = buttonGroup.getTextArea();
		var selectionRange = txtarea.getSelection();
		txtarea.insertTextRange( "[" + this.getCode() + "]" + selectionRange + "[/" + this.getCode() + "]" );
	};
	
	this.setHTMLElement = function(el) {
		htmlEl = el;
		htmlEl.onclick = function(e) {
			self.insertCode(this); 
			return false;
		}
	};
	
	this.getHTMLElement = function(el) {
		return htmlEl;
	};
	
	this.setHTMLElement(el);
};

/**
 * Sonderbutton - LINK 
 * @param el
 */
function BBCodeLinkButton(el) {
	this.constructor(el);
	var link_bb_code = "link";
	var regExpURI = new RegExp(/[http|https|ftp|ftps]:\/\/[a-zA-Z0-9-.][a-zA-Z0-9-.]+(S+)?/);
	this.insertCode = function(obj) {
		if (!this.canInsert()) 
			return;
		var buttonGroup = this.getButtonGroup();
		window.setTimeout(function(){ 
			var txtarea = buttonGroup.getTextArea();
			var selectionRange = txtarea.getSelection().trim();
			var insert_link = (regExpURI.test( selectionRange ))?window.prompt(lang["bbcode_link_url"], selectionRange):window.prompt(lang["bbcode_link_url"],"https://");
	
			if (!insert_link || insert_link == '' || insert_link == "https://" || insert_link == "http://" || insert_link == "ftp://" || insert_link == "ftps://") 
				return;
			
			if (selectionRange == '' || regExpURI.test( selectionRange )) 
				selectionRange = window.prompt(lang["bbcode_link_text"], "");
			if (selectionRange != null) {
				if(selectionRange != '')
					txtarea.insertTextRange( "["+link_bb_code+"=" + insert_link + "]" + selectionRange + "[/"+link_bb_code+"]" );
				else
					txtarea.insertTextRange( "["+link_bb_code+"]" + insert_link + "[/"+link_bb_code+"]" );
			}
		}, 150);
    };
};

/**
 * Sonderbutton mit Promt-Box
 * @param el
 * @param quest
 * @param par
 */
function BBCodePromtButton(el, quest, par) {
	this.constructor(el);
	par = par || "";
	this.insertCode = function(obj) {
		if (!this.canInsert()) 
			return;
		var buttonGroup = this.getButtonGroup();
		var code = this.getCode();
		window.setTimeout(function(){
			var txtarea = buttonGroup.getTextArea();
			var selectionRange = txtarea.getSelection().trim();
		
			if (selectionRange == "") {
				var p = window.prompt(quest, par);
				if (p && p.trim() != "" && p.trim() != par ) 
					txtarea.insertTextRange( "[" + code + "]" + p + "[/" + code + "]" );
			}
			else
				txtarea.insertTextRange( "[" + code + "]" + selectionRange + "[/" + code + "]" );
		}, 150);
	};
};

/**
 * Sonderbutton - COLOR
 * @param el
 */
function BBCodeColorChooserButton(el) {
	this.constructor(el);
	var colors = ['#fff','#ccc','#c0c0c0','#999','#666','#333','#000',
                  '#fcc','#f66','#f00','#c00','#900','#600','#300',
                  '#fc9','#f96','#f90','#f60','#c60','#930','#630',
                  '#ff9','#ff6','#fc6','#fc3','#c93','#963','#633',
                  '#ffc','#ff3','#ff0','#fc0','#990','#660','#330',
                  '#9f9','#6f9','#3f3','#3c0','#090','#060','#030',
                  '#9ff','#3ff','#6cc','#0cc','#399','#366','#033',
                  '#cff','#6ff','#3cf','#36f','#33f','#009','#006',
                  '#ccf','#99f','#66c','#63f','#60c','#339','#309',
                  '#fcf','#f9f','#c6c','#c3c','#939','#636','#303'];
	
	
	var colorTable     = document.createElement("table");
	var colorTableBody = document.createElementWithAttributes("tbody", {}, colorTable);
	var self = this;
	var row = document.createElementWithAttributes("tr", {}, colorTableBody);
	for (var i=0; i<colors.length; i++) {
		var cell = document.createElementWithAttributes("td", {}, row);
		cell.style.backgroundColor = colors[i];
		cell.style.width = "15px";
		cell.style.height = "15px";
		cell.style.fontSize = "15px";
		var link = document.createElementWithAttributes("a", {"href": "#", "extension": "="+colors[i], "onclick": function(e) { self.insertOptionCode(this); return false; } }, cell);
		link.classList.add("js-display-block");
		link.appendChild( document.createTextNode( String.fromCharCode(160) ) );
		
		if((i+1)%7==0)
			row = document.createElementWithAttributes("tr", {}, colorTableBody);
	}

	this.insertOptionCode = function(obj) {
		if (!this.canInsert()) 
			return;
		var buttonGroup = this.getButtonGroup();	
		var txtarea = buttonGroup.getTextArea();
		var code = this.getCode();
		txtarea.insertTextRange( "[" + code + obj.extension + "]" + txtarea.getSelection() + "[/" + code + "]" );			
		buttonGroup.getAdditionalOptionsWindow().enableOptionList(false);
	};
	
	this.insertCode = function(obj) {
		if (!this.canInsert()) 
			return;
		var buttonGroup = this.getButtonGroup();
		var objPos = document.getElementPoSi(obj);
		buttonGroup.getAdditionalOptionsWindow().setOptionList(colorTable);
		buttonGroup.getAdditionalOptionsWindow().enableOptionList(true, objPos);	
	};
};

/**
 * Sonderbutton mit zusaetzlichen Optionen
 * @param el
 * @param list
 * @param quest
 * @param par
 */
function BBCodeOptionButton(el, list, quest, par) {
	this.constructor(el);
	if (!list) return;
	quest = quest || false;
	par = par || "";
	var optionList = document.createElement("ul");
	var self = this;
	for (var i=0; i<list.length; i++) {
		var obj = list[i];
		var listElement = document.createElementWithAttributes("li", {}, optionList);
		var link = document.createElementWithAttributes("a", {"href": "#", "attribute": obj.attribute, "onclick": function(e) { self.insertOptionCode(this); return false; } }, listElement);
		link.appendChild( document.createTextNode(obj.label) );
		optionList.appendChild(listElement);
	}
	
	this.insertOptionCode = function(obj) {
		if (!this.canInsert()) 
			return;
		var buttonGroup = this.getButtonGroup();
		// Ausnahme INLINECODE
		var codestart = this.getCode(), codeend = this.getCode();	
		window.setTimeout(function(){
			var txtarea = buttonGroup.getTextArea();
			var selectionRange = txtarea.getSelection();
		
			if (obj.attribute.toLowerCase() == "inlinecode") {
				codestart = codeend = obj.attribute;
			}
			if (obj.attribute.trim() && obj.attribute.toLowerCase() != "inlinecode")
				codestart += "=" + obj.attribute;
			
			if (quest && selectionRange == "") {
				var p = window.prompt(quest, par);
				if (p && p.trim() != "" && p.trim() != par ) 
					txtarea.insertTextRange( "[" + codestart + "]" + p + "[/" + codeend + "]" );
			}	
			else
				txtarea.insertTextRange( "[" + codestart + "]" + selectionRange + "[/" + codeend + "]" );
			
			buttonGroup.getAdditionalOptionsWindow().enableOptionList(false);
		}, 150);
	};
	
	this.insertCode = function(obj) {
		if (!this.canInsert()) 
			return;
		var buttonGroup = this.getButtonGroup();	
		var objPos = document.getElementPoSi(obj);
		buttonGroup.getAdditionalOptionsWindow().setOptionList(optionList);
		buttonGroup.getAdditionalOptionsWindow().enableOptionList(true, objPos);	
	};
};

/**
 * Sonderbutton - LIST
 * @param el
 */
function BBCodeListButton(el) {
	this.constructor(el);
	this.insertCode = function(obj) {
		if (!this.canInsert()) 
			return;
		var buttonGroup = this.getButtonGroup();	
		var txtarea = buttonGroup.getTextArea();
		var selectionRange = txtarea.getSelection();
		var listStr = "";
		var listEntrys = selectionRange.split(/(\n|\r|\r\n)+/);
		for (var i=0; i<listEntrys.length; i++)
			if (listEntrys[i].trim() != "")
				listStr += "\r\n[*]" + listEntrys[i];
		if (listStr.trim() == "")
			listStr = "\r\n[*]...\r\n[*]...\r\n[*]...";
		
		txtarea.insertTextRange( "\r\n[list]" + listStr + "\r\n[/list]\r\n");
	};
};

/**
 * Sonderbutton - einzelnes Smilies
 * @param el
 */
function BBCodeSingleSmilieButton(el) {
	this.constructor(el);
	this.insertCode = function(obj) {
		if (!this.canInsert()) 
			return;
		var buttonGroup = this.getButtonGroup();	
		var txtarea = buttonGroup.getTextArea();
		
		var selectionRange = txtarea.getSelection();
		txtarea.insertTextRange( selectionRange + this.getCode() + " " );
	};
};

/**
 * Sonderbutton - Smilies
 * @param el
 * @param list
 */
function BBCodeSmilieButton(el, list) {
	this.constructor(el, list);
	var self = this;
	//var smilies = document.createElement("div");
	var smilies = document.createElementWithAttributes("div", {"id": "additional-smilies"}, null);
	
	for (var i=0; i<list.length; i++) {
		var link = document.createElementWithAttributes("a", {"href": "#", "title": list[i].title, "code": list[i].code, "onclick": function(e) { self.insertOptionCode(this); return false; } }, smilies);
		link.appendChild( list[i].label );
		//if ((i+1)%5==0)
		//	document.createElementWithAttributes("br", {}, smilies);
		//else
		//smilies.appendChild( document.createTextNode( String.fromCharCode(32) ) );
	}
		
	this.insertOptionCode = function(obj) {
		if (!this.canInsert()) 
			return;
		var buttonGroup = this.getButtonGroup();	
		var txtarea = buttonGroup.getTextArea();
		var code = obj.code;
		txtarea.insertTextRange( txtarea.getSelection() +code + " " );
		buttonGroup.getAdditionalOptionsWindow().enableOptionList(false);
	}
	
	this.insertCode = function(obj) {
		if (!this.canInsert()) 
			return;
		var buttonGroup = this.getButtonGroup();	
		//var txtarea = buttonGroup.getTextArea();
		//selectionRange = txtarea.getSelection();
		var objPos = document.getElementPoSi(obj);
		buttonGroup.getAdditionalOptionsWindow().setOptionList(smilies);
		buttonGroup.getAdditionalOptionsWindow().enableOptionList(true, objPos);	
	};	
};

/**
 * Sonderbutton mit Zusatzfenster
 * @param el
 * @param uri
 * @param width
 * @param height
 */
function BBCodePopUpButton(el, uri, width, height) {
	this.constructor(el);
	
	width  = typeof(width)  != "number"?350:width;
	height = typeof(height) != "number"?350:height;
	
	var left = (screen.width-width)/2;
	var top  = (screen.height-height)/4;
	
	this.insertCode = function(obj) {
		if (!this.canInsert()) 
			return;
		var win = window.open(uri,"MyLittleForum","height="+height+",width="+width+",left="+left+", top="+top+",scrollbars,resizable");
		window.mlfBBCodeButton = this;
		win.focus();
	};
};

/* Vererbung */
BBCodeLinkButton.prototype   = new BBCodeButton;
BBCodeSmilieButton.prototype = new BBCodeButton;
BBCodeSingleSmilieButton.prototype = new BBCodeButton;
BBCodeColorChooserButton.prototype  = new BBCodeButton;
BBCodeOptionButton.prototype  = new BBCodeButton;
BBCodeListButton.prototype    = new BBCodeButton;
BBCodePopUpButton.prototype   = new BBCodeButton;
BBCodePromtButton.prototype  = new BBCodeButton;

/**
 * ButtonGroup, die alle BB-Code-Button verwaltet
 * @param form
 */
function ButtonGroup(f) {
	if (!f) 
		return;

	var textarea = f.elements["text"];
	if (!textarea)
		textarea = document.createElementWithAttributes("textarea", {"name": "text", "id": "text", "cols": 80, "rows": 20}, f);
	
	var hasUserButtons = false;
	var buttons = [];
	
	var additionalOptionsWindow = null;
	var self = this;
		
	/**
	 * Pruefe das Formaular, ob alle notwendigen Felder ausgefuellt sind!
	 * return isComplete
	 */
	f.onsubmit = function(e) {
		var error_message = '';

		if (f.elements['name_required'] && f.elements['name'] && f.elements['name'].value.trim() == '') 
			error_message += "- "+lang["error_no_name"]+"\n";
		
		if (f.elements['subject'] && f.elements['subject'].value.trim() =='')
			error_message += "- "+lang["error_no_subject"]+"\n";
		
		if (f.elements['text_required'] && f.elements['text'] && f.elements['text'].value.trim() == '')
			error_message += "- "+lang["error_no_text"]+"\n";

		if (f.elements['terms_of_use_required'] && f.elements['terms_of_use_agree'] && f.elements['terms_of_use_agree'].checked == false)
			error_message += "- "+lang["terms_of_use_error_posting"]+"\n";
		
		if (error_message) {
			window.alert(error_message);
			return false;
		}
		if (document.getElementById('throbber-submit')) {
			document.getElementById('throbber-submit').classList.remove('js-visibility-hidden');
		}
		return true;
	};
	
	/**
	 * Wandelt die Smilie-Anleitung in klickbare Elemente um
	 */
	var convertInstructionsToSmilies = function() {
		if (!document.getElementById("smiley-bar"))
			return;
		var buttonBar = document.getElementById("smiley-bar");
		if (document.getElementById("smiley-instructions")) {
			var el = document.getElementById("smiley-instructions").firstChild;
			var obj = null;
			var list = [];
			while (el != null) {
				if (el.nodeName && el.nodeName.toLowerCase() == "dt") {
					obj = {
						code    : el.firstChild.nodeValue,
						title   : el.title,
						classes : el.className,
						isSmilie: true,
						childs  : []
					};
				}
				else if (obj && el.nodeName && el.nodeName.toLowerCase() == "dd") {
					obj.label = el.firstChild;
					if (obj.classes.search(/default/) != -1)
						createSingleButton(obj, buttonBar);
					else
						list.push(obj);
					obj = null;
				}				
				el = el.nextSibling;
			}
			if (list && list.length > 0) {
				obj = {
					code    : "",
					title   : lang["more_smilies_title"],
					label   : lang["more_smilies_label"],
					classes : "more-smilies",
					isSmilie: true,
					childs  : list
				};
				createSingleButton(obj, buttonBar);
			}
		}
	}
	
	/**
	 * Wandelt die BB-Code-Anleitung in klickbare Elemente um
	 */
	var convertInstructionsToButton = function() {
		if (!document.getElementById("bbcode-bar"))
			return;
		var buttonBar = document.getElementById("bbcode-bar");
		
		if (document.getElementById("bbcode-instructions")) {
			var el = document.getElementById("bbcode-instructions").firstChild;
			var obj = null;

			while (el != null) {
				if (el.nodeName && el.nodeName.toLowerCase() == "dt") {
					if (obj)
						createSingleButton(obj, buttonBar);
					obj = {
						code    : el.id,
						label   : el.title,
						title   : el.firstChild.nodeValue,
						classes : el.className,
						childs  : []
					};
				}
				else if (obj && el.nodeName && el.nodeName.toLowerCase() == "dd") {
					var attChild = {
						attribute : el.id,
						label : el.title
					}
					obj.childs.push( attChild );
				}
				el = el.nextSibling;
			}
			if (obj)
				createSingleButton(obj, buttonBar);
		}
	};
	
	/**
	 * Fuegt einen BB-Button dem Dokument hinzu.
	 * @param button
	 * @param isUserButton
	 */
	var addButton = function(button, isUserButton) {
		isUserButton = isUserButton || false;
		if (button instanceof BBCodeButton) {
			button.addButtonGroup(self);
			if (isUserButton || !hasUserButtons)
				buttons[ button.getCode() ] = button;
			else if (buttons[ button.getCode() ]) {
				var firstHtmlEl = buttons[ button.getCode() ].getHTMLElement();
				var lastHtmlEl = button.getHTMLElement();
				button.setHTMLElement( firstHtmlEl );
				lastHtmlEl.parentNode.removeChild( lastHtmlEl );
				buttons[ button.getCode() ] = button;
			}
			else if (!isUserButton && hasUserButtons) {
				var htmlEl = button.getHTMLElement();
				htmlEl.parentNode.removeChild( htmlEl );
			}
		}
	}
	
	/**
	 * Erzeugt einen einfachen Klick-Button, der ein SPAN-Element enthaelt
	 * aus einem spezifischen Objekt
	 * @param obj
	 * @param buttonBar
	 */
	var createSingleButton = function(obj, buttonBar) {
		var par = {"className": obj.classes, "name": obj.code, "type": "button", "title": obj.title, "tabIndex": -1};
		var id = obj.code.trim() == ""?"bbcodebutton":"bbcodebutton-"+obj.code;
		if (obj.isSmilie)
			par["isSmilie"] = obj.isSmilie;
		else
			par["id"] = id;
			
		var b = document.createElementWithAttributes("button", par, buttonBar);
		var buttonSpan = document.createElement("span");
		if (typeof obj.label == "string")
			buttonSpan.appendChild(document.createTextNode( obj.label ));
		else
			buttonSpan.appendChild( obj.label );
		b.appendChild(buttonSpan);
		addButton(createBBCodeButton(b, obj.childs));
	};
	
	/**
	 * Erzeugt aus einem normalen Klick-Button ein
	 * BBCodeButton-Objekt (ggf. mit Zusatzoptionen)
	 * @param button
	 * @param list
	 * @return button
	 */
	var createBBCodeButton = function(button, list) {
		var bbCodeButton = null;
		var bname=button&&button.name?button.name.toLowerCase():"";
		//switch(button.name.toLowerCase()) {
		switch(bname) {
		case "link":
			bbCodeButton = new BBCodeLinkButton( button );
		break;
		case "img":
			if (list && list.length > 1)
				bbCodeButton = new BBCodeOptionButton(button, list, lang["bbcode_image_url"], "https://" );
			else
				bbCodeButton = new BBCodePromtButton( button, lang["bbcode_image_url"], "https://" ); 
		break;
		case "color":
			bbCodeButton = new BBCodeColorChooserButton( button );
		break;
		case "list":
			bbCodeButton = new BBCodeListButton( button );
		break;
		case "upload":
			bbCodeButton = new BBCodePopUpButton( button, "index.php?mode=upload_image", settings["upload_popup_width"], settings["upload_popup_height"]); 
		break;
		case "tex":
			bbCodeButton = new BBCodePromtButton( button, lang["bbcode_tex_code"] ); 
		break;
		default:
			if (button.isSmilie && list && list.length > 0)
				bbCodeButton = new BBCodeSmilieButton( button, list );
			else if (button.isSmilie)
				bbCodeButton = new BBCodeSingleSmilieButton( button );
			else if (list && list.length > 1) 
				bbCodeButton = new BBCodeOptionButton( button, list );
			else
				bbCodeButton = new BBCodeButton( button );
		break;
		}
		return bbCodeButton;
	};
		
	/** 
	 * Erzeugt ein Fenster, in dem die Zusatzoptionen
	 * angezeigt werden koennen
	 * @return win
	 */
	var createAdditionalOptionsWindow = function() {
		var w = document.createElementWithAttributes("div", {"id": "bbcode-options"}, document.body);
		var content = document.createElementWithAttributes("div", {}, w);
		w.classList.add("js-display-none");
		var timeout = null;
		
		w.onmouseover = function(e) {
			if (timeout)
				window.clearTimeout(timeout);
		}
		
		w.onmouseout = function(e) {
			e = e || window.event;
			var toElement = e.relatedTarget || e.toElement || false;
			var self = this;
			if (!this.contains(toElement)) {
				timeout = window.setTimeout( function() {
					self.enableOptionList(false);
				}, 125);
			}
			return false;
		};
		
		w.setOptionList = function(list) {
			if (!content.firstChild) 
				content.appendChild(list);
			else
				content.replaceChild(list, content.firstChild);
		};
	
		w.enableOptionList = function(enable, pos) {
			if (pos) {
				this.style.left = pos.left + "px"; 
				this.style.top = pos.top + "px";
			}
			if (enable)
				this.classList.remove("js-display-none");
			else
				this.classList.add("js-display-none");
		};
		
		var oldOnKeyPressFunc = window.document.onmousedown;
		window.document.onkeypress = function(e) { 
			var keyCode = document.getKeyCode(e);
			if (keyCode == 27)
				self.enableOptionList(false);	
				
			if (typeof oldOnKeyPressFunc == "function")
				oldOnKeyPressFunc(e);
		}	
		
		return w;
	};
	
	/**
	 * Sucht nach Button, die der Nutzer
	 * ins Dokument eingefuegt hat
	 * @param isSmilie
	 */
	var initUserBBCodeButtons = function(isSmilie) {
		isSmilie = isSmilie || false;
		hasUserButtons = false;
		var id = isSmilie?"smiley-bar":"bbcode-bar";
		if (!document.getElementById(id)) 
			return;
		var userButtons = document.getElementById(id).getElementsByTagName("button");
		if (userButtons && userButtons.length > 0) {
			for (var i=0; i<userButtons.length; i++) {
				hasUserButtons = true;
				userButtons[i].isSmilie = isSmilie;
				addButton(createBBCodeButton(userButtons[i], null), true);
			}
		}		
	};
	
	/**
	 * Initialisiert die Textarea
	 * und setzt Funktionen zum Ermitteln 
	 * des selektierten Textes
	 */
	var initTextArea = function() {
		// Sichert den (alten) Text in der Area
		textarea.quote = "";
		if (document.getElementById("quote") && document.getElementById("quote").value == "true") {
			textarea.quote = textarea.value;
			textarea.value = "";
		}
			
		textarea.getQuote = function() {
			return textarea.quote.trim();
		}
		
		// Zitieren-Link einfuegen
		if (textarea.getQuote() != "" && document.getElementById("message")) {
			var labels = document.getElementById("message").getElementsByTagName("label");
			var label = null;
			for (var i=0; i<labels.length; i++) { 
				if (labels[i].className.search(/textarea/) != -1) {
					label = labels[i];
					break;
				}
			}
			if (label) {
				label.appendChild( document.createTextNode( String.fromCharCode(160) ) );
				var quoteLink = document.createElementWithAttributes("a", {"onclick": function(e) {textarea.value = textarea.getQuote() + "\r\n\r\n" + textarea.value; this.classList.add("js-display-none"); textarea.focus(); return false;}, "id": "insert-quote", "href": window.location.href, "title": lang["quote_title"], "tabIndex": -1 }, label);
				quoteLink.appendChild( document.createTextNode(lang["quote_label"]) );
			}
		}
		
		textarea.getSelection = function() {
			this.focus();
			if (typeof this.selectionStart == "number" && typeof this.selectionEnd == "number") {
				return this.value.substring(this.selectionStart, this.selectionEnd);
			}
			else if (document.selection && document.selection.createRange) {
				return document.selection.createRange().text;
			}
			else 
				return "";
		};
		
		textarea.insertTextRange = function(range) {
			this.focus();
			if (typeof this.selectionStart != 'undefined') {
				var oldScrollTop = this.scrollTop;
				var s1 = this.value.substring(0, this.selectionStart);
				var s2 = range;
				var s3 = this.value.substring(this.selectionEnd, this.textLength);
				this.value = s1 + range + s3;
				this.selectionStart = s1.length;
				this.selectionEnd = s1.length + range.length;
				this.scrollTop = oldScrollTop;
				this.focus();
			}
			else if (document.selection && document.selection.createRange) {
				var sel = document.selection.createRange();
				sel.text = range;
				sel.select();
			}
			else 
				this.value += range;
		};
	};
	
	/**
	 * Liefert die Textarea
	 * @return area
	 */
	this.getTextArea = function() {
		return textarea;
	};	
	
	/**
	 * Liefert das Option-Window
	 * @return win
	 */
	this.getAdditionalOptionsWindow = function() {
		return additionalOptionsWindow;
	};
		
	var initDeleteCookieLink = function() {
		var span = null;
		if (!(span = document.getElementById("delete_cookie")))
			return;
		var link = document.getFirstChildByElement(span, "a");
		link.onclick = function(e) {
			document.cookie = settings["session_prefix"]+'userdata=; expires=Thu, 01-Jan-70 00:00:01 GMT;';
			span.innerHTML = "";
                        if(f.elements["setcookie"]) f.elements["setcookie"].checked = false;
			return false;
		};		
	};
	
	var removeIntroductionElements = function() {
		if (document.getElementById("bbcode-instructions")) {
			var el = document.getElementById("bbcode-instructions");
			el.parentNode.removeChild(el);
		}
		if (document.getElementById("smiley-instructions")) {
			var el = document.getElementById("smiley-instructions");
			el.parentNode.removeChild(el);
		}
	};

	/**
	 * Initialisiert ButtonGroup
	 *
	 */
	(function() {
		additionalOptionsWindow = createAdditionalOptionsWindow();
		// Erzeuge Textarea
		initTextArea();
		// erstelle HTML-Button und fuege sie ein
		initUserBBCodeButtons(false);
		convertInstructionsToButton();
		initUserBBCodeButtons(true);
		convertInstructionsToSmilies();
		initDeleteCookieLink();
		// entferne die Introduction-Elemente zur Definition der Button
		removeIntroductionElements();
	}());
}

window.ready.push(function() {
	if (typeof settings == "object" && typeof lang == "object") 
		new ButtonGroup( document.getElementById("postingform") );
});