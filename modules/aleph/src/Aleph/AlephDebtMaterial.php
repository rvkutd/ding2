<?php

namespace Drupal\aleph\Aleph;

/**
 * Class AlephDebtMaterial.
 *
 * Material information related to debt.
 *
 * @package Drupal\aleph\Aleph
 */
class AlephDebtMaterial {
  protected $title;

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
   * Create debt material.
   *
   * @param string $title
   *    The debt material title.
   *
   * @return \Drupal\aleph\Aleph\AlephDebtMaterial
   *    The Aleph debt material.
   */
  public static function createDebtMaterial($title) {
    $debt_material = new self();
    $debt_material->setTitle($title);
    return $debt_material;
  }

}
