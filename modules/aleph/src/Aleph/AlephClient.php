<?php

namespace Drupal\aleph\Aleph;

/**
 * @file
 * Provides a client for the Aleph library information webservice.
 */

use GuzzleHttp\Client;

/**
 * Implements the AlephClient class.
 */
class AlephClient {
  /**
   * The base server URL to run the requests against.
   *
   * @var string
   */
  protected $baseUrl;

  protected $baseUrlRest;

  /**
   * The GuzzleHttp Client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $client;

  /**
   * Constructor, checking if we have a sensible value for $base_url.
   *
   * @param string $base_url
   *   The base url for the Aleph end-point.
   *
   * @param $base_url_rest
   *    The base url for the Aleph REST end-point.
   */
  public function __construct($base_url, $base_url_rest) {
    $this->baseUrl = $base_url;
    $this->baseUrlRest = $base_url_rest;
    $this->client = new Client();
  }

  /**
   * Perform request to the Aleph server.
   *
   * @param string $method
   *    The query method (GET, POST, etc.).
   * @param string $operation
   *    The operation to run in Aleph.
   * @param array $params
   *    The extra query parameters to send.
   * @param string $branch
   *    The branch to do the request against.
   *
   * @return \SimpleXMLElement
   *    A SimpleXMLElement object.
   *
   * @throws \RuntimeException
   */
  public function request($method, $operation, array $params = array(), $branch = 'BBAAA') {
    $options = array(
      'query' => array(
        'op' => $operation,
        'library' => 'ICE53',
        'sub_library' => $branch,
      ) + $params,
      'allow_redirects' => FALSE,
    );

    // Send the request.
    $response = $this->client->request($method, $this->baseUrl, $options);

    // Status from Aleph is OK.
    if ($response->getStatusCode() === 200) {
      return new \SimpleXMLElement($response->getBody());
    }

    // Throw exception if the status from Aleph is not OK.
    throw new \RuntimeException('Request error: ' . $response->code . $response->error);
  }

  /**
   * Send a request via the REST service.
   *
   * @param string $method
   *    The method to use, like post, get, put, etc.
   * @param string $url
   *    The URL the send the request to.
   * @param array $options
   *    The options to send via GuzzleHttp.
   *
   * @return \SimpleXMLElement
   *    The returned XML from Aleph.
   *
   * @throws \RuntimeException
   */
  public function requestRest($method, $url, array $options = array()) {
    $response = $this->client->request($method, $this->baseUrlRest . '/' . $url, $options);
    // Status from Aleph is OK.
    if ($response->getStatusCode() === 200) {
      return new \SimpleXMLElement($response->getBody());
    }

    // Throw exception if the status from Aleph is not OK.
    throw new \RuntimeException('Request error: ' . $response->code . $response->error);
  }

  /**
   * Authenticate the patron.
   *
   * @param string $bor_id
   *    Patron ID.
   * @param string $verification
   *    Patron PIN.
   *
   * @return \SimpleXMLElement
   *    The authentication response from Aleph or error message.
   *
   * @throws \RuntimeException
   */
  public function authenticate($bor_id, $verification) {
    $response = $this->request('GET', 'bor-auth', array(
      'bor_id' => $bor_id,
      'verification' => $verification,
    ));

    return $response;
  }

  /**
   * Get information about the patron.
   *
   * @param \Drupal\aleph\Aleph\AlephPatron $patron
   *    The Aleph Patron.
   *
   * @return \SimpleXMLElement
   *    The response from Aleph.
   *
   * @throws \RuntimeException
   */
  public function borInfo(AlephPatron $patron) {
    $response = $this->request('GET', 'bor-info', array(
      'bor_id' => $patron->getId(),
      'verification' => $patron->getVerification(),
    ));

    return $response;
  }

  /**
   * Change the patrons pin code.
   *
   * @param \Drupal\aleph\Aleph\AlephPatron $patron
   *    The Aleph patron.
   * @param string $new_pin
   *    The new pin code.
   *
   * @throws \RuntimeException
   */
  public function changePin(AlephPatron $patron, $new_pin) {
    $options = array();

    $xml = new \SimpleXMLElement('<get-pat-pswd></get-pat-pswd>');
    $password_parameters = $xml->addChild('password_parameters');
    $password_parameters->addChild('old-password', $patron->getVerification());
    $password_parameters->addChild('new-password', $new_pin);

    $options['body'] = 'post_xml=' . $xml->asXML();

    $this->requestRest('POST', 'patron/' . $patron->getId() . '/patronInformation/password', $options);
  }

  /**
   * Get patrons debts.
   *
   * @param \Drupal\aleph\Aleph\AlephPatron $patron
   *    The Aleph patron to get debts from.
   *
   * @return \SimpleXMLElement
   *    The SimpleXMLElement response from Aleph.
   *
   * @throws \RuntimeException
   */
  public function getDebts(AlephPatron $patron) {
    return $this->requestRest('GET', 'patron/' . $patron->getId() . '/circulationActions/cash?view=full');
  }

  /**
   * @param \Drupal\aleph\Aleph\AlephMaterial $material
   *    The Aleph material to get items from.
   *
   * @return \SimpleXMLElement
   *    The SimpleXMLElement response from Aleph.
   *
   * @throws \RuntimeException
   */
  public function getItems(AlephMaterial $material) {
    return $this->requestRest('GET', 'record/' . 'ICE01' . $material->getId() . '/items?view=full');
  }

  /**
   * Get patron's loans.
   *
   * @param \Drupal\aleph\Aleph\AlephPatron $patron
   *    The patron to get loans from.
   *
   * @return \SimpleXMLElement
   *    The response from Aleph.
   *
   * * @throws \RuntimeException
   */
  public function getLoans(AlephPatron $patron) {
    return $this->requestRest('GET', 'patron/' . $patron->getId() . '/circulationActions/loans?view=full');
  }

}
