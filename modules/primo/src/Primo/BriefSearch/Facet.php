<?php

namespace Primo\BriefSearch;

/**
 * A facet returned when accessing the Primo brief search service.
 *
 * Here a facet constitutes one aspect of the returned results e.g. material
 * type, language or year of publishing and the facet values are each of the
 * possible values within the facet such as books, english or 2017.
 */
class Facet {
  use DocumentTrait;

  /**
   * The id of the facet.
   *
   * This is a string value which for some facets may be mistaken as a name.
   * However some facet names prove that they are in facet not meant for end-
   * user text.
   *
   * @var string
   */
  protected $id;

  /**
   * Facet constructor.
   *
   * @param \DOMDocument $document
   *   The document containing the facet.
   * @param string $id
   *   The facet id. This is also referred to as the NAME in XML.
   */
  public function __construct(\DOMDocument $document, $id) {
    $this->document = $document;
    $this->id = $id;
  }

  /**
   * Gets the facet id.
   *
   * @return string
   */
  public function getId() {
    return $this->id;
  }

  /**
   * Returns the facet values and their frequency.
   *
   * @return int[]
   *   A map from facet values to the frequency of each value within the search
   *   as represented by the document.
   */
  public function getValues() {
    $values = [];

    $facetValueNodes = $this->xpath('//search:FACET[@NAME="' . $this->id . '"]/search:FACET_VALUES');
    foreach ($facetValueNodes as $facetValueNode) {
      /* @var \DOMNode $facetValueNode */
      $keyAttribute = $facetValueNode->attributes->getNamedItem('KEY');
      $valueAttribute = $facetValueNode->attributes->getNamedItem('VALUE');
      $values[$keyAttribute->nodeValue] = $valueAttribute->nodeValue;
    }

    return $values;
  }

}
