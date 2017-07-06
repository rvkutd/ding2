<?php

/**
 * @file
 * Handles user authentication with the aleph provider.
 */

use Drupal\aleph\Aleph\Handler\AlephUserHandler;

/**
 * Implements hook_user_authenticate().
 *
 * {@inheritdoc}
 */
function aleph_user_authenticate($uid, $pass) {
  $return['success'] = FALSE;

  // Get the patron object.
  $user_handler = new AlephUserHandler(aleph_client());
  $patron = $user_handler->getPatron($uid, $pass);

  // Check if the user is authenticated.
  $patron->isAuthenticated() ? $return['success'] = TRUE : $return['success'] = FALSE;

  // Check if the user is blocked.
  if ($patron->isBlocked()) {
    $return['success'] = FALSE;
    // The user is blocked. Show error(s).
    foreach ($patron->getBlockCodes() as $values) {
      drupal_set_message($values['message'], 'error');
    }
  }

  // Set credentials.
  $return['creds'] = array(
    'name' => $uid,
    'pass' => $pass,
  );

  // Set user information.
  $return['user'] = array(
    'data' => array(
      'display_name' => $patron->getName(),
    ),
  );

  return $return;
}
