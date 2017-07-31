<?php

namespace Drupal\aleph\Aleph;

/**
 * @file
 * Provides the AlephPatron.
 */

use Drupal\aleph\Aleph\Handler\AlephPatronHandler;

/**
 * Class AlephPatron.
 */
class AlephPatron extends AlephPatronHandler {

  private $borId;
  private $verification;
  private $authenticated;
  private $name;

  /**
   * Constructor.
   *
   * @param \Drupal\aleph\Aleph\AlephClient $client
   *    The Aleph Client.
   */
  public function __construct(AlephClient $client) {
    parent::__construct($client);
  }

  /**
   * Check if patron is authenticated.
   */
  public function isAuthenticated() {
    return (bool) $this->authenticated;
  }

  /**
   * Set user authentication state.
   *
   * @param bool $authenticated
   *    If the user is authenticated.
   */
  public function setAuthenticated($authenticated) {
    $this->authenticated = $authenticated;
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
    return $this->name;
  }

  /**
   * Set the patron's name.
   *
   * @param string $name
   *    The patron's name.
   */
  public function setName($name) {
    $this->name = $name;
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
      $response = $this->client->authenticate($this->getId(), $this->getVerification());
      if (empty($response->xpath('error'))) {
        $block_codes[$code] = array(
          'code' => (string) $response->xpath('z305/' . $code)[0],
        );
      }
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
    $request = $this->client->request('GET', 'bor-info', array(
      'bor_id' => $this->getId(),
      'verification' => $this->getVerification(),
    ));
    $response = $request->xpath('item-l');
    $loans = array();
    foreach ($response as $id => $material) {
      $loans[$id] = new AlephMaterial($this->client, $material);
    }
    return $loans;
  }

  /**
   * Returns the patron's ID.
   *
   * @return string
   *    The bor_id.
   */
  public function getId() {
    return $this->borId;
  }

  /**
   * Set the patron's ID.
   *
   * @param string $bor_id
   *    The patron's ID.
   */
  public function setId($bor_id) {
    $this->borId = $bor_id;
  }

  /**
   * Get the verification code.
   *
   * @return string
   *    The pin/verification code.
   */
  public function getVerification() {
    return $this->verification;
  }

  /**
   * Set verification for patron.
   *
   * @param string $verification
   *    The patron's pin code.
   */
  public function setVerification($verification) {
    $this->verification = $verification;
  }

}
