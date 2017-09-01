<?php

namespace Drupal\aleph\Aleph;

/**
 * Class AlephReservation.
 *
 * @package Drupal\aleph\Aleph
 */
class AlephReservation {
  protected $item;
  protected $request;

  /**
   * @param \Drupal\aleph\Aleph\AlephMaterial $item
   */
  public function setItem(AlephMaterial $item) {
    $this->item = $item;
  }

  /**
   * @return \Drupal\aleph\Aleph\AlephMaterial
   */
  public function getItem() {
    return $this->item;
  }

  /**
   * @param \Drupal\aleph\Aleph\AlephRequest $request
   */
  public function setRequest(AlephRequest $request) {
    $this->request = $request;
  }

  /**
   * @return \Drupal\aleph\Aleph\AlephRequest
   */
  public function getRequest() {
    return $this->request;
  }
}
