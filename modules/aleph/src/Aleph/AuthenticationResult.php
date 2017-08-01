<?php

namespace Drupal\aleph\Aleph;

/**
 * Class AuthenticationResult.
 *
 * @package Drupal\aleph\Aleph
 */
class AuthenticationResult {

  private $borId;
  private $verification;
  private $client;
  private $patron;

  /**
   * AuthenticationResult constructor.
   *
   * @param \Drupal\aleph\Aleph\AlephClient $client
   *    The Aleph client.
   * @param string $bor_id
   *    The patron's ID.
   * @param string $verification
   *    The patron's pin.
   */
  public function __construct(AlephClient $client, $bor_id, $verification) {
    $this->borId = $bor_id;
    $this->verification = $verification;
    $this->client = $client;
  }

  /**
   * Check the patron is authenticated.
   */
  public function isAuthenticated() {
    if (!$this->getClientError() && !$this->isBlocked()) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Check if the user is blocked.
   */
  public function isBlocked() {
    foreach ($this->getBlockCodes() as $key => $value) {
      if ($value['code'] !== '00') {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Return users block codes and messages.
   *
   * @return array
   *    Array with an array for each identifier (z303-delinq-1).
   *    Each array contains an array with a code and a message.
   */
  public function getBlockCodes() {
    $block_codes = array();

    $codes = array('z305-delinq-1', 'z305-delinq-2', 'z305-delinq-3');

    foreach ($codes as $code) {
      $response = $this->client->authenticate($this->borId, $this->verification);
      if (!$this->getClientError()) {
        $block_codes[$code] = array(
          'code' => (string) $response->xpath('z305/' . $code)[0],
        );
      }
    }

    return $block_codes;
  }

  /**
   * Set the patron.
   *
   * @param \Drupal\aleph\Aleph\AlephPatron $patron
   *    The Patron.
   */
  public function setPatron(AlephPatron $patron) {
    $this->patron = $patron;
  }

  /**
   * Return the patron.
   *
   * @return AlephPatron
   *    The Aleph Patron.
   */
  public function getPatron() {
    return $this->patron;
  }

  /**
   * Return client error message.
   *
   * @return string|false
   *    The client error message.
   */
  public function getClientError() {
    $response = $this->client->authenticate($this->borId, $this->verification);
    if (!empty($response->xpath('error'))) {
      return (string) $response->xpath('error')[0];
    }
    return FALSE;
  }
}