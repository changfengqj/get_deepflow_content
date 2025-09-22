<?php

namespace Drupal\get_deepflow_content\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\NodeType;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Configure example settings for this site.
 */
class getDeepflowcontentSettingForm extends ConfigFormBase {

  /** 
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'get_deepflow_content.client_key';

  /** 
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'get_deepflow_content_settings_form';
  }

  /** 
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * 获取指定内容类型的所有自定义字段
   */
  protected function getContentTypeFields($type) {
    $fields = [];
    
    if (empty($type)) {
      return $fields;
    }
    
    // 获取内容类型的所有字段定义
    $field_definitions = \Drupal::service('entity_field.manager')->getFieldDefinitions('node', $type);
    
    foreach ($field_definitions as $field_name => $field_definition) {
      // 在Drupal 10中区分基础字段和自定义字段的正确方式
      // 基础字段是BaseFieldDefinition的实例，自定义字段是FieldConfig的实例
      if (!$field_definition instanceof BaseFieldDefinition) {
        // 只添加自定义字段
        $fields[$field_name] = $field_definition->getLabel() . ' (' . $field_name . ')';
      }
    }
    
    return $fields;
  }

  /** 
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);
    
    // 获取所有内容类型作为下拉选项
    $node_types = NodeType::loadMultiple();
    $content_types = [];
    foreach ($node_types as $type) {
      $content_types[$type->id()] = $type->label();
    }

    $form['contenttype'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('内容类型设置'),
    ];
    
    // 内容类型下拉选择
    $form['contenttype']['type'] = [
      '#type' => 'select',
      '#title' => $this->t('内容类型'),
      '#options' => $content_types,
      '#empty_option' => $this->t('请选择内容类型'),
      '#default_value' => $config->get('type') ?? '',
      '#ajax' => [
        'callback' => '::updateFields',
        'wrapper' => 'dynamic-fields-wrapper',
        'event' => 'change',
      ],
    ];
    
    // 动态字段容器
    $form['contenttype']['dynamic_fields'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'dynamic-fields-wrapper'],
    ];
    
    // 获取当前选中的内容类型
    $selected_type = $form_state->getValue('type') ?? $config->get('type') ?? '';
    
    // 根据不同内容类型显示其实际字段
    if (!empty($selected_type)) {
      $fields = $this->getContentTypeFields($selected_type);
      
      if (!empty($fields)) {
        // Body映射字段下拉选择
        $form['contenttype']['dynamic_fields']['body_field'] = [
          '#type' => 'select',
          '#title' => $this->t('Body映射的字段'),
          '#options' => $fields,
          '#empty_option' => $this->t('请选择字段'),
          '#default_value' => $config->get('body_field') ?? '',
          '#description' => $this->t('选择作为Body内容映射的字段'),
        ];
        
        // 图片映射字段下拉选择
        $form['contenttype']['dynamic_fields']['image_field'] = [
          '#type' => 'select',
          '#title' => $this->t('图片映射的字段'),
          '#options' => $fields,
          '#empty_option' => $this->t('请选择字段'),
          '#default_value' => $config->get('image_field') ?? '',
          '#description' => $this->t('选择作为图片内容映射的字段'),
        ];

        // 摘要映射字段下拉选择
        $form['contenttype']['dynamic_fields']['brief_field'] = [
          '#type' => 'select',
          '#title' => $this->t('摘要映射的字段'),
          '#options' => $fields,
          '#empty_option' => $this->t('请选择字段'),
          '#default_value' => $config->get('brief_field') ?? '',
          '#description' => $this->t('选择作为摘要内容映射的字段'),
        ];

        // 关键词映射字段下拉选择
        $form['contenttype']['dynamic_fields']['keywords_field'] = [
          '#type' => 'select',
          '#title' => $this->t('关键词映射的字段'),
          '#options' => $fields,
          '#empty_option' => $this->t('请选择字段'),
          '#default_value' => $config->get('keywords_field') ?? '',
          '#description' => $this->t('选择作为关键词内容映射的字段'),
        ];
      }
      else {
        $form['contenttype']['dynamic_fields']['no_fields'] = [
          '#type' => 'markup',
          '#markup' => '<p>' . $this->t('所选内容类型没有可用字段') . '</p>',
        ];
      }
    }
    
    $form['service'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('接口设置'),
    ];
    $form['service']['server'] = [
      '#type' => 'textfield',
      '#title' => $this->t('服务器地址'),
      '#description' => $this->t('请输入服务器地址'),
      '#default_value' => $config->get('server') ?? '',
      '#required' => true,
    ];
    $form['service']['key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Key'),
      '#description' => $this->t('请输入来自www.deepflow.com的key'),
      '#default_value' => $config->get('key') ?? '',
      '#required' => true,
    ];
    $form['service']['token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Token'),
      '#description' => $this->t('请输入来自www.deepflow.com的token'),
      '#default_value' => $config->get('token') ?? '',
      '#required' => true,
    ];
    
    return parent::buildForm($form, $form_state);
  }
  
  /**
   * AJAX回调函数，用于更新字段
   */
  public function updateFields(array &$form, FormStateInterface $form_state) {
    return $form['contenttype']['dynamic_fields'];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(&$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    
    $config = $this->configFactory->getEditable(static::SETTINGS)
      ->set('server', $values['server'])
      ->set('key', $values['key'])
      ->set('token', $values['token'])
      ->set('image_field', $values['image_field'])
      ->set('body_field', $values['body_field'])
      ->set('brief_field', $values['brief_field'])
      ->set('keywords_field', $values['keywords_field'])
      ->set('type', $values['type']);
      
    // 保存动态字段值
    $dynamic_fields = $values['dynamic_fields'] ?? [];
    foreach ($dynamic_fields as $field => $value) {
      $config->set($field, $value);
    }
    
    $config->save();

    parent::submitForm($form, $form_state);
  }
}