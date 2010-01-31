function sf(id)
 {
  if(document.getElementById(id)) document.getElementById(id).focus();
 }

function simple_ajax_request(url,query)
 {
  var strURL = 'index.php';
  var xmlHttpReq = false;
  var self = this;
  // Mozilla/Safari
  if (window.XMLHttpRequest) {
      self.xmlHttpReq = new XMLHttpRequest();
  }
  // IE
  else if (window.ActiveXObject) {
      self.xmlHttpReq = new ActiveXObject("Microsoft.XMLHTTP");
  }
  self.xmlHttpReq.open('POST', strURL, true);
  self.xmlHttpReq.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  self.xmlHttpReq.onreadystatechange = function() {
      if(self.xmlHttpReq.readyState == 4) {
       // just do nothing...
      }
  }
  self.xmlHttpReq.send(query);
 }


function highlight_posting(thread,threadcolor,posting,postingcolor)
 {
  if(document.getElementById(thread)) document.getElementById(thread).style.background=threadcolor;
  if(document.getElementById(posting)) document.getElementById(posting).style.background=postingcolor;
 }

function fold_thread(id,expand_img,fold_img)
 {
  var ULs = document.getElementById('thread-'+id).getElementsByTagName("ul");
  if(ULs[0] && (ULs[0].style.display=='block' || ULs[0].style.display==''))
   {
    document.getElementById('expand_img_'+id).src = expand_img;
    for(var i = 0; i < ULs.length; i++)
    ULs[i].style.display = 'none';
   }
  else
   {
    document.getElementById('expand_img_'+id).src = fold_img;
    for(var i = 0; i < ULs.length; i++)
    ULs[i].style.display = 'block';
   }
 }

function hide_posting(id,show_posting_img,hide_posting_img)
 {
  if(document.getElementById('posting-'+id).style.display!='none')
   {
    document.getElementById('posting-'+id).style.display='none';
    if(document.getElementById('avatar-'+id)) document.getElementById('avatar-'+id).style.display='none';
    if(document.getElementById('hide-posting-'+id)) document.getElementById('hide-posting-'+id).src=show_posting_img;
   }
  else
   {
    document.getElementById('posting-'+id).style.display='block';
    if(document.getElementById('avatar-'+id)) document.getElementById('avatar-'+id).style.display='block';
    if(document.getElementById('hide-posting-'+id)) document.getElementById('hide-posting-'+id).src=hide_posting_img;
   }
 }

function getElementsByClassName(class_name)
 {
  var all_obj,ret_obj=new Array(),j=0;
  if(document.all)all_obj=document.all;
  else if(document.getElementsByTagName && !document.all)all_obj=document.getElementsByTagName("*");
  for(i=0;i<all_obj.length;i++)
   {
    if(all_obj[i].className==class_name)
     {
      ret_obj[j]=all_obj[i];
      j++
     }
   }
  return ret_obj;
 }

function hide_all_postings(show_posting_image)
 {
  var obj=getElementsByClassName('postingcontainer');
  for(i=0;i<obj.length;i++)
   {
    obj[i].style.display='none';
   }
  var obj=getElementsByClassName('hide-posting');
  for(i=0;i<obj.length;i++)
   {
    obj[i].src=show_posting_image;
   }
  var obj=getElementsByClassName('avatar');
  for(i=0;i<obj.length;i++)
   {
    obj[i].style.display='none';
   }
 }

function show_all_postings(hide_posting_image)
 {
  var obj=getElementsByClassName('postingcontainer');
  for(i=0;i<obj.length;i++)
   {
    obj[i].style.display='block';
   }
  var obj=getElementsByClassName('hide-posting');
  for(i=0;i<obj.length;i++)
   {
    obj[i].src=hide_posting_image;
   }
  var obj=getElementsByClassName('avatar');
  for(i=0;i<obj.length;i++)
   {
    obj[i].style.display='block';
   }
 }

function hide_replies()
 {
  document.getElementsByTagName("ul").style.display='none';
 }

function hide_sidebar(id,minimized_width,maximized_width,hide_image,show_image)
 {
  if(document.getElementById(id+'-container').style.display=='none')
   {
    document.getElementById(id+'-container').style.display='block';
    document.getElementById(id+'-toggle').src=hide_image;
    document.getElementById(id).style.width=maximized_width;
    simple_ajax_request('index.php','hide='+id);
   }
  else
   {
    document.getElementById(id+'-container').style.display='none';
    document.getElementById(id+'-toggle').src=show_image;
    document.getElementById(id).style.width=minimized_width;
    simple_ajax_request('index.php','hide='+id);
   }
 }

function toggle_sidebar(hide_image,show_image)
 {
  if(document.getElementById('sidebarcontent').style.display=='none')
   {
    document.getElementById('sidebarcontent').style.display='block';
    document.getElementById('sidebartoggle').src=hide_image;
   }
  else
   {
    document.getElementById('sidebarcontent').style.display='none';
    document.getElementById('sidebartoggle').src=show_image;
   }
  simple_ajax_request('index.php','toggle_sidebar=true');
 }

function bbcode(id,code,value)
 {
  value = typeof(value) != 'undefined' ? value : '';
  if(value!='') value = '='+value;

  if (document.selection) // IE
   {
    var str = document.selection.createRange().text;
    document.getElementById(id).focus();
    var sel = document.selection.createRange();
    sel.text = '[' + code + value + "]" + str + '[/' + code + ']';
    return;
   }
  else if((typeof document.getElementById(id).selectionStart) != 'undefined') // Mozilla
   {
    var txtarea = document.getElementById(id);
    var selLength = txtarea.textLength;
    var selStart = txtarea.selectionStart;
    var selEnd = txtarea.selectionEnd;
    var oldScrollTop = txtarea.scrollTop;
    var s1 = (txtarea.value).substring(0,selStart);
    var s2 = (txtarea.value).substring(selStart, selEnd);
    var s3 = (txtarea.value).substring(selEnd, selLength);
    txtarea.value = s1 + '[' + code + value + ']' + s2 + '[/' + code + ']' + s3;
    txtarea.selectionStart = s1.length;
    txtarea.selectionEnd = s1.length + code.length*2 + value.length + s2.length + 5;
    txtarea.scrollTop = oldScrollTop;
    txtarea.focus();
    return;
   }
  else insert(id,'[' + code + value + '][/' + code + ']');
 }

function insert(id,what)
 {
  if(document.getElementById(id).createTextRange) // IE
   {
    document.getElementById(id).focus();
    document.selection.createRange().duplicate().text = what;
   }
  else if((typeof document.getElementById(id).selectionStart) != 'undefined') // Mozilla
   {
    var tarea = document.getElementById(id);
    var selEnd = tarea.selectionEnd;
    var txtLen = tarea.value.length;
    var txtbefore = tarea.value.substring(0,selEnd);
    var txtafter =  tarea.value.substring(selEnd, txtLen);
    var oldScrollTop = tarea.scrollTop;
    tarea.value = txtbefore + what + txtafter;
    tarea.selectionStart = txtbefore.length + what.length;
    tarea.selectionEnd = txtbefore.length + what.length;
    tarea.scrollTop = oldScrollTop;
    tarea.focus();
   }
  else
   {
    document.getElementById(id).value += what;
    document.getElementById(id).focus();
   }
 }

function insert_link(id,link_text,link_target) // overworked by Milo, see http://mylittleforum.net/forum/index.php?id=4482
 {
  var link_text = decodeURIComponent(link_text);
  var link_target = decodeURIComponent(link_target);
  var link_bb_code = "link";
  var regExpURI = new RegExp(/[http|https|ftp|ftps]:\/\/[a-zA-Z0-9-.][a-zA-Z0-9-.]+(S+)?/);
  var regExpFID = new RegExp(/[?|&]id=([0-9]+)/);
  var forumURI = window.location.hostname + window.location.pathname;
  if(document.selection) // IE
   {
    var str = document.selection.createRange().text;
    document.getElementById(id).focus();
    var sel = document.selection.createRange();
    var insert_link = (regExpURI.test(sel.text))?prompt(link_target, sel.text):prompt(link_target,'http://');
    if(!insert_link || insert_link == '' || insert_link == 'http://') return;
    if(insert_link.indexOf(forumURI) > 0 && regExpFID.test(insert_link))
     {
      var msgQuery = regExpFID.exec(insert_link);
      link_bb_code = "msg";
      insert_link = msgQuery[1];
     }
    if(sel.text=='' || regExpURI.test(sel.text)) var str = prompt(link_text,'');
    if(str!=null)
     {
      if(str!='')
       {
        sel.text = "["+link_bb_code+"=" + insert_link + "]" + str + "[/"+link_bb_code+"]";
       }
      else
       {
        sel.text = "["+link_bb_code+"]" + insert_link + "[/"+link_bb_code+"]";
       }
     }
    return;
   }
  else if((typeof document.getElementById(id).selectionStart) != 'undefined') // Mozilla
   {
    var txtarea = document.getElementById(id);
    var selLength = txtarea.textLength;
    var selStart = txtarea.selectionStart;
    var selEnd = txtarea.selectionEnd;
    var oldScrollTop = txtarea.scrollTop;
    var s1 = (txtarea.value).substring(0,selStart);
    var s2 = (txtarea.value).substring(selStart, selEnd);
    var s3 = (txtarea.value).substring(selEnd, selLength);
    var insert_link = (regExpURI.test(s2))?prompt(link_target, s2):prompt(link_target,'http://');
    if(!insert_link || insert_link == '' || insert_link == 'http://') return;
    if(insert_link.indexOf(forumURI) > 0 && regExpFID.test(insert_link)) {
    var msgQuery = regExpFID.exec(insert_link);
    link_bb_code = "msg";
    insert_link = msgQuery[1];
   }
  if(selEnd-selStart==0 || regExpURI.test(s2))
   {
    var s2 = prompt(link_text,'');
    var no_selection = true;
   }
  if(s2!=null)
   {
    if(s2!='')
     {
      txtarea.value = s1 + "["+link_bb_code+"=" + insert_link + "]" + s2 + "[/"+link_bb_code+"]" + s3;
      var codelength = 14 + insert_link.length + s2.length;
     }
    else
     {
      txtarea.value = s1 + "["+link_bb_code+"]" + insert_link + "[/"+link_bb_code+"]" + s3;
      var codelength = 13 + insert_link.length;
     }
    if(no_selection) txtarea.selectionStart = s1.length + codelength;
    else txtarea.selectionStart = s1.length;
    txtarea.selectionEnd = s1.length + codelength;
    txtarea.scrollTop = oldScrollTop;
    txtarea.focus();
    return;
   }
  }
  else insert(id,'[link=http://www.domain.tld/]Link[/link]');
 }

function insert_image(id,image_url_label)
 {
  var image_url_label = decodeURIComponent(image_url_label);
  if(document.selection) // IE
   {
    var str = document.selection.createRange().text;
    document.getElementById(id).focus();
    var sel = document.selection.createRange();
    if(str=='')
     {
      var image_url = prompt(image_url_label,'http://');
      if(image_url!=null) sel.text = "[img]" + image_url + "[/img]";
     }
    else
     {
      sel.text = "[img]" + str + "[/img]";
     }
    return;
   }
  else if((typeof document.getElementById(id).selectionStart) != 'undefined') // Mozilla
   {
    var txtarea = document.getElementById(id);
    var selLength = txtarea.textLength;
    var selStart = txtarea.selectionStart;
    var selEnd = txtarea.selectionEnd;
    var oldScrollTop = txtarea.scrollTop;
    var s1 = (txtarea.value).substring(0,selStart);
    var s2 = (txtarea.value).substring(selStart, selEnd);
    var s3 = (txtarea.value).substring(selEnd, selLength);
    if(selEnd-selStart==0)
     {
      var s2 = prompt(image_url_label,'http://');
      var no_selection = true;
     }
    if(s2!=null)
     {
      txtarea.value = s1 + '[img]' + s2 + '[/img]' + s3;
      var codelength = 11 + s2.length;
      if(no_selection) txtarea.selectionStart = s1.length + codelength;
      else txtarea.selectionStart = s1.length;
      txtarea.selectionEnd = s1.length + codelength;
      txtarea.scrollTop = oldScrollTop;
      txtarea.focus();
      return;
     }
   }
  else insert(id,'[img][/img]');
 }

function clear_input(form,field)
 {
  document.forms[form].elements[field].value = '';
  document.forms[form].elements[field].focus();
 }

function show_box(id,x,y)
 {
  x = typeof(x) != 'undefined' ? x : 0;
  y = typeof(y) != 'undefined' ? y : 0;
  if(!document.getElementById(id).style.display || document.getElementById(id).style.display=='none')
   {
    s_box = document.getElementById(id);
    s_box.style.display = 'block';
    s_box.style.left  = posX+x + 'px';
    s_box.style.top = posY+y + 'px';
   }
  else
   {
    document.getElementById(id).style.display = 'none';
   }
 }

function popup(url,width,height)
 {
  width = typeof(width) != 'undefined' ? width : 340;
  height = typeof(height) != 'undefined' ? height : 340;
  winpops = window.open(url,'','width='+width+',height='+height+',scrollbars,resizable');
 }

function delete_cookie(deleting_cookie_message)
 {
  document.getElementById('delete_cookie').innerHTML = decodeURIComponent(deleting_cookie_message);

    var strURL = 'index.php';
    var xmlHttpReq = false;
    var self = this;
    // Mozilla/Safari
    if (window.XMLHttpRequest) {
        self.xmlHttpReq = new XMLHttpRequest();
    }
    // IE
    else if (window.ActiveXObject) {
        self.xmlHttpReq = new ActiveXObject("Microsoft.XMLHTTP");
    }
    self.xmlHttpReq.open('POST', strURL, true);
    self.xmlHttpReq.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    self.xmlHttpReq.onreadystatechange = function() {
        if (self.xmlHttpReq.readyState == 4) {
            document.getElementById('delete_cookie').innerHTML = '&nbsp;';
            document.getElementById('setcookie').checked = '';
        }
    }
    self.xmlHttpReq.send('mode=delete_cookie&method=ajax');
 }

function ajax_preview(id,show_reply_link)
 {
  var srl = typeof(show_reply_link) == 'undefined' ? 0 : show_reply_link;
  if(document.getElementById('ajax-preview') && document.getElementById("ajax-preview").className == "active-"+id) // close preview if the same link is clicked again
   {
    ajax_preview_close();
   }
  else // display preview
   {
    if(!document.getElementById('ajax-preview'))
     {
      // create preview container:
      var ap = document.createElement('div');
      ap.setAttribute('id','ajax-preview');
      // append it to the HTML body :
      document.getElementsByTagName('body')[0].appendChild(ap);
     }
    // set unique class name to be able check whether to open or close the preview:
    document.getElementById("ajax-preview").className = 'active-'+id;
    // insert HTML structure:
    if(typeof ajax_preview_structure == 'undefined') ajax_preview_structure = '<div id="ajax-preview-top"></div><div id="ajax-preview-body"><div><a id="ajax-preview-close" href="#" onclick="ajax_preview_close(); return false">[x]</a></div><div id="ajax-preview-throbber"></div><div id="ajax-preview-content"></div></div>'; // this is only if the structure is not defined in the templae! Make design changes in ajax_preview.tpl.inc.
    document.getElementById('ajax-preview').innerHTML = ajax_preview_structure;
    // place it to the mouse position:
    ap = document.getElementById('ajax-preview');
    ap.style.left  = posX-8 + "px";
    ap.style.top = posY+2 + "px";
    // show throbber:
    document.getElementById("ajax-preview-throbber").style.display = "block";
    // get data via AJAX:
    var strURL = 'index.php';
    var xmlHttpReq = false;
    var self = this;
    if (window.XMLHttpRequest) // Mozilla/Safari
     {
      self.xmlHttpReq = new XMLHttpRequest();
     }
    else if (window.ActiveXObject) // IE
     {
      self.xmlHttpReq = new ActiveXObject("Microsoft.XMLHTTP");
     }
    self.xmlHttpReq.open('POST', strURL, true);
    self.xmlHttpReq.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    self.xmlHttpReq.onreadystatechange = function()
                                          {
                                           if(self.xmlHttpReq.readyState == 4)
                                            {
                                             updatepage(id,self.xmlHttpReq.responseXML,srl);
                                            }
                                          }
    self.xmlHttpReq.send('mode=entry&ajax_preview=true&id='+id);
   }
 }

function updatepage(id,xml,show_reply_link)
 {
  var show_reply_link = typeof(show_reply_link) == 'undefined' || show_reply_link == 0 ? 0 : 1;
  var content = xml.getElementsByTagName('content')[0].firstChild.data;
  if(content=='') content = '<p>-</p>';
  document.getElementById("ajax-preview-content").innerHTML = content;
  // hide throbber:
  document.getElementById("ajax-preview-throbber").innerHTML = '';
  document.getElementById("ajax-preview-throbber").style.display = 'none';
  if(document.getElementById("ajax-preview-replylink"))
   {
    if(show_reply_link==1)
     {
      document.getElementById("ajax-preview-replylink").style.display = 'block';
      document.getElementById("replylink").href = 'index.php?mode=posting&id='+id;
     }
    else
     {
      document.getElementById("ajax-preview-replylink").innerHTML = '';
      document.getElementById("ajax-preview-replylink").style.display = 'none';
     }
   }
 }

function ajax_preview_close()
 {
  if(document.getElementById('ajax-preview'))
   {
    document.getElementsByTagName('body')[0].removeChild(document.getElementById('ajax-preview'));
   }
 }

function mouse_position(e)
 {
  if(!e) e = window.event;
  posX  = e.clientX + (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
  posY = e.clientY + (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
	// close preview on click outside of bubble:
  if(document.getElementById('ajax-preview'))
   {
    var obj = e.target || e.srcElement;
    var boxX = document.getElementById('ajax-preview').offsetLeft;
    var boxY = document.getElementById('ajax-preview').offsetTop;
    var boxWidth = document.getElementById('ajax-preview').offsetWidth;
    var boxHeight = document.getElementById('ajax-preview').offsetHeight;
    if((posX < boxX || posX > (boxX+boxWidth) || posY < boxY || posY > (boxY+boxHeight)) && obj.className!='ap') ajax_preview_close();
   }
 }

function hide_element(e)
 {
  document.getElementById(e).style.display = 'none';
 }

function mark(id,marked_image,unmarked_image,process_mark_image,mark_title,unmark_title)
 {
    pmi = new Image();
    pmi.src = process_mark_image;
    mi = new Image();
    mi.src = marked_image;
    umi = new Image();
    umi.src = unmarked_image;

    document.getElementById('markimg_'+id).src = process_mark_image;
    document.getElementById('markimg_'+id).alt = '[ ]';

    var strURL = 'index.php';
    var xmlHttpReq = false;
    var self = this;
    // Mozilla/Safari
    if (window.XMLHttpRequest) {
        self.xmlHttpReq = new XMLHttpRequest();
    }
    // IE
    else if (window.ActiveXObject) {
        self.xmlHttpReq = new ActiveXObject("Microsoft.XMLHTTP");
    }
    self.xmlHttpReq.open('POST', strURL, true);
    self.xmlHttpReq.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    self.xmlHttpReq.onreadystatechange = function() {
        if (self.xmlHttpReq.readyState == 4) {
            var action = self.xmlHttpReq.responseXML.getElementsByTagName('action')[0].firstChild.data;
            if(action==1)
             {
              document.getElementById('markimg_'+id).src = marked_image;
              document.getElementById('markimg_'+id).alt = '[●]';
              document.getElementById('marklink_'+id).title = decodeURIComponent(unmark_title);
              document.getElementById('markimg_'+id).title = decodeURIComponent(unmark_title);
             }
            else
             {
              document.getElementById('markimg_'+id).src = unmarked_image;
              document.getElementById('markimg_'+id).alt = '[○]';
              document.getElementById('marklink_'+id).title = decodeURIComponent(mark_title);
              document.getElementById('markimg_'+id).title = decodeURIComponent(mark_title);
             }
        }
    }
    self.xmlHttpReq.send('mode=posting&mark='+id+'&method=ajax');
 }

function delete_posting_confirm(this_link,confirm_question)
 {
  var confirmed = confirm(decodeURIComponent(confirm_question));
  if(confirmed) this_link.href += '&delete_posting_confirm=true';
  return confirmed;
 }

function clear_input(form,field)
 {
  document.forms[form].elements[field].value = '';
  document.forms[form].elements[field].focus();
 }

function hide_quote()
 {
  document.getElementById('insert_quote_link').style.visibility = 'visible';
  quotes = document.forms['postingform'].elements['text'].value;
  document.forms['postingform'].elements['text'].value = '';
 }

function insert_quote()
 {
  document.getElementById('insert_quote_link').style.visibility = 'hidden';
  var current_value = document.forms['postingform'].elements['text'].value;
  document.forms['postingform'].elements['text'].value = quotes + '\n\n' + current_value;
  document.forms['postingform'].elements['text'].focus();
 }

function is_postingform_complete(name_error,subject_error,text_error,terms_of_use_error)
 {
	terms_of_use_error = typeof(terms_of_use_error) != 'undefined' ? terms_of_use_error : '';
  error_message='';
	if(document.forms['postingform'].elements['name'] && document.forms['postingform'].elements['name'].value=='')
	 {
		error_message += "- "+decodeURIComponent(name_error)+"\n";
	 }
	if(document.forms['postingform'].elements['subject'].value=='')
	 {
		error_message += "- "+decodeURIComponent(subject_error)+"\n";
	 }
	if(text_error!='' && document.forms['postingform'].elements['text'].value=='')
	 {
		error_message += "- "+decodeURIComponent(text_error)+"\n";
	 }
	if(terms_of_use_error!='' && document.forms['postingform'].elements['terms_of_use_agree'].checked==false)
	 {
		error_message += "- "+decodeURIComponent(terms_of_use_error)+"\n";
	 }
  if(error_message)
	 {
		alert(error_message);
		return false;
	 }
	else
	 {
		document.getElementById('throbber-submit').style.visibility = 'visible';
    return true;
	 }
 }

function insert_avatar(avatar)
 {
  document.getElementById('avatar').innerHTML = '<a href="index.php?mode=avatar" onclick="popup(\'index.php?mode=avatar\'); return false"><img src="'+avatar+'" alt="Avatar" /><\/a>';
 }

document.onmousedown = mouse_position;
