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
 *   name = @Translation("Asset Bank file source"),
 *   label = @Translation("Asset Bank"),
 *   description = @Translation("Select a file using Asset Bank media."),
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
            $filename = static::validateAll($element, rawurldecode($filesystem->basename($url)), $request->getHeaders(), $form_state);

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
        '#attributes' => [
          'id' => 'assetbank_url',
        ],
      ];

      $element['filefield_assetbank']['assetbank_url_public'] = [
        '#type' => 'textfield',
        '#description' => t('Asset Bank image selection process.'),
        '#disabled' => TRUE,
        '#attributes' => [
          'id' => 'assetbank_url_public',
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
        '#value' => t('Save'),
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
        '#markup' => t('Configuration for Asset Bank missing. Please @conf it before using this feature.', [
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
      $element['assetbank_url_public']['#field_prefix'] = $renderer->render($element['submit']);
    }

    if (!empty($element['transfer'])) {
      $element['assetbank_url_public']['#field_suffix'] = $renderer->render($element['transfer']);
    }

    $output .= $renderer->render($element['assetbank_url']);
    $output .= $renderer->render($element['assetbank_url_public']);

    return '<div class="filefield-source filefield-source-assetbank clear-block">' . $output . '</div>';
  }

  /**
   *  Validate extension and file size.
   */
  private static function validateAll(array $element, string $filename, array $headers, FormStateInterface &$form_state) {
    $dev = 'stop';

    $field = \Drupal::entityTypeManager()
      ->getStorage('field_config')
      ->load($element['#entity_type'] . '.' . $element['#bundle'] . '.' . $element['#field_name']);

    $filename = filefield_sources_clean_filename($filename, $field->getSetting('file_extensions'));
    $pathinfo = pathinfo($filename);

    if (empty($pathinfo['extension'])) {
      $form_state->setError($element, t('The URL must be a file and have an extension.'));
    }

    $extensions = $field->getSetting('file_extensions');
    $regex = '/\.(' . preg_replace('/[ +]/', '|', preg_quote($extensions)) . ')$/i';

    if (!empty($extensions) && !preg_match($regex, $filename)) {
      $form_state->setError($element, t('Only files with the following extensions are allowed: %files-allowed.', ['%files-allowed' => $extensions]));
    }

    // Check file size based off of header information.
    if (!empty($element['#upload_validators']['file_validate_size'][0])) {
      $max_size = $element['#upload_validators']['file_validate_size'][0];
      $file_size = $headers['Content-Length'];
      if (is_array($file_size) && !empty($file_size[0]) && $file_size[0] > $max_size) {
        $form_state->setError($element, t('The remote file is %filesize exceeding the maximum file size of %maxsize.', ['%filesize' => format_size($file_size), '%maxsize' => format_size($max_size)]));
      }
    }

    return $filename;
  }
}
