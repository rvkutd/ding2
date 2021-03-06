<?php

/**
 * @file
 * The provider functions.
 */

/**
 * Make a request to Openlist.
 *
 * This just wraps the ting_openlist_request() function, into the
 * ding_provider way of life.
 *
 * @param string $function
 *   Function name of the function to call on the service.
 * @param array $arguments
 *   Numerical array with the arguments, in their respectable order.
 * @param bool $detailed_log
 *   If set to true, the arguments send is logged by watchdog, if logging is
 *   active.
 *
 * @return mixed
 *   The Openlist request.
 */
function ting_openlist_handler_request($function, array $arguments, $detailed_log = FALSE) {
  return ting_openlist_request($function, $arguments, $detailed_log);
}

/**
 * Get a user identifier.
 *
 * @param object $account
 *   The user account.
 *
 * @return mixed
 *   The identifier or FALSE otherwise.
 */
function ting_openlist_handler_user_identifier($account = NULL) {
  return ting_openlist_user_identifier($account);
}

/**
 * Get a user depending on the openlist_uid.
 *
 * @param string $openlist_uid
 *   The openlist user id.
 *
 * @return object
 *   The user account.
 */
function ting_openlist_handler_get_user($openlist_uid) {
  return ting_openlist_get_user($openlist_uid);
}

/**
 * Update the user account with the openlist_uid.
 *
 * @param object $account
 *   The user account to update.
 */
function ting_openlist_handler_update_user($account) {
  ting_openlist_update_user($account);
}

/**
 * Reset an openlist user.
 *
 * @param object $account
 *   The user account to reset.
 */
function ting_openlist_handler_reset_user($account) {
  $user_identifier = ting_openlist_user_identifier($account);
  return ting_openlist_request('callModule', array('ResetUser', 'reset', array(
    $user_identifier,
  )));
}

/**
 * Create a list.
 *
 * @param string $user_identifier
 *   The unique user identifier.
 * @param string $title
 *   List title.
 * @param string $type
 *   List type.
 * @param array $data
 *   Extra data.
 *
 * @return mixed
 *   Openlist id or NULL.
 */
function ting_openlist_handler_create_list($user_identifier, $title, $type, array $data) {
  return ting_openlist_request('createList', array(
    'owner' => $user_identifier,
    'title' => $title,
    'type' => $type,
    'data' => $data,
  ));
}

/**
 * Edit a list.
 *
 * @param string $id
 *   Openlist id.
 * @param string $title
 *   List title.
 * @param array $data
 *   Extra data.
 */
function ting_openlist_handler_edit_list($id, $title, array $data) {
  ting_openlist_request('editList', array(
    'list_id' => $id,
    'title' => $title,
    'data' => $data,
  ));
}

/**
 * Delete an element.
 *
 * @param array $ids
 *   List of ids to delete.
 */
function ting_openlist_handler_delete_element(array $ids) {
  ting_openlist_request('deleteElement', array(
    'element_id' => $ids,
  ));
}

/**
 * Create an element.
 *
 * @param string $id
 *   Openlist list id.
 * @param array $data
 *   Element data.
 *
 * @return mixed
 *   openlist id of the elemnet or false.
 */
function ting_openlist_handler_create_element($id, array $data) {
  return ting_openlist_request('createElement', array(
    'list_id' => $id,
    'data' => $data,
  ));
}

/**
 * Edit an element.
 *
 * @param string $id
 *   Openlist list id.
 * @param array $data
 *   Element data.
 */
function ting_openlist_handler_edit_element($id, array $data) {
  ting_openlist_request('editElement', array(
    'list_id' => $id,
    'data' => $data,
  ));
}

/**
 * Delete a list.
 *
 * @param int $id
 *   Openlist id.
 */
function ting_openlist_handler_delete_list($id) {
  ting_openlist_request('deleteList', array(
    'list_id' => $id,
  ));
}

/**
 * Move an element to after another element.
 *
 * @param int $id
 *   Openlist id, of the element you're moving.
 * @param int $previous_id
 *   Openlist id, of the element you're moving.
 */
function ting_openlist_handler_set_element_after($id, $previous_id) {
  ting_openlist_request('setElementAfter', array(
    'element_id' => $id,
    'previous_id' => $previous_id,
  ));
}

/**
 * Get the user's lists.
 *
 * @param string $user_identifier
 *   The user.
 * @param int $from
 *   Only get lists with changes from this timestamp.
 *
 * @return array
 *   List of lists.
 */
function ting_openlist_handler_get_lists($user_identifier, $from) {
  return ting_openlist_request('getLists', array(
    'owner' => $user_identifier,
    'from' => $from,
  ));
}

/**
 * Get all the user's elements.
 *
 * @param string $user_identifier
 *   The user.
 * @param int $from
 *   Only get elements with changes from this timestamp.
 *
 * @return array
 *   List of elements
 */
function ting_openlist_handler_get_user_elements($user_identifier, $from) {
  return ting_openlist_request('getUserElements', array(
    'owner' => $user_identifier,
    'from' => $from,
  ));
}

/**
 * Call a module function on the openlist service.
 *
 * @param string $module
 *   Name of the module.
 * @param string $method
 *   The method to call.
 * @param array $arguments
 *   List of ordered arguments.
 *
 * @return mixed
 *   The return of the module.
 */
function ting_openlist_handler_call_module($module, $method, array $arguments = array()) {
  return ting_openlist_request('callModule', array(
    'module_name' => $module,
    'method' => $method,
    'args' => $arguments,
  ));
}
