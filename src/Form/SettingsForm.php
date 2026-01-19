<?php

namespace Drupal\minitrack\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class SettingsForm extends ConfigFormBase {
  public function getFormId() {
    return 'minitrack_settings_form';
  }

  protected function getEditableConfigNames() {
    return ['minitrack.settings'];
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('minitrack.settings');
    $api_keys_config = (array) $config->get('api_keys');

    // Use an add-more style UI: repeated key/description fields.
    $num = $form_state->get('api_keys_num');
    if ($num === NULL) {
      $num = max(1, count($api_keys_config));
      $form_state->set('api_keys_num', $num);
    }

    $form['api_keys'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'minitrack-api-keys'],
      '#tree' => TRUE,
    ];

    for ($i = 0; $i < $num; $i++) {
      $default_key = '';
      $default_desc = '';
      if (isset($api_keys_config[$i]) && is_array($api_keys_config[$i]) && isset($api_keys_config[$i]['key'])) {
        $default_key = $api_keys_config[$i]['key'];
        $default_desc = isset($api_keys_config[$i]['description']) ? $api_keys_config[$i]['description'] : '';
      }

      $form['api_keys'][$i]['key'] = [
        '#type' => 'textfield',
        '#title' => $this->t('API key'),
        '#default_value' => $default_key,
      ];

      $form['api_keys'][$i]['description'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Description'),
        '#default_value' => $default_desc,
      ];
    }

    $form['api_keys']['add_more'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add API key'),
      '#submit' => [[$this, 'addMoreSubmit']],
      '#limit_validation_errors' => [],
      '#weight' => 100,
    ];

    $form['cors_origins'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Allowed CORS origins (one per line)'),
      '#default_value' => implode("\n", (array) $config->get('cors_origins')),
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $raw = $form_state->getValue('api_keys');
    $api_keys = [];
    if (is_array($raw)) {
      foreach ($raw as $entry) {
        if (!is_array($entry)) { continue; }
        $k = isset($entry['key']) ? trim($entry['key']) : '';
        if ($k === '') { continue; }
        $d = isset($entry['description']) ? trim($entry['description']) : '';
        $api_keys[] = ['key' => $k, 'description' => $d];
      }
    }

    $this->config('minitrack.settings')
      ->set('api_keys', $api_keys)
      ->set('cors_origins', preg_split('/\r\n|\r|\n/', $form_state->getValue('cors_origins')))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Submit handler for the Add API key button.
   */
  public function addMoreSubmit(array &$form, FormStateInterface $form_state) {
    $num = $form_state->get('api_keys_num');
    $num = $num ? $num + 1 : 1;
    $form_state->set('api_keys_num', $num);
    $form_state->setRebuild();
  }
}
