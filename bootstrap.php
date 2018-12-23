<?php

/**
 * @file
 * Implements bootstrap commands.
 */

// CLI includes.
if (COCKPIT_CLI) {
  $this->path('#cli', __DIR__ . '/cli');
}

// Incldude admin.
if (COCKPIT_ADMIN && !COCKPIT_API_REQUEST) {
  $this->module('helpers')->extend([
    'getQuickActions' => function() {
      $config = $this->app->config['helpers'];
      return $config['quickactions'] ?? [];
    },
  ]);

  include_once __DIR__ . '/admin.php';
  include_once __DIR__ . '/actions.php';
}
