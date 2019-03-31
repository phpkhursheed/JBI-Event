<?php
/**
 * @file
 * Contains \Drupal\resume\Form\subscribeForm.
 */
namespace Drupal\jbi_event\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;
use \Drupal\Core\Link;

class SubscribeForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public $nid;

  public function getFormId() {
    return 'subscribe_form';
  }

  /**
   * {@inheritdoc}
   */
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
      '#placeholder' => t('Name contain letters and spaces only. Min 5 & Max 15 characters.'),
      '#required' => TRUE,
    );
    $form['subscriber_mail'] = array(
      '#type' => 'email',
      '#title' => t('Email ID:'),
      '#placeholder' => t('Enter your Email.'),
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

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $subscriber_name = $form_state->getValue("subscriber_name");
    $subscriber_mail = $form_state->getValue("subscriber_mail");
    $usernamelength  = strlen($subscriber_name);

    if (isset($subscriber_name)) {
        if ($usernamelength < 6) {
          $form_state->setErrorByName('subscriber_name', t('Invalid username. Username must be at least 5 characters.'));
        }
        if ($usernamelength > 15) {
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


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $link = Link::createFromRoute('Event List.', 'jbi_event.content')->toString()->getGeneratedLink();
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
      drupal_set_message(t("Thanks, You have succesfully subscribe for this event. Please go back to $link"));
      return;
    }      
  }
}

