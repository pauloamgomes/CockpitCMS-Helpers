<?php

/**
 * @file
 * Implements CLI Command for exporting singletons data.
 */

if (!COCKPIT_CLI) {
  return;
}

$name = $app->param('name', NULL);

if (!$name) {
  return CLI::writeln("--name parameter is missing", FALSE);
}

$data = $app->module('singletons')->getData($name);
if (!$data) {
  return CLI::writeln("Cannot access singleton with name ${name}", FALSE);
}

$data = json_encode($data, JSON_PRETTY_PRINT);
CLI::writeln("Exporting data from singleton ${name}");

$res = $app->helper('fs')->write("#storage:exports/singletons/${name}.json", $data);

if (!$res) {
  return CLI::writeln("Error writing singleton data to ${name}", FALSE);
}

CLI::writeln("Singleton ${name} exported to #storage:exports/singletons/${name}.json - ${res} bytes written", TRUE);
