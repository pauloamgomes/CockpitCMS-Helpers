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
  'assets'
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
});

/**
 * Add json entry view on collections entry sidebar.
 */
$this->on('collections.entry.aside', function () use ($app) {
  // Json view.
  if ($app->module('cockpit')->hasaccess('helpers', 'jsonview')) {
    $editAccess = $app->module('cockpit')->hasaccess('helpers', 'jsonedit');
    $this->renderView("helpers:views/partials/json-entry-aside.php", ["editAccess" => $editAccess]);
  }

  // Collection preview.
  $settings = $app->config['helpers'] ?? [];
  extract($settings);
  if (isset($preview) && !empty($preview['url'])) {
    $this->renderView("helpers:views/partials/collection-preview.php", ["previewUrl" => $preview['url']]);
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

