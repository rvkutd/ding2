<?php

/**
 * @file
 * Handles user loans.
 */

use Drupal\aleph\Aleph\Handler\AlephPatronHandler;

/**
 * Implements provider loan, list.
 *
 * @return array
 *   Loan information.
 *
 * @throws \DingProviderAuthException
 *   Given operation cannot be completed until the user authenticates.
 */
function aleph_loan_list() {
  // Get creds is outside cache function as it may throw exception if the user
  // is not longer logged in.
  $patron = aleph_get_patron_by_account();
  $patron_handler = new AlephPatronHandler(aleph_client());
  $patron_handler->setPatron($patron);
  $loans = $patron_handler->getLoans();
  $allowed_branches = array_keys(aleph_get_branches());

  $results = array();

  $ids = [];
  foreach ($loans as $loan) {
    $id = $loan->getId();
    $ids[$id] = $id;
  }
  $ids = ding_provider_build_entity_id($ids);

  foreach ($loans as $loan) {
    // Filter the list; only show materials for the branches in
    // aleph_get_branches().
    if (in_array($loan->getSubLibraryCode(), $allowed_branches, TRUE)) {
      $material_id = $loan->getId();
      $loan_data = array(
        'ding_entity_id' => $ids[$material_id],
        'display_name' => $loan->getTitle(),
        'loan_date' => $loan->getLoanDate(),
        'expiry' => $loan->getDueDate(),
        'materials_number' => $material_id,
        'renewable' => $loan->isRenewable(),
      );

      $results[$material_id] = new DingProviderLoan($material_id, $loan_data);
    }
  }

  return $results;
}

/**
 * Implements provider loan, renew.
 *
 * @param object $account
 *    Drupal user object to renew loan for.
 * @param array $loan_ids
 *    The loan IDs.
 *
 * @return array Result of renewals.
 *    Result of renewals.
 */
function aleph_loan_renew($account, array $loan_ids) {
  $patron = aleph_get_patron_by_account($account);
  $patron_handler = new AlephPatronHandler(aleph_client());
  $patron_handler->setPatron($patron);
  $returned_loans = $patron_handler->renewLoans($loan_ids);
  $result = array();

  foreach ($returned_loans as $id => $returned_loan) {
    if ($returned_loan->isRenewed()) {
      $result[$id] = DingProviderLoan::STATUS_RENEWED;
    }
    else {
      $result[$id] = DingProviderLoan::STATUS_NOT_RENEWED;
    }
  }

  return $result;
}
