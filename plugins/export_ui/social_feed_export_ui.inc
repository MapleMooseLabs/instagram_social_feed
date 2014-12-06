<?php
/**
 * @file
 */

/**
 * Define this Export UI plugin.
 */
$plugin = array(
  'schema' => 'social_feeds_instagram',  // As defined in hook_schema().
  'access' => 'administer social_feed settings',  // Define a permission users must have to access these pages.
  // Define the menu item.
  'menu' => array(
    'menu prefix' => 'admin/config/services',
    'menu item' => 'social_feed',
    'menu title' => 'Manage feeds',
    'menu description' => 'Administer Instagram feeds.',
  ),
  // Define user interface texts.
  'title singular' => t('preset'),
  'title plural' => t('presets'),
  'title singular proper' => t('Instagram feed'),
  'title plural proper' => t('Instagram feeds'),
  // Define the names of the functions that provide the add/edit forms.
  'form' => array(
    'settings' => 'social_feed_ctools_export_ui_form',
    'submit' => 'social_feed_ctools_submit',
    'validate' => 'social_feed_ctools_validate',
  ),
);

/**
 * Define the preset add/edit form.
 */
function social_feed_ctools_export_ui_form(&$form, &$form_state) {
  $preset = $form_state['item'];
  $form['type'] = array(
    '#type' => 'textfield',
    '#title' => t('Feed type'),
    '#default_value' => $preset->type,
    '#required' => TRUE,
  );
  $form['search_term'] = array(
    '#type' => 'textfield',
    '#title' => t('Search term (if applicable)'),
    '#description' => t("Leave blank if selecting user feed or user's own photos"),
    '#default_value' => $preset->search_term,
  );
  $form['auto_publish'] = array(
    '#type' => 'textfield',
    '#title' => t('Auto-publish'),
    '#default_value' => $preset->auto_publish,
  );
  $form['num_photos'] = array(
    '#type' => 'textfield',
    '#title' => t('Number of photos'),
    '#description' => t('Number of photos to display at a time with this feed'),
    '#default_value' => $preset->num_photos,
    '#attributes' => array('placeholder' => 'num_photos'),
    '#required' => TRUE,
  );
  $form['enabled'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable this feed?'),
    '#default_value' => $preset->enabled,
    '#required' => FALSE,
  );
}

/**
 * Define submit handler to encrypt password
 */
function social_feed_ctools_submit(&$form, &$form_state) {
  $form_state['values']['ftp_password'] = utf8_encode(crypt($form_state['values']['ftp_password']));
}

/**
 * Check storage path for invalid file characters
 */
function social_feed_ctools_validate(&$form, &$form_state) {
  if (preg_match('/[^A-Za-z0-9.#\\-$]/', $form_state['values']['file_storage'])) {
    form_set_error('file_storage', 'Invalid characters in storage directory path.');
  }
}
