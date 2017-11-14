<?php


namespace Primo\Ting;


use Matriphe\ISO639\ISO639;
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
    return $this->document->getSourceId();
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
  public function getShortTitle() {
    // Primo does not distinguish between short and default title lengths.
    return $this->getTitle();
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
  public function getContributors() {
    return $this->document->getContributors();
  }

  /**
   * @inheritDoc
   */
  public function getCreators($format = self::NAME_FORMAT_DEFAULT) {
    // Create a mapper for each name format. A mapper should take an array of
    // elements for a name and combine them into a single string.
    $defaultMapper = function(array $nameElements) {
      return implode(' ', $nameElements);
    };
    $surnameFirstMapper = function(array $nameElements) {
      return implode(', ', array_reverse($nameElements));
    };
    $mapper = ($format === self::NAME_FORMAT_SURNAME_FIRST) ? $surnameFirstMapper : $defaultMapper;

    return array_map($mapper, $this->document->getCreators());
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
  public function getMaterialSource() {
    return $this->document->getSource();
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
  public function getSeriesTitles() {
    return $this->document->getSeriesData();
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
  public function getSubjects() {
    $subjects = $this->document->getSubjects();
    // Primo returns subjects as a single string but Ding2 expects an array of
    // subject name strings. Explode by Primos delimiter.
    return explode(' ; ', $subjects);
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
  public function getType() {
    return $this->document->getType();
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
  public function getAbstract() {
    // Return nothing. We do not support abstracts.
  }

  /**
   * @inheritDoc
   */
  public function getClassification() {
    // Return nothing. We do not support classification.
  }

  /**
   * @inheritDoc
   */
  public function getFormat() {
    // Return nothing. The contents of this field is partly duplicated by
    // getExtent().
  }

  /**
   * @inheritDoc
   */
  public function isPartOf() {
    // Return nothing. IsPartOf is not supported at the moment.
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
  public function getRelations() {
    // Return nothing. We do not support relations at the moment.
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
  public function getSpoken() {
    // Return nothing. We do not support spoken information.
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

  /**
   * @inheritDoc
   */
  public function getLanguage() {
    $lang = $this->document->getLanguage();
    if (!empty($lang)) {
      // Languages returned by Primo is in ISO-639 format. To return something
      // understandable by users we return the language for the code in the
      // native tounge.
      $languageConverter = new ISO639();
      return $languageConverter->nativeByCode3($lang);
    }
  }

  /**
   * @inheritDoc
   */
  public function getOnlineUrl() {
    // TODO: Implement getOnlineUrl() method.
  }

  /**
   * @inheritDoc
   */
  public function isOnline() {
    // TODO: Implement isOnline() method.
  }

}
