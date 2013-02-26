<?php
define('SYSPATH', '../gleez/');
define('I18N_DICTONARY', 'i18n/dict.php');
define('I18N_DICTONARY_PO', 'i18n/dict.po');

$dict = load_dictonary();

$po = php2po($dict);

file_put_contents(I18N_DICTONARY_PO, $po);

// Functions
function php2po (Array $array)
{
  $po = '';

  foreach ($array as $untranslated => $translated) {
    $po .= sprintf("msgid \"%s\"\nmsgstr \"%s\"\n\n",
      po_encode($untranslated),
      po_encode($translated)
    );
  }

  return $po;
}

function po_encode ($str)
{
  $str = preg_replace(array(
    '/(?=")/',
    '/\\\\(?=\')/', 
    '/\n/',
  ), array(
    '\\',
    '',
    "\"\n\"",
  ), $str);

  return $str;
}

function load_dictonary()
{
  if (file_exists(I18N_DICTONARY)) {
    return include I18N_DICTONARY;
  }
  return array();
}

