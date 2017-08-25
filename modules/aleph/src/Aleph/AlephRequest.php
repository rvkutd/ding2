<?php

namespace Drupal\aleph\Aleph;

/**
 * Class AlephRequest.
 *
 * Entity with setters/getters for Z37 (REQUESTS) in Aleph.
 *
 * The Z37 contains requests information.
 * The Z37 record functions both for requests for
 * loaned items (waiting requests), and for requests for delivery of available
 * items. It is not necessarily expected that a library will convert requests
 * when installing the ALEPH system. In many cases, the library will inform
 * patrons that there is a system switchover, that outstanding requests are
 * not retained, and must be re-entered after switchover.
 *
 * https://www.obvsg.at/uploads/media/Z37.pdf
 *
 * @package Drupal\aleph\Aleph
 */
class AlephRequest {

  /**
   * Z37-STATUS.
   * Hold request status.
   *
   * A = Active (newly opened, not yet processed).
   *
   * S = Hold Shelf - Request has been trapped, patron has been notified that
   * the requested material is ready to be picked up.
   *
   * W = Waiting. System generated when the "print call slip" service
   * (b- cir-12) was run, and the system detected that there was not an
   * available copy
   *
   * @var string
   */
  protected $status;

  /**
   * Z37-ITEM-SEQUENCE.
   * Item sequence number.
   *
   * The number is within one ADM number.
   *
   * @var string
   */
  protected $itemSequence;

  /**
   * Z37-SEQUENCE.
   * Sequence number of a request.
   *
   * Used to distinguish between multiple requests placed on the same item.
   *
   * @var string
   */
  protected $sequence;

  /**
   * Z37-ID.
   * Patron’s ID.
   *
   * @var string
   */
  protected $id;

  /**
   * Z37-PICKUP-LOCATION.
   * User’s pickup sub-library.
   *
   * The default is the user's home sub-library.
   * If no home sub-library was defined for the user, the system uses the
   * item’s sub-library.
   *
   * @var string
   */
  protected $pickupLocation;

  /**
   * Z37-FILTER-SUB-LIBRARY.
   * Sub-library code.
   *
   * @var string
   * @example BBAAA
   */
  protected $subLibraryCode;

  /**
   * Z37-REQUEST-TYPE.
   *
   * The type of request.
   * For example 'Hold Request'.
   *
   * @var string
   */
  protected $requestType;

  /**
   * Z37-REQUEST-DATE.
   *
   * The starting date of the period of time during which the patron is
   * interested in receiving the material.
   *
   * @var string
   * Format YYYYMMDD.
   */
  protected $requestDate;

  /**
   * Z37-END-REQUEST-DATE.
   * Last date of interest for the hold request.
   *
   * @var string
   */
  protected $endRequestDate;


  /**
   * Z37-OPEN-DATE.
   * Date the item was requested.
   *
   * @var string
   * Format YYYYMMDD.
   */
  protected $openDate;

  /**
   * Z37-DOC-NUMBER.
   * System number of the administrative record associated to the request.
   *
   * @var string
   */
  protected $docNumber;

  /**
   * Z37-HOLD-DATE.
   * The date on which a letter was sent to the patron informing him that the
   * requested material is ready to be picked up.
   * These requests have status "S".
   *
   * @var string
   */
  protected $holdDate;

  /**
   * Z37-REQUEST-NUMBER
   * The request number.
   *
   * @var string
   */
  protected $requestNumber;

  /**
   * Set the Z37 status message.
   *
   * @param string $status
   *    The Z37 status.
   */
  public function setStatus($status) {
    $this->status = $status;
  }

  /**
   * Get the Z37 status message.
   *
   * @return string
   *    The Z37 status.
   */
  public function getStatus() {
    return $this->status;
  }

  /**
   * Set the Z37 item sequence number.
   *
   * @param string $item_sequence
   *    The Z37 item sequence number.
   */
  public function setItemSequence($item_sequence) {
    $this->itemSequence = $item_sequence;
  }

  /**
   * Get the Z37 item sequence number.
   *
   * @return string
   *    The Z37 item sequence number.
   */
  public function getItemSequence() {
    return $this->itemSequence;
  }

  /**
   * Set the Z37 sequence number.
   *
   * @param string $sequence
   */
  public function setSequence($sequence) {
    $this->sequence = $sequence;
  }

  /**
   * Get the Z37 sequence number.
   *
   * @return string
   */
  public function getSequence() {
    return $this->sequence;
  }

  /**
   * Set the Z37 id.
   *
   * @param string $id
   */
  public function setId($id) {
    $this->id = $id;
  }

  /**
   * Get the Z37 id.
   *
   * @return string
   */
  public function getId() {
    return $this->id;
  }

  /**
   * Set the Z37 pickup location.
   *
   * @param string $pickup_location
   */
  public function setPickupLocation($pickup_location) {
    $this->pickupLocation = $pickup_location;
  }

  /**
   * Get the Z37 pickup location.
   *
   * @return string
   */
  public function getPickupLocation() {
    return $this->pickupLocation;
  }

  /**
   * Set the Z37 filter sub-library.
   *
   * @param $sub_library_code
   */
  public function setSubLibraryCode($sub_library_code) {
    $this->subLibraryCode = $sub_library_code;
  }

  /**
   * Get the Z37 filter sub-library.
   *
   * @return string
   */
  public function getSubLibraryCode() {
    return $this->subLibraryCode;
  }

  /**
   * Set the Z37 request type.
   *
   * @param $request_type
   */
  public function setRequestType($request_type) {
    $this->requestType = $request_type;
  }

  /**
   * Get the Z37 request type.
   *
   * @return string
   */
  public function getRequestType() {
    return $this->requestType;
  }

  /**
   * Set the Z37 request date.
   *
   * @param string $request_date
   */
  public function setRequestDate($request_date) {
    $this->requestDate = $request_date;
  }

  /**
   * Get the Z37 request date.
   *
   * @return string
   */
  public function getRequestDate() {
    return $this->requestDate;
  }

  /**
   * Set the Z37 end request date.
   *
   * @param string $end_request_date
   */
  public function setEndRequestDate($end_request_date) {
    $this->endRequestDate = $end_request_date;
  }

  /**
   * Get the Z37 end request date.
   *
   * @return string
   */
  public function getEndRequestDate() {
    return $this->endRequestDate;
  }

  /**
   * Set the Z37 open date.
   *
   * @param string $open_date
   */
  public function setOpenDate($open_date) {
    $this->openDate = $open_date;
  }

  /**
   * Get the Z37 open date.
   *
   * @return string
   */
  public function getOpenDate() {
    return $this->openDate;
  }

  /**
   * Set the Z37 doc number.
   *
   * @param string $doc_number
   */
  public function setDocNumber($doc_number) {
    $this->docNumber = $doc_number;
  }

  /**
   * Get the Z37 doc number.
   *
   * @return string
   *    The Z37 doc number.
   */
  public function getDocNumber() {
    return $this->docNumber;
  }

  /**
   * Set the Z37 hold date.
   *
   * @param string $hold_date
   *    The Z37 hold date.
   */
  public function setHoldDate($hold_date) {
    $this->holdDate = $hold_date;
  }

  /**
   * @return string
   *    The Z37 hold date.
   */
  public function getHoldDate() {
    return $this->holdDate;
  }

  /**
   * Set the Z37 request number.
   *
   * @param string $request_number
   *    The Z37 request number.
   */
  public function setRequestNumber($request_number) {
    $this->requestNumber = $request_number;
  }

  /**
   * Get the Z37 request number.
   *
   * @return string
   *    The Z37 request number.
   */
  public function getRequestNumber() {
    return $this->requestNumber;
  }

}
