<?php
if (!defined('IN_INDEX')) {
	header('Location: ../index.php');
	exit;
}

if (isset($_SESSION[$settings['session_prefix'].'usersettings']['theme']) && $smarty->templateExists($_SESSION[$settings['session_prefix'].'usersettings']['theme'].'/main.tpl')) $theme = $_SESSION[$settings['session_prefix'].'usersettings']['theme'];
else $theme = $settings['theme'];

$theme_config = parse_ini_file('./'.THEMES_DIR.'/'.$theme.'/js_config.ini');

if (isset($_GET['user_type'])) $user_type = intval($_GET['user_type']);
if (isset($user_type) && $user_type > 2) unset($user_type);

$smarty->configLoad($language_file, 'general');
$lang = $smarty->getConfigVars();

if ($settings['ajax_preview']) {
	$template = 'ajax_preview.tpl';
	$smarty->assign('theme', $theme);
	$ajax_preview_structure = $smarty->fetch($theme.'/'.$template);
	$ajax_preview_structure = addslashes(preg_replace("/\015\012|\015|\012/", "", $ajax_preview_structure));
}

$expires = 2592000; // 30 days
header("Pragma: public");
header("Cache-Control: maxage=".$expires);
header('Expires: ' . gmdate('D, d M Y H:i:s', TIMESTAMP + $expires) . ' GMT');
header('Content-type: application/javascript');

?>var lang = new Array();
<?php if ($settings['ajax_preview']): ?>
	lang["ajax_preview_title"] =               "<?php echo addslashes($lang['ajax_preview_title']); ?>";
	lang["close"] =                            "<?php echo addslashes($lang['close']); ?>";
	lang["no_text"] =                          "<?php echo addslashes($lang['no_text']); ?>";
	lang["reply_link"] =                       "<?php echo addslashes($lang['reply_link']); ?>";
<?php endif; ?>
lang["fold_threads"] =                     "<?php echo addslashes($lang['fold_threads']); ?>";
lang["fold_threads_linktitle"] =           "<?php echo addslashes($lang['fold_threads_linktitle']); ?>";
lang["expand_threads"] =                   "<?php echo addslashes($lang['expand_threads']); ?>";
lang["expand_threads_linktitle"] =         "<?php echo addslashes($lang['expand_threads_linktitle']); ?>";
lang["expand_fold_thread_linktitle"] =     "<?php echo addslashes($lang['expand_fold_thread_linktitle']); ?>";
lang["fold_posting_title"] =               "<?php echo addslashes($lang['fold_posting_title']); ?>";
lang["fold_postings"] =                    "<?php echo addslashes($lang['fold_postings']); ?>";
lang["fold_postings_title"] =              "<?php echo addslashes($lang['fold_postings_title']); ?>";
lang["show_password_title"] =              "<?php echo addslashes($lang['show_password_title']); ?>";
lang["hide_password_title"] =              "<?php echo addslashes($lang['hide_password_title']); ?>";
<?php if(isset($user_type) && $user_type >= 0): ?>
	lang["drag_and_drop_title"] =              "<?php echo addslashes($lang['drag_and_drop_title']); ?>";
<?php endif; ?>
<?php if($settings['entries_by_users_only']==0 || isset($user_type)): ?>
	lang["quote_label"] =                      "<?php echo addslashes($lang['quote_label']); ?>";
	lang["quote_title"] =                      "<?php echo addslashes($lang['quote_title']); ?>";
	<?php if($settings['bbcode']): ?>
		lang["bbcode_link_text"] =                 "<?php echo addslashes($lang['bbcode_link_text']); ?>";
		lang["bbcode_link_url"] =                  "<?php echo addslashes($lang['bbcode_link_url']); ?>";
		lang["bbcode_image_url"] =                 "<?php echo addslashes($lang['bbcode_image_url']); ?>";
	<?php endif; ?>
	<?php if($settings['bbcode_latex'] && !empty($settings['bbcode_latex_uri'])): ?>
		lang["bbcode_tex_code"] =                  "<?php echo addslashes($lang['bbcode_tex_code']); ?>";
	<?php endif; ?>
	<?php if($settings['smilies']): ?>
		lang["more_smilies_label"] =               "<?php echo addslashes($lang['more_smilies_label']); ?>";
		lang["more_smilies_title"] =               "<?php echo addslashes($lang['more_smilies_title']); ?>";
	<?php endif; ?>
	lang["error_no_name"] =                    "<?php echo addslashes($lang['error_no_name']); ?>";
	lang["error_no_subject"] =                 "<?php echo addslashes($lang['error_no_subject']); ?>";
	lang["error_no_text"] =                    "<?php echo addslashes($lang['error_no_text']); ?>";
	lang["terms_of_use_error_posting"] =       "<?php echo addslashes($lang['terms_of_use_error_posting']); ?>";
<?php endif; ?>
<?php if(isset($user_type) && $user_type==0 && $settings['user_edit']>0 || !isset($user_type) && $settings['user_edit']==2): ?>
	lang["delete_posting_confirm"] =           "<?php echo addslashes($lang['delete_posting_confirm']); ?>";
<?php elseif(isset($user_type) && $user_type>0): ?>
	lang["delete_posting_confirm"] =           "<?php echo addslashes($lang['delete_posting_replies_confirm']); ?>";
<?php endif; ?>
<?php if(isset($user_type) && $user_type>0): ?>
	lang["mark_linktitle"] =                   "<?php echo addslashes($lang['mark_linktitle']); ?>";
	lang["unmark_linktitle"] =                 "<?php echo addslashes($lang['unmark_linktitle']); ?>";
<?php endif; ?>
<?php if(isset($user_type) && $user_type==2): ?>
	lang["check_all"] =                        "<?php echo addslashes($lang['check_all']); ?>";
	lang["uncheck_all"] =                      "<?php echo addslashes($lang['uncheck_all']); ?>";
	lang["delete_backup_confirm"] =            "<?php echo addslashes($lang['delete_backup_confirm']); ?>";
	lang["delete_sel_backup_confirm"] =        "<?php echo addslashes($lang['delete_sel_backup_confirm']); ?>";
<?php endif; ?>

var settings = new Array();
settings["session_prefix"] =               "<?php echo addslashes($settings['session_prefix']); ?>";
settings["hide_sidebar_image"] =           "<?php echo $theme_config['hide_sidebar_image']; ?>";
settings["show_sidebar_image"] =           "<?php echo $theme_config['show_sidebar_image']; ?>";
settings["expand_thread_image"] =          "<?php echo $theme_config['expand_thread_image']; ?>";
settings["fold_thread_image"] =            "<?php echo $theme_config['fold_thread_image']; ?>";
settings["expand_thread_inactive_image"] = "<?php echo $theme_config['expand_thread_inactive_image']; ?>";
<?php if($settings['terms_of_use_agreement']): ?>
settings["terms_of_use_popup_width"] =     <?php echo $theme_config['terms_of_use_popup_width']; ?>;
settings["terms_of_use_popup_height"] =    <?php echo $theme_config['terms_of_use_popup_height']; ?>;
<?php endif; ?>
<?php if ($settings['ajax_preview']): ?>
settings["ajaxPreviewStructure"] =         "<?php echo $ajax_preview_structure; ?>";
settings["ajax_preview_image"] =           "<?php echo $theme_config['ajax_preview_image']; ?>";
settings["ajax_preview_throbber_image"] =  "<?php echo $theme_config['ajax_preview_throbber_image']; ?>";
settings["ajax_preview_onmouseover"] =     <?php echo ($settings['ajax_preview'] > 1 ? 'true':'false'); ?>;
<?php endif; ?>
<?php if (isset($user_type) && $user_type>0 && $settings['upload_images'] > 0 || isset($user_type) && $settings['upload_images'] > 1 || $settings['upload_images'] > 2): ?>
settings["upload_popup_width"] =           <?php echo $theme_config['upload_popup_width']; ?>;
settings["upload_popup_height"] =          <?php echo $theme_config['upload_popup_height']; ?>;
<?php endif; ?>
<?php if (isset($user_type) && $settings['avatars']): ?>
settings["avatar_popup_width"] =           <?php echo $theme_config['avatar_popup_width']; ?>;
settings["avatar_popup_height"] =          <?php echo $theme_config['avatar_popup_height']; ?>;
<?php endif; ?>
<?php if (isset($user_type) && $user_type > 0): ?>
settings["mark_process_image"] =           "<?php echo $theme_config['mark_process_image']; ?>";
settings["marked_image"] =                 "<?php echo $theme_config['marked_image']; ?>";
settings["unmarked_image"] =               "<?php echo $theme_config['unmarked_image']; ?>";
<?php endif; ?>

<?php if(isset($theme_config['preload'])): ?>
var preload = new Array();
<?php foreach($theme_config['preload'] as $key => $val): ?>
preload[<?php echo $key; ?>] =                               "<?php echo $val; ?>";
<?php endforeach; ?>
<?php endif; ?>

<?php exit; ?>
