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
   * @throws \Exception
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
    if ($response->getStatusCode() == 200) {
      $xml = new \SimpleXMLElement($response->getBody());
      return $xml;
    }

    // Throw exception if the status from Aleph is not OK.
    throw new \RuntimeException('Request error: ' . $response->code . $response->error);
  }

  /**
   * Send a request via the REST service.
   *
   * @param string $url
   *    The URL the send the request to.
   * @param string $method
   *    The method to use, like post, get, put, etc.
   * @param array $options
   *    The options to send via GuzzleHttp.
   *
   * @return \SimpleXMLElement
   */
  public function requestRest($url, $method, array $options) {
    $response = $this->client->request($method, $this->baseUrlRest . '/' . $url, $options);
    // Status from Aleph is OK.
    if ($response->getStatusCode() == 200) {
      $xml = new \SimpleXMLElement($response->getBody());
      return $xml;
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
   * @return \SimpleXMLElement
   */
  public function changePin(AlephPatron $patron, $new_pin) {
    $options = array();

    $xml = new \SimpleXMLElement('<get-pat-pswd></get-pat-pswd>');
    $password_parameters = $xml->addChild('password_parameters');
    $password_parameters->addChild('old-password', $patron->getVerification());
    $password_parameters->addChild('new-password', $new_pin);

    $options['body'] = $xml->asXML();

    return $this->requestRest('patron/' . $patron->getId() . '/patronInformation/password', 'POST', $options);
  }

}
