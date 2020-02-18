<?php

namespace Drupal\uw_assetbank\Plugin\FilefieldSource;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\filefield_sources\Annotation\FilefieldSource;
use Drupal\filefield_sources\FilefieldSourceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Class UWAssetbank
 *
 * @package Drupal\uw_assetbank\Plugin\FilefieldSource
 *
 * @FilefieldSource(
 *   id = "uwassetbank",
 *   name = @Translation("UW Assetbank file source"),
 *   label = @Translation("UW Assetbank"),
 *   description = @Translation("Select a file using Assetbank media."),
 *   weight = 10
 * )
 */
class UWAssetbank implements FilefieldSourceInterface {
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

  }

  /** {@inheritDoc} */
  public static function process(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $element['filefield_uwassetbank'] = [
      '#weight' => 100.5,
      '#theme' => 'filefield_sources_element',
      '#source_id' => 'uwassetbank',
      // Required for proper theming.
      '#filefield_source' => TRUE,
    ];

    $element['filefield_uwassetbank']['url'] = [
      '#type' => 'textfield',
      '#description' => t('UW Assetbank image selection process.'),
    ];

    $element['filefield_uwassetbank']['submit'] = [
      '#type' => 'submit',
      '#value' => 'Submit',
    ];


    return $element;
  }

  /** {@inheritDoc} */
  public static function element($variables) {
    // Static call for Drupal service, can't use create/__construct.
    $renderer = \Drupal::service('renderer');
    $element = $variables['element'];

    $element['url']['#field_suffix'] = $renderer->render($element['submit']);

    return '<div class="filefield-source filefield-source-uwassetbank clear-block">' . $renderer->render($element['url']) . '</div>';
  }

}
