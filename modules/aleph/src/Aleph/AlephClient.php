<?php

namespace Drupal\aleph\Aleph;

/**
 * @file
 * Provides a client for the Aleph library information webservice.
 */

require __DIR__ . '/../../vendor/autoload.php';

use GuzzleHttp\Client;
use Exception;
use SimpleXMLElement;

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
    if (stripos($base_url, 'http') === 0 && filter_var($base_url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)) {
      $this->baseUrl = $base_url;
      $this->client = new Client();
    }
    else {
      // TODO: Use a specialised exception for this.
      throw new Exception('Invalid base URL: ' . $base_url);
    }
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
   * @param bool $check_status
   *    Check the status element, and throw an exception if it is not ok.
   *
   * @return \SimpleXMLElement
   *    A SimpleXMLElement object.
   *
   * @throws AlephClientCommunicationError
   * @throws AlmaClientHTTPError
   */
  public function request($method, $operation, array $params, $check_status = TRUE) {
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
      $xml = new SimpleXMLElement($response->getBody());

      // Check for errors from Aleph and throw error exception.
      $error_message = $xml->xpath('error');

      if (!$check_status || !empty($error_message)) {
        throw new AlephClientCommunicationError('Status is not okay: ' . $error_message[0]);
      }

      // If there's no errors, return the SimpleXMLElement.
      return $xml;
    }

    // Throw exception if the status from Aleph is not OK.
    throw new AlmaClientHTTPError('Request error: ' . $response->code . $response->error);
  }

  /**
   * Aleph bor-auth (authentication) request.
   *
   * @param string $bor_id
   *    The patron's ID.
   * @param string $verification
   *    The patron's pin code.
   * @param string|null $branch
   *    The local branch. Hardcoded for now.
   *
   * @return \SimpleXMLElement
   *    The response in a SimpleXMLElement.
   */
  public function borAuth($bor_id, $verification, $branch = 'BBAAA') {
    $operation = array(
      'bor_id' => $bor_id,
      'verification' => $verification,
      'library' => 'ICE53',
    );

    if ($branch) {
      $operation['sub_library'] = $branch;
    }

    $response = $this->request('GET', 'bor-auth', $operation);

    return $response;
  }

}

/**
 * AlephClientInvalidURLError exception.
 */
class AlephClientInvalidURLError extends Exception {}

/**
 * AlephClientHTTPError exception.
 */
class AlephClientHTTPError extends Exception {}

/**
 * AlephClientCommunicationError exception.
 */
class AlephClientCommunicationError extends Exception {}

/**
 * AlephClientInvalidPatronError exception.
 */
class AlephClientInvalidPatronError extends Exception {}

/**
 * AlephClientUserAlreadyExistsError exception.
 */
class AlephClientUserAlreadyExistsError extends Exception {}

/**
 * AlephClientBorrCardNotFound exception.
 */
class AlephClientBorrCardNotFound extends Exception {}

/**
 * AlephClientReservationNotFound exception.
 */
class AlephClientReservationNotFound extends Exception {}

/**
 * AlmaClientHTTPError exception.
 */
class AlmaClientHTTPError extends Exception {}
