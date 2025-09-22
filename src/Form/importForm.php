<?php


namespace Drupal\get_deepflow_content\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Symfony\Component\HttpFoundation\Response;
use Drupal\user\Entity\User;
use PhpOffice\PhpSpreadsheet\IOFactory;
/**
 * Implements the SimpleForm form controller.
 *
 * This example demonstrates a simple form with a singe text input element. We
 * extend FormBase which is the simplest form base class used in Drupal.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class importForm extends FormBase {

  /**
   * Build the simple form.
   *
   * A build form method constructs an array that defines how markup and
   * other form elements are included in an HTML form.
   *
   * @param array $form
   *   Default form array structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object containing current form state.
   *
   * @return array
   *   The render array defining the elements of the form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    global $base_url;
    $params = \Drupal::request()->query->all();
    $account = \Drupal::currentUser();
    $uid = $account->id();

    $form['markup'] = [
      '#markup' => '<div class="text">立即导入来自deepflow的内容</div>',
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('立即导入'),
      '#attributes' => array(
        'class'=> array('confirm'),
      ),
    ];
    
    return $form;
  }

  public function getFormId() {
    return 'import_form';
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $operations = array();
    $site_config = \Drupal::config('get_deepflow_content.client_key');
    $server = $site_config->get('server');
    $key = $site_config->get('key');
    $token = $site_config->get('token');
    $type = $site_config->get('type');
    $image_field = $site_config->get('image_field');
    $body_field = $site_config->get('body_field');
    $brief_field = $site_config->get('brief_field');
    $keywords_field = $site_config->get('keywords_field');
    $signature_client = hash('sha512', $key.$token);
    $url = $server.'/personal-admin/website/publisher/content?token='.$token.'&signature='.$signature_client;
    $data = [
      'token' => $token,
      'signature' => $signature_client,
    ];

    //$content_json = self::https_post($url,$data);
    $content_json = file_get_contents($url);
    // print $content_json;
    
    $content_arr = json_decode($content_json);
    //print_r($content_arr);
    //adsf();
    if($content_arr->node_count > 0){
      foreach ($content_arr->data as $record) {
        $title = $record->title;
        $content = $record->body;
        $image = $record->image;
        $brief = $record->brief;
        $keywords = $record->keywords;
        $operations[] = [
          '\Drupal\get_deepflow_content\Controller\BatchImportContentController::importcontent',
          [$title, $content, $brief,$keywords,$image,$type,$body_field,$image_field,$brief_field,$keywords_field]  // 传递两个参数
        ];
      }
    }
    $batch = [
      'title' => $this->t('Importing content'),
      'operations' => $operations,
      'finished' => '\Drupal\get_deepflow_content\Controller\BatchImportContentController::finishedCallback',
    ];
    batch_set($batch);
  }
  public static function https_post($url,$data){
    
    $headers = [];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
      return 'Error:' . curl_error($ch);
    }
    curl_close($ch);
    return $response;
  }
}