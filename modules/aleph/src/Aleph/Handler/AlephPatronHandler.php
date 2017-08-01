<?php

namespace Drupal\aleph\Aleph\Handler;

use Drupal\aleph\Aleph\AlephMaterial;
use Drupal\aleph\Aleph\AlephPatron;
use Drupal\aleph\Aleph\AlephClient;
use Drupal\aleph\Aleph\AuthenticationResult;

/**
 * Class AlephPatronHandler.
 *
 * @property bool authenticated
 * @package Drupal\aleph\Aleph\Handler
 */
class AlephPatronHandler extends AlephHandlerBase {

  protected $client;
  private $patron;

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
      $patron = new AlephPatron($this->client);
      $patron->setId($bor_id);
      $patron->setVerification($verification);
      $patron->setName((string) $response->xpath('z303/z303-name')[0]);
      $result->setPatron($patron);
      $this->patron = $patron;
    }
    return $result;
  }

  /**
   * Returns the patron's loans.
   *
   * @param \Drupal\aleph\Aleph\AlephPatron $patron
   *    The Aleph patron.
   *
   * @return \Drupal\aleph\Aleph\AlephMaterial[]
   *    Array with AlephMaterials.
   */
  public function getLoans(AlephPatron $patron) {
    $request = $this->client->request('GET', 'bor-info', array(
      'bor_id' => $patron->getId(),
      'verification' => $patron->getVerification(),
    ));
    $response = $request->xpath('item-l');
    $loans = array();
    foreach ($response as $id => $material) {
      $loans[$id] = new AlephMaterial($this->client, $material);
    }
    return $loans;
  }

  /**
   * Return the authenticated Aleph Patron.
   *
   * @return AlephPatron
   *    The authenticated Aleph patron.
   */
  public function getPatron() {
    return $this->patron;
  }

  /**
   * {@inheritdoc}
   */
  protected function getIdentity() {
    return 'AlephPatronHandler';
  }

}
