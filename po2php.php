<?php
define('SYSPATH', '../gleez/');
define('I18N_DICTONARY', 'i18n/dict.php');
define('I18N_DICTONARY_PO', 'i18n/dict.po');

$dict = po2php(I18N_DICTONARY_PO);
if (count($dict)) {
  dump_php_array(I18N_DICTONARY, $dict);
}

// Functions
function po2php ($file)
{
  $dict = file_get_contents($file);
  $i18n = array();


  preg_match_all('/^msgid "(?P<untranslated>(?:\\\\.|[^\\\\"]+)*(?:"[\r\n]+"(?:\\\\.|[^\\\\"]+)*)*)"\s+msgstr "(?P<translated>(?:\\\\.|[^\\\\"]+)*(?:"[\r\n]+"(?:\\\\.|[^\\\\"]+)*)*)/m', $dict, $match, PREG_SET_ORDER);

  foreach ($match as $str) {
    $i18n[po_decode($str['untranslated'])] = po_decode($str['translated']);
  }

  return $i18n;
}

function po_decode ($str)
{

  $str = preg_replace(array(
    '/\\\\(?=")/',
    '/"$\n"/m',
  ), array(
    '',
    "\n",
  ), $str);
  return $str;
}

function dump_php_array($file, Array $array)
{
  uksort($array, 'strnatcasecmp');

  file_put_contents($file, '<?php defined("SYSPATH") OR die("No direct script access.");'."\n".'return '.var_export($array, true).';');
}
