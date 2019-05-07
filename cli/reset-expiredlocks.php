<?php

/**
 * @file
 * Implements CLI Command to reset expired locks.
 */

if (!COCKPIT_CLI) {
  return;
}

$time = (int) $app->param('time', 0);
if ($time) {
  $time = strtotime("-{$time} seconds");
}

$keys = $app->memory->keys('locked:*');

$count = 0;
foreach ($keys as $key) {
  $meta = $app->memory->get($key, FALSE);
  print(date('Y-m-d H:i', $meta['time']) . " " . date('Y-m-d H:i', $time));
  print "\n";
  if (!$time || ($meta && $meta['time'] < $time)) {
    $app->memory->del($key);
    $count++;
  }
}

CLI::writeln("Done! Removed {$count} expired locks");
