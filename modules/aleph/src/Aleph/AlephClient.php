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
  private $baseUrl;

  /**
   * The GuzzleHttp Client.
   *
   * @var \GuzzleHttp\Client
   */
  private $client;

  /**
   * Constructor, checking if we have a sensible value for $base_url.
   *
   * @param string $base_url
   *   The base url for the Aleph end-point.
   *
   * @throws \Exception
   */
  public function __construct($base_url) {
    $this->baseUrl = $base_url;
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
   *    Query string parameters in the form of key => value.
   *
   * @return \SimpleXMLElement
   *    A SimpleXMLElement object.
   *
   * @throws \RuntimeException
   */
  public function request($method, $operation, array $params) {
    $query = array(
      'op' => $operation,
    );

    $options = array(
      'query' => array_merge($query, $params),
      'allow_redirects' => FALSE,
    );

    // Send the request.
    $response = $this->client->request($method, $this->baseUrl, $options);

    // Status from Aleph is OK.
    if ($response->getStatusCode() == 200) {
      $xml = new \SimpleXMLElement($response->getBody());

      // Check for errors from Aleph and throw error exception.
      $error_message = $xml->xpath('error');

      if (!empty($error_message)) {
        throw new \RuntimeException('Status is not okay: ' . $error_message[0]);
      }

      // If there's no errors, return the SimpleXMLElement.
      return $xml;
    }

    // Throw exception if the status from Aleph is not OK.
    throw new \RuntimeException('Request error: ' . $response->code . $response->error);
  }

}
