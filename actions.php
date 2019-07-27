<?php

$app->on('collections.save.before', function($name, $entry, $isUpdate) use ($app) {

    $config = $app->config['helpers'];

    if (empty($config['uniqueFields']) || empty($config['uniqueFields'][$name])) {
      return;
    }

    $uniqueFields = $config['uniqueFields'][$name];

    foreach ($uniqueFields as $field) {
      if (!isset($entry[$field]) || !$entry[$field]) continue;

      $filter = [];
      $filter[$field] = $entry[$field];

      if ($isUpdate && isset($entry['_id'])) {
        if ($app->storage->type === 'mongodb') {
          $filter['_id'] = ['$ne' => new MongoDB\BSON\ObjectId($entry['_id'])];
        }
        else {
          $filter['_id'] = ['$not' => $entry['_id']];
        }
      }

      $check = $app->module('collections')->findOne($name, $filter);

      if ($check) {
        $app->stop(['error' => "<strong>{$field}</strong> must be unique!"], 412);
      }
    }
});

/**
 * Recheck collection structure and remove fields that doesnt exist anymore.
 */
$app->on('collections.save.after', function($name, &$entry, $isUpdate) use($app) {
  // Handle max revisions.
  if ($isUpdate) {
    $app->module('helpers')->removeMaxRevisions('collections', $name, $entry['_id']);
  }

  // Enforce collection schema update if there are changes.
  $config = $app->config['helpers'];
  $collection = $app->module('collections')->collection($name);
  if (empty($config['checkSchema']) || !$config['checkSchema']) {
    return;
  }

  $core_fields = ['_id', '_mby', '_by', '_modified', '_created', '_o', '_pid'];

  $collection_fields = array_map(function($item) {
    return $item['name'];
  }, $collection['fields']);

  // We retrieve list of languages because field may contain locale key.
  // E.g. field_jp or field_example_fr.
  $locales = [];
  foreach ($app->retrieve('config/languages', []) as $key => $val) {
    if (is_numeric($key)) $key = $val;
    $locales[] = $key;
  }

  $update = FALSE;
  foreach ($entry as $name => $value) {
    if (in_array($name, $core_fields)) {
      continue;
    }

    // Since we need to be sure that we don't remove service field with _slug, _locale or _locale_slug.
    // Clean field from services parts above.
    // E.g. field_example_slug -> field_example, 
    // another_field_example_jp_slug -> another_field_example.
    
    $pure_field = $name;
    $field_parts = explode("_", $name);
    if (count($field_parts) > 1) {
      $pure_field = implode(
        array_filter($field_parts, function($part) use ($locales) {
          return $part !== "slug" && !in_array($part, $locales);
        }
      ), "_");
    }

    if (!in_array($pure_field, $collection_fields)) {
      $update = TRUE;
      unset($entry[$name]);
    }
  }

  // Since core update performs an array merge between old and new data.
  // We need to remove and re-insert the entry (the id will not change).

  if ($update) {
    // Remove entry.
    $app->storage->remove("collections/{$collection['_id']}", ['_id' => $entry['_id']]);

    // Reinsert entry with removed fields.
    $app->storage->insert("collections/{$collection['_id']}", $entry);
  }
});

/**
 * Recheck singleton structure and remove fields that doesnt exist anymore.
 */
$app->on('singleton.saveData.before', function($singleton, &$data) use($app) {
  $config = $app->config['helpers'];
  if (empty($config['checkSchema']) || !$config['checkSchema']) {
    return;
  }

  if (empty($data) || !is_array($data)) {
    return;
  }

  $core_fields = ['_mby', '_by'];

  $singleton_fields = array_map(function($item) {
    return $item['name'];
  }, $singleton['fields']);

  $locales = [];
  foreach ($app->retrieve('config/languages', []) as $key => $val) {
    if (is_numeric($key)) $key = $val;
    $locales[] = $key;
  }

  foreach ($data as $name => $value) {
    if (in_array($name, $core_fields)) {
      continue;
    }
    
    $pure_field = $name;
    $field_parts = explode("_", $name);
    if (count($field_parts) > 1) {
      $pure_field = implode(
        array_filter($field_parts, function($part) use ($locales) {
          return $part !== "slug" && !in_array($part, $locales);
        }
      ), "_");
    }

    if (!in_array($pure_field, $singleton_fields)) {
      unset($data[$name]);
    }
  }
});

/**
 * Handle singletons on saveData after.
 */
$app->on('singleton.saveData.after', function($singleton, $data) use($app) {
  if (!empty($singleton['_id'])) {
    $app->module('helpers')->removeMaxRevisions('singletons', $singleton['name'], $singleton['_id']);
  }
});
