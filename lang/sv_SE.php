<?php
i18n::include_locale_file('modules: multilingual', 'en_US');
global $lang;

if(array_key_exists('sv_SE', $lang) && is_array($lang['sv_SE'])) {
	$lang['sv_SE'] = array_merge($lang['en_US'], $lang['sv_SE']);
} else {
	$lang['sv_SE'] = $lang['en_US'];
}
$lang['sv_SE']['Multilingual']['VISIBLEINLANGS'] = 'Synlig för språk';
$lang['sv_SE']['Multilingual']['LANGUAGELABEL'] = 'Välj språk';

?>