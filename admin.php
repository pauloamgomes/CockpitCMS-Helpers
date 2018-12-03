<?php

/**
 * @file
 * Implements admin functions.
 */

// Module ACL definitions.
$this("acl")->addResource('helpers', [
  'jsonview',
  'quickcreate',
  'assets'
]);


$this->on('admin.init', function () use ($app) {

  $this->helper('admin')->addAssets('helpers:assets/css/helpers.css');
  $settings = $app->config['helpers'] ?? [];

  // Add assets to modules menu.
  if ($app->module('cockpit')->hasaccess('helpers', 'assets')) {
    $this('admin')->addMenuItem('modules', [
      'label' => 'Assets',
      'icon' => 'assets:app/media/icons/assets.svg',
      'route' => '/assetsmanager',
      'active' => strpos($this['route'], '/assetsmanager') === 0,
    ]);
  }

  if (!empty($settings['environment']) && $app->path('helpers:assets/css/helpers-' . $settings['environment'] . '.css')) {
    $this->helper('admin')->addAssets('helpers:assets/css/helpers-' . $settings['environment'] . '.css');
  }

  // Check we have quickactions in the configuration.
  if (!empty($settings['quickactions']) && $app->module('cockpit')->hasaccess('helpers', 'quickactions')) {
    $this->helper('admin')->addAssets('helpers:assets/cp-quickactions.tag');
    $this->helper('admin')->addAssets('helpers:assets/quickactions.js');
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
