/***********************************************************************
*                         MyLittleJavaScript                           *
************************************************************************
* Created by Michael Loesler <http://derletztekick.com>                *
*                                                                      *
* This script is part of my little forum <http://mylittleforum.net>    *
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
* this script is used by default (admin.min.js). Changes in this file  *
* do not have any effect unless it is loaded by the template           *
* (themes/[THEME FOLDER]/main.tpl).                                    *
* The minimized version was created with the YUI Compressor            *
* <http://developer.yahoo.com/yui/compressor/>.                        *
***********************************************************************/

/**
 * Kleiner Feature im Adminbereich werden
 * von diesem Objekt realisiert.
 * Hauptsaechlich handelt es sich um Sicherheitsabfragen
 */
function MyLittleAdmin() {

	/**
	 * Initialisiert in den globalen Einstellungen
	 * den CSS-Klassenwechsel bei RADIO und CHECKBOX
	 */
	var initGlobalSettings = function() {
		var f = document.getElementById("settings");
		
		if (!f)
			return;
			
		var changeClassName = function(id, active) {
			if (id && document.getElementById(id+"_label"))
				document.getElementById(id+"_label").className = active?"active":"inactive";		
		};
		
		var changeCollectionClassName = function(col) {
			for (var i=0; i<col.length; i++)
				changeClassName(col[i].id, col[i].checked);
		};
		
		for (var i=0; i<f.elements.length; i++) {
			var el = f.elements[i];
			if (el.type == "checkbox" || el.type == "radio") {
				el.onchange = function(e) {
					var els = f.elements[this.name];
					if (els) {
						if (typeof els.length != "number") 
							els = [els];
						changeCollectionClassName(els);
					}
				};
			}
		}
		
	};

	/**
	 * Initialisiert die Backup-Loesch-Abfragen
	 *
	 */
	var initBackupControls = function() {
		var el = document.getElementById("selectioncontrols");
		var f  = document.getElementById("selectform")
		if (!el || !f)
			return;
		var cb = f.elements["delete_backup_files[]"];
		// Elements liefert bei einem Element leider kein Array sondern nur das Element.
		if (cb && typeof cb.length != "number") 
			cb = [cb];
		
		var links = f.getElementsByTagName("a");
		for (var i=0; i<links.length; i++) {
			if (links[i].href.search("delete_backup_files") != -1) {
				links[i].onclick = function(e) {
					var confirmed = window.confirm( lang["delete_backup_confirm"] );
					if (confirmed) 
						this.href += "&delete_backup_files_confirm="+true;
					this.blur();
					return confirmed;	
				};
			}							
		}
		
		var selectAll = function(s) {
			for (var i=0; i<cb.length; i++)
				cb[i].checked = s;
		};
		
		f.onsubmit = function(e) {
			// Pruefe, ob ein File geloescht werden soll
			var c = false;
			for (var i=0; i<cb.length; i++)
				if ((c = cb[i].checked) != false)
					break;
			if (!c)
				return false;
				
			c = window.confirm( lang["delete_sel_backup_confirm"] );
			if (c && this.elements["delete_backup_files_confirm"])
				this.elements["delete_backup_files_confirm"].value = true;
			return c;
		}
		
		var wrapperEl = document.createElementWithAttributes("span", {"className": "checkall"}, el);
		var checkAll  = document.createElementWithAttributes("a", {"onclick": function(e) {selectAll(this.setSelect); return false;}, "href": "#", "setSelect": true}, wrapperEl);
		wrapperEl.appendChild(document.createTextNode(" / "));
		var checkNone = document.createElementWithAttributes("a", {"onclick": function(e) {selectAll(this.setSelect); return false;}, "href": "#", "setSelect": false}, wrapperEl);
		checkAll.appendChild( document.createTextNode( lang["check_all"] ));
		checkNone.appendChild( document.createTextNode( lang["uncheck_all"] ));

	};
	
	
	/**
	 * Initialisiert die moeglichen Admin-Funktionen
	 */
	(function() {
		initGlobalSettings();
		initBackupControls();
	}());
}

/**
 * DragAndDropTable ermoeglicht das Tauschen von 
 * Zeilen (TR) innerhalb von TBODY
 *
 * @param table
 * @see http://www.isocra.com/2007/07/dragging-and-dropping-table-rows-in-javascript/
 */
function DragAndDropTable(table) {
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
		var page  = getLocationQueryByParameter("action");
		var order = getRowOrder();
		if (!page || !order)
			return;
		var querys = [
				new Query("mode",   "admin"),
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
		row.style.cursor = "move";
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
}

window.ready.push(function() {
	new MyLittleAdmin();
	new DragAndDropTable(document.getElementById("sortable"));
});
