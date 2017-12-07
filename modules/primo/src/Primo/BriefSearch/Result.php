<?php

namespace Primo\BriefSearch;

/**
 * A result from accessing the Primo brief search service.
 */
class Result {
  use DocumentTrait;

  /**
   * Result constructor.
   *
   * @param string $data
   *   The data returned from Primo.
   */
  public function __construct($data) {
    $this->document = new \DOMDocument();
    $this->document->loadXML($data);
  }

  /**
   * Extracts the documents returned within the search result.
   *
   * @return \Primo\BriefSearch\Document[]
   *   The resulting documents.
   */
  public function getDocuments() {
    $recordIdNodes = $this->xpath('//search:DOC//primo:control/primo:recordid');
    $recordIds = $this->nodeValues($recordIdNodes);

    $documents = array_map(function($recordId) {
      return new Document($this->document, $recordId);
    }, array_combine($recordIds, $recordIds));

    return $documents;
  }

  /**
   * Returns the number of items in the result.
   *
   * @return int
   *   The number of results.
   */
  public function getNumResults() {
    $docset = $this->xpath('//sear:DOCSET');
    // Empty list or false if we hit an error or can't find the element.
    if (empty($docset)) {
      return 0;
    }
    $docset = $docset->item(0);

    // Cast the string return-value from getAttributes. getAttributes returns an
    // empty string if the attribute cannot be found which will be cast to 0.
    // which will be cast to 0.
    return (int) $docset->getAttribute('TOTALHITS');
  }

  /**
   * Returns all facets included in the search result.
   *
   * @return \Primo\BriefSearch\Facet[]
   *   Facets in the search result.
   */
  public function getFacets() {
    $facets = [];

    $facetNodes = $this->xpath('//search:FACET');
    foreach ($facetNodes as $facetNode) {
      /* @var \DOMNode $facetNode */
      $nameAttribute = $facetNode->attributes->getNamedItem('NAME');
      $facets[] = new Facet($this->document, $nameAttribute->nodeValue);
    }

    return $facets;
  }

  /**
   * Returns a specific facet if it is included in the search result.
   *
   * @param string $id
   *   The id of the facet to retrieve.
   *
   * @return \Primo\BriefSearch\Facet|NULL
   *   The facet with the provided id. NULL if it is not found in the result.
   */
  public function getFacet($id) {
    $facets = array_filter($this->getFacets(), function(Facet $facet) use ($id) {
      return $facet->getId() === $id;
    });
    return array_shift($facets);
  }
}
