<?php

namespace Drupal\os2web_citizen_proposals\Form;

use Drupal\comment\Entity\Comment;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

class ProposalVoteForm extends FormBase {

  private $formId = 'os2web_citizen_proposals_vote_form';
  private $proposalId;

    /**
   * @inheritDoc
   */
  public function getFormId() {
    // We are using the same form multiple times on the same page, this is need
    // to avoid submission of the wrong form (always the first). See :
    // https://www.drupal.org/project/drupal/issues/2821852
    static $count = 0;
    $count++;

    return $this->formId . $count;
  }

  /**
   * Builds the form.
   *
   * @param array $form
   * @param FormStateInterface $form_state
   * @return array
   */
  public function buildForm(array $form, FormStateInterface $form_state, $proposalId = NULL) {
    // Saving proposal id.
    $this->proposalId = $proposalId;

    $form['vote_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your name'),
      '#required' => TRUE,
    ];

    $form['vote_address'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Address'),
      '#required' => TRUE,
    ];

    $form['vote_zipcode'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Zip'),
      '#required' => TRUE,
    ];

    $form['vote_city'] = [
      '#type' => 'textfield',
      '#title' => $this->t('City'),
      '#required' => TRUE,
    ];

    $form['vote_email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('E-mail'),
      '#required' => TRUE,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send vote'),
      '#name' => "send-vote-$proposalId"
    ];

    return $form;
  }

  /**
   * Adds vote for the selected proposal.
   *
   * @param array $form
   * @param FormStateInterface $form_state
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $name = $form_state->getValue('vote_name');
    $address = $form_state->getValue('vote_address');
    $zipcode = $form_state->getValue('vote_zipcode');
    $city = $form_state->getValue('vote_city');
    $email = $form_state->getValue('vote_email');

    // Checking This email was use previously for voting for the same proposal.
    $query = \Drupal::entityQuery('comment');
    $query->condition('comment_type', 'os2web_citizen_proposals_vote');
    $query->condition('entity_id', $this->proposalId);
    $query->condition('field_os2web_cit_props_v_email', $email);
    $previousVotes = $query->count()->execute();

    if ($previousVotes) {
      $this->messenger()->addWarning($this->t('You have already votes for this proposal'));
      return;
    }

    // This email was not used for voting before.
    $values = [
      'entity_type' => 'node',
      'entity_id'   => $this->proposalId,
      'field_name'  => 'field_os2web_cit_props_votes',
      'uid' => 0,
      'comment_type' => 'os2web_citizen_proposals_vote',
      'subject' => 'Vote from ' . $email,
      'field_os2web_cit_props_v_name' => $name,
      'field_os2web_cit_props_v_address' => $address,
      'field_os2web_cit_props_v_zipcode' => $zipcode,
      'field_os2web_cit_props_v_city' => $city,
      'field_os2web_cit_props_v_email' => $email,
      'status' => 1,
    ];
    $comment = Comment::create($values);
    $comment->save();

    /** @var \Drupal\node\NodeInterface $proposal */
    $proposal = Node::load($this->proposalId);
    $this->messenger()->addStatus($this->t('Your vote for %title is now registered', ['%title' => $proposal->label()]));

    $config = \Drupal::config(SettingsForm::$configName);
    $notify = $config->get('proposal_vote_notify');
    $countVotes = 0;
    if ($notify && (int) $notify > 1) {
        $countVotes = $proposal->get('field_os2web_cit_props_votes')->comment_count;
        if (fmod($countVotes, $notify) == 0) {
            $emailService = \Drupal::service('os2web_citizen_proposals.email_service');
            $emailService->sendUserVoteReceivedEmail($proposal);
        }

    } else {
        $emailService = \Drupal::service('os2web_citizen_proposals.email_service');
        $emailService->sendUserVoteReceivedEmail($proposal);
    }

    // Checking if votes have maxed.
    $config = \Drupal::config(SettingsForm::$configName);
    $maxVotes = $config->get('proposal_max_votes');
    if ($proposal->get('field_os2web_cit_props_votes')->comment_count == $maxVotes) {
	    $emailService = \Drupal::service('os2web_citizen_proposals.email_service');
	    $emailService->sendAdminVotesMaxedEmail($proposal);
	    $emailService->sendUserVotesMaxedEmail($proposal);
    }
  }
}
