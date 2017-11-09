<?php

/**
 * @file
 * Implements searching in Primo
 */

use Primo\BriefSearch\Document;
use Primo\Ting\Object;
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
  return opensearch_search_collection_load($id);
}

/**
 * Perform a search.
 *
 * @param \Ting\Search\TingSearchRequest $ting_query
 *   The query to execute.
 *
 * @return \Ting\Search\TingSearchResultInterface
 *   The search result.
 */
function primo_search_search(TingSearchRequest $ting_query) {
  return opensearch_search_search($ting_query);
}

/**
 * Allows the provider to programtically filter relations.
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
