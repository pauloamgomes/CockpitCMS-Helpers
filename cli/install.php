<?php

/**
 * @file
 * Implements CLI Command for installing addon data.
 */

if (!COCKPIT_CLI) {
  return;
}

$name = $app->param('name', FALSE);
$force = $app->param('force', FALSE);
$nodata = $app->param('nodata', FALSE);

CLI::writeln('');
CLI::writeln('~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~');
CLI::writeln(' Cockpit CMS Addon installer');
CLI::writeln('~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~');
CLI::writeln('');

if (!$name) {
  return CLI::writeln('--name parameter is missing', FALSE);
}

// Check that addon exists.
if (!$module = $app->module(strtolower($name))) {
  return CLI::writeln("Invalid addon name: {$name}", FALSE);
}

// Check that info file exists.
if (!file_exists($module->_dir . '/info.yaml')) {
  return CLI::writeln("Cannot find addon info file at {$module->_dir} /info.yaml", FALSE);
}

// Load info file.
if (!$info = Spyc::YAMLLoad($module->_dir . '/info.yaml')) {
  return CLI::writeln("Error loading info file at {$module->_dir}/info.yaml", FALSE);
}

if (empty($info['install'])) {
  return CLI::writeln("No installation steps found at {$module->_dir}/info.yaml", FALSE);
}

if ($nodata) {
  CLI::writeln('nodata flag is active, processing only data structures, no contents will be imported');
}

$install = $info['install'];

// Handle Singletons installation.
if (!empty($install['singletons'])) {
  CLI::writeln('Processing singletons...');
  foreach ($install['singletons'] as $singleton) {
    if (empty($singleton['name']) || (empty($singleton['source']) && empty($singleton['data']))) {
      CLI::writeln("Invalid format for singletons in addon info file.", FALSE);
      continue;
    }
    $name = $singleton['name'];
    $exists = FALSE;
    if (!empty($singleton['source'])) {
      $source = $module->_dir . '/' . $singleton['source'];
      CLI::writeln(" Installing singleton {$name} from {$source}");
      if (!file_exists($source)) {
        CLI::writeln("Singleton '{$name}' source file {$source} missing.", FALSE);
        continue;
      }
      $target = $app->path('#storage:singleton/') . "{$name}.singleton.php";
      if (file_exists($target) && !$force) {
        CLI::writeln("Singleton '{$name}' already exists.", FALSE);
        $exists = TRUE;
      }
      else {
        $app->helper('fs')->copy($source, $target);
        CLI::writeln("* Singleton '{$name}' created.", TRUE);
      }
    }
    if (!empty($singleton['data']) && !$nodata) {
      // Import data.
      $source = $module->_dir . '/' . $singleton['data'];
      if (!$data = $app->helper('fs')->read($source)) {
        CLI::writeln("Cannot read json data from {$source}", FALSE);
        continue;
      }
      $data = json_decode($data, TRUE);
      if (!$exists || $force || empty($singleton['source'])) {
        $res = $app->module('singletons')->saveData($name, $data, ['revision' => TRUE]);
        if ($res) {
          CLI::writeln("* Singleton '{$name}' data imported.", TRUE);
        }
        else {
          CLI::writeln("Singleton '{$name}' data install failed.", FALSE);
        }
      }
    }
  }
}

// Handle Collections installation.
if (!empty($install['collections'])) {
  CLI::writeln('Processing collections...');
  foreach ($install['collections'] as $idx => $collection) {
    if (empty($collection['name']) || empty($collection['source'])) {
      CLI::writeln("Invalid format for collections in addon info file.", FALSE);
      continue;
    }
    $name = $collection['name'];
    $source = $module->_dir . '/' . $collection['source'];
    CLI::writeln(" Installing collection {$name} from {$source}");
    $target = $app->path('#storage:collections/') . "{$name}.collection.php";
    $exists = FALSE;
    if (file_exists($target) && !$force) {
      $exists = TRUE;
      CLI::writeln("Collection '{$name}' already exists.", FALSE);
    }
    else {
      $app->helper('fs')->copy($source, $target);
      CLI::writeln("* Collection '{$name}' created.", TRUE);
      if (!empty($install['collections'][$idx]['rules'])) {
        CLI::writeln(" Installing collection {$name} rules");
        // Import collection rules if any.
        foreach ($install['collections'][$idx]['rules'] as $rule) {
          $type = key($rule);
          $source = $module->_dir . '/' . reset($rule);
          if (!file_exists($source)) {
            continue;
          }
          $target = $app->path('#storage:collections/rules/') . "{$name}.{$type}.php";
          if (file_exists($target) && !$force) {
            CLI::writeln("Collection '{$name}' rule '{$type}' already exists.", FALSE);
          }
          else {
            if ($app->helper('fs')->copy($source, $target)) {
              CLI::writeln("* Collection '{$name}' rule '{$type}' created.", TRUE);
            }
          }
        }
      }

      if ((!$exists || $force) && !empty($install['collections'][$idx]['data']) && !$nodata) {
        $source = $module->_dir . '/' . $install['collections'][$idx]['data'];
        if (!file_exists($source)) {
          CLI::writeln("Collection data source {$source} doesn't exists!", FALSE);
          continue;
        }
        if (!$_collection = $app->module('collections')->collection($name)) {
          CLI::writeln("Collection {$name} doesn't exists!", FALSE);
          continue;
        }

        if (!$data = $app->helper('fs')->read($source)) {
          CLI::writeln("Cannot read from {$source}", FALSE);
          continue;
        }

        $entries = json_decode($data, TRUE);

        CLI::writeln(" Importing collection {$name} (" . count($entries) . " entries)");

        $count = 0;
        $cid  = $_collection['_id'];
        foreach ($entries as $entry) {
          $mode = "insert";
          // Check if exists.
          if ($app->storage->count("collections/{$cid}", ['_id' => $entry['_id']])) {
            $res = $app->module('collections')->save($name, $entry, ['revision' => TRUE]);
            $mode = "update";
          }
          else {
            $res = $app->storage->insert("collections/{$cid}", $entry);
          }
          if ($res) {
            $count++;
            CLI::writeln("* Imported collection '{$name}' entry -> _id:{$entry['_id']} ({$mode})", TRUE);
          }
          else {
            CLI::writeln("Failed importing collection entry {$entry['_id']}", FALSE);
          }
        }
        CLI::writeln("Collection {$name} import done. Imported {$count} entries", TRUE);
      }
    }
  }
}

// Handle Custom storage installation.
if (!empty($install['customStorage'])) {
  CLI::writeln('Processing custom storage...');
  foreach ($install['customStorage'] as $item) {
    $source = $module->_dir . '/' . $item['source'];
    if (!file_exists($source)) {
      continue;
    }
    $target = $app->path('#storage:') . $item['target'];
    CLI::writeln(" Importing file into {$target}");
    if (file_exists($target) && !$force) {
      CLI::writeln("File '{$target}' already exists.", FALSE);
    }
    else {
      $folder = dirname($target);
      if (!is_dir($folder)) {
        $app->helper('fs')->mkdir($folder);
      }
      if ($app->helper('fs')->copy($source, $target)) {
        CLI::writeln("* File '{$target}' created.", TRUE);
      }
    }
  }
}
