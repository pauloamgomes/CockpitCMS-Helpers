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

if (!$_collection = $app->module('collections')->collection($name)) {
  return CLI::writeln("Collection ${name} doesnt exists!", FALSE);
}

if (!$data = $app->helper('fs')->read("#storage:exports/collections/${name}.json")) {
  return CLI::writeln("Cannot read from #storage:exports/collections/${name}.json", FALSE);
}

if (!$entries = json_decode($data, TRUE)) {
  return CLI::writeln("Cannot find any collection entries in #storage:exports/collections/${name}.json", FALSE);
}

CLI::writeln("Importing collection {$name} (" . count($entries) . " entries)");

$count = 0;
$cid  = $_collection['_id'];
foreach ($entries as &$entry) {
  $mode = "insert";
  // Check if exists.
  if ($app->storage->count("collections/{$cid}", ['_id' => $entry['_id']])) {
    $res = $app->module('collections')->save($name, $entry, ['revision' => TRUE]);
    $mode = "update";
  }
  else {
    $res = $app->storage->insert("collections/{$cid}", $entry);
  }
  if ($res) {
    $count++;
    CLI::writeln("Imported " . $entry['_id'] . " (${mode})", TRUE);
  }
  else {
    CLI::writeln("Failed importing collection entry " . $entry['_id'], FALSE);
  }
}


CLI::writeln("Collection {$name} import done. Imported {$count} entries", TRUE);
