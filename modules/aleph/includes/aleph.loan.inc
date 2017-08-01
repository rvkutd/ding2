<?php

/**
 * @file
 * Handles user loans.
 */

/**
 * Implements provider loan, list.
 *
 * @param object $account
 *   User to fetch list for.
 *
 * @return array
 *   Loan information.
 */
function aleph_loan_list($account) {
  // Get creds is outside cache function as it may throw exception if the user
  // is not longer logged in.
  $patron = aleph_get_patron_by_global_account($account);
  $loans = $patron->getLoans($patron);

  $result = array();

  foreach ($loans as $loan) {
    $loan_data = array(
      'ding_entity_id' => ding_provider_build_entity_id($loan->getId()),
      'display_name' => $loan->getTitle(),
      'loan_date' => $loan->getLoanDate(),
      'expiry' => $loan->getDueDate(),
      'materials_number' => $loan->getId(),
      'renewable' => $loan->isRenewable(),
    );

    $result[] = new DingProviderLoan($loan->getId(), $loan_data);
  }

  return $result;
}
