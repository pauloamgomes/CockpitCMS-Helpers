<?php

/**
 * @file
 * Implements CLI Command for updating collection entries.
 * The update will take in consideration the field definition of the collection.
 */

if (!COCKPIT_CLI) {
  return;
}

$name = $app->param('name', TRUE);

if (!$name) {
  return CLI::writeln("--name parameter is missing", FALSE);
}

if (!$collection = $app->module('collections')->collection($name)) {
  return CLI::writeln("Collection '{$name}' doesnt exists!", FALSE);
}

$_id = $collection['_id'];

$core_fields = ['_id', '_mby', '_by', '_modified', '_created', '_o', '_pid'];

$collection_fields = array_map(function($item) {
  return $item['name'];
}, $collection['fields']);

$entries = $app->storage->getCollection("collections/{$_id}")->find();
$entries = $entries->toArray();
$updated = 0;

$locales = [];
foreach ($app->retrieve('config/languages', []) as $key => $val) {
  if (is_numeric($key)) $key = $val;
  $locales[] = $key;
}

CLI::writeln("");
CLI::writeln("Collection '{$name}' - Updating fields...");

foreach ($entries as $idx => $entry) {
  $update = FALSE;
  foreach ($entry as $field_name => $field_value) {
    if (in_array($field_name, $core_fields)) {
      continue;
    }

    $pure_field_name = $field_name;
    $field_name_parts = explode("_", $field_name);
    if (count($field_name_parts) > 1) {
      $pure_field_name = implode(
        array_filter($field_name_parts, function($part) use ($locales) {
          return $part !== "slug" && !in_array($part, $locales);
        }
      ), "_");
    }

    if (!in_array($pure_field_name, $collection_fields)) {
      $update = TRUE;
      unset($entry[$field_name]);
    }
  }

  if ($update) {
    // Since core update performs an array merge between old and new data.
    // We need to remove and re-insert the entry (the id will not change).

    // Remove entry.
    $app->storage->remove("collections/{$_id}", ['_id' => $entry['_id']]);

    // Reinsert entry with removed fields.
    $app->storage->insert("collections/{$_id}", $entry);

    CLI::writeln("Entry {$entry['_id']} updated.", TRUE);
    $updated++;
  }
}

CLI::writeln("Done! {$updated} entries updated.");
