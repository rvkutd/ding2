<?php
/**
 * @file
 * ting_material_details.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function ting_material_details_ctools_plugin_api($module = NULL, $api = NULL) {
  if ($module == "field_group" && $api == "field_group") {
    return array("version" => "1");
  }
}
