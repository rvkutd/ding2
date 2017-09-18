<?php

namespace Drupal\aleph\Aleph\Entity;

/**
 * Class AlephLoan.
 *
 * Entity with setters/getters for loans in Aleph.
 *
 * @package Drupal\aleph\Aleph
 */

class AlephLoan {
  protected $loanId;
  protected $statusCode;
  protected $docNumber;

  /**
   * @return mixed
   */
  public function getLoanId() {
    return $this->loanId;
  }

  /**
   * @param mixed $loanId
   */
  public function setLoanId($loanId) {
    $this->loanId = $loanId;
  }

  /**
   * @return mixed
   */
  public function getStatusCode() {
    return $this->statusCode;
  }

  /**
   * @param mixed $statusCode
   */
  public function setStatusCode($statusCode) {
    $this->statusCode = $statusCode;
  }

  /**
   * @return bool
   */
  public function isRenewed() {
    return $this->getStatusCode() === 'Y';
  }

  public function getDocNumber() {
    return $this->docNumber;
  }

  public function setDocNumber($docNumber) {
    $this->docNumber = $docNumber;
  }

}
