<?php

namespace Drupal\aleph\Aleph;

use DateTime;
use Drupal\aleph\Aleph\Handler\AlephMaterialHandler;

/**
 * Class AlephMaterial.
 *
 * @package Drupal\aleph\Aleph
 */
class AlephMaterial extends AlephMaterialHandler {

  private $loans;
  private $title;
  private $loanDate;
  private $id;
  private $dueDate;

  /**
   * AlephMaterial constructor.
   *
   * @param \Drupal\aleph\Aleph\AlephClient $client
   *    Instance of the Aleph Client.
   * @param \SimpleXMLElement $material
   *    The SimpleXMLElement object for the material.
   *
   * @internal param $loans Array of loan objects from the Aleph API.*    Array of loan objects from the Aleph API.
   */
  public function __construct(AlephClient $client, \SimpleXMLElement $material) {
    parent::__construct($client);
    $this->title = (string) $material->z13->{'z13-title'};
    $this->loanDate = (string) $material->z36->{'z36-loan-date'};
    $this->id = (string) $material->z30->{'z30-doc-number'};
    $this->dueDate = (string) $material->z36->{'z36-due-date'};
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
    return DateTime::createFromFormat('d/m/Y', $this->loanDate)->format('Y-m-d');
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
   * Get the due date for the material.
   *
   * @return string
   *    The due date.
   */
  public function getDueDate() {
    return DateTime::createFromFormat('d/m/Y', $this->dueDate)->format('Y-m-d');
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
    return FALSE;
  }

}