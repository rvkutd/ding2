<?php


namespace Primo\Ting;


use Primo\BriefSearch\Document;
use Ting\TingObjectInterface;

/**
 * Ting Object implementation for Primo.
 *
 * This class acts as a wrapper around a Primo document and exposes methods
 * which will extract data required by Ting.
 */
class Object implements TingObjectInterface {

  /**
   * The Primo document which the object represents.
   *
   * @var \Primo\BriefSearch\Document
   */
  protected $document;

  /**
   * Object constructor.
   *
   * @param \Primo\BriefSearch\Document $document
   *   The Primo document to wrap.
   */
  public function __construct(Document $document) {
    $this->document = $document;
  }

  /**
   * @inheritDoc
   */
  public function getId() {
    return $this->document->getRecordId();
  }

  /**
   * @inheritDoc
   */
  public function getSourceId() {
    return $this->document->getSourceRecordId();
  }

  /**
   * @inheritDoc
   */
  public function isLocal() {
    // TODO: Implement isLocal() method.
  }

  /**
   * @inheritDoc
   */
  public function getOwnerId() {
    // TODO: Implement getOwnerId() method.
  }

  /**
   * @inheritDoc
   */
  public function getTitle() {
    return $this->document->getTitle();
  }

  /**
   * @inheritDoc
   */
  public function getDescription() {
    return $this->document->getDescription();
  }

  /**
   * @inheritDoc
   */
  public function getRelations() {
    // TODO: Implement getRelations() method.
  }


  /**
   * @inheritDoc
   */
  public function getAge() {
    // TODO: Implement getAge() method.
  }

  /**
   * @inheritDoc
   */
  public function getAudience() {
    // TODO: Implement getAudience() method.
  }

  /**
   * @inheritDoc
   */
  public function getExtent() {
    return $this->document->getDisplayFormat();
  }

  /**
   * @inheritDoc
   */
  public function getFormat() {
    // TODO: Implement getFormat() method.
  }

  /**
   * @inheritDoc
   */
  public function getGenere() {
    // TODO: Implement getGenere() method.
  }

  /**
   * @inheritDoc
   */
  public function getIsbn() {
    // A Primo record may contain multiple ISBNs - ISBN-10 and ISBN-13.
    // Ding2 only works with a single ISBN value for an object and we prefer
    // ISBN-13 so sort values by length and return the longest.
    $isbns = $this->document->getIsbns();
    usort($isbns, function($a, $b) {
      return strlen($b) - strlen($a);
    });
    return reset($isbns);
  }

  /**
   * @inheritDoc
   */
  public function getPublisher() {
    return $this->document->getPublisher();
  }

  /**
   * @inheritDoc
   */
  public function getSource() {
    $source = $this->document->getLocalDisplayField(6);
    // Source texts may contain the text 'Á frummáli: ' meaning 'In the original
    // language'. However such a prefix will be added by the calling code so
    // we remove it here.
    // Note that this is strictly tied to how Primo is used by Icelandic
    // libraries.
    return str_replace('Á frummáli: ', '', $source);
  }

  /**
   * @inheritDoc
   */
  public function getSpoken() {
    // TODO: Implement getSpoken() method.
  }

  /**
   * @inheritDoc
   */
  public function getTracks() {
    // TODO: Implement getTracks() method.
  }

  /**
   * @inheritDoc
   */
  public function getURI() {
    // TODO: Implement getURI() method.
  }

  /**
   * @inheritDoc
   */
  public function isPartOf() {
    // TODO: Implement isPartOf() method.
  }

  /**
   * @inheritDoc
   */
  public function getType() {
    return $this->document->getFormat();
  }

  /**
   * @inheritDoc
   */
  public function getYear() {
    return $this->document->getYear();
  }

  // Below are parts of the TingObjectInterface which the Primo modules
  // currently do not support.
  // Note that this does not necessarily mean that the information is not
  // available from Primo. It is just not implemented at the moment. Please
  // check each getter for any additional information.

  /**
   * @inheritDoc
   */
  public function getShortTitle() {
    // Return nothing. We do not support short titles at the moment.
  }

  /**
   * @inheritDoc
   */
  public function getMusician() {
    // Return nothing. We do not support musician information.
  }

  /**
   * @inheritDoc
   */
  public function getPegi() {
    // Return nothing. We do not support PEGI information.
  }

  /**
   * @inheritDoc
   */
  public function getReferenced() {
    // Return nothing. We do not support reference information.
  }

  /**
   * @inheritDoc
   */
  public function getReplacedBy() {
    // Return nothing. We do not support version replacement information.
  }

  /**
   * @inheritDoc
   */
  public function getReplaces() {
    // Return nothing. We do not support version replacement information.
  }

  /**
   * @inheritDoc
   */
  public function getRights() {
    // Return nothing. We do not support rights information.
  }

  /**
   * @inheritDoc
   */
  public function getSeriesDescription() {
    // Return nothing. We do not support series description.
  }

  /**
   * @inheritDoc
   */
  public function getSpatial() {
    // Return nothing. We do not support spatial information.
  }

  /**
   * @inheritDoc
   */
  public function getSubTitles() {
    // Return nothing. We do not support subtitle information.
  }

  /**
   * @inheritDoc
   */
  public function getVersion() {
    // Do nothing. Primo does not distinguish between difference versions of
    // the same object.
  }

}
