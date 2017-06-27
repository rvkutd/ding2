<?php

/**
 * @file
 * Provides the AlephPatron.
 */

use Drupal\aleph\Aleph\AlephClient;
use Drupal\aleph\Aleph\Handler\AlephUserHandler;

/**
 * Class AlephPatron.
 */
class AlephPatron extends AlephUserHandler {

  private $borId;
  private $verification;

  /**
   * Constructor.
   *
   * @param \Drupal\aleph\Aleph\AlephClient $client
   *    The Aleph Client.
   * @param string $borId
   *    Aleph borrower ID.
   * @param string $verification
   *    Aleph borrower pin.
   */
  public function __construct(AlephClient $client, $borId, $verification) {
    parent::__construct($client);
    $this->borId = $borId;
    $this->verification = $verification;
  }

  /**
   * Check if the patron is authenticated.
   *
   * @return bool
   *    Returns TRUE if authentication succeeded.
   */
  public function isAuthenticated() {
    if ($this->client->borAuth($this->borId, $this->verification)) {
      return TRUE;
    }
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
   * Return the patron's name.
   *
   * @return string
   *    The patron's name.
   */
  public function getName() {
    $xml = $this->client->borAuth($this->borId, $this->verification);
    return (string) $xml->xpath('z303/z303-name')[0];
  }

  /**
   * Return users block codes and messages.
   *
   * @return array
   *    Array with an array for each identifier (z303-delinq-1).
   *    Each array contains an array with a code and a message.
   */
  public function getBlockCodes() {
    $xml = $this->client->borAuth($this->borId, $this->verification);
    $block_codes = array();

    $codes = array(
      'z305-delinq-1' => 'z305-delinq-n-1',
      'z305-delinq-2' => 'z305-delinq-n-2',
      'z305-delinq-3' => 'z305-delinq-n-3',
    );

    foreach ($codes as $code => $message) {
      $block_codes[$code] = array(
        'code' => (string) $xml->xpath('z305/' . $code)[0],
        'message' => (string) $xml->xpath('z305/' . $message)[0],
      );
    }

    return $block_codes;
  }

}
