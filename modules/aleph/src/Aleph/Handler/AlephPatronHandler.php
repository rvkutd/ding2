<?php

namespace Drupal\aleph\Aleph\Handler;

use Drupal\aleph\Aleph\AlephDebt;
use Drupal\aleph\Aleph\AlephMaterial;
use Drupal\aleph\Aleph\AlephPatron;
use Drupal\aleph\Aleph\AlephClient;
use Drupal\aleph\Aleph\AlephRequest;
use Drupal\aleph\Aleph\AlephReservation;
use Drupal\aleph\Aleph\AuthenticationResult;

/**
 * Class AlephPatronHandler.
 *
 * Handles authentication, getting loans, etc. for a patron.
 *
 * @package Drupal\aleph\Aleph\Handler
 */
class AlephPatronHandler extends AlephHandlerBase {

  protected $client;
  protected $patron;

  /**
   * AlephPatronHandler constructor.
   *
   * @param \Drupal\aleph\Aleph\AlephClient $client
   *    The Aleph client.
   *
   * @param \Drupal\aleph\Aleph\AlephPatron $patron
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
   *
   * @return \Drupal\aleph\Aleph\AuthenticationResult
   *    The authenticated Aleph patron.
   */
  public function authenticate($bor_id, $verification) {
    $response = $this->client->authenticate($bor_id, $verification);
    $result = new AuthenticationResult($this->client, $bor_id, $verification);
    if ($result->isAuthenticated()) {
      $patron = new AlephPatron();
      $patron->setId($bor_id);
      $patron->setVerification($verification);
      $patron->setName((string) $response->xpath('z303/z303-name')[0]);
      $result->setPatron($patron);
    }
    return $result;
  }

  /**
   * Get patron's loans.
   *
   * @var \SimpleXMLElement[] $loans
   *
   * @return \Drupal\aleph\Aleph\AlephMaterial[]
   *
   * @throws \RuntimeException
   */
  public function getLoans() {
    $result = array();
    $loans = $this->client->getLoans($this->getPatron())->xpath('loans/institution/loan');
    foreach ($loans as $loan) {
      $material = new AlephMaterial();
      $material->setTitle((string) $loan->xpath('z13/z13-title')[0]);
      $material->setId((string) $loan->xpath('z30/z30-doc-number')[0]);
      $material->setDueDate((string) $loan->xpath('z36/z36-due-date')[0]);
      $material->setLoanDate((string) $loan->xpath('z36/z36-loan-date')[0]);
      $result[] = $material;
    }
    return $result;
  }

  /**
   * Change patron's pin code.
   *
   * @param string $pin
   *    The new pin code.
   */
  public function setPin($pin) {
    $this->client->changePin($this->getPatron(), $pin);
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
   */
  public function getPatron() {
    return $this->patron;
  }

  /**
   * Get patron debts.
   *
   * @return \Drupal\aleph\Aleph\AlephDebt[]
   *    Array of AlephDebt objects.
   */
  public function getDebts() {
    $xml = $this->client->getDebts($this->getPatron());
    $debts = new AlephDebt();
    return $debts::debtsFromCashApi($xml);
  }

  /**
   * Get a patron's reservations.
   *
   * @return \Drupal\aleph\Aleph\AlephReservation[]
   * @throws \RuntimeException
   */
  public function getReservations() {
    $reservations = array();
    $hold_requests = $this->client->getReservations($this->getPatron())->xpath('hold-requests/institution/hold-request');
    foreach ($hold_requests as $hold_request) {
      if ($hold_request->xpath('z37/z37-request-type') === 'Hold Request') {
        $reservation = new AlephReservation();
        $request = new AlephRequest();
        $material = new AlephMaterial();

        // Create the request object.
        $request->setStatus((string) $hold_request->xpath('z37/z37-status')[0]);
        $request->setPickupLocation((string) $hold_request->xpath('z37/z37-pickup-location')[0]);
        $request->setOpenDate((string) $hold_request->xpath('z37/z37-open-date')[0]);
        $request->setEndRequestDate((string) $hold_request->xpath('z37/z37-end-request-date')[0]);
        $request->setDocNumber((string) $hold_request->xpath('z37/z37-doc-number')[0]);
        $request->setHoldDate((string) $hold_request->xpath('z37/z37-hold-date')[0]);
        $request->setRequestNumber((string) $hold_request->xpath('z37/z37-request-number')[0]);
        $request->setSequence(ltrim((string) $hold_request->xpath('z37/z37-sequence')[0], 0));

        // Create the material object.
        $material->setTitle((string) $hold_request->xpath('z13/z13-title')[0]);
        $material->setId((string) $hold_request->xpath('z13/z13-doc-number')[0]);

        // Create the reservation object.
        $reservation->setItem($material);
        $reservation->setRequest($request);

        // Add reservation object to array.
        $reservations[] = $reservation;
      }
    }
    return $reservations;
  }

  /**
   * {@inheritdoc}
   */
  protected function getIdentity() {
    return 'AlephPatronHandler';
  }

}
