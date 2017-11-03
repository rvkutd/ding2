<?php

namespace Primo\BriefSearch;

/**
 * A result from accesing the Primo brief search servide.
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


}
