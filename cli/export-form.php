<?php

/**
 * @file
 * Implements CLI Command for exporting single form data.
 */

if (!COCKPIT_CLI) {
  return;
}

$name = $app->param('name', NULL);

if (!$name) {
  return CLI::writeln("--name parameter is missing", FALSE);
}

$form = $app->module('forms')->form($name);
if (!$form) {
  return CLI::writeln("Cannot find form with name ${name}", FALSE);
}

$fid = $form['_id'];
$items = $app->storage->find("forms/{$fid}")->toArray();

$total = count($items);

if (!$total) {
  return CLI::writeln("Form ${name} has no data", FALSE);
}

CLI::writeln("Exporting form {$name} (${total} entries) to #storage:exports/forms/{$name}.json");
$res = $app->helper('fs')->write("#storage:exports/forms/{$name}.json", json_encode($items, JSON_PRETTY_PRINT));
if (!$res) {
  return CLI::writeln("Error writing form entries data to ${name}", FALSE);
}

CLI::writeln("Form ${name} entries exported to #storage:exports/forms/${name}.json - ${res} bytes written", TRUE);


