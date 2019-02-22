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
* this script is used by default (admin.min.js). Changes in this file  *
* do not have any effect unless it is loaded by the template           *
* (themes/[THEME FOLDER]/main.tpl).                                    *
* The minimized version was created with the YUI Compressor            *
***********************************************************************/

/**
 * Kleiner Feature im Adminbereich werden
 * von diesem Objekt realisiert.
 * Hauptsaechlich handelt es sich um Sicherheitsabfragen
 */
function MyLittleAdmin() {

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
		};
		
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
		initBackupControls();
	}());
}

window.ready.push(function() {
	new MyLittleAdmin();
	new DragAndDropTable(document.getElementById("sortable"), "admin", "action");
});
