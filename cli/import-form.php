<?php

/**
 * @file
 * Implements CLI Command for importing singletons data.
 */

if (!COCKPIT_CLI) {
  return;
}

$name = $app->param('name', TRUE);

if (!$name) {
  return CLI::writeln("--name parameter is missing", FALSE);
}

if (!$_form = $app->module('forms')->form($name)) {
  return CLI::writeln("Form ${name} doesnt exists!", FALSE);
}

if (!$data = $app->helper('fs')->read("#storage:exports/forms/${name}.json")) {
  return CLI::writeln("Cannot read from #storage:exports/forms/${name}.json", FALSE);
}

if (!$entries = json_decode($data, TRUE)) {
  return CLI::writeln("Cannot find any form data in #storage:exports/forms/${name}.json", FALSE);
}

CLI::writeln("Importing form {$name} (" . count($entries) . " entries)");

$count = 0;
$fid  = $_form['_id'];
foreach ($entries as &$entry) {
  $mode = "insert";
  // Check if exists.
  if ($app->storage->count("forms/{$fid}", ['_id' => $entry['_id']])) {
    $res = $app->module('forms')->save($name, $entry);
    $mode = "update";
  }
  else {
    $res = $app->storage->insert("forms/{$fid}", $entry);
  }
  if ($res) {
    $count++;
    CLI::writeln("Imported " . $entry['_id'] . " (${mode})", TRUE);
  }
  else {
    CLI::writeln("Failed importing form entry " . $entry['_id'], FALSE);
  }
}


CLI::writeln("Form {$name} import done. Imported {$count} entries", TRUE);
