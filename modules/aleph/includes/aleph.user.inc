<?php

/**
 * @file
 * Handles user authentication with the aleph provider.
 */

use Drupal\aleph\Aleph\Handler\AlephPatronHandler;

/**
 * Implements hook_user_authenticate().
 *
 * {@inheritdoc}
 */
function aleph_user_authenticate($uid, $pass) {
  $return['success'] = FALSE;

  // Setup client and patron handler.
  $client = aleph_client();
  $patron_handler = new AlephPatronHandler($client);

  // Authenticate user against Aleph.
  $auth_result = $patron_handler->authenticate($uid, $pass);

  // Check if the user is authenticated.
  if ($auth_result->isAuthenticated()) {
    $return['success'] = TRUE;

    // Get patron.
    $patron = $auth_result->getPatron();

    // Set user information.
    $return['user'] = array(
      'data' => array(
        'display_name' => $patron->getName(),
      ),
    );
  }

  // Set credentials.
  $return['creds'] = array(
    'name' => $uid,
    'pass' => $pass,
  );

  return $return;
}
