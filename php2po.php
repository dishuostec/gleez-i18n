<?php
define('SYSPATH', '../gleez/');
define('I18N_FILE', 'zh.php');

foreach (new RecursiveIteratorIterator (new RecursiveDirectoryIterator ('i18n')) as $file)
{
  if ($file->getExtension() === 'php') {
    $po_file = sprintf("%s/%s.po", $file->getPath(), $file->getBasename('.php'));

    $po = php2po($file->getPathname());
    file_put_contents($po_file, $po);
  }
}

function php2po ($file)
{
  $dict = include $file;
  $po = '';

  foreach ($dict as $untranslated => $translated) {
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
    '/[\r\n]+/',
  ), array(
    '\\',
    '',
    "\"\n\"",
  ), $str);

  return $str;
}
