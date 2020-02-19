<?php

namespace Drupal\filefield_sources_assetbank\Plugin\FilefieldSource;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\filefield_sources\Annotation\FilefieldSource;
use Drupal\filefield_sources\FilefieldSourceInterface;
use GuzzleHttp\Exception\RequestException;


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

  /** {@inheritDoc} */
  public static function value(array &$element, &$input, FormStateInterface $form_state) {
    if (!empty($input['filefield_assetbank']['assetbank_url'])
      && strlen($input['filefield_assetbank']['assetbank_url']) > 0
      && UrlHelper::isValid($input['filefield_assetbank']['assetbank_url'])
    ) {
      $url = $input['filefield_assetbank']['assetbank_url'];

      if ($temp_folder = \Drupal::service('file_system')->getTempDirectory()) {
        $client = \Drupal::httpClient();

        try {
          $request = $client->get($url);

          if ($request->getStatusCode() === 200) {
            $file_contents = $request->getBody()->getContents();

            /** @var \Drupal\Core\File\FileSystem $filesystem */
            $filesystem = \Drupal::service('file_system');
            $filename = rawurldecode($filesystem->basename($url));

            $field = \Drupal::entityTypeManager()
              ->getStorage('field_config')
              ->load($element['#entity_type'] . '.' . $element['#bundle'] . '.' . $element['#field_name']);

            $filename = filefield_sources_clean_filename($filename,
            $field->getSetting('file_extensions'));

            $filepath = \Drupal::service('file_system')
              ->createFilename($filename, $temp_folder);

            if ($file_contents && $fp = @fopen($filepath, 'w')) {
              fwrite($fp, $file_contents);
              fclose($fp);
            }

            if ($file = filefield_sources_save_file($filepath, $element['#upload_validators'], $element['#upload_location'])) {
              if (!in_array($file->id(), $input['fids'])) {
                $input['fids'][] = $file->id();
              }
            }

            @unlink($filepath);
          }
        }
        catch (RequestException $exception) {
          \Drupal::logger('filefield_source_assetbank')->critical($exception->getMessage());
        }
      }
      else {
        $message = 'The temp directory is not writable, not set, or you don\'t have permissions.';
        \Drupal::logger('filefield_sources_assetbank')->log(E_NOTICE, $message);
        \Drupal::messenger()->addError($message);
      }

    }

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
        '#type' => 'hidden',
        '#description' => t('Assetbank image selection process.'),
//        '#disabled' => TRUE,
        '#attributes' => [
          'id' => 'assetbank_url',
        ],
      ];

      $element['filefield_assetbank']['submit'] = [
        '#name' => implode('_', $element['#parents']) . '_submit',
        '#type' => 'button',
        '#value' => t('Select'),
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

      $element['filefield_assetbank']['transfer'] = [
        '#name' => implode('_', $element['#parents']) . '_transfer',
        '#type' => 'button',
        '#value' => t('Fetch'),
        '#validate' => [],
        '#submit' => [
          'filefield_sources_field_submit',
        ],
        '#limit_validation_errors' => [$element['#parents']],
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
    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    $renderer = \Drupal::service('renderer');
    $element = $variables['element'];
    $output = '';

    if (!empty($element['submit'])) {
      $output .= $renderer->render($element['submit']);
    }

    if (!empty($element['transfer'])) {
      $output .= $renderer->render($element['transfer']);
    }

    $output .= $renderer->render($element['assetbank_url']);

    return '<div class="filefield-source filefield-source-assetbank clear-block">' . $output . '</div>';
  }

}
