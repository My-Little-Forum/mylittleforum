<div id="colorpicker">
{literal}
<script type="text/javascript">
/* <![CDATA[ */
var colors = new Array('#fff','#ccc','#c0c0c0','#999','#666','#333','#000',
                       '#fcc','#f66','#f00','#c00','#900','#600','#300',
                       '#fc9','#f96','#f90','#f60','#c60','#930','#630',
                       '#ff9','#ff6','#fc6','#fc3','#c93','#963','#633',
                       '#ffc','#ff3','#ff0','#fc0','#990','#660','#330',
                       '#9f9','#6f9','#3f3','#3c0','#090','#060','#030',
                       '#9ff','#3ff','#6cc','#0cc','#399','#366','#033',
                       '#cff','#6ff','#3cf','#36f','#33f','#009','#006',
                       '#ccf','#99f','#66c','#63f','#60c','#339','#309',
                       '#fcf','#f9f','#c6c','#c3c','#939','#636','#303');
document.write('<table border="0" cellpadding="0" cellspacing="0"><tr>');
for(var i = 0; i < colors.length; ++i)
 {
  document.write('<td style="background:'+colors[i]+';"><a href="#" onclick="bbcode(\'text\',\'color\',\''+colors[i]+'\'); hide_element(\'colorpicker\'); return false"><img src="themes/{/literal}{$theme}{literal}/images/plain.png" alt="'+colors[i]+'" title="" width="15" height="15" /></a></td>');
  if((i+1)%7==0) document.write('</tr><tr>');
 }
document.write('</tr></table>');
/* ]]> */
</script>
{/literal}
</div>
