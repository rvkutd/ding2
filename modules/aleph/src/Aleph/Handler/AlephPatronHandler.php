<?php

namespace Drupal\aleph\Aleph\Handler;

use Drupal\aleph\Aleph\AlephPatron;
use Drupal\aleph\Aleph\AlephClient;

/**
 * Class AlephPatronHandler.
 *
 * @property bool authenticated
 * @package Drupal\aleph\Aleph\Handler
 */
class AlephPatronHandler extends AlephHandlerBase {

  /**
   * AlephUserHandler constructor.
   *
   * @param \Drupal\aleph\Aleph\AlephClient $client
   *    The Aleph Client.
   */
  public function __construct(AlephClient $client) {
    parent::__construct($client);
  }

  /**
   * Get patron from Aleph.
   *
   * @param string $bor_id
   *    The user ID (z303-id).
   * @param string $verification
   *    The user pin-code/verification code.
   *
   * @return AlephPatron
   *    The authenticated Aleph patron.
   */
  public function getPatron($bor_id, $verification) {
    $patron = new AlephPatron($this->client);
    $response = $this->authenticate($bor_id, $verification);
    if ($response) {
      $patron->setAuthenticated(TRUE);
      $patron->setId($bor_id);
      $patron->setVerification($verification);
      $patron->setName((string) $response->xpath('z303/z303-name')[0]);
    }
    return $patron;
  }

  /**
   * Aleph bor-auth (authentication) request.
   *
   * @param string $bor_id
   *    The borrower ID.
   * @param string $verification
   *    The borrower's verification/pin.
   *
   * @return \SimpleXMLElement|false
   *    The response in a SimpleXMLElement or FALSE if authentication failed.
   */
  public function authenticate($bor_id, $verification) {
    $response = $this->client->authenticate($bor_id, $verification);

    if (!empty($response->xpath('error'))) {
      return FALSE;
    }

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  protected function getIdentity() {
    return 'AlephPatronHandler';
  }

}
