<?php

/**
 * @file
 * Administration interface for the Primo module.
 */

/**
 * Setting form for Primo configuration.
 *
 * @return []
 *   A Form API array
 */
function primo_settings_form() {
  $form = array();

  $form['primo'] = array(
    '#type' => 'fieldset',
    '#title' => t('Primo service settings'),
    '#tree' => FALSE,
  );

  $form['primo']['primo_base_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Base URL'),
    '#description' => t('Base URL for Primo service.'),
    '#required' => TRUE,
    '#default_value' => variable_get('primo_base_url', ''),
  );

  $form['primo']['primo_institution_code'] = array(
    '#type' => 'textfield',
    '#title' => t('Institution code'),
    '#description' => t('Relevant for restricted scopes or for when searching against Primo Central.'),
    '#required' => TRUE,
    '#default_value' => variable_get('primo_institution_code', ''),
  );

  $form['primo']['primo_location_scopes'] = array(
    '#type' => 'textfield',
    '#title' => t('Search scope'),
    '#description' => t('List of institution codes the searches should be scoped within. The list must be comma-seperated without spaces and suitable for a local loc parameter. See <a href="@briefsearch-documentation">the Brief Search documentation</a>',
      ['@briefsearch-documentation' => 'https://developers.exlibrisgroup.com/primo/apis/webservices/xservices/search/briefsearch']),
    '#required' => FALSE,
    '#default_value' => variable_get('primo_location_scopes', ''),
  );

  $form['primo']['primo_sourceid'] = array(
    '#type' => 'textfield',
    '#title' => t('Source ID'),
    '#description' => t('Used for creating record ID from source record ID.'),
    '#required' => TRUE,
    '#default_value' => variable_get('primo_sourceid', ''),
  );

  $form['primo']['primo_source_systems'] = array(
    '#type' => 'textarea',
    '#title' => t('Source systems'),
    '#default_value' => _primo_textarea_array_value(variable_get('primo_source_systems', [])),
    '#value_callback' => '_primo_textarea_value_array',
    '#description' => t("System identifies the system used by the source repositories indexed by Primo. Examples: Aleph, ADAM, MetaLib, SFX, and Digitool).\nEnter one per line and note that spelling must be exactly as it appears in Primo."),
    '#required' => TRUE,
  );

  $form['primo']['primo_enable_logging'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable logging'),
    '#default_value' => variable_get('primo_enable_logging', FALSE),
    '#description' => t('Logs requests to the Primo webservice.'),
  );

  $form['#submit'][] = 'primo_settings_form_submit';
  return system_settings_form($form);
}

/**
 * Submit handler for the primo provider settings form.
 */
function primo_settings_form_submit($form, &$form_state) {
  // Do some basic whitespace cleanup in case the user was sloppy.
  if (isset($form_state['values']['primo_location_scopes'])) {
    $form_state['values']['primo_location_scopes'] = trim(str_replace(' ', '', $form_state['values']['primo_location_scopes']));
  }
}

/**
 * Form for configuring mapping between Primo codes and patron texts.
 *
 * @return mixed
 *   A Form API array
 */
function primo_code_mapping_form() {
  $form = [];

  $form['primo_material_type_map'] = [
    '#type' => 'textarea',
    '#title' => t('Material types'),
    '#default_value' => _primo_textarea_map_value(variable_get('primo_material_type_map', [])),
    '#value_callback' => '_primo_textarea_value_map',
    '#element_validate' => ['_primo_textarea_map_validate'],
    '#description' => t('Configure a mapping from Primo material type codes to descriptions that are understandable by a patron. Enter one mapping per line in the format "[primo code]|[patron text]" (without " and []).'),
    '#required' => TRUE,
  ];

  $form['primo_genre_map'] = [
    '#type' => 'textarea',
    '#title' => t('Genres'),
    '#default_value' => _primo_textarea_map_value(variable_get('primo_genre_map', [])),
    '#value_callback' => '_primo_textarea_value_map',
    '#element_validate' => ['_primo_textarea_map_validate'],
    '#description' => t('Configure a mapping from Primo genre codes to descriptions that are understandable by a patron. Enter one mapping per line in the format "[primo code]|[patron text]" (without " and []).'),
    '#required' => TRUE,
  ];

  return system_settings_form($form);
}

/**
 * Value callback for text area elements which represent arrays of strings.
 *
 * This callback is useful for textareas where the user is supposed to enter
 * one value per line. The contents of the textarea is then mapped from a
 * multiline string to an array of singleline strings.
 *
 * Use with #value_callback
 *
 * @param array $element
 *   The form element.
 * @param string|FALSE $input
 *   The user input string. FALSE if there is no user input.
 * @param array $form_state
 *   The form state.
 *
 * @return string[]
 *   Array of single-line strings.
 */
function _primo_textarea_value_array(array $element, $input = FALSE, $form_state = []) {
  // Return any default value if no value is provided.
  if ($input === FALSE) {
    return isset($element['#default_value']) ? $element['#default_value'] : NULL;
  }

  // Split into multiple single-line strings and clean up whitespace.
  $elements = explode("\n", $input);
  return array_map('trim', $elements);
}

/**
 * Converts an array of strings to a single multiline string.
 *
 * This is basicly the reverse of _primo_textarea_value_array() and useful for
 * displaying an array of strings in a textarea.
 *
 * @param mixed $array
 *   Input value. Should be an array of strings.
 *
 * @return string
 *   A single multiline string.
 */
function _primo_textarea_array_value($array) {
  return implode("\n", $array);
}

/**
 * Value callback for text area elements which represent a map of strings.
 *
 * This callback is useful for textareas where the user is supposed to enter
 * one mapping per line in the format [key]|[value]. The contents of the
 * textarea is then mapped from a multiline string to a map.
 *
 * Use with #value_callback
 *
 * @param array $element
 *   The form element.
 * @param string|FALSE $input
 *   The user input string. FALSE if there is no user input.
 * @param array $form_state
 *   The form state.
 *
 * @return string[]
 *   Map of values.
 */
function _primo_textarea_value_map(array $element, $input = FALSE, $form_state = []) {
  // Return any default value if no value is provided.
  if ($input === FALSE) {
    return isset($element['#default_value']) ? $element['#default_value'] : NULL;
  }

  // Convert the input to an array of singleline strings.
  $array = _primo_textarea_value_array($element, $input, $form_state);

  // Convert each array entry into an entry in the map.
  $map = [];
  array_walk($array, function($entry) use (&$map) {
    list ($key, $value) = preg_split('/\s*\|\s*/', $entry, 2);
    $map[$key] = $value;
  });
  return $map;
}


/**
 * Converts an map strings to a single multiline string.
 *
 * This is basicly the reverse of _primo_textarea_value_map() and useful for
 * displaying a map strings in a textarea.
 *
 * @param mixed $map
 *   Input value. Should be a map of strings.
 *
 * @return string
 *   A single multiline string.
 */
function _primo_textarea_map_value($map) {
  // Convert the map into an array of strings.
  $array = array_map(function($key, $value) {
    return implode('|', [ $key, $value ]);
  }, array_keys($map), $map);
  // Convert the array into a single multiline string.
  return _primo_textarea_array_value($array);
}

/**
 * Validation callback for map elements.
 *
 * This should ensure that the contents of the element is in the correct format.
 *
 * Use with #element_validate.
 *
 * @param array $element
 *   The form element. It is important that this is passed by reference so we
 *   can update the element value if validation fails.
 * @param array $form_state
 *   The form state.
 * @param array $form
 *   The form.
 */
function _primo_textarea_map_validate(array &$element, array &$form_state, array $form) {
  foreach ($element['#value'] as $key => $value) {
    if (empty($value) || empty($key)) {
      form_error($element, t('Unable to extract mapping from element. Please ensure format is correct.'));
      // When raising an error we have to manually convert our map back to a
      // value suitable for the element. For some reason this does not work with
      // $form_state.
      $element['#value'] = _primo_textarea_map_value($element['#value']);
      break;
    }
  };
}
