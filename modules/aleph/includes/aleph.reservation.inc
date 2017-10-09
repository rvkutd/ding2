<?php
/**
 * @file
 * Handles reservation with Aleph.
 */

use Drupal\aleph\Aleph\Handler\AlephPatronHandler;

/**
 * @file
 * Handles reservation with Aleph.
 */

function aleph_reservation_options() {
  return array();
}

function aleph_reservation_default_options() {
  return array();
}

function aleph_reservation_options_submit() {
  return NULL;
}

/**
 * Provider list callback.
 *
 * @param object $account
 *    User to list reservations for.
 * @param string|null $requested_type
 *    Type of reservations to list. NULL for all.
 *
 * @return \DingProviderReservation[]
 *    Array of DingProviderReservations.
 */
function aleph_reservation_list($account, $requested_type = NULL) {
  // Throw exception if we're not logged in.
  ding_user_get_creds($account);

  $result = array(
    DING_RESERVATION_READY => array(),
    DING_RESERVATION_NOT_READY => array(),
    DING_RESERVATION_INTERLIBRARY_LOANS => array(),
  );

  try {
    $patron_handler = new AlephPatronHandler(aleph_client());
    $patron_handler->setPatron(aleph_get_patron_by_account());
    $res = $patron_handler->getReservations();
  }
  catch (Exception $e) {
    watchdog_exception('aleph', $e);
    return $result;
  }

  foreach ($res as $reservation) {
    $request = $reservation->getRequest();
    $material = $reservation->getItem();

    $type = $request->getStatus() === 'S' ? DING_RESERVATION_READY : DING_RESERVATION_NOT_READY;

    $ding_reservation = array(
      'ding_entity_id' => ding_provider_build_entity_id($material->getId()),
      'id' => $material->getId(),
      'pickup_branch_id' => $request->getPickupLocation(),
      'created' => $request->getOpenDate(),
      'expiry' => $request->getEndRequestDate(),
      'display_name' => $material->getTitle(),
      // Required by ding_reservation.
      'reservation_type' => $type,
    );

    if ($type === DING_RESERVATION_READY) {
      $ding_reservation['pickup_date'] = $request->getHoldDate();
      $ding_reservation['pickup_order_id'] = $request->getDocNumber();
      $ding_reservation['order_arrived'] = TRUE;
    }
    elseif ($type === DING_RESERVATION_NOT_READY) {
      if (!empty($request->getSequence())) {
        $ding_reservation['queue_number'] = $request->getSequence();
      }
    }

    $result[$type][$ding_reservation['id']] = new DingProviderReservation($ding_reservation['id'], $ding_reservation);
  }

  // Filter the result based on the requested type of reservations.
  if ($requested_type !== NULL && isset($result[$requested_type])) {
    return $result[$requested_type];
  }
  else {
    watchdog('aleph', 'Unknown reservation type (%type) requested', array('%type' => $requested_type), WATCHDOG_ERROR);
  }

  return $result;
}

function aleph_reservation_create() {
  return array();
}

function aleph_reservation_update() {
  return NULL;
}

function aleph_reservation_delete() {
  return NULL;
}

/**
 * Implements provider reservation, reservation_deletion_enabled.
 *
 * Check where reservation deletion have been enabled in the providers settings.
 *
 * @return bool
 *   Whether deletion is enabled.
 */
function aleph_reservation_reservation_deletion_enabled() {
  return variable_get('aleph_enable_reservation_deletion', FALSE);
}

/**
 * Return a branch name for a given branch id.
 *
 * @param string $branch_id
 *   The identifier for a given pick-up branch (e.g. BBAAA).
 *
 * @return string
 *   The human name for the id or NULL if one do not exist.
 */
function aleph_reservation_branch_name($branch_id) {
  // $branch_id _CAN_ be the human readable name of the branch.
  // Branch ID's are 5 characters, so if it's more, return it.
  if (strlen($branch_id) > 5) {
    return $branch_id;
  }

  $branches = aleph_get_branches();

  if (isset($branches[$branch_id])) {
    return $branches[$branch_id];
  }

  return NULL;
}

/**
 * Provider pickup_branches callback.
 *
 * @param $account
 *    The Drupal account object.
 *
 * @return array
 *    The branches as key and name.
 */
function aleph_reservation_pickup_branches($account) {
  // Throw exception if we're not logged in.
  ding_user_get_creds($account);
  return aleph_get_branches();
}

function aleph_reservation_get_reservations() {
  return NULL;
}

function aleph_reservation_clear_cache() {
  return NULL;
}

function aleph_reservation_exists() {
  return FALSE;
}

function aleph_reservation_format_date() {
  return NULL;
}
