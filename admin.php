<?php

/**
 * @file
 * Implements admin functions.
 */

// Module ACL definitions.
$this("acl")->addResource('helpers', [
  'jsonview',
  'jsonedit',
  'quickactions',
  'assets',
  'collectionSelect',
  'singletonSelect'
]);

$this->on('admin.init', function () use ($app) {

  $this->helper('admin')->addAssets('helpers:assets/css/helpers.css');
  $settings = $app->config['helpers'] ?? [];
  extract($settings);

  // Add assets to modules menu.
  if ($app->module('cockpit')->hasaccess('helpers', 'assets')) {
    $this('admin')->addMenuItem('modules', [
      'label' => 'Assets',
      'icon' => 'assets:app/media/icons/assets.svg',
      'route' => '/assetsmanager',
      'active' => strpos($this['route'], '/assetsmanager') === 0,
    ]);
  }

  // Load environment specific css.
  if (!empty($environment) && $app->path("helpers:assets/css/helpers-{$environment}.css")) {
    $this->helper('admin')->addAssets("helpers:assets/css/helpers-{$environment}.css");
  }

  // Check we have quickactions in the configuration.
  if (!empty($quickactions) && $app->module('cockpit')->hasaccess('helpers', 'quickactions')) {
    $this->helper('admin')->addAssets('helpers:assets/cp-quickactions.tag');
    $this->helper('admin')->addAssets('helpers:assets/quickactions.js');
  }

  // Load custom fields.
  $this->helper('admin')->addAssets('helpers:assets/field-collectionselect.tag');
  $this->helper('admin')->addAssets('helpers:assets/field-singletonselectlink.tag');
  $this->helper('admin')->addAssets('helpers:assets/renderers.js');
});

/**
 * Add json entry view on collections entry sidebar.
 */
$this->on('collections.entry.aside', function ($name) use ($app) {
  $perms['entryEdit'] = $app->module('collections')->hasaccess($name, 'entries_edit');
  $perms['entryCreate'] = $app->module('collections')->hasaccess($name, 'entries_create');
  $perms['entryDelete'] = $app->module('collections')->hasaccess($name, 'entries_delete');
  $perms['jsonEdit'] = $app->module('cockpit')->hasaccess('helpers', 'jsonedit');
  $perms['jsonView'] = $app->module('cockpit')->hasaccess('helpers', 'jsonview');
  $this->renderView("helpers:views/partials/extra-actions-aside.php", ['permissions' => $perms]);
  if ($perms['jsonView']) {
    $this->renderView("helpers:views/partials/json-entry-aside.php");
  }

  // Collection preview.
  $settings = $app->config['helpers'] ?? [];
  extract($settings);
  if (isset($preview) && !empty($preview['url'])) {
    $app->trigger("helpers.preview.url", [&$preview]);
    $this->renderView("helpers:views/partials/collection-preview.php", [
      "previewUrl" => $preview['url'],
      "previewToken" => $preview['token'] ?? ''
    ]);
  }
});

/**
 * Add json entry view on collections entry sidebar.
 */
$this->on('singletons.form.aside', function () use ($app) {
  if ($app->module('cockpit')->hasaccess('helpers', 'jsonview')) {
    $editAccess = $app->module('cockpit')->hasaccess('helpers', 'jsonedit');
    $this->renderView("helpers:views/partials/json-singleton-entry-aside.php", ["editAccess" => $editAccess]);
  }
});


/**
 * Extend cockpit internal search.
 */
$app->on('cockpit.search', function($search, $list) use ($app) {
  $settings = $app->config['helpers'] ?? [];
  extract($settings);

  if (!isset($cockpitSearch) || empty($cockpitSearch['collections'])) {
    return;
  }

  $search = preg_quote($search, '/');

  $options['limit'] = $search['limit'] ?? 10;
  $options['simple'] = 1;
  $options['populate'] = 0;
  $options['sort'] = ['_modified' => -1];

  foreach ($cockpitSearch['collections'] as $name => $field) {
    $options['filter'] = [$field => ['$regex' => $search, '$options' => 'i']];
    $options['fields'] = [
      '_id' => 1,
      $field => 1,
    ];

    $entries = $app->module('collections')->find($name, $options);
    if (!empty($entries)) {
      foreach ($entries as $key => $entry) {
        $list[] = [
          'icon'  => 'file-text',
          'title' => $entry[$field],
          'url'   => $this->routeUrl("/collections/entry/{$name}/{$entry['_id']}"),
        ];
      }
    }
  }
});
