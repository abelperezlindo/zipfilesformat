<?php

namespace Drupal\zip_field_files\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Component\Utility\Html;

/**
 * Controller class for download all in zip.
 */
class ZipfilesController extends ControllerBase {

  /**
   * Search node file fields and download it zipped.
   */
  public function download(Request $request, $nid, $field_name) {
    $return = $request->query->get('return') ?? '/';

    // Check if ZipArchive class exists.
    if (!class_exists('ZipArchive')) {
      \Drupal::logger('zip_field_files')->warning('Could not compress file, PHP is compiled without zip support.');
      \Drupal::messenger()->addMessage("The site does not have support for compressing files in ZIP format", 'warning');
      return new RedirectResponse($return);
    }

    $node = Node::load($nid);
    if (empty($node)) {
      \Drupal::messenger()->addMessage("The node does not exist", 'warning');
      return new RedirectResponse($return);
    }
    if (!$node->hasField($field_name)) {
      return new RedirectResponse($return);
    }
    elseif ($node->{$field_name}->isEmpty()) {
      return new RedirectResponse($return);
    }

    $filename = $this->getFileName($node);
    $complete_filename = $this->getPreparedDestination($node, $filename);
    $config = \Drupal::configFactory()->getEditable('zip_field_files.settings');
    $operation = \ZipArchive::OVERWRITE;
    if ($config->get('save')) {
      $operation = \ZipArchive::CREATE;
    }
    $zip = new \ZipArchive();
    if ($zip->open($complete_filename, $operation) === TRUE) {
      foreach ($node->{$field_name}->referencedEntities() as $file) {
        $field_filepath = \Drupal::service('file_system')->realpath($file->getFileUri());
        if (file_exists($field_filepath)) {
          $zip->addFile($field_filepath, $file->getFilename());
        }
        else {
          \Drupal::messenger()->addMessage("The file {$file->getFilename()} does not exist", 'warning');
        }
      }
    }
    $zip->close();

    if (file_exists($complete_filename)) {
      $response = new Response();
      $response->headers->set('Expires', '0');
      $response->headers->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
      $response->headers->set('Content-Type', 'application/zip');
      $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
      $response->headers->set('Content-Transfer-Encoding', 'binary');
      $response->setContent(file_get_contents($complete_filename));
      return $response;
    }
    return new RedirectResponse($return);
  }

  /**
   * Get the name to use in the file.
   */
  protected function getFileName(Node $node) {
    $config = \Drupal::configFactory()->getEditable('zip_field_files.settings');
    $filename = '';
    if (!empty($config->get('filename'))) {
      $token_service = \Drupal::token();
      $token_data = ['node' => $node];
      $token_options = ['clear' => TRUE];
      $text = HTML::escape($config->get('filename'));
      $filename = $token_service->replace($text, $token_data, $token_options);
    }
    if (empty($filename)) {
      $filename = \Drupal::time()->getRequestTime();
    }

    $filename .= '.zip';

    return $filename;
  }

  /**
   * Prepare destination directory.
   */
  protected function getPreparedDestination(Node $node, String $filename) {
    // Prepare destination directory.
    $file_system = \Drupal::service('file_system');
    $config = \Drupal::configFactory()->getEditable('zip_field_files.settings');

    if ($config->get('save')) {
      $save_uri = $config->get('save_uri');
      $file_system->prepareDirectory($save_uri, FileSystemInterface:: CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
      $complete_filename = $file_system->realpath($save_uri . '/' . $filename);
    }
    elseif ($tmp = $file_system->getTempDirectory()) {
      $complete_filename = $file_system->tempnam($tmp, 'zipfile');
    }

    return $complete_filename;
  }

}
