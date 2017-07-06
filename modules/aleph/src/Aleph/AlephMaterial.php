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

  private $material;
  private $loans;

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
    $this->material = $material;
  }

  /**
   * Returns the material title.
   *
   * @return string
   *    The material title.
   */
  public function getTitle() {
    return (string) $this->material->z13->{'z13-title'};
  }

  /**
   * Return the date for the loan of the material.
   *
   * @return string
   *   The formatted date.
   */
  public function getLoanDate() {
    $loan_date = (string) $this->material->z36->{'z36-loan-date'};
    return DateTime::createFromFormat('d/m/Y', $loan_date)->format('Y-m-d');
  }

  /**
   * Get the material ID.
   *
   * @return string
   *    The material ID.
   */
  public function getId() {
    return (string) $this->material->z30->{'z30-doc-number'};;
  }

  /**
   * Get the due date for the material.
   *
   * @return string
   *    The due date.
   */
  public function getDueDate() {
    $due_date = (string) $this->material->z36->{'z36-due-date'};
    return DateTime::createFromFormat('d/m/Y', $due_date)->format('Y-m-d');
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