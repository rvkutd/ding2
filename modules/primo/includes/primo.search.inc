<?php

/**
 * @file
 * Implements searching in Primo.
 */

use Primo\BriefSearch\Document;
use Primo\Exception\TransferException;
use Primo\Ting\Collection;
use Primo\Ting\Object;
use Primo\Ting\Result;
use Ting\Search\SearchProviderException;
use Ting\Search\TingSearchRequest;

// TODO Remove this once Primo search provider is fully implemented.
// Ding does not support mixing multiple partial implementations of a provider
// so we fall back to OpenSearch while we implement Primo. Thus we need to
// load the OpenSearch search provider manually to have the functions available
// here.
module_load_include('inc', 'opensearch',
  'includes/opensearch.search');

/**
 * Get a list of material types from the Well.
 */
function primo_search_material_types() {
  return opensearch_search_material_types();
}

/**
 * Load objects from Open Search.
 *
 * @param string[] $ids
 *   Array of ids to load from Open Search.
 *
 * @return Ting\TingObjectInterface[]
 *   An array of loaded objects.
 */
function primo_search_object_load(array $ids) {
  $client = primo_client();
  $documents = $client->documents($ids);
  return array_map(function(Document $document) {
    return new Object($document);
  }, $documents);
}

/**
 * Load a collection from Open Search.
 *
 * @param string $id
 *   Id of material to load as a collection.
 *
 * @return Ting\TingObjectCollectionInterface
 *   The collection.
 */
function primo_search_collection_load($id) {
  $client = primo_client();
  // We don't support collections, so load a single document, filter it to
  // avoid NULL-results, and return a Collection object loaded with the array
  // containing 0 or 1 document.
  $documents = array_filter($client->documents([$id]));
  return new Collection($documents);
}

/**
 * Perform a search.
 *
 * @param \Ting\Search\TingSearchRequest $ting_query
 *   The query to execute.
 *
 * @return \Ting\Search\TingSearchResultInterface
 *   The search result.
 *
 * @throws \Ting\Search\SearchProviderException
 *   Thrown if an error occurs.
 */
function primo_search_search(TingSearchRequest $ting_query) {
  $query = '';
  $client = primo_client();

  // Add a general quoted free text search.
  if (!empty($free_text_query = $ting_query->getFullTextQuery())) {

    // Querying against the "any" field will:
    // "search against most of the (red: available) fields at once." as pre the
    // documentation.
    // We always use fuzzy searches, so no need for setFuzzy/isFuzzy.
    $query .= 'any,contains,' . urlencode($free_text_query);
  }

  // TODO: Verify the paging.
  $page = $ting_query->getPage();
  $count = $ting_query->getCount();

  // Execute the search, return the result.
  try {
    $primo_result = $client->search($query, $page, $count);
    return new Result($primo_result, $ting_query);
  }
  catch (TransferException $e) {
    throw new SearchProviderException('Exception occured while searching against primo', 0, $e);
  }
}

/**
 * Provide standard object ID's for Primo provider.
 *
 * @param array|string $ids
 *    Array with id's.
 *
 * @return array
 *    Array with multiple ID's.
 *    ID as key and Primo sourceid as value.
 */
function primo_search_object_ids($ids) {
  $translated_ids = array();

  if (is_array($ids)) {
    foreach ($ids as $id) {
      $translated_ids[$id] = variable_get('primo_sourceid') . $id;
    }
  }
  else {
    $translated_ids[$id] = variable_get('primo_sourceid') . $id;
  }

  return $translated_ids;
}

/**
 * Allows the provider to programmatically filter relations.
 *
 * @return array
 *   Filtered list.
 */
function primo_search_filter_relations($relations) {
  return opensearch_search_filter_relations($relations);
}

/**
 * Produce a list of relations we can handle.
 *
 * @return string[]
 *   Array of supported type-titles keyed by type name.
 */
function primo_search_supported_relations() {
  return opensearch_search_supported_relations();
}

/**
 * Returns a ting_relation render-array given a TingRelation.
 *
 * @param \TingRelation $relation
 *   The relation to render.
 *
 * @return array
 *   The render array.
 */
function primo_search_render_inline_relation(TingRelation $relation) {
  return opensearch_search_render_inline_relation($relation);
}

/**
 * Mapping between common fields and their provider-specific name.
 *
 * @return array
 *   Mapping between TingSearchCommonFields::* fields and their provider-
 *   specific counterpart.
 */
function primo_search_map_common_condition_fields() {
  return opensearch_search_map_common_condition_fields();
}

/**
 * Constructs a TingSearchRequest based on a "reference" search query.
 *
 * The implementation should assume that the user has entered a string with the
 * express purpose of finding a specific material. That is, if the string seems
 * to match the pattern of a unique material ID, the query should be constructed
 * to look up by id.
 *
 * The provider can also choose to support advanced provider-specific querying.
 *
 * @param string $query_string
 *   The user-provided search query.
 *
 * @return \Ting\Search\TingSearchRequest
 *   The prepared query object.
 */
function primo_search_prepare_reference_query($query_string) {
  return opensearch_search_prepare_reference_query($query_string);
}

/**
 * Provide additional sort options.
 *
 * @return array
 *   List of sort options represented by their labels and a TingSearchSort
 *   instance. Keyed by a machine-name.
 */
function primo_search_sort_options() {
  return opensearch_search_sort_options();
}

/**
 * Auto-complete callback for the ting search block.
 *
 * @param string $query
 *   The string to search suggestion with.
 *
 * @return array $items
 *   Items similar to the query.
 */
function primo_search_autocomplete($query) {
  // TODO: Only search on titles, and do something to speed up the query.
  $results = ting_start_query()->setCount(10)->setFullTextQuery($query)->execute();

  $suggestions = [];
  foreach ($results->getTingEntityCollections() as $collection) {
    $suggestions[$collection->getTitle()] = $collection->getTitle();
  }

  return $suggestions;
}
