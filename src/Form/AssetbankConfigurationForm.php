<?php

namespace Drupal\filefield_sources_assetbank\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AssetbankConfigurationForm
 *
 * Stores configuration parameters for assetbank, like host of assetbank
 * application and local folder name to transfer files to.
 *
 * @package Drupal\filefield_sources_assetbank\Form
 */
class AssetbankConfigurationForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'filefield_sources_assetbank_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'filefield_sources_assetbank.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('filefield_sources_assetbank.settings');

    $form['assetbank_url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Asset Bank URL'),
      '#default_value' => $config->get('assetbank_url'),
      '#description' => $this->t('Path to Asset Bank, use format http(s)://{host}/action/selectImageForCms'),
      '#size' => 100,
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $config = $this->configFactory->getEditable('filefield_sources_assetbank.settings');

    $config->set('assetbank_url', $values['assetbank_url'])->save();

    parent::submitForm($form, $form_state);
  }
}
