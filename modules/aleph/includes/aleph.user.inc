<?php

/**
 * @file
 * Handles user authentication with the aleph provider.
 */

/**
 * Implements hook_user_authenticate().
 *
 * {@inheritdoc}
 */
function aleph_user_authenticate($uid, $pass) {
  $return['success'] = FALSE;

  // Get the patron object.
  $patron = aleph_get_patron($uid, $pass);

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
