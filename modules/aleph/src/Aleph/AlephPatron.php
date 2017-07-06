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
  private $authenticated;
  private $response;
  private $operation = array();

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
   * @param string $branch
   *   The branch name to authenticate against.
   *
   * @return \SimpleXMLElement|false
   *    The response in a SimpleXMLElement or FALSE if authentication failed.
   */
  public function authenticate($branch = 'BBAAA') {
    $this->operation['bor_id'] = $this->borId;
    $this->operation['verification'] = $this->verification;
    $this->operation['library'] = 'ICE53';
    $this->operation['sub_library'] = $branch;

    $response = $this->client->request('GET', 'bor-auth', $this->operation);

    if (!empty($response)) {
      $this->authenticated = TRUE;
      return $response;
    }

<<<<<<< Updated upstream
    return FALSE;
=======
    $this->authenticated = TRUE;
    return $response;
>>>>>>> Stashed changes
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

  /**
   * Returns the patron's loans.
   *
   * @return \Drupal\aleph\Aleph\AlephMaterial[]
   *    Array with AlephMaterials.
   */
  public function getLoans() {
    $request = $this->client->request('GET', 'bor-info', $this->operation);
    $response = $request->xpath('item-l');
    $loans = array();
    foreach ($response as $id => $material) {
      $loans[$id] = new AlephMaterial($this->client, $material);
    }
    return $loans;
  }

}
