<?php
/**
 * @file
 * aleph.features.field_instance.inc
 */

/**
 * Implements hook_field_default_field_instances().
 */
function aleph_field_default_field_instances() {
  $field_instances = array();

  // Exported field_instance: 'profile2-provider_aleph-field_aleph_email'
  $field_instances['profile2-provider_aleph-field_aleph_email'] = array(
    'bundle' => 'provider_aleph',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'profile2',
    'field_name' => 'field_aleph_email',
    'label' => 'Email',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 60,
      ),
      'type' => 'text_textfield',
      'weight' => 33,
    ),
  );

  // Exported field_instance: 'profile2-provider_aleph-field_aleph_expiry_date'
  $field_instances['profile2-provider_aleph-field_aleph_expiry_date'] = array(
    'bundle' => 'provider_aleph',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 2,
      ),
    ),
    'entity_type' => 'profile2',
    'field_name' => 'field_aleph_expiry_date',
    'label' => 'Expiry date',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 60,
      ),
      'type' => 'text_textfield',
      'weight' => 35,
    ),
  );

  // Exported field_instance: 'profile2-provider_aleph-field_aleph_interest_period'
  $field_instances['profile2-provider_aleph-field_aleph_interest_period'] = array(
    'bundle' => 'provider_aleph',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 4,
      ),
    ),
    'entity_type' => 'profile2',
    'field_name' => 'field_aleph_interest_period',
    'label' => 'Interest period',
    'required' => 0,
    'settings' => array(
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'options',
      'settings' => array(),
      'type' => 'options_select',
      'weight' => 32,
    ),
  );

  // Exported field_instance: 'profile2-provider_aleph-field_aleph_phone_number'
  $field_instances['profile2-provider_aleph-field_aleph_phone_number'] = array(
    'bundle' => 'provider_aleph',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 1,
      ),
    ),
    'entity_type' => 'profile2',
    'field_name' => 'field_aleph_phone_number',
    'label' => 'Phone number',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 60,
      ),
      'type' => 'text_textfield',
      'weight' => 34,
    ),
  );

  // Exported field_instance: 'profile2-provider_aleph-field_aleph_preferred_branch'
  $field_instances['profile2-provider_aleph-field_aleph_preferred_branch'] = array(
    'bundle' => 'provider_aleph',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 3,
      ),
    ),
    'entity_type' => 'profile2',
    'field_name' => 'field_aleph_preferred_branch',
    'label' => 'Preferred branch',
    'required' => 1,
    'settings' => array(
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'options',
      'settings' => array(),
      'type' => 'options_select',
      'weight' => 31,
    ),
  );

  // Translatables
  // Included for use with string extractors like potx.
  t('Email');
  t('Expiry date');
  t('Interest period');
  t('Phone number');
  t('Preferred branch');

  return $field_instances;
}
