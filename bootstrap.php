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

    'getCollectionEntries' => function($type, $limit = 25) {

      $options = [
        'populate' => 0,
        'limit' => $limit,
      ];

      $entries = $this->app->module('collections')->find($type, $options);

      return $entries ?? [];
    },

    'removeMaxRevisions' => function($type, $name, $_id) {
      $settings = $this->app->config['helpers'] ?? [];
      $maxRevisions = $settings['maxRevisions'] ?? FALSE;
      $maxRev = 0;
      if (!empty($maxRevisions[$name])) {
        $maxRev = (int) $maxRevisions[$name];
      }
      elseif (!empty($maxRevisions[$type])) {
        $maxRev = (int) $maxRevisions[$type];
      }
      $revisions = $this->app->helper('revisions')->getList($_id);
      if ($maxRev > 1 && count($revisions) > $maxRev) {
        $revisions = array_slice($revisions, $maxRev, count($revisions) - 1);
        foreach ($revisions as $revision) {
          $this->app->helper('revisions')->remove($revision['_id']);
        }
      }
    }

  ]);

  include_once __DIR__ . '/admin.php';
  include_once __DIR__ . '/actions.php';
}
