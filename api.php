<?php

/**
 * @file
 * Implements REST API events.
 */

$app->on('collections.find.after', function ($name, &$entries) use ($app) {
  // Get the collection.
  $collection = $app->module('collections')->collection($name);
  // Exclude on unpublished state.
  $field_name = NULL;
  foreach ($collection['fields'] as $field) {
    if ($field['type'] === 'singletonselectlink') {
      $field_name = $field['name'];
      break;
    }
  }

  if (!$field_name) {
    return;
  }

  foreach ($entries as $idx => $entry) {
    if (!empty($entry[$field_name])) {
      $data = $app->module('singletons')->getData($entry[$field_name]);
      if ($data) {
        $entries[$idx][$field_name] = $data;
      }
    }
  }
});
