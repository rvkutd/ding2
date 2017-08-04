<?php

namespace Drupal\aleph\Aleph\Handler;

use Drupal\aleph\Aleph\AlephClient;

/**
 * Class AlephMaterialHandler.
 *
 * @package Drupal\aleph\Aleph\Handler
 */
class AlephMaterialHandler extends AlephHandlerBase {

  /**
   * AlephMaterialHandler constructor.
   *
   * @param \Drupal\aleph\Aleph\AlephClient $client
   *    Instance of the Aleph Client.
   */
  public function __construct(AlephClient $client) {
    parent::__construct($client);
  }

  /**
   * {@inheritdoc}
   */
  protected function getIdentity() {
    return 'AlephMaterialHandler';
  }

}
