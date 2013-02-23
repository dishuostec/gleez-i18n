<?php
define('SYSPATH', '../gleez/');
define('I18N_FILE', 'zh.php');

foreach (new RecursiveIteratorIterator (new RecursiveDirectoryIterator ('i18n')) as $file)
{
  if ($file->getExtension() === 'po') {
    $php_file = sprintf("%s/%s.php", $file->getPath(), $file->getBasename('.po'));

    $php = po2php($file->getPathname());
    file_put_contents($php_file, $php);
  }
}

function po2php ($file)
{
  $dict = file_get_contents($file);
  $i18n = array();


  preg_match_all('/^msgid "(?P<untranslated>(?:\\\\.|[^\\\\"]+)+(?:"[\r\n]+"(?:\\\\.|[^\\\\"]+)+)?)"\s+msgstr "(?P<translated>(?:\\\\.|[^\\\\"]+)+)/m', $dict, $match, PREG_SET_ORDER);

  foreach ($match as $str) {
    $i18n[po_decode($str['untranslated'])] = po_decode($str['translated']);
  }

  uksort($i18n, 'strnatcasecmp');

  $i18n = '<?php defined("SYSPATH") OR die("No direct script access.");'."\n".'return '.var_export($i18n, true).';';
  return $i18n;
}

function po_decode ($str)
{

  $str = preg_replace(array(
    '/\\\\(?=")/',
    '/"$[\r\n]+"/m',
  ), array(
    '',
    "\n",
  ), $str);
  return $str;
}
