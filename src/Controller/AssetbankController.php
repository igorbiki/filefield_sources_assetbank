<?php

/**
 * @file
 * Contains \Drupal\filefield_sources_assetbank\Controller\AssetbankController.
 */
namespace Drupal\filefield_sources_assetbank\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

class AssetbankController extends ControllerBase {
  /**
   * {@inheritdoc}
   */
  public function selectImage() {
    $response = $_POST["xml"];
    $photo_details = json_decode(json_encode(simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA), TRUE), TRUE);
    $image_url = $photo_details['url'];
    $html = '<script>window.opener.postMessage("' . $image_url . '", "*"); self.close();</script>';
    $response = new Response();
    $response->setContent($html);
    return $response;
  }

  public function saveImage($source, $destination) {
    // https://drupalize.me/blog/201512/speak-http-drupal-httpclient
    // With source image url, create managed_file, upload it, and pass fid.
  }
}
