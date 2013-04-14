<?php
define('SYSPATH', '../gleez/');
define('I18N_DICTONARY', 'i18n/dict.php');
define('I18N_FILE', 'zh.php');

$counter_file = 0;
$counter_item_new = 0;
$counter_item_untranslated = 0;

$i18n = array();

$dict = load_dictonary();

get_from_module('gleez', TRUE);
get_from_module('user');

if ($counter_item_untranslated) {
  dump_php_array(I18N_DICTONARY, $dict);
}

// Functions
function get_from_module($module, $is_main = FALSE)
{
  global $counter_file, $counter_item_new, $counter_item_untranslated, $i18n;

  $counter_file = 0;
  $counter_item_new = 0;
  $i18n = array();

  $i18n_file = './i18n/'.$module.'.'.I18N_FILE;
  
  if (file_exists($i18n_file)) {
    $i18n = include $i18n_file;
  }

  $count_prev = count($i18n);

  message(SYSPATH."modules/$module/messages");
  walk(SYSPATH."modules/$module/views");
  walk(SYSPATH."modules/$module/classes");

  if ($is_main) {
    walk(SYSPATH."themes");
  }

  $count_now = count($i18n);

  if ($count_prev !== $count_now) {
    dump_php_array($i18n_file, $i18n);
  }

  printf("%s : %s files, %s strings, %s new, %s untranslated\n", $module, $counter_file, $count_now, $counter_item_new, $counter_item_untranslated);
}

function message($path)
{
  global $counter_file;
  foreach (new RecursiveIteratorIterator (new RecursiveDirectoryIterator ($path)) as $file)
  {
    if ($file->getExtension() === 'php') {
      $filename = $file->getPathname();
      $counter_file++;
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
  global $counter_file;
  foreach (new RecursiveIteratorIterator (new RecursiveDirectoryIterator ($path)) as $file)
  {
    if ($file->getExtension() === 'php') {
      $filename = $file->getPathname();
      $counter_file++;
      parse_file(file_get_contents($filename));
    }
  }
}

function parse_file($data)
{
  preg_match_all('/\b__\(\'((?:\\\\.|[^\\\\\']+)+)\'/', $data, $match, PREG_PATTERN_ORDER);

  foreach ($match[1] as $string) {
    $string = preg_replace('/\\\\(.)/', '$1', $string);
    add_to_i18n ($string);
  }

  preg_match_all('/\b__\("((?:\\\\.|[^\\\\"]+)+)"/', $data, $match, PREG_PATTERN_ORDER);

  foreach ($match[1] as $string) {
    $string = str_replace('\n', "\n", $string);
    add_to_i18n ($string);
  }
}

function add_to_i18n ($string) {
  global $dict, $i18n, $counter_item_new, $counter_item_untranslated;

  if ( ! array_key_exists($string, $dict) && ! empty($i18n[$string])) {
    $dict[$string] = $i18n[$string];
    return;
  }

  if (empty($string) || !empty($i18n[$string]) || !preg_match('/[a-zA-Z]/', $string)) {
    return;
  }


  if ( ! array_key_exists($string, $dict)) {
    $counter_item_new++;
    $dict[$string] = '';
  }

  if (empty($dict[$string])) {
    $counter_item_untranslated++;
  } else {
    $i18n[$string] = $dict[$string];
  }

}

function load_dictonary()
{
  if (file_exists(I18N_DICTONARY)) {
    return include I18N_DICTONARY;
  }
  return array();
}

function dump_php_array($file, Array $array)
{
  uksort($array, 'strnatcasecmp');

  file_put_contents($file, '<?php defined("SYSPATH") OR die("No direct script access.");'."\n".'return '.var_export($array, true).';');
}
