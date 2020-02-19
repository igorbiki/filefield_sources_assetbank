<?php

namespace Drupal\filefield_sources_assetbank\Plugin\FilefieldSource;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\filefield_sources\Annotation\FilefieldSource;
use Drupal\filefield_sources\FilefieldSourceInterface;


/**
 * Class Assetbank
 *
 * @package Drupal\filefield_sources_assetbank\Plugin\FilefieldSource
 *
 * @FilefieldSource(
 *   id = "assetbank",
 *   name = @Translation("Assetbank file source"),
 *   label = @Translation("Assetbank"),
 *   description = @Translation("Select a file using Assetbank media."),
 *   weight = 10
 * )
 */
class Assetbank implements FilefieldSourceInterface {
//
//  /** @var \Drupal\Core\Messenger\MessengerInterface  */
//  protected $messenger;
//
//  /** @var \Drupal\Core\Render\RendererInterface  */
//  protected $renderer;
//
//  /**
//   * UWAssetbank constructor.
//   *
//   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
//   *   Messenger interface for posting messages.
//   * @param \Drupal\Core\Render\RendererInterface $renderer
//   *   Renderer interface for rendering fields.
//   */
//  public function __construct(MessengerInterface $messenger, RendererInterface $renderer) {
//    $this->messenger = $messenger;
//    $this->renderer = $renderer;
//  }
//
//  /**
//   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
//   *
//   * @return static
//   */
//  public static function create(ContainerInterface $container) {
//    return new static(
//      $container->get('messenger'),
//      $container->get('renderer')
//    );
//  }

  /** {@inheritDoc} */
  public static function value(array &$element, &$input, FormStateInterface $form_state) {

    $dev = 'stop';
  }

  /** {@inheritDoc} */
  public static function process(array &$element, FormStateInterface $form_state, array &$complete_form) {
    /** @var \Consolidation\Config\ConfigInterface $assetbank_config */
    $assetbank_config = \Drupal::config('filefield_sources_assetbank.settings');
    $assetbank_host = $assetbank_config->get('assetbank_url');

    $element['filefield_assetbank'] = [
      '#weight' => 101.5,
      '#theme' => 'filefield_sources_element',
      '#source_id' => 'assetbank',
      // Required for proper theming.
      '#filefield_source' => TRUE,
    ];

    if (!empty($assetbank_host)) {
      $element['filefield_assetbank']['assetbank_url'] = [
        '#type' => 'textfield',
        '#description' => t('Assetbank image selection process.'),
        '#disabled' => TRUE,
        '#attributes' => [
          'id' => 'assetbank_url',
        ],
      ];

      $element['filefield_assetbank']['submit'] = [
        '#name' => implode('_', $element['#parents']) . '_transfer',
        '#type' => 'button',
        '#value' => t('Browse'),
        '#validate' => [],
        '#submit' => [
          'filefield_sources_assetbank_field_submit',
          'filefield_sources_field_submit',
        ],
        '#limit_validation_errors' => [$element['#parents']],
        '#attached' => [
          'library' => [
            'filefield_sources_assetbank/assetbank-global',
          ],
          'drupalSettings' => [
            'assetbank' => [
              'host' => $assetbank_config->get('assetbank_url'),
            ],
          ],
        ],
        '#attributes' => [
          'class' => ['assetbank-selection']
        ],
      ];
    }
    else {
      $url = Link::createFromRoute(
        t('configure'),
        'filefield_sources_assetbank.settings'
      );

      $element['filefield_assetbank']['assetbank_url'] = [
        '#markup' => t('Configuration for assetbank missing. Please @conf it before using this feature.', [
          '@conf' => $url->toString(),
        ]),
      ];
    }

    return $element;
  }

  /** {@inheritDoc} */
  public static function element($variables) {
    // Static call for Drupal service, can't use create/__construct.
    $renderer = \Drupal::service('renderer');
    $element = $variables['element'];

    if (!empty($element['submit'])) {
      $element['assetbank_url']['#field_suffix'] = $renderer->render($element['submit']);
    }

    return '<div class="filefield-source filefield-source-assetbank clear-block">' . $renderer->render($element['assetbank_url']) . '</div>';
  }

}
