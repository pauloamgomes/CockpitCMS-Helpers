<?php


/**
 * Recheck collection structure and remove fields that doesnt exist anymore.
 */
$app->on('collections.save.after', function($name, &$entry, $isUpdate) use($app) {
  $collection = $app->module('collections')->collection($name);

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
