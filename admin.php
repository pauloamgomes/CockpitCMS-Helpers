<?php

/**
 * @file
 * Implements admin functions.
 */

// Module ACL definitions.
$this("acl")->addResource('helpers', [
  'jsonview',
  'quickcreate'
]);


$this->on('admin.init', function () use ($app) {

  $settings = $app->config['helpers'] ?? [];
  $this->helper('admin')->addAssets('helpers:assets/css/helpers.css');

  if (!empty($settings['environment']) && $app->path('helpers:assets/css/helpers-' . $settings['environment'] . '.css')) {
    $this->helper('admin')->addAssets('helpers:assets/css/helpers-' . $settings['environment'] . '.css');
  }

  if ($app->module('cockpit')->hasaccess('helpers', 'quickactions')) {
    // Check we have quickactions in the configuration.
    $config = $app->config['helpers'];
    if (!empty($config['quickactions'])) {
      $this->helper('admin')->addAssets('helpers:assets/cp-quickactions.tag');
      $this->helper('admin')->addAssets('helpers:assets/quickactions.js');
      // Add assets to modules menu.
      $this('admin')->addMenuItem('modules', [
        'label' => 'Assets',
        'icon' => 'assets:app/media/icons/assets.svg',
        'route' => '/assetsmanager',
        'active' => strpos($this['route'], '/assetsmanager') === 0,
      ]);
    }
  }
});

/**
 * Add json entry view on collections entry sidebar.
 */
$this->on('collections.entry.aside', function () use ($app) {
  if ($app->module('cockpit')->hasaccess('helpers', 'jsonview')) {
    $this->renderView("helpers:views/partials/json-entry-aside.php");
  }
});
