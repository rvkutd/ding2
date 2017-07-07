<?php

namespace Drupal\aleph\Aleph;

/**
 * @file
 * Provides the AlephPatron.
 */

use Drupal\aleph\Aleph\Handler\AlephUserHandler;

/**
 * Class AlephPatron.
 */
class AlephPatron extends AlephUserHandler {

  private $borId;
  private $verification;
  private $authenticated;
  private $response;

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
    $this->response = $this->authenticate();
  }

  /**
   * Aleph bor-auth (authentication) request.
   *
   * @param string|null $branch
   *    The local branch. Hardcoded for now.
   *
   * @return \SimpleXMLElement|bool
   *    The response in a SimpleXMLElement or FALSE if authentication failed.
   */
  public function authenticate($branch = 'BBAAA') {
    $operation = array(
      'bor_id' => $this->borId,
      'verification' => $this->verification,
      'library' => 'ICE53',
    );

    if ($branch) {
      $operation['sub_library'] = $branch;
    }

    $response = $this->client->request('GET', 'bor-auth', $operation);

    if (!empty($response->xpath('error'))) {
      $this->authenticated = FALSE;
      return FALSE;
    }

    $this->authenticated = TRUE;
    return $response;

  }

  /**
   * Check if patron is authenticated.
   */
  public function isAuthenticated() {
    return (bool) $this->authenticated;
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
    return (string) $this->response->xpath('z303/z303-name')[0];
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

    $codes = array(
      'z305-delinq-1' => 'z305-delinq-n-1',
      'z305-delinq-2' => 'z305-delinq-n-2',
      'z305-delinq-3' => 'z305-delinq-n-3',
    );

    foreach ($codes as $code => $message) {
      $block_codes[$code] = array(
        'code' => (string) $this->response->xpath('z305/' . $code)[0],
        'message' => (string) $this->response->xpath('z305/' . $message)[0],
      );
    }

    return $block_codes;
  }

}
