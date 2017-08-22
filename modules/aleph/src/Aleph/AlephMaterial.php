<?php

namespace Drupal\aleph\Aleph;

use DateTime;

/**
 * Class AlephMaterial.
 *
 * This entity describes a material from Aleph which can be anything from a
 * book, an e-book, a CD, etc.
 *
 * @package Drupal\aleph\Aleph
 */
class AlephMaterial {

  protected $loans;
  protected $title;
  protected $loanDate;
  protected $id;
  protected $dueDate;

  /**
   * Set the material title.
   *
   * @param string $title
   *    The title of the material.
   */
  public function setTitle($title) {
    $this->title = $title;
  }

  /**
   * Returns the material title.
   *
   * @return string
   *    The material title.
   */
  public function getTitle() {
    return $this->title;
  }

  /**
   * Return the date for the loan of the material.
   *
   * @return string
   *   The formatted date.
   */
  public function getLoanDate() {
    return DateTime::createFromFormat('Ymd', $this->loanDate)->format('Y-m-d');
  }

  /**
   * Set material loan date.
   *
   * @param string $date
   *    The loan date.
   */
  public function setLoanDate($date) {
    $this->loanDate = $date;
  }

  /**
   * Get the material ID.
   *
   * @return string
   *    The material ID.
   */
  public function getId() {
    return $this->id;
  }

  /**
   * Set the material ID.
   *
   * @param string $id
   *    The material ID.
   */
  public function setId($id) {
    $this->id = $id;
  }

  /**
   * Get the due date for the material.
   *
   * @return string
   *    The due date.
   */
  public function getDueDate() {
    return DateTime::createFromFormat('Ymd', $this->dueDate)->format('Y-m-d');
  }

  /**
   * Set the due date.
   *
   * @param string $date
   *   The due date.
   */
  public function setDueDate($date) {
    $this->dueDate = $date;
  }

  /**
   * If the material is renewable.
   *
   * @return bool
   *    TRUE if the material is renewable.
   */
  public function isRenewable() {
    // The patron is only allowed to renew an item two times.
    if ($this->loans <= 2) {
      return TRUE;
    }
  }

  /**
   * Returns the patron's loans from SimpleXMLElement.
   *
   * @param \SimpleXMLElement $xml
   *    The SimpleXMLElement from bor_info.
   *
   * @return array
   *    Array with AlephMaterials.
   */
  public static function loansFromBorInfo(\SimpleXMLElement $xml) {
    $items = $xml->xpath('item-l');
    $loans = array();
    foreach ($items as $item) {
      $material = new self();
      $material->setTitle((string) $item->z13->{'z13-title'}[0]);
      $material->setId((string) $item->z30->{'z30-doc-number'}[0]);
      $material->setDueDate((string) $item->z36->{'z36-due-date'}[0]);
      $material->setLoanDate((string) $item->z36->{'z36-loan-date'}[0]);
      $loans[] = $material;
    }
    return $loans;
  }

}
