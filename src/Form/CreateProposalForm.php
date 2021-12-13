<?php

namespace Drupal\os2web_citizen_proposals\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;

class CreateProposalForm extends FormBase {

  private $formId = 'os2web_citizen_proposals_create_form';

  /**
   * @inheritDoc
   */
  public function getFormId() {
    return $this->formId;
  }

  /**
   * Builds the form.
   *
   * @param array $form
   * @param FormStateInterface $form_state
   * @return array
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['proposal_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Proposal title'),
      '#required' => TRUE,
    ];

    $form['proposal_body'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Proposal'),
      '#required' => TRUE,
    ];

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your name'),
      '#required' => TRUE,
    ];

    $form['address'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Address'),
      '#required' => TRUE,
    ];

    $form['zipcode'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Zip code'),
      '#required' => TRUE,
    ];

    $form['city'] = [
      '#type' => 'textfield',
      '#title' => $this->t('City'),
      '#required' => TRUE,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Your email'),
      '#required' => TRUE,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send proposal'),
    ];

    return $form;
  }

  /**
   * Creates new os2web_citizen_proposals node.
   *
   * Sends the relevant emails.
   *
   * @param array $form
   * @param FormStateInterface $form_state
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $proposal_title = $form_state->getValue('proposal_title');
    $proposal_body = $form_state->getValue('proposal_body');
    $name = $form_state->getValue('name');
    $address = $form_state->getValue('address');
    $zipcode = $form_state->getValue('zipcode');
    $city = $form_state->getValue('city');
    $email = $form_state->getValue('email');

    $proposal = Node::create(['type' => 'os2web_citizen_proposals']);
    $proposal->set('title', $proposal_title);
    $proposal->set('field_os2web_cit_props_proposal', $proposal_body);
    $proposal->set('field_os2web_cit_props_name', $name);
    $proposal->set('field_os2web_cit_props_address', $address);
    $proposal->set('field_os2web_cit_props_zip_code', $zipcode);
    $proposal->set('field_os2web_cit_props_city', $city);
    $proposal->set('field_os2web_cit_props_email', $email);
    $proposal->enforceIsNew();
    $proposal->save();

    $this->messenger()->addStatus($this->t('Proposal is created @title.', ['@title' => $proposal_title]));

    /** @var \Drupal\os2web_citizen_proposals\Service\ProposalEmailService $emailService */
    $emailService = \Drupal::service('os2web_citizen_proposals.email_service');

    $emailService->sendUserProposalCreatedEmail($proposal);
    $emailService->sendAdminProposalCreatedEmail($proposal);

    $current_uri = \Drupal::request()->getRequestUri();
//    $httpAndHost = \Drupal::request()->getSchemeAndHttpHost();
    $url = Url::fromUri('internal:' . $current_uri, ['fragment' => 'proposals-list']);
    $form_state->setRedirectUrl($url);
  }
}
