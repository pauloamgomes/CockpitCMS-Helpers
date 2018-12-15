<?php

/**
 * @file
 * Implements CLI Command for exporting single collections data.
 */

if (!COCKPIT_CLI) {
  return;
}

$name = $app->param('name', NULL);

if (!$name) {
  return CLI::writeln("--name parameter is missing", FALSE);
}

$collection = $app->module('collections')->collection($name);
if (!$collection) {
  return CLI::writeln("Cannot find collection with name ${name}", FALSE);
}

$cid = $collection['_id'];
$items = $app->storage->find("collections/{$cid}")->toArray();

$total = count($items);

if (!$total) {
  return CLI::writeln("Collection ${name} has no entries.", FALSE);
}

CLI::writeln("Exporting collection {$name} (${total} entries) to #storage:exports/collections/{$name}.json");
$res = $app->helper('fs')->write("#storage:exports/collections/{$name}.json", json_encode($items, JSON_PRETTY_PRINT));
if (!$res) {
  return CLI::writeln("Error writing collection entries data to ${name}", FALSE);
}

CLI::writeln("Collection ${name} entries exported to #storage:exports/collections/${name}.json - ${res} bytes written", TRUE);


