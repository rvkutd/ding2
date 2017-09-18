<?php

namespace Drupal\aleph\Aleph\Entity;

/**
 * Class AlephItem.
 *
 * Entity with setters/getters for item in Aleph.
 *
 * Contains information on the copy’s sub-library, collection, location,
 * material type, etc.
 *
 * https://www.obvsg.at/uploads/media/Z30.pdf
 *
 * @package Drupal\aleph\Aleph\Entity
 */
class AlephItem {

  /**
   * System number of the administrative record (ADM) associated to the item.
   *
   * @var string
   */
  protected $docNumber;

  /**
   * Item sequence number, within one ADM number.
   *
   * @var string
   */
  protected $itemSequence;

  /**
   * Unique identifier of the item.
   *
   * @var string
   */
  protected $barcode;

  /**
   * Code of the sub-library that “owns” the item.
   *
   * @var string
   */
  protected $subLibrary;

  /**
   * Material type. This can be VIDEO, BOOK, ISSUE, etc.
   * "ISSUE" has special functionality within the system, all other types are
   * used in an equal manner.
   *
   * @var string
   */
  protected $material;

  /**
   * Creation date of the copy.
   *
   * @var string
   */
  protected $openDate;

  /**
   * Date copy was last updated.
   *
   * @var string
   */
  protected $updateDate;

  /**
   * Number of times the item was loaned.
   *
   * @var string
   */
  protected $noLoans;

  /**
   * Where the item is located at the library.
   *
   * @var string
   */
  protected $collection;

  protected $callNo;
  protected $callNo2;
  protected $description;
  protected $noteCirculation;
  protected $noteInternal;
  protected $orderNumber;

  // Issues
  protected $issueDate;
  protected $expectedArrivalDate;
  protected $arrivalDate;
  protected $copyId;


  /**
   * @return mixed
   */
  public function getDocNumber() {
    return $this->docNumber;
  }

  /**
   * @param mixed $docNumber
   */
  public function setDocNumber($docNumber) {
    $this->docNumber = $docNumber;
  }

  /**
   * @return mixed
   */
  public function getItemSequence() {
    return $this->itemSequence;
  }

  /**
   * @param mixed $itemSequence
   */
  public function setItemSequence($itemSequence) {
    $this->itemSequence = $itemSequence;
  }

  /**
   * @return mixed
   */
  public function getBarcode() {
    return $this->barcode;
  }

  /**
   * @param mixed $barcode
   */
  public function setBarcode($barcode) {
    $this->barcode = $barcode;
  }

  /**
   * @return mixed
   */
  public function getSubLibrary() {
    return $this->subLibrary;
  }

  /**
   * @param mixed $subLibrary
   */
  public function setSubLibrary($subLibrary) {
    $this->subLibrary = $subLibrary;
  }

  /**
   * @return mixed
   */
  public function getMaterial() {
    return $this->material;
  }

  /**
   * @param mixed $material
   */
  public function setMaterial($material) {
    $this->material = $material;
  }

  /**
   * @return mixed
   */
  public function getOpenDate() {
    return $this->openDate;
  }

  /**
   * @param mixed $openDate
   */
  public function setOpenDate($openDate) {
    $this->openDate = $openDate;
  }

  /**
   * @return mixed
   */
  public function getUpdateDate() {
    return $this->updateDate;
  }

  /**
   * @param mixed $updateDate
   */
  public function setUpdateDate($updateDate) {
    $this->updateDate = $updateDate;
  }

  /**
   * @return mixed
   */
  public function getNoLoans() {
    return $this->noLoans;
  }

  /**
   * @param mixed $noLoans
   */
  public function setNoLoans($noLoans) {
    $this->noLoans = $noLoans;
  }

  /**
   * @return mixed
   */
  public function getCollection() {
    return $this->collection;
  }

  /**
   * @param mixed $collection
   */
  public function setCollection($collection) {
    $this->collection = $collection;
  }

  /**
   * @return mixed
   */
  public function getCallNoType() {
    return $this->callNoType;
  }

  /**
   * @param mixed $callNoType
   */
  public function setCallNoType($callNoType) {
    $this->callNoType = $callNoType;
  }

  /**
   * @return mixed
   */
  public function getCallNo() {
    return $this->callNo;
  }

  /**
   * @param mixed $callNo
   */
  public function setCallNo($callNo) {
    $this->callNo = $callNo;
  }

  /**
   * @return mixed
   */
  public function getCallNo2() {
    return $this->callNo2;
  }

  /**
   * @param mixed $callNo2
   */
  public function setCallNo2($callNo2) {
    $this->callNo2 = $callNo2;
  }

  /**
   * @return mixed
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * @param mixed $description
   */
  public function setDescription($description) {
    $this->description = $description;
  }

  /**
   * @return mixed
   */
  public function getNoteCirculation() {
    return $this->noteCirculation;
  }

  /**
   * @param mixed $noteCirculation
   */
  public function setNoteCirculation($noteCirculation) {
    $this->noteCirculation = $noteCirculation;
  }

  /**
   * @return mixed
   */
  public function getNoteInternal() {
    return $this->noteInternal;
  }

  /**
   * @param mixed $noteInternal
   */
  public function setNoteInternal($noteInternal) {
    $this->noteInternal = $noteInternal;
  }

  /**
   * @return mixed
   */
  public function getOrderNumber() {
    return $this->orderNumber;
  }

  /**
   * @param mixed $orderNumber
   */
  public function setOrderNumber($orderNumber) {
    $this->orderNumber = $orderNumber;
  }

  /**
   * @return mixed
   */
  public function getIssueDate() {
    return $this->issueDate;
  }

  /**
   * @param mixed $issueDate
   */
  public function setIssueDate($issueDate) {
    $this->issueDate = $issueDate;
  }

  /**
   * @return mixed
   */
  public function getExpectedArrivalDate() {
    return $this->expectedArrivalDate;
  }

  /**
   * @param mixed $expectedArrivalDate
   */
  public function setExpectedArrivalDate($expectedArrivalDate) {
    $this->expectedArrivalDate = $expectedArrivalDate;
  }

  /**
   * @return mixed
   */
  public function getArrivalDate() {
    return $this->arrivalDate;
  }

  /**
   * @param mixed $arrivalDate
   */
  public function setArrivalDate($arrivalDate) {
    $this->arrivalDate = $arrivalDate;
  }

  /**
   * @return mixed
   */
  public function getCopyId() {
    return $this->copyId;
  }

  /**
   * @param mixed $copyId
   */
  public function setCopyId($copyId) {
    $this->copyId = $copyId;
  }
}
