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

 $form['social_feed_pane_count'] = array(
   '#type' => 'textfield',
   '#title' => t('Photo count'),
   '#description' => t('Number of photos to be displayed'),
   '#default_value' => $conf['social_feed_pane_count'],
 );

 return $form;
}

/**
 * Edit form submit function.
 */
function social_feed_pane_form_submit($form, &$form_state) {
  $form_state['conf']['social_feed_pane_count'] = $form_state['values']['social_feed_pane_count'];
}

/**
 * Render the panel.
 */
function social_feed_pane_render($subtype, $conf, $args, $contexts) {
  if ($context->empty) {
    return;
  }

  // Render as a block.
  $block = new stdClass();
  $block->module = 'social_feed';

  $block->content = social_feed_panel_pane_content($conf['social_feed_pane_count']);// . social_feed_block_view('social_feed_block');
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

