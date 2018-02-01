<?php

namespace Drupal\aleph\Aleph\Handler;

use DateTime;
use Drupal\aleph\Aleph\AlephPatronInvalidPin;
use Drupal\aleph\Aleph\Entity\AlephDebt;
use Drupal\aleph\Aleph\Entity\AlephHoldGroup;
use Drupal\aleph\Aleph\Entity\AlephLoan;
use Drupal\aleph\Aleph\Entity\AlephMaterial;
use Drupal\aleph\Aleph\Entity\AlephPatron;
use Drupal\aleph\Aleph\AlephClient;
use Drupal\aleph\Aleph\Entity\AlephRequest;
use Drupal\aleph\Aleph\Entity\AlephRequestResponse;
use Drupal\aleph\Aleph\Entity\AlephReservation;
use Drupal\aleph\Aleph\AuthenticationResult;

/**
 * Class AlephPatronHandler.
 *
 * Handles authentication, getting loans, etc. for a patron.
 *
 * @package Drupal\aleph\Aleph\Handler
 */
class AlephPatronHandler extends AlephHandlerBase {

  protected $patron;

  /**
   * AlephPatronHandler constructor.
   *
   * @param \Drupal\aleph\Aleph\AlephClient $client
   *    The Aleph client.
   *
   * @param \Drupal\aleph\Aleph\Entity\AlephPatron $patron
   *    The Aleph patron.
   */
  public function __construct(AlephClient $client, AlephPatron $patron = NULL) {
    parent::__construct($client);
    $this->client = $client;
    $this->patron = $patron;
  }

  /**
   * Authenticate user from Aleph.
   *
   * @param string $bor_id
   *    The user ID (z303-id).
   * @param string $verification
   *    The user pin-code/verification code.
   * @param string[] $allowed_login_branches
   *    Allowed login branches.
   *
   * @return \Drupal\aleph\Aleph\AuthenticationResult
   *    The authenticated Aleph patron.
   *
   * @throws \RuntimeException
   */
  public function authenticate($bor_id, $verification, array $allowed_login_branches = []) {
    $response = $this->client->authenticate($bor_id, $verification);

    $result = new AuthenticationResult(
      $this->client, $bor_id, $verification, $allowed_login_branches,
      $this->getActiveBranches($bor_id)
    );

    if ($result->isAuthenticated()) {
      $patron = new AlephPatron();
      $patron->setId($bor_id);
      $patron->setVerification($verification);
      $patron->setName((string) $response->xpath('z303/z303-name')[0]);
      $this->setPatron($patron);
      $result->setPatron($patron);
    }

    return $result;
  }

  /**
   * Get patron's loans.
   *
   * @var \SimpleXMLElement[] $loans
   *
   * @return \Drupal\aleph\Aleph\Entity\AlephMaterial[]
   *
   * @throws \RuntimeException
   */
  public function getLoans() {
    $results = array();
    $loans = $this->client->getLoans($this->getPatron())->xpath('loans/institution/loan');
    foreach ($loans as $loan) {
      $material = AlephMaterial::materialFromItem($loan);
      $results[] = $material;
    }
    return $results;
  }

  /**
   * Change patron's pin code.
   *
   * @param string $pin
   *    The new pin code.
   *
   * @return bool
   *    True if setting new pincode succeeded.
   */
  public function setPin($pin) {
    try {
      return $this->client->changePin($this->getPatron(), $pin);
    }
    catch (AlephPatronInvalidPin $e) {
      watchdog_exception('aleph', $e);
      return FALSE;
    }
  }

  /**
   * Set the Aleph patron object.
   *
   * @param AlephPatron $patron
   *    The Aleph patron.
   */
  public function setPatron(AlephPatron $patron) {
    $this->patron = $patron;
  }

  /**
   * Get the Aleph patron object.
   *
   * @return \Drupal\aleph\Aleph\Entity\AlephPatron
   */
  public function getPatron() {
    return $this->patron;
  }

  /**
   * Get patron debts.
   *
   * @return \Drupal\aleph\Aleph\Entity\AlephDebt[]
   *    Array of AlephDebt objects.
   *
   * @throws \RuntimeException
   */
  public function getDebts() {
    $xml = $this->client->getDebts($this->getPatron());
    $debts = new AlephDebt();
    return $debts::debtsFromCashApi($xml);
  }

  /**
   * Get a patron's reservations.
   *
   * @return \Drupal\aleph\Aleph\Entity\AlephReservation[]
   * @throws \RuntimeException
   */
  public function getReservations() {
    $reservations = array();
    $hold_requests = $this->client->getReservations($this->getPatron())->xpath('hold-requests/institution/hold-request');
    foreach ($hold_requests as $hold_request) {
      if ((string) $hold_request->xpath('z37/z37-request-type')[0] === 'Hold Request') {
        $reservation = new AlephReservation();
        $request = new AlephRequest();
        $material = new AlephMaterial();

        $end_request_date = (string) $hold_request->xpath('z37/z37-end-request-date')[0];

        $request->setStatus((string) $hold_request->xpath('z37/z37-status')[0]);
        $request->setPickupLocation((string) $hold_request->xpath('z37/z37-pickup-location')[0]);
        $request->setOpenDate((string) $hold_request->xpath('z37/z37-open-date')[0]);
        $request->setEndRequestDate(DateTime::createFromFormat(ALEPH_DATE_FORMAT,
          $end_request_date));
        $request->setDocNumber((string) $hold_request->xpath('z37/z37-doc-number')[0]);
        $request->setHoldDate((string) $hold_request->xpath('z37/z37-hold-date')[0]);
        $request->setRequestNumber((string) $hold_request->xpath('z37/z37-request-number')[0]);
        $request->setSequence((string) $hold_request->xpath('z37/z37-sequence')[0]);
        $request->setInstitutionCode((string) $hold_request->xpath('z37/translate-change-active-library')[0]);
        $request->setItemSequence((string) $hold_request->xpath('z37/z37-item-sequence')[0]);

        $material->setTitle((string) $hold_request->xpath('z13/z13-title')[0]);
        $material->setId((string) $hold_request->xpath('z13/z13-doc-number')[0]);
        $material->setSubLibraryCode((string) $hold_request->xpath('z30-sub-library-code')[0]);

        $reservation->setItem($material);
        $reservation->setRequest($request);

        $reservations[$reservation->getItem()->getId()] = $reservation;
      }
    }
    return $reservations;
  }

  /**
   * Renew a patron's loans.
   *
   * @param $ids
   *
   * @return AlephLoan[]
   * @throws \RuntimeException
   */
  public function renewLoans($ids) {
    $response = $this->client->renewLoans($this->getPatron(), $ids);
    $loans = $response->xpath('renewals/institution/loan');
    $renewed_loans = array();

    foreach ($loans as $loan) {
      $loan_details = $this->client->getLoans(
        $this->getPatron(), (string) $loan['id'][0]
      );

      $renewed_loan = new AlephLoan();
      $renewed_loan->setLoanId((string) $loan['id'][0]);
      $renewed_loan->setStatusCode((string) $loan->xpath('status-code')[0]);
      $renewed_loan->setDocNumber((string) $loan_details->xpath('loan/z36/z36-doc-number')[0]);

      if (in_array($renewed_loan->getDocNumber(), $ids, TRUE)) {
        $renewed_loans[$renewed_loan->getDocNumber()] = $renewed_loan;
      }
    }

    return $renewed_loans;
  }

  /**
   * Create a reservation for a patron.
   *
   * @param AlephPatron $patron
   *    The Aleph patron.
   *
   * @param AlephReservation $reservation
   *    The reservation object.
   *
   * @param AlephHoldGroup[] $holding_groups
   *    The holding groups.
   *
   * @throws \RuntimeException
   *
   * @return \Drupal\aleph\Aleph\Entity\AlephRequestResponse
   */
  public function createReservation($patron, $reservation, $holding_groups) {
    $response = $this->client->createReservation(
      $patron, $reservation->getRequest(), $holding_groups
    );
    return AlephRequestResponse::createRequestResponseFromXML($response);
  }

  /**
   * Delete a reservation.
   *
   * @param AlephPatron $patron
   * @param AlephReservation $reservation
   *
   * @return \Drupal\aleph\Aleph\Entity\AlephRequestResponse
   * @throws \RuntimeException
   */
  public function deleteReservation($patron, $reservation) {
    $response = $this->client->deleteReservation($patron,
      $reservation->getRequest());

    return AlephRequestResponse::createRequestResponseFromXML($response);
  }

  /**
   * @param AlephPatron $patron
   * @param AlephMaterial $material
   *
   * @return AlephHoldGroup[]
   * @throws \RuntimeException
   */
  public function getHoldingGroups($patron, $material) {
    $groups = $this->client->getHoldingGroups($patron, $material);
    $result = [];
    foreach ($groups as $group) {
      $result[(string) $group['href']] = AlephHoldGroup::createHoldGroupFromXML($group);
    }
    return $result;
  }

  /**
   * Get the branches where the patron is active.
   *
   * @param string $bor_id
   *    The Aleph patron ID.
   *
   * @return string[] $result
   *    Array with branches the use is active in.
   *
   * @throws \RuntimeException
   */
  public function getActiveBranches($bor_id) {
    $branches = $this->client->getPatronBlocks($bor_id)->xpath('blocks_messages/institution/sublibrary/@code');
    return array_map('strval', $branches);
  }

  /**
   * {@inheritdoc}
   */
  protected function getIdentity() {
    return 'AlephPatronHandler';
  }

}
