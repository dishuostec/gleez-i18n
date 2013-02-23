<?php
define('SYSPATH', '../gleez/');
define('I18N_FILE', 'zh.php');

$file_count = 0;
$new_string = 0;
$i18n = array();

get_from_module('gleez');
get_from_module('user');

// Functions
function get_from_module($module)
{
  global $file_count, $new_string, $i18n;

  $file_count = 0;
  $new_string = 0;
  $i18n = array();

  $i18n_file = './i18n/'.$module.'.'.I18N_FILE;

  if (file_exists($i18n_file)) {
    $i18n = include $i18n_file;
  }

  message(SYSPATH."modules/$module/messages");
  walk(SYSPATH."modules/$module/views");
  walk(SYSPATH."modules/$module/classes");

  uksort($i18n, 'strnatcasecmp');

  if ($new_string) {
    file_put_contents($i18n_file, '<?php defined("SYSPATH") OR die("No direct script access.");'."\n".'return '.var_export($i18n, true).';');
  }

  printf("%s : %s files, %s new string\n", $module, $file_count, $new_string);
}

function message($path)
{
  global $file_count;
  foreach (new RecursiveIteratorIterator (new RecursiveDirectoryIterator ($path)) as $file)
  {
    if ($file->getExtension() === 'php') {
      $filename = $file->getPathname();
      $file_count++;
      parse_message($filename);
    }
  }
}

function  parse_message ($file)
{
  $message = include $file;

  foreach (new RecursiveIteratorIterator (new RecursiveArrayIterator($message)) as $string) {
    add_to_i18n ($string);
  }
}

function walk($path)
{
  global $file_count;
  foreach (new RecursiveIteratorIterator (new RecursiveDirectoryIterator ($path)) as $file)
  {
    if ($file->getExtension() === 'php') {
      $filename = $file->getPathname();
      $file_count++;
      parse_file(file_get_contents($filename));
    }
  }
}

function parse_file($data)
{
  preg_match_all('/\b__\(\'((?:\\\\.|[^\\\\\']+)+)\'/', $data, $match, PREG_PATTERN_ORDER);

  foreach ($match[1] as $string) {
    add_to_i18n ($string);
  }

  preg_match_all('/\b__\("((?:\\\\.|[^\\\\"]+)+)"/', $data, $match, PREG_PATTERN_ORDER);

  foreach ($match[1] as $string) {
    add_to_i18n ($string);
  }
}

function add_to_i18n ($string) {
  global $i18n, $new_string;

  if (empty($string) || array_key_exists($string, $i18n) || !preg_match('/[a-zA-Z]/', $string)) {
    return;
  }
  $string = preg_replace('/\\\\(.)/', '$1', $string);
  $new_string++;
  $i18n[$string] = '';
}
