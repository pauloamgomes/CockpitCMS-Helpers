<?php

/**
 * @file
 * Implements REST API events.
 */

$app->on('collections.find.after', function ($name, &$entries) use ($app) {
  // Get the collection.
  $collection = $app->module('collections')->collection($name);
  $field_name = NULL;
  $subfield_name = NULL;
  foreach ($collection['fields'] as $field) {
    if ($field['type'] === 'set') {
      foreach ($field['options']['fields'] as $subfield) {
        if ($subfield['type'] === 'singletonselectlink') {
          $field_name = $field['name'];
          $subfield_name = $subfield['name'];
        }
      }
    }
    elseif ($field['type'] === 'singletonselectlink') {
      $field_name = $field['name'];
      break;
    }
  }

  if (!$field_name) {
    return;
  }

  foreach ($entries as $idx => $entry) {
    if ($subfield_name && !empty($entry[$field_name]) && !empty($entry[$field_name][$subfield_name])) {
      $data = $app->module('singletons')->getData($entry[$field_name][$subfield_name]);
      if ($data) {
        $entries[$idx][$field_name][$subfield_name] = $data;
      }
    }
    elseif (!empty($entry[$field_name])) {
      $data = $app->module('singletons')->getData($entry[$field_name]);
      if ($data) {
        $entries[$idx][$field_name] = $data;
      }
    }
  }

});
