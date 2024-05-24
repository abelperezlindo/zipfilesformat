<?php

namespace Drupal\zipfiles\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller class for download all in zip.
 */
class ZipfilesController extends ControllerBase {

  /**
   * Search node file fields and download it zipped.
   */
  public function download(Request $request, $nid) {
    $return = $request->query->get('return') ?? '/';
    $response = new Response();
    $schema = 'public://';
    $filebasepath = \Drupal::service('file_system')->realpath($schema);
    $filename = 'Archivos' . \Drupal::time()->getRequestTime() . '.zip';
    $realpath = $filebasepath . '/' . $filename;
    // Check if ZipArchive class exists.
    if (!class_exists('ZipArchive')) {
      \Drupal::logger('negociaciones_zipfiles')->warning('Could not compress file, PHP is compiled without zip support.');
      \Drupal::messenger()->addMessage("The site does not have support for compressing files in ZIP format", 'warning');
      return new RedirectResponse($return);
    }

    $node = Node::load($nid);
    if (empty($node)) {
      \Drupal::messenger()->addMessage("The node does not exist", 'warning');
      return new RedirectResponse($return);
    }

    $file_fields = $this->nodeHasFileFields($node);

    $zip = new \ZipArchive;
    if ($zip->open($realpath, \ZipArchive::CREATE) === TRUE) {
      foreach ($file_fields as $field_name) {
        /** @var Drupal\file\Plugin\Field\FieldType\FileFieldItemList $field_items */
        $field_items = $node->{$field_name};
        if (!$field_items->isEmpty()) {
          /** @var Drupal\file\Entity\File $file */
          foreach ($field_items->referencedEntities() as $file) {
            $file_reaLpath = str_replace($schema, $filebasepath . '/', $file->getFileUri());
            if (file_exists($file_reaLpath)) {
              $zip->addFile($file_reaLpath, $file->getFilename());
            }
            else {
              \Drupal::messenger()->addMessage("The file {$file->getFilename()} does not exist", 'warning');
            }
          }
        }
      }
      $zip->close();
    }

    if (file_exists($realpath)) {
      $response->headers->set('Expires', '0');
      $response->headers->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
      $response->headers->set('Content-Type', 'application/zip');
      $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
      $response->headers->set('Content-Transfer-Encoding', 'binary');
      $response->setContent(file_get_contents($realpath));
      return $response;
    }
    return new RedirectResponse($return);
  }

  /**
   * Search the file type fields in node.
   */
  public function nodeHasFileFields($node): Array {
    $file_fields = [];
    $fields_of_node = $node->getFields($include_computed = FALSE);

    foreach ($fields_of_node as $name => $data) {
      /** @var Drupal\Core\Field\FieldItemList $data */
      $type = $data->getFieldDefinition()->getType();

      if ($type === 'file') {
        $file_fields[] = $name;
      }
    }
    return $file_fields;
  }

}
