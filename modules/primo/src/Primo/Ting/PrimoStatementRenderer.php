<?php

namespace Primo\Ting;

use Ting\Search\BooleanStatementGroup;
use Ting\Search\BooleanStatementInterface;
use Ting\Search\SearchProviderException;
use Ting\Search\TingSearchCommonFields;
use Ting\Search\TingSearchFieldFilter;
use Ting\Search\UnsupportedSearchQueryException;

/**
 * Renders a list of statements into primo queries.
 *
 * @package Primo\Ting
 */
class PrimoStatementRenderer {
  protected $mapping = NULL;

  /**
   * Functions that can map a field value, keyed by field name.
   *
   * Initialized via initializeValueMappers().
   *
   * @var array
   */
  protected $valueMappers = [];

  /**
   * PrimoStatementRenderer constructor.
   *
   * @param array $mapping
   *   Mapping between common fields and primo fields.
   */
  public function __construct(array $mapping) {
    $this->mapping = $mapping;

    // Setup the local valueMappers property.
    $this->initializeValueMappers();
  }

  /**
   * Setup value mappers.
   *
   * This is done in a function as we're not allowed to do it at property
   * initialization.
   */
  protected function initializeValueMappers() {
    // Setup value mappers.
    $this->valueMappers = [
      // Map facet languages back to ISO639.
      'facet_lang' => function ($lang) {
        return ValueMapper::mapLanguageToIso639($lang);
      },
      // Map genres back to codes.
      'facet_genre' => function ($genre) {
        return ValueMapper::mapGenreToCode($genre);
      },
    ];
  }

  /**
   * Renders a list of statements into primo briefsearch queries.
   *
   * @param \Ting\Search\FilterStatementInterface[] $statements
   *   One or more filters to render.
   *
   * @return array
   *   The rendered statements as a list of
   *   [parameter-name => [parameter values], ...]
   *   Where 'parameter-name' is a valid Briefsearch query parameter.
   *   Eg. ['query' => ['name,exact,value OR VALUE', '...'], 'query' => ...].
   *   Primo briefsearch query parameter.
   *
   * @throws \Ting\Search\UnsupportedSearchQueryException
   *   If Primo cannot support the query.
   */
  public function renderStatements(array $statements) {
    // Ensure we can support the statements and normalize the statements into
    // a list of groups.
    $groups = $this->preprocessStatements($statements);

    // We now have a list of groups containing one or more fields each. If the
    // group contains several statements, they will be against the same field.
    // We can now map the array using $this->renderGroup();
    return array_reduce(array_map([$this, 'renderGroup'], $groups), function ($carry, $rendered_group) {
      foreach ($rendered_group as $parameter_key => $parameter_value) {
        if (isset($carry[$parameter_key])) {
          $carry[$parameter_key] = array_merge($carry[$parameter_key], $parameter_value);
        }
        else {
          $carry[$parameter_key] = $parameter_value;
        }
      }
      return $carry;
    }, []);
  }

  /**
   * Verify we can process the statements and convert them to groups.
   *
   * @param \Ting\Search\FilterStatementInterface[] $statements
   *   One or more instances of BooleanStatementGroup and/or
   *   TingSearchFieldFilter.
   *
   * @return \Ting\Search\BooleanStatementGroup[]
   *   Statements processed into a list of one or more groups.
   *
   * @throws \Ting\Search\UnsupportedSearchQueryException
   *   Thrown if the statement is not supported by Primo.
   */
  public function preprocessStatements(array $statements) {
    return array_reduce($statements, function ($carry, $statement) {
      // SAL dictates that all statements added on their own will be AND'ed
      // together and this aligns with Primo where all query= statements are
      // AND'ed, so if we hit a statement we can just add it and continue.
      if ($statement instanceof TingSearchFieldFilter) {
        // Convert the statement to a group.
        $carry[] = new BooleanStatementGroup([$statement]);
        return $carry;
      }

      // The only other statement-type we support is groups, so make sure we
      // did'nt get passed something we don't support.
      if (!($statement instanceof BooleanStatementGroup)) {
        // We got something unexpected.
        $details = is_object($statement) ? get_class($statement) : (string) $statement;
        throw new UnsupportedSearchQueryException(
          'Encountered unknown filter type ' . $details
        );
      }

      // We have a group, examine it to determine whether we Primo will be able
      // to execute the statements.
      /** @var \Ting\Search\BooleanStatementGroup $statement */
      $group_statements = $statement->getStatements();

      // Collect the unique field-names in the group.
      $field_names = array_unique(array_map(function ($statement) {
        /** @var \Ting\Search\TingSearchFieldFilter $statement */
        return $statement->getName();
      }, $group_statements));

      // If the group is joined by anything but AND we require the field-name
      // to be the same so that we can combine the field statements into
      // a single "query=name,exact,value OR VALUE OR VALUE".
      if (count($field_names) > 1) {
        // The group references more than one field, if it joined by AND we
        // can stil support it.
        if ($statement->getLogicOperator() === BooleanStatementGroup::OP_AND) {
          // Wrap each statement in the group in its own group and add it. This
          // will work as
          // "name=value AND (name=value AND name=value)"
          // equals
          // "name=value AND name=value AND name=value".
          $carry = array_merge($carry, array_map(function ($statement) {
            return new BooleanStatementGroup([$statement]);
          }, $group_statements));
        }
        else {
          // The group referenced more than one field and was not joined by AND.
          throw new UnsupportedSearchQueryException(
            'Encountered ' . $statement->getLogicOperator() . ' operator between the fields '
            . implode(', ', $field_names)
            . '. Primo only supports AND between different fields.'
          );
        }
      }
      else {
        // The all statements in the group is referencing the same field, go
        // ahead and add it.
        $carry[] = $statement;
      }
      return $carry;
    }, []);
  }

  /**
   * Render a group into a statement.
   *
   * The groups must be "Primo" compatible. See parameter documentation for
   * details.
   *
   * @param \Ting\Search\BooleanStatementGroup $group
   *   A list of BooleanStatementGroup instances containing one or more
   *   statements all against the same field if they are joined by OR.
   *
   * @return array
   *   An associative array with keys that are valid primo query parameters such
   *   as "query" and values that are an array of valid primo parameter-values.
   *   Eg. ['query' => ['author,exact,Rowlings', 'title,exact,Harry Potter']].
   */
  protected function renderGroup(BooleanStatementGroup $group) {
    // We're a group group containing one or more field statements (against the
    // same field) into a single query=<fieldname>,exact,<list of values>.
    // See https://developers.exlibrisgroup.com/primo/apis/webservices/xservices/search/briefsearch
    // for more details.
    $statements = $group->getStatements();

    // The group has been preprocessed so we know it to contain at least one
    // statement and all statements will be against the same field so the
    // following is safe.
    $field_name = $statements[0]->getName();
    // If this is a field we have a mapping for, map it.
    if (isset($this->mapping[$field_name])) {
      $field_name = $this->mapping[$field_name];
    }

    // Get all the values and process them.
    $values = array_map(function (TingSearchFieldFilter $statement) {
      return $statement->getValue();
    }, $statements);

    // If this is a field we have a value-mapper for, do the mapping.
    if (isset($this->valueMappers[$field_name])) {
      $values = array_map($this->valueMappers[$field_name], $values);
    }

    $values = array_map(function ($statement) {
      // "Escape" the string by replacing commas with spaces.
      return str_replace(',', ' ', $this->escapeValue($statement));
    }, $values);

    $values_joined = implode(' ' . $group->getLogicOperator() . ' ', $values);

    // Eg. query=rtype,exact,audiobooks OR articles.
    return ['query' => [$field_name . ',exact,' . $values_joined]];
  }

  /**
   * Escapes a value to be placed into a primo query parameter.
   *
   * @param string $value
   *   The value.
   *
   * @return string
   *   The escaped string.
   */
  public function escapeValue($value) {
    $replacements = [
      // AND and OR are keywords, strip them.
      ' AND ' => ' ',
      ' OR ' => ' ',
      // Commas should be replaced with spaces.
      ',' => ' ',
    ];

    return strtr($value, $replacements);
  }

}
