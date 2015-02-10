<?php

$plugin = array(
  'single' => TRUE,
  'title' => t('Social feed pane'),
  'description' => t('Display photos from the Social Feed module'),
  'category' => t('Custom Panes'),
  'edit form' => 'social_feed_pane_form',
  'render callback' => 'social_feed_pane_render',
  'admin info' => 'social_feed_pane_info',
  'defaults' => array(),
  'all contexts' => TRUE,
);

/**
 * Edit form.
 */
function social_feed_pane_form($form, &$form_state) {
  $conf = $form_state['conf'];

  $result = db_select('social_feeds_instagram', 'f')
    ->fields('f')
    ->condition('enabled', 1, '=')
    ->execute();

  $form['social_feed_pane_count'] = array(
    '#type' => 'textfield',
    '#title' => t('Photo count'),
    '#description' => t('Number of photos to be displayed'),
    '#default_value' => $conf['social_feed_pane_count'],
  );

  $form['social_feed_feed_selection'] = array(
    '#type' => 'select',
    '#title' => t('Social Feed'),
    '#description' => t('Which feed should be used as a source?'),
    '#default_value' => $conf['social_feed_feed_selection'],
    '#options' => array(),
  );
  $form['social_feed_more'] = array(
    '#type' => 'fieldset',
    '#title' => t('More Link'),
    '#collapsible' => FALSE,
    '#collapsed' => FALSE,
    '#description' => t('Enter a URL and text to create a general link in the display. Leave Link URL blank to display none.'),
  );
  $form['social_feed_more']['social_feed_more_uri'] = array(
    '#type' => 'textfield',
    '#title' => t('Link URL'),
    '#default_value' => $conf['social_feed_more_uri'],
    '#description' => t('Ex: http://instagram.com/mediacurrent.'),
    '#size' => 60,
    '#maxlength' => 255,
  );
  $form['social_feed_more']['social_feed_more_text'] = array(
    '#type' => 'textfield',
    '#title' => t('Link Text'),
    '#default_value' => $conf['social_feed_more_text'],
    '#size' => 60,
    '#maxlength' => 255,
  );

  foreach ($result as $row) {
    $form['social_feed_feed_selection']['#options'][$row->id] = $row->name;
  }

  return $form;
}

/**
 * Edit form submit function.
 */
function social_feed_pane_form_submit($form, &$form_state) {
  $form_state['conf']['social_feed_pane_count'] = $form_state['values']['social_feed_pane_count'];
  $form_state['conf']['social_feed_feed_selection'] = $form_state['values']['social_feed_feed_selection'];
  $form_state['conf']['social_feed_more_uri'] = $form_state['values']['social_feed_more_uri'];
  $form_state['conf']['social_feed_more_text'] = $form_state['values']['social_feed_more_text'];
}

/**
 * Render the panel.
 */
function social_feed_pane_render($subtype, $conf, $args, $contexts) {
  foreach ($contexts as $context) {
    if ($context->empty) {
      return;
    }
  }

  // Render as a block.
  $block = new stdClass();
  $block->module = 'social_feed';

  $block->content = social_feed_panel_pane_content(array(
    'count' => $conf['social_feed_pane_count'],
    'feed' =>  $conf['social_feed_feed_selection'],
    'more_uri' => !empty($conf['social_feed_more_uri']) ? $conf['social_feed_more_uri'] : FALSE ,
    'more_text' => !empty($conf['social_feed_more_text']) ? $conf['social_feed_more_text'] : 'More',
  ));
  return $block;
}

/**
 * Admin info.
 */
function social_feed_taxonomy_pane_info($subtype, $conf, $contexts) {
  if (!empty($conf)) {
    $content = '<p><b>Photo Count:</b> ' . $conf['social_feed_pane_count'] . '</p>';

    $block = new stdClass;
    $block->title = $conf['override_title'] ? $conf['override_title_text'] : '';
    $block->content = $content;
    return $block;
  }
}

