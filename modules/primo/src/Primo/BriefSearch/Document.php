<?php

namespace Primo\BriefSearch;

/**
 * A document returned when accessing the Primo brief search service.
 */
class Document {
  use DocumentTrait;

  /**
   * The id of the document.
   *
   * @var string
   */
  protected $recordId;

  /**
   * Document constructor.
   *
   * @param \DOMDocument $document
   *   The XML search response document which contains the current document.
   * @param string $recordId
   *   Document record id.
   */
  public function __construct(\DOMDocument $document, $recordId) {
    $this->document = $document;
    $this->recordId = $recordId;
  }

  /**
   * Get the record id for the document.
   *
   * @return string
   *   Document record id.
   */
  public function getRecordId() {
    return $this->recordId;
  }

  /**
   * Get all thumbnail urls for the document.
   *
   * @return string[]
   *   Thumbnail urls.
   */
  public function getThumbnailUrls() {
    $thumbnailNodes = $this->recordXpath($this->recordId, '//search:thumbnail');
    $thumbnailUrls = $this->nodeValues($thumbnailNodes);

    // Primo results are known to include duplicates urls. Remove these.
    return array_unique($thumbnailUrls);
  }

}
