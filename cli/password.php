<?php

/**
 * @file
 * Implements CLI Command for changing user password.
 */

if (!COCKPIT_CLI) {
  return;
}

$uid = $app->param('uid', null);
$user = $app->param('user', null);
$pass = $app->param('pass', null);

if (!$user && !$uid) {
  return CLI::writeln("--user or --uid parameter is missing", false);
}

if (!$pass) {
  return CLI::writeln("--pass parameter is missing", false);
}

if (strlen($pass) < 4) {
  return CLI::writeln("Invalid password provided. Ensure you have at least 5 chars.", false);
}

if ($uid) {
  $account = $app->storage->findOne('cockpit/accounts', ['_id' => $uid]);
  if (!$account) {
    return CLI::writeln("No account found for uid ${uid}", false);
  }
} else {
  $account = $app->storage->findOne('cockpit/accounts', ['user' => $user]);
  if (!$account) {
    return CLI::writeln("No account found for user ${user}", false);
  }
}

$account['password'] = $app->hash($pass);
$res = $app->storage->save('cockpit/accounts', $account);
if ($res) {
  CLI::writeln("User password changed.", true);
} else {
  return CLI::writeln("Unexpected error when saving user account ${user}", false);
}
