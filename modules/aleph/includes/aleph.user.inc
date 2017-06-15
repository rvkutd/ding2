<?php
/**
 * @file
 * Handles user authentication with the aleph provider.
 */

 /**
  * Implements hook_user_authenticate().
  */
 function aleph_user_authenticate($uid, $pass) {
   $client = aleph_client();
   return $client->authenticate($uid, $pass);
 }
