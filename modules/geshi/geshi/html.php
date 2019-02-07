<?php
/*************************************************************************************
 * html5.php
 * ---------------
 * Author: Nigel McNie (nigel@geshi.org)
 * Copyright: (c) 2004 Nigel McNie (http://qbnz.com/highlighter/)
 * Release Version: 1.0.9.0
 * Date Started: 2004/07/10
 *
 * HTML 5 language file for GeSHi.
 *
 * CHANGES
 * -------
 * 2005/12/28 (1.0.4)
 *   -  Removed escape character for strings
 * 2004/11/27 (1.0.3)
 *   -  Added support for multiple object splitters
 * 2004/10/27 (1.0.2)
 *   -  Added support for URLs
 * 2004/08/05 (1.0.1)
 *   -  Added INS and DEL
 *   -  Removed the background colour from tags' styles
 * 2004/07/14 (1.0.0)
 *   -  First Release
 *
 * TODO (updated 2004/11/27)
 * -------------------------
 * * Check that only HTML4 strict attributes are highlighted
 * * Eliminate empty tags that aren't allowed in HTML4 strict
 * * Split to several files - html4trans, xhtml1 etc
 *
 *************************************************************************************
 *
 *     This file is part of GeSHi.
 *
 *   GeSHi is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   GeSHi is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with GeSHi; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 ************************************************************************************/

$language_data = array (
    'LANG_NAME' => 'HTML5',
    'COMMENT_SINGLE' => array(),
    'COMMENT_MULTI' => array(),
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    'QUOTEMARKS' => array("'", '"'),
    'ESCAPE_CHAR' => '',
    'KEYWORDS' => array(
        2 => array(
            'a', 'abbr', 'address', 'area', 'article', 'aside', 'audio',

            'b', 'base', 'bdi', 'bdo', 'blockquote', 'body', 'br', 'button',

            'canvas', 'caption', 'cite', 'code', 'col', 'colgroup', 'command',

            'data', 'datalist', 'dd', 'del', 'details', 'dfn', 'div', 'dl', 'dt',

            'em', 'embed',

            'fieldset', 'figcaption', 'figure', 'footer', 'form',

            'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'head', 'header', 'hr', 'html',

            'i', 'iframe', 'img', 'input', 'ins',

            'kbd', 'keygen',

            'label', 'legend', 'li', 'link',

            'main', 'map', 'mark', 'math', 'meta', 'meter',

            'nav', 'noscript',

            'object', 'ol', 'optgroup', 'option', 'output',

            'p', 'param', 'picture', 'pre', 'progress',

            'q',

            'rp', 'rt', 'ruby',

            's', 'samp', 'script', 'section', 'select', 'slot', 'small', 'source', 'span', 'strong', 'style', 'sub', 'summary', 'sup', 'svg',

            'table', 'tbody', 'td', 'template', 'textarea', 'tfoot', 'th', 'thead', 'time', 'title', 'tr', 'track',

            'u', 'ul',

            'var', 'video',

            'wbr',
            ),
        3 => array(
            'abbr', 'accept', 'accept-charset', 'accesskey', 'action', 'alt', 'async', 'autocomplete', 'autofocus', 'autoplay', 'autosave',
            'buffered',
            'charset', 'charset', 'checked', 'cite', 'class',  'cols', 'colspan', 'content', 'contenteditable', 'contextmenu', 'controls', 'coords', 'crossorigin',
            'data', 'datetime', 'default', 'defer', 'dir', 'dirname', 'disabled', 'download', 'draggable', 'dropzone',
            'enctype',
            'for', 'form', 'formaction', 'formenctype', 'formmethod', 'formnovalidate', 'formtarget',
            'headers', 'height', 'hidden', 'high', 'href', 'hreflang', 'http-equiv',
            'icon', 'id', 'ismap', 'itemid', 'itemprop', 'itemref', 'itemscope', 'itemtype',
            'keytype', 'kind',
            'label', 'lang', 'language', 'list', 'low',
            'manifest', 'max', 'maxlength', 'media', 'mediagroup', 'method', 'min', 'multiple', 'muted',
            'name', 'novalidate',
            'object', 'onblur', 'onchange', 'onclick', 'ondblclick', 'onfocus', 'onkeydown', 'onkeypress', 'onkeyup', 'onload', 'onmousedown', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onselect', 'onsubmit', 'onunload', 'onafterprint', 'onbeforeprint', 'onbeforeonload', 'onerror', 'onhaschange', 'onmessage', 'onoffline', 'ononline', 'onpagehide', 'onpageshow', 'onpopstate', 'onredo', 'onresize', 'onstorage', 'onundo', 'oncontextmenu', 'onformchange', 'onforminput', 'oninput', 'oninvalid', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onmousewheel', 'onscroll', 'oncanplay', 'oncanplaythrough', 'ondurationchange', 'onemptied', 'onended', 'onloadeddata', 'onloadedmetadata', 'onloadstart', 'onpause', 'onplay', 'onplaying', 'onprogress', 'onratechange', 'onreadystatechange', 'onseeked', 'onseeking', 'onstalled', 'onsuspend', 'ontimeupdate', 'onvolumechange', 'onwaiting', 'open', 'optimum',
            'pattern', 'ping', 'placeholder', 'poster', 'preload',
            'readonly', 'rel', 'required', 'reversed', 'role', 'rows', 'rowspan',
            'sandbox', 'scope', 'selected', 'shape', 'size', 'sizes', 'span', 'src', 'srcdoc', 'srclang', 'srcset', 'spellcheck', 'start', 'step', 'style', 'summary', 'spellcheck', 'step',
            'tabindex', 'target', 'title', 'type',
            'usemap',
            'value',
            'width', 'wrap'
            )
        ),
    'SYMBOLS' => array(
        '/', '='
        ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        2 => false,
        3 => false,
        ),
    'STYLES' => array(
        'KEYWORDS' => array(
            2 => 'color: #000000; font-weight: bold;',
            3 => 'color: #000066;'
            ),
        'COMMENTS' => array(
            ),
        'ESCAPE_CHAR' => array(
            0 => 'color: #000099; font-weight: bold;'
            ),
        'BRACKETS' => array(
            0 => 'color: #66cc66;'
            ),
        'STRINGS' => array(
            0 => 'color: #ff0000;'
            ),
        'NUMBERS' => array(
            0 => 'color: #cc66cc;'
            ),
        'METHODS' => array(
            ),
        'SYMBOLS' => array(
            0 => 'color: #66cc66;'
            ),
        'SCRIPT' => array(
            -2 => 'color: #404040;', // CDATA
            -1 => 'color: #808080; font-style: italic;', // comments
            0 => 'color: #00bbdd;',
            1 => 'color: #ddbb00;',
            2 => 'color: #009900;'
            ),
        'REGEXPS' => array(
            )
        ),
    'URLS' => array(
        2 => 'http://december.com/html/4/element/{FNAMEL}.html',
        3 => ''
        ),
    'OOLANG' => false,
    'OBJECT_SPLITTERS' => array(
        ),
    'REGEXPS' => array(
        ),
    'STRICT_MODE_APPLIES' => GESHI_ALWAYS,
    'SCRIPT_DELIMITERS' => array(
        -2 => array(
            '<![CDATA[' => ']]>'
            ),
        -1 => array(
            '<!--' => '-->'
            ),
        0 => array(
            '<!DOCTYPE' => '>'
            ),
        1 => array(
            '&' => ';'
            ),
        2 => array(
            '<' => '>'
            )
    ),
    'HIGHLIGHT_STRICT_BLOCK' => array(
        -2 => false,
        -1 => false,
        0 => false,
        1 => false,
        2 => true
        ),
    'TAB_WIDTH' => 4,
    'PARSER_CONTROL' => array(
        'KEYWORDS' => array(
            2 => array(
                'DISALLOWED_BEFORE' => '(?<=&lt;|&lt;\/)',
                'DISALLOWED_AFTER' => '(?=\s|\/|&gt;)',
            )
        )
    )
);
