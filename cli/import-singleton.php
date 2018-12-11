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

if (!$data = $app->helper('fs')->read("#storage:exports/singletons/${name}.json")) {
  return CLI::writeln("Cannot read from #storage:exports/singletons/${name}.json", FALSE);
}

$data = json_decode($data, TRUE);

$res = $app->module('singletons')->saveData($name, $data, ['revision' => TRUE]);

if (!$res) {
  return CLI::writeln("Error updating singleton ${name}", FALSE);
}

CLI::writeln("Singleton ${name} data imported from #storage:exports/singletons/${name}.json");
