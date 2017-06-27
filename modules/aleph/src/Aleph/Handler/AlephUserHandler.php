<?php

namespace Drupal\aleph\Aleph\Handler;

use AlephPatron;
use Drupal\aleph\Aleph\AlephClient;

/**
 * Class AlephUserHandler.
 */
class AlephUserHandler extends AlephHandlerBase {

  /**
   * The Aleph client.
   *
   * @var \Drupal\aleph\Aleph\AlephClient
   */
  protected $client;

  /**
   * AlephUserHandler constructor.
   *
   * @param \Drupal\aleph\Aleph\AlephClient $client
   *    The Aleph Client.
   */
  public function __construct(AlephClient $client) {
    parent::__construct($client);
    $this->client = $client;
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
   *    The Aleph patron.
   */
  public function getPatron($bor_id, $verification) {
    return new AlephPatron($this->client, $bor_id, $verification);
  }

  /**
   * {@inheritdoc}
   */
  protected function getIdentity() {
    return 'AlephUserHandler';
  }
}