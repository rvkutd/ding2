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
  public function getAbstract() {
    return $this->document->getLocalDisplayField(7);
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
  public function getDescription() {
    return $this->document->getDescription();
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
  public function getLanguage() {
    $lang = $this->document->getLanguage();
    if (!empty($lang)) {
      // Languages returned by Primo is in ISO-639 format. To return something
      // understandable by users we convert it to English and let Drupal try to
      // translate it to the user language.
      $lang = (new ISO639())->languageByCode2b($lang);
      $lang = t($lang);
    }
    return (!empty($lang)) ? $lang : FALSE;
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
  public function getOnlineUrl() {
    return $this->document->getOnlineUrl();
  }

  /**
   * @inheritDoc
   */
  public function isOnline() {
    return !empty($this->document->getOnlineUrl());
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
  public function getSeriesDescription() {
    // Series data contain both title of series and number for current document.
    // We only want the series title so split and return first element.
    $seriesData = explode(' ; ', $this->document->getSeriesData());
    return array_shift($seriesData);
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
  public function getClassification() {
    // We do not support classification.
    return FALSE;
  }

  /**
   * @inheritDoc
   */
  public function getFormat() {
    // The contents of this field is partly duplicated by getExtent().
    return FALSE;
  }

  /**
   * @inheritDoc
   */
  public function isPartOf() {
    // IsPartOf is not supported at the moment.
    return [];
  }

  /**
   * @inheritDoc
   */
  public function getMusician() {
    // We do not support musician information.
    return [];
  }

  /**
   * @inheritDoc
   */
  public function getPegi() {
    // We do not support PEGI information.
    return FALSE;
  }

  /**
   * @inheritDoc
   */
  public function getReferenced() {
    // We do not support reference information.
    return [];
  }

  /**
   * @inheritDoc
   */
  public function getRelations() {
    // We do not support relations at the moment.
    return [];
  }

  /**
   * @inheritDoc
   */
  public function getReplacedBy() {
    // We do not support version replacement information.
    return FALSE;
  }

  /**
   * @inheritDoc
   */
  public function getReplaces() {
    // We do not support version replacement information.
    return FALSE;
  }

  /**
   * @inheritDoc
   */
  public function getRights() {
    // We do not support rights information.
    return FALSE;
  }

  /**
   * @inheritDoc
   */
  public function getSeriesTitles() {
    // We do not support retrieval of other titles in same series.
    return FALSE;
  }

  /**
   * @inheritDoc
   */
  public function getSpatial() {
    // We do not support spatial information.
    return FALSE;
  }

  /**
   * @inheritDoc
   */
  public function getSpoken() {
    // We do not support spoken information.
    return FALSE;
  }

  /**
   * @inheritDoc
   */
  public function getSubTitles() {
    // We do not support subtitle information.
    return [];
  }

  /**
   * @inheritDoc
   */
  public function getVersion() {
    // Primo does not distinguish between difference versions of the same
    // object.
    return FALSE;
  }

}
