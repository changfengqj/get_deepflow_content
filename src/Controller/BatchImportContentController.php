<?php
namespace Drupal\get_deepflow_content\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\Core\Url;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\File\FileSystemInterface;

class BatchImportContentController {

  public static function importcontent($title,$body,$brief,$keywords,$image_path,$type,$body_field,$image_field,$brief_field,$keywords_field, &$context){
    $message = '执行中...';
    $results = array();
    if($title != ''){
      save_deepflow_data($title,$body,$brief,$keywords,$image_path,$type,$body_field,$image_field,$brief_field,$keywords_field);
      $results[] = $title;
    }
    $context['message'] = $message;
    $context['results'][] = $results;
  }
  public static function finishedCallback($success, $results, $operations) {
    if ($success) {
      $message = \Drupal::translation()->formatPlural(
        count($results),
        '执行成功.', '执行成功.'
      );
    }
    else {
      $message = t('Finished with an error.');
    }
    \Drupal::messenger()->addMessage(t('%message', [
           '%message' => $message,
         ]));
  }
}