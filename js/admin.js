function updatePagesOrder()
 {
  //document.getElementById("throbber").style.display = "block";
  var options = {
                 method : 'post',
                 parameters : 'mode=admin&action=reorder&data=pages&'+Sortable.serialize('items')
                 //,
                 //onComplete : function(request)
                 // {
                   //document.getElementById("throbber").style.display = "none";
                 // }
                };
  new Ajax.Request('index.php', options);
 }

function updateSmiliesOrder()
 {
  var options = {
                 method : 'post',
                 parameters : 'mode=admin&action=reorder&data=smilies&'+Sortable.serialize('items')
                };
  new Ajax.Request('index.php', options);
 }

function updateCategoryOrder()
 {
  var options = {
                 method : 'post',
                 parameters : 'mode=admin&action=reorder&data=categories&'+Sortable.serialize('items')
                };
  new Ajax.Request('index.php', options);
 }

function moveTitle(id,label)
 {
  document.getElementById(id).style.cursor = 'move';
  document.getElementById(id).title = label;
 }

function addMoveTitle(label)
 {
  var TRs = document.getElementById('items').getElementsByTagName('tr');
  if(TRs[0])
   {
    for(var i = 0; i < TRs.length; i++)
     {
      TRs[i].style.cursor = 'move';
      TRs[i].title = label;
     }
   }
  else
   {
    document.getElementById('expand_img_'+id).src = fold_img;
    document.getElementById('expand_link_'+id).title = fold_title;
    for(var i = 0; i < ULs.length; i++)
    ULs[i].style.display = 'block';
   }
 }

function delete_backup_confirm(this_link,confirm_question)
 {
  var confirmed = confirm(decodeURIComponent(confirm_question));
  if(confirmed) this_link.href += '&delete_backup_files_confirm=true';
  return confirmed;
 }

function delete_backup_selected_confirm(confirm_question)
 {
  var confirmed = confirm(decodeURIComponent(confirm_question));
  if(confirmed) document.forms['selectform'].elements['delete_backup_files_confirm'].value = 'true';
  return confirmed;
 }

function checkall(form, selects, check)
 {
  var elts = (typeof(document.forms[form].elements[selects]) != 'undefined')
                  ? document.forms[form].elements[selects]
                  : 0;
  var elts_cnt  = (typeof(elts.length) != 'undefined')
                  ? elts.length
                  : 0;
  if (elts_cnt)
   {
    for (var i = 0; i < elts_cnt; i++)
     {
      elts[i].checked = check;
     }
   }
  else
   {
    elts.checked = check;
   }
  return true;
 }

function set_label_class(l,c)
 {
  c = typeof(c) != 'undefined' ? c : '';
  if(c=='')
   {
    if(document.getElementById(l).className == 'inactive') document.getElementById(l).className = 'active';
    else document.getElementById(l).className = 'inactive';
   }
  else
   {
    document.getElementById(l).className = c;
   }
 }

function change_label_classes(ids)
 {
  if(document.getElementById(ids[0]).className == 'active') document.getElementById(ids[0]).className = 'inactive';
  else if(document.getElementById(ids[0]).className == 'inactive') document.getElementById(ids[0]).className = 'active';

  for(var i = 1; i < ids.length; ++i)
   {
    if(document.getElementById(ids[i]).className == 'active') document.getElementById(ids[i]).className = 'inactive';
   }
 }
