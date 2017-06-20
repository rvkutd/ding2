<?php

/**
 * @file
 * Provides a client for the Aleph library information webservice.
 */

require __DIR__ . '/../../vendor/autoload.php';

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
   * @var GuzzleHttp\Client
   */
  private $client;

  /**
   * The returned DOMDocument object from the request.
   *
   * @var \DOMDocument
   */
  private $dom;

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
   * @return DOMDocument
   *    A DOMDocument object with the response.
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
      $dom = new DOMDocument();
      $dom->loadXML($response->getBody());

      // Check for errors from Aleph and throw error exception.
      $error_message = NULL;
      $error_nodes = $dom->getElementsByTagName('error');

      if (!empty($error_nodes[0])) {
        $error_message = $error_nodes[0]->nodeValue;
      }

      if (!$check_status || !is_null($error_message)) {
        throw new AlephClientCommunicationError('Status is not okay: ' . $error_message);
      }

      // If there's no errors, return the dom.
      else {
        return $this->dom = $dom;
      }
    }

    // Throw exception if the status from Aleph is not OK.
    else {
      throw new AlmaClientHTTPError('Request error: ' . $response->code . $response->error);
    }
  }

  /**
   * Authenticate against Aleph.
   *
   * @param string $bor_id
   *    The user ID (z303-id).
   * @param string $verification
   *    The user pin-code/verification code.
   * @param string $library
   *    The global library.
   * @param string $sub_library
   *    The sub-library.
   *
   * @return array
   *    Array with user information and 'success'-key with true or false.
   */
  public function authenticate($bor_id, $verification, $library = 'ICE53', $sub_library = 'BBAAA') {
    $return = array('success' => FALSE);

    try {
      $response = $this->request('GET', 'bor-auth', array(
        'bor_id' => $bor_id,
        'verification' => $verification,
        'library' => $library,
        'sub_library' => $sub_library,
      ));

      // Set credentials.
      $return['creds'] = array(
        'name' => $bor_id,
        'pass' => $verification,
      );

      // Check if the user is blocked for each sub-library.
      $is_blocked = FALSE;

      $sub_libraries = array(
        // Element name of the status-code => message for the status.
        // Local record (Z305). Aleph can hold three block codes.
        'z305-delinq-1' => 'z305-delinq-n-1',
        'z305-delinq-2' => 'z305-delinq-n-2',
        'z305-delinq-3' => 'z305-delinq-n-3',
      );

      foreach ($sub_libraries as $block_code => $message) {
        $values = $this->get(array($block_code, $message));

        // If block code is not 00, the user is blocked.
        if ($values[$block_code] !== '00') {
          $is_blocked = TRUE;
          // Display the reason why from Aleph.
          drupal_set_message($values[$message], 'error');
        }
      }

      if ($response && !$is_blocked) {
        $return['success'] = TRUE;
      }

      else {
        $return['success'] = FALSE;
      }
    }
    catch (Exception $e) {
      watchdog('aleph', 'Authentication error for user @user: “@message”', array(
        '@user' => $bor_id,
        '@message' => $e->getMessage(),
      ), WATCHDOG_ERROR);
    }
    return $return;
  }

  /**
   * Get dom elements by tag name.
   *
   * @param array $tags
   *    The tags for the values you want to have returned.
   *
   * @return array
   *    Array with the tag as key and the value as value or an empty array.
   */
  public function get($tags) {
    // Array to store the returned key/value pairs.
    $tag_values = array();

    // Loop through each provided tag.
    foreach ($tags as $tag) {
      // Extract each value from all the tags.
      if ($results = $this->dom->getElementsByTagName($tag)) {
        foreach ($results as $result) {
          // If there's multiple matches, the last one is used.
          $tag_values[$tag] = $result->nodeValue;
        }
      }
    }
    return $tag_values;
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
