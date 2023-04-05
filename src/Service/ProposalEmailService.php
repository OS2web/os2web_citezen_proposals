<?php

namespace Drupal\os2web_citizen_proposals\Service;

use Drupal\node\NodeInterface;
use Drupal\os2web_citizen_proposals\Form\SettingsForm;
use Drupal\taxonomy\Entity\Term;

class ProposalEmailService {

  /**
   * Sends email to the user about proposal being created.
   *
   * @param \Drupal\node\NodeInterface $proposal
   *   Proposal node.
   *
   * @return array
   *   The $message array structure containing all details of the message. If
   *   already sent ($send = TRUE), then the 'result' element will contain the
   *   success indicator of the email, failure being already written to the
   *   watchdog. (Success means nothing more than the message being accepted at
   *   php-level, which still doesn't guarantee it to be delivered.)
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function sendUserProposalCreatedEmail(NodeInterface $proposal) {
    $template_key = 'create_proposal_user';
    return $this->prepareAndSendEmail($proposal, $template_key);
  }

  /**
   * Sends email to the user about proposal being accepted.
   *
   * @param \Drupal\node\NodeInterface $proposal
   *   Proposal node.
   *
   * @return array
   *   The $message array structure containing all details of the message. If
   *   already sent ($send = TRUE), then the 'result' element will contain the
   *   success indicator of the email, failure being already written to the
   *   watchdog. (Success means nothing more than the message being accepted at
   *   php-level, which still doesn't guarantee it to be delivered.)
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function sendUserProposalAcceptedEmail(NodeInterface $proposal) {
    $template_key = 'proposal_accepted_user';
    return $this->prepareAndSendEmail($proposal, $template_key);
  }


  /**
   * Sends email to the user about proposal being rejected.
   *
   * @param \Drupal\node\NodeInterface $proposal
   *   Proposal node.
   *
   * @return array
   *   The $message array structure containing all details of the message. If
   *   already sent ($send = TRUE), then the 'result' element will contain the
   *   success indicator of the email, failure being already written to the
   *   watchdog. (Success means nothing more than the message being accepted at
   *   php-level, which still doesn't guarantee it to be delivered.)
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function sendUserProposalRejectedEmail(NodeInterface $proposal) {
    $template_key = 'proposal_rejected_user';
    return $this->prepareAndSendEmail($proposal, $template_key);
  }

  /**
   * Sends email to admin about proposal being created.
   *
   * @param \Drupal\node\NodeInterface $proposal
   *   Proposal node.
   *
   * @return array
   *   The $message array structure containing all details of the message. If
   *   already sent ($send = TRUE), then the 'result' element will contain the
   *   success indicator of the email, failure being already written to the
   *   watchdog. (Success means nothing more than the message being accepted at
   *   php-level, which still doesn't guarantee it to be delivered.)
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function sendUserVoteReceivedEmail(NodeInterface $proposal) {
    $template_key = 'vote_received_user';
    return $this->prepareAndSendEmail($proposal, $template_key);
  }


  public function sendUserVotesMaxedEmail(NodeInterface $proposal) {
    $template_key = 'proposal_vote_maxed_user';
    return $this->prepareAndSendEmail($proposal, $template_key);
  }

  public function sendUserProposalNotMaxedEmail(NodeInterface $proposal) {
    $template_key = 'proposal_not_vote_maxed_user';
    return $this->prepareAndSendEmail($proposal, $template_key);
  }


  /**
   * Sends email to admin about proposal being created.
   *
   * @param \Drupal\node\NodeInterface $proposal
   *   Proposal node.
   *
   * @return array
   *   The $message array structure containing all details of the message. If
   *   already sent ($send = TRUE), then the 'result' element will contain the
   *   success indicator of the email, failure being already written to the
   *   watchdog. (Success means nothing more than the message being accepted at
   *   php-level, which still doesn't guarantee it to be delivered.)
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function sendAdminProposalCreatedEmail(NodeInterface $proposal) {
    $template_key = 'create_proposal_admin';
    return $this->prepareAndSendEmail($proposal, $template_key);
  }

  /**
   * Sends email to admin votes maxed.
   *
   * @param \Drupal\node\NodeInterface $proposal
   *   Proposal node.
   *
   * @return array
   *   The $message array structure containing all details of the message. If
   *   already sent ($send = TRUE), then the 'result' element will contain the
   *   success indicator of the email, failure being already written to the
   *   watchdog. (Success means nothing more than the message being accepted at
   *   php-level, which still doesn't guarantee it to be delivered.)
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function sendAdminVotesMaxedEmail(NodeInterface $proposal) {
    $template_key = 'vote_maxed_admin';
    return $this->prepareAndSendEmail($proposal, $template_key);
  }

  /**
   * Prepares email for sending and send it using mail service.
   *
   * @param \Drupal\node\NodeInterface $proposal
   *   Proposal node.
   * @param $template_key
   *   Key of the template to user for subject and body.
   *
   * @return array
   *   The $message array structure containing all details of the message. If
   *   already sent ($send = TRUE), then the 'result' element will contain the
   *   success indicator of the email, failure being already written to the
   *   watchdog. (Success means nothing more than the message being accepted at
   *   php-level, which still doesn't guarantee it to be delivered.)
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  private function prepareAndSendEmail(NodeInterface $proposal, $template_key) {
    $config = \Drupal::config(SettingsForm::$configName);

    $subject = $config->get("email_subject_$template_key");
    $body = $config->get("email_body_$template_key");
    $copy_email = $config->get("copy_email");

    // Getting the email.
    if ($template_key == 'create_proposal_admin' || $template_key == 'vote_maxed_admin') {
      $email = $config->get('admin_email');
      if (!$email) {
        $email = \Drupal::config('system.site')->get('mail');
      }
    }
    else {
      $email = $proposal->get('field_os2web_cit_props_email')->value;
    }

    $message = [
      'to' => $email,
      'subject' => $subject,
      'body' => $body,
    ];

    $this->makeReplacements($message, $proposal);

    // Setting front-end theme.
    /** @var \Drupal\Core\Theme\ThemeInitialization $theme_initialization */
    $theme_initialization = \Drupal::service('theme.initialization');
    $active_theme = \Drupal::theme()->getActiveTheme();
    $config = \Drupal::config('system.theme');
    $defaultTheme =  $config->get('default');
    \Drupal::theme()->setActiveTheme($theme_initialization->getActiveThemeByName($defaultTheme));

    $mailSentStatus = \Drupal::service('plugin.manager.mail')
      ->mail('os2web_citizen_proposals', $template_key, $message['to'], \Drupal::languageManager()
        ->getDefaultLanguage()
        ->getId(), $message);

    if ($mailSentStatus && !empty($copy_email) && filter_var($copy_email, FILTER_VALIDATE_EMAIL)) {
      $message['to'] = $copy_email;
      $message['subject'] = ":: COPY :: " . $message['subject'];
      \Drupal::service('plugin.manager.mail')
        ->mail('os2web_citizen_proposals', $template_key, $message['to'], \Drupal::languageManager()
        ->getDefaultLanguage()
        ->getId(), $message);
      }

      // Changing theme back.
    \Drupal::theme()->setActiveTheme($active_theme);

    return $mailSentStatus;
  }

  /**
   * Replaces the certain text with the provided substitution text in both
   * message subject and body,
   *
   * @param $message
   * @param $token
   * @param $replacement
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  private function makeReplacements(&$message, NodeInterface $proposal) {
    $token = [
      '@proposal_link',
      '@proposal_title',
      '@proposal_body',
      '@proposal_vote_count',
      '@person_name',
      '@person_address',
      '@person_zipcode',
      '@person_city',
      '@person_email',
      '@proposal_reject_reason_ext',
      '@proposal_reject_reason'
    ];

    $rejectReason = NULL;
    if ($rejectReasonTid = $proposal->get('field_os2web_cit_props_rej_reas')->target_id) {
      $rejectReason = Term::load($rejectReasonTid);
    }

    $replacement = [
      $proposal->toUrl()->setAbsolute()->toString(),//'@proposal_link',
      $proposal->label(),//'@proposal_title',
      $proposal->get('field_os2web_cit_props_proposal')->value,//'@proposal_body',
      $proposal->get('field_os2web_cit_props_votes')->comment_count,//'@proposal_vote_count',
      $proposal->get('field_os2web_cit_props_name')->value,//'@person_name',
      $proposal->get('field_os2web_cit_props_address')->value,//'@person_address',
      $proposal->get('field_os2web_cit_props_zip_code')->value,//'@person_zipcode',
      $proposal->get('field_os2web_cit_props_city')->value,//'@person_city',
      $proposal->get('field_os2web_cit_props_email')->value,//'@person_email',
      $proposal->get('field_os2web_cit_props_rej_ext')->value,//'@proposal_reject_reason_ext'
      ($rejectReason) ? $rejectReason->label() : '',//'@proposal_reject_reason',
    ];

    $message['subject'] = str_replace($token, $replacement, $message['subject']);
    $message['body'] = str_replace($token, $replacement, $message['body']);
  }
}
