<?php

/**
 * @file
 * Implements CLI Command to reset api tokens.
 */

if (!COCKPIT_CLI) {
  return;
}

$name = $app->param('name', NULL);
$key = $app->param('key', NULL);
$number = (int) $app->param('number', 0);

if (!$name) {
  return CLI::writeln("--name parameter is missing", FALSE);
}

if ($key && strlen($key) < 4) {
  return CLI::writeln("Invalid key provided. Ensure you have 30 chars.", FALSE);
}

if (!$key) {
  $key = substr(bin2hex(openssl_random_pseudo_bytes(64)), 0, 30);
}

$keys = $app->module('cockpit')->loadApiKeys();

if ($name === "master") {
  $keys[$name] = $key;
}
elseif ($name === "special") {
  if (!$number) {
    return CLI::writeln("--number parameter is missing (> 1)", FALSE);
  }
  $number--;
  if (!array_key_exists($number, $keys[$name])) {
    return CLI::writeln("Cannot find key number " . ($number -1) . " on special", FALSE);
  }
  else {
    $keys[$name][$number]['token'] = $key;
  }
}
else {
  return CLI::writeln("Cannot found {$name} in tokens", FALSE);
}

$res = $app->module('cockpit')->saveApiKeys($keys);
if ($res) {
  CLI::writeln("API key {$name} set to {$key}", TRUE);
}
else {
  return CLI::writeln("Error saving API key", FALSE);
}


