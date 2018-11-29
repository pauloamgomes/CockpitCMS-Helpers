<?php

/**
 * @file
 * Implements admin functions.
 */

// Module ACL definitions.
$this("acl")->addResource('helpers', [
  'access',
]);


$this->on('admin.init', function () use ($app) {

  $settings = $app->config['helpers'] ?? [];
  $this->helper('admin')->addAssets('helpers:assets/helpers.css');

  if (!empty($settings['environment']) && $app->path('helpers:assets/helpers-' . $settings['environment'] . '.css')) {
    $this->helper('admin')->addAssets('helpers:assets/helpers-' . $settings['environment'] . '.css');
  }
});

/**
 * Add json entry view on collections entry sidebar.
 */
$this->on('collections.entry.aside', function () use ($app) {
  if ($app->module('cockpit')->hasaccess('helpers', 'access')) {
    $this->renderView("helpers:views/partials/json-entry-aside.php");
  }
});

