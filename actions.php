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
        if ($this->app->storage->type === 'mongodb') {
          $filter['_id'] = ['$ne' => new MongoDB\BSON\ObjectId($entry['_id'])];
        }
        else {
          $filter['_id'] = ['$not' => $entry['_id'];
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
  $collection = $app->module('collections')->collection($name);

  // Handle max revisions.
  if ($isUpdate) {
    $app->module('helpers')->removeMaxRevisions('collections', $name, $entry['_id']);
  }

  // Check if collection structure changed.
  $core_fields = ['_id', '_mby', '_by', '_modified', '_created'];

  $collection_fields = array_map(function($item) {
    return $item['name'];
  }, $collection['fields']);

  $update = FALSE;
  foreach ($entry as $name => $value) {
    if (in_array($name, $core_fields)) {
      continue;
    }
    if (!in_array($name, $collection_fields)) {
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

  if (empty($data) || !is_array($data)) {
    return;
  }

  $core_fields = ['_mby', '_by'];

  $singleton_fields = array_map(function($item) {
    return $item['name'];
  }, $singleton['fields']);

  foreach ($data as $name => $value) {
    if (in_array($name, $core_fields)) {
      continue;
    }
    if (!in_array($name, $singleton_fields)) {
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
