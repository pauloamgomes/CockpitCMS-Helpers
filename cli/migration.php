<?php

/**
 * @file
 * Implements CLI Command for running basic migrations.
 * Keep in mind this is mostly a wrapper for your migrations and to ensure that
 * a standard naming/convention is used.
 */

if (!COCKPIT_CLI) {
  return;
}

$addon = $app->param('addon', TRUE);
$name = $app->param('name', TRUE);

if (!$addon) {
  return CLI::writeln("--addon parameter is missing", FALSE);
}

if (!$name) {
  return CLI::writeln("--name parameter is missing", FALSE);
}

// Check that addon exists.
if (!$module = $app->module(strtolower($addon))) {
  return CLI::writeln("Invalid addon: {$addon}", FALSE);
}

// Check that migration exists.
if (!$module = $app->module(strtolower($addon))) {
  return CLI::writeln("Invalid addon: {$addon}", FALSE);
}

$migration_file = "{$module->_dir}/migrations/{$name}.php";
$migration_function = "migration_" . str_replace("-", "_", $name);

if (!file_exists($migration_file)) {
  return CLI::writeln("Cannot find migration file: {$migration_file}", FALSE);
}

include_once $migration_file;

if (!function_exists($migration_function)) {
  return CLI::writeln("Cannot find migration function: {$migration_function}", FALSE);
}

CLI::writeln("Running migration {$name}...");

$migration_function($app);

CLI::writeln("Done!");
