<?php

/**
 * @file
 * Handles debt with Aleph.
 */

use Drupal\aleph\Aleph\Handler\AlephPatronHandler;

/**
 * Return patron debts.
 *
 * @return array
 *    Array of DingProviderDebt objects containing debts for the patron.
 */
function aleph_debt_list() {
  $result = array();
  $response = array();

  $patron = aleph_get_patron_by_global_account();
  $patron_handler = new AlephPatronHandler(aleph_client());
  $patron_handler->setPatron($patron);

  try {
    $response = $patron_handler->getDebts();
  }
  catch (Exception $e) {
    watchdog_exception('aleph', $e);
  }

  $debts = $response->xpath('charges/institution/cash');

  $id = 0;
  foreach ($debts as $debt) {
    $id++;
    $amount = str_replace(array('(', ')'), '', (string) $debt->xpath('z31/z31-sum')[0]);
    $data = array(
      'date' => (string) $debt->xpath('z31/z31-date')[0],
      'display_name' => (string) $debt->xpath('z13/z13-title')[0],
      'amount' => $amount,
      // The only thing we can do here is set it to zero, as the original
      // amount isn't available.
      'amount_paid' => 0,
      'invoice_number' => NULL,
      'type' => (string) $debt->xpath('z31/z31-type')[0],
    );
    $result[$id] = new DingProviderDebt($id, $data);
  }

  return $result;
}

function aleph_debt_payment_received() {
  return array();
}

function aleph_debt_translate_debt_type() {
  return '';
}
