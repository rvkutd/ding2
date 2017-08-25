<?php

namespace Drupal\aleph\Aleph\Handler;

use Drupal\aleph\Aleph\AlephDebt;
use Drupal\aleph\Aleph\AlephMaterial;
use Drupal\aleph\Aleph\AlephPatron;
use Drupal\aleph\Aleph\AlephClient;
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
   */
  public function __construct(AlephClient $client) {
    parent::__construct($client);
    $this->client = $client;
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
      $this->setPatron($patron);
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
   * {@inheritdoc}
   */
  protected function getIdentity() {
    return 'AlephPatronHandler';
  }

}
