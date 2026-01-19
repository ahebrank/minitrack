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

    $form['api_keys'] = [
      '#type' => 'textarea',
      '#title' => $this->t('API keys (one per line)'),
      '#description' => $this->t('Provide one or more API keys. Use separate lines for multiple keys.'),
      '#default_value' => implode("\n", (array) $config->get('api_keys')),
    ];

    $form['cors_origins'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Allowed CORS origins (one per line)'),
      '#default_value' => implode("\n", (array) $config->get('cors_origins')),
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $api_keys = preg_split('/\r\n|\r|\n/', $form_state->getValue('api_keys'));
    $api_keys = array_values(array_filter(array_map('trim', $api_keys), function($v){ return $v !== ''; }));

    $this->config('minitrack.settings')
      ->set('api_keys', $api_keys)
      ->set('cors_origins', preg_split('/\r\n|\r|\n/', $form_state->getValue('cors_origins')))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
