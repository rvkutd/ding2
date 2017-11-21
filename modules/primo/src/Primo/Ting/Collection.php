<?php
/**
 * @file
 * The Collection class.
 */

namespace Primo\Ting;

use Primo\BriefSearch\Document;
use Ting\TingObjectCollectionInterface;

/**
 * A collection of materials.
 */
class Collection implements TingObjectCollectionInterface {

  /**
   * List of the Ting objects that makes up the collection.
   *
   * @var \Primo\Ting\Object[]
   */
  protected $objects;

  /**
   * Collection constructor.
   *
   * @param \Primo\BriefSearch\Document[] $documents
   *   Documents from a Primo search.
   */
  public function __construct($documents) {
    // Wrap each Primo document in a Ting compatible Object instance.
    $this->objects = array_map(function(Document $document) {
      return new Object($document);
    }, $documents);
  }

  /**
   * Returns the objects in the collection.
   *
   * @return \Ting\TingObjectInterface[]
   *   Returns the objects that makes up the collection.
   */
  public function getObjects() {
    return $this->objects;
  }

  /**
   * Get the primary Object in this collection.
   *
   * @return \Ting\TingObjectInterface
   *   The object.
   */
  public function getPrimaryObject() {
    return $this->objects[0];
  }
}
