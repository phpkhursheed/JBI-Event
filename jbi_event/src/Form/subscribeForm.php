<?php
/**
 * @file
 * Contains \Drupal\resume\Form\subscribeForm.
 */
namespace Drupal\jbi_event\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

class subscribeForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public $nid;

  public function getFormId() {
    return 'subscribe_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $nid = NULL) {
    $this->nid = $nid;
    $nid = base64_decode(urldecode($nid));
    $node = Node::load($nid);
    $form['test1'] = array(
      '#markup' => '<h1 class="page-title">Subscribe Event : '.$node->title->value.'</h1>',
    );
    $form['subscriber_name'] = array(
      '#type' => 'textfield',
      '#title' => t('Name:'),
      '#required' => TRUE,
    );
    $form['subscriber_mail'] = array(
      '#type' => 'email',
      '#title' => t('Email ID:'),
      '#required' => TRUE,
    );
    $form['node_id'] = array(
      '#type' => 'hidden',
      '#default_value' => $nid,
    );    	
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    );
    return $form;
  }

   public function validateForm(array &$form, FormStateInterface $form_state) {
    $subscriber_name = $form_state->getValue("subscriber_name");
    $subscriber_mail = $form_state->getValue("subscriber_mail");
    $usernamelength  = strlen($subscriber_name);

    if (isset($subscriber_name)) {
        if ($usernamelength < 6){
          $form_state->setErrorByName('subscriber_name', t('Invalid username. Username must be at least 6 characters.'));
        }
        if ($usernamelength > 15){
          $form_state->setErrorByName('subscriber_name', t('Invalid username. Username cannot be greater than 15 characters.'));
        }
        if (ctype_alpha(str_replace(' ', '', $subscriber_name)) === false) {
          $form_state->setErrorByName('subscriber_name', t('Name must contain letters and spaces only'));
        }
    }

    if (isset($subscriber_mail)) {
      if (!valid_email_address($subscriber_mail)) {
        $form_state->setErrorByName('subscriber_mail', t('That is not a valid e-mail address.'));
      }      
    }   
  }


  public function submitForm(array &$form, FormStateInterface $form_state) {
    $userData = $form_state->getValues();
    $user_Name = $userData['subscriber_name'];
    $user_Email = $userData['subscriber_mail'];
    $node_id = $userData['node_id'];    
    $date = date('d-m-Y h:i:s');
    $dbInsert = db_insert('jbi_event_subscriber')
              ->fields(array(
                'name' => $user_Name,
                'email' => $user_Email, 
                'node_id' => $node_id,
                'submission_date' => $date,      
              ))->execute();
    if($dbInsert){
      drupal_set_message("Thanks, You have succesfully subscribe for this event."); 
      return;
    }
      
  }
}

