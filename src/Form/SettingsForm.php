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

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API key'),
      '#default_value' => $config->get('api_key'),
    ];

    $form['cors_origins'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Allowed CORS origins (one per line)'),
      '#default_value' => implode("\n", (array) $config->get('cors_origins')),
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('minitrack.settings')
      ->set('api_key', $form_state->getValue('api_key'))
      ->set('cors_origins', preg_split('/\r\n|\r|\n/', $form_state->getValue('cors_origins')))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
