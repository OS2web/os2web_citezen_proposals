<?php

namespace Drupal\os2web_citizen_proposals\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure os2web_citizen_proposals settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * Name of the config.
   *
   * @var string
   */
  public static $configName = 'os2web_citizen_proposals.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'os2web_citizen_proposals_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [SettingsForm::$configName];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(SettingsForm::$configName);

    // General settings.
    $form['admin_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Admin email'),
      '#default_value' => $config->get('admin_email'),
      '#description' => $this->t('Email to receive admin proposal notifications'),
    ];

    $form['copy_email'] = [
        '#type' => 'email',
        '#title' => $this->t('Copy email'),
        '#default_value' => $config->get('copy_email'),
        '#description' => $this->t('Email to receive a copy of all proposal notifications with :: COPY :: in subject'),
    ];

    $form['proposal_max_votes'] = [
      '#type' => 'textfield',
      '#attributes' => array(
        ' type' => 'number',
      ),
      '#title' => $this->t('Maximum votes'),
      '#default_value' => $config->get('proposal_max_votes'),
      '#description' => $this->t('Number of votes needed for proposal')
    ];

    $form['proposal_vote_notify'] = [
      '#type' => 'textfield',
      '#attributes' => array(
        ' type' => 'number',
      ),
      '#title' => $this->t('Vote notify'),
      '#default_value' => ($config->get('proposal_vote_notify') ?? 0),
      '#description' => $this->t('Number of new votes before notify')
    ];


    $form['proposal_publish_period_months'] = [
      '#type' => 'textfield',
      '#attributes' => array(
        ' type' => 'number',
      ),
      '#field_suffix' => $this->t('months'),
      '#title' => $this->t('Unpublish proposal older than'),
      '#default_value' => $config->get('proposal_publish_period_months'),
      '#description' => $this->t('Any proposals older that this period will be unpublished')
    ];

    $form['proposal_life_period_months'] = [
      '#type' => 'textfield',
      '#attributes' => array(
        ' type' => 'number',
      ),
      '#field_suffix' => $this->t('months'),
      '#title' => $this->t('Delete proposal older than'),
      '#default_value' => $config->get('proposal_life_period_months'),
      '#description' => $this->t('Any proposals older that this period will be deleted')
    ];


    $form['proposal_publish_date_on_author'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Set approved date on create date'),
      '#default_value' => $config->get('proposal_publish_date_on_author'),
    ];


    // Email templates.
    $form['email_templates'] = [
      '#type' => 'details',
      '#title' => t('Email templates'),
    ];

    $email_template_keys = [
      'create_proposal_user' => 'Email to user - proposal created',
      'proposal_accepted_user' => 'Email to user - proposal accepted',
      'proposal_rejected_user' => 'Email to user - proposal rejected',
      'vote_received_user' => 'Email to user - vote received',
      'proposal_vote_maxed_user' => 'Email to user . proposal maxed',
      'proposal_not_vote_maxed_user' => 'Email to user - proposal not maxed',
      'create_proposal_admin' => 'Email to admin - proposal created',
      'vote_maxed_admin' => 'Email to admin - votes have maxed',
    ];

    $template_description = $this->t("You can use the following replacement tokens: <br>
      <b>@proposal_link</b> => Link to proposal node<br>
      <b>@proposal_title</b> => Title of the proposal<br>
      <b>@proposal_body</b> => Body of the proposal<br>
      <b>@proposal_vote_count</b> => Count of votes for the proposal<br>
      <b>@person_name</b> => Author name<br>
      <b>@person_address</b> => Author address<br>
      <b>@person_zipcode</b> => Author ZIP code<br>
      <b>@person_city</b> => Author city<br>
      <b>@person_email</b> => Author email<br>
      <b>@proposal_reject_reason</b> => Reject reason for proposal<br>
      <b>@proposal_reject_reason_ext</b> => Reject reason for proposal (extended)<br>");

    foreach ($email_template_keys as $template_key => $template_name) {
      $form['email_templates']["template_details_$template_key" ] = [
        '#type' => 'details',
        '#title' => t('Email template %title', ['%title' => $template_name]),
      ];
      $form['email_templates']["template_details_$template_key"]["email_subject_$template_key"] = [
        '#type' => 'textfield',
        '#title' => $this->t('Subject'),
        '#default_value' => $config->get("email_subject_$template_key"),
      ];
      $form['email_templates']["template_details_$template_key"]["email_body_$template_key"] = [
        '#type' => 'textarea',
        '#title' => $this->t('Body'),
        '#default_value' => $config->get("email_body_$template_key"),
        '#description' => $template_description
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $confObject = $this->config(SettingsForm::$configName);
    foreach ($form_state->getValues() as $key => $value) {
      $confObject->set($key, $value);
    }
    $confObject->save();

    parent::submitForm($form, $form_state);
  }

}
