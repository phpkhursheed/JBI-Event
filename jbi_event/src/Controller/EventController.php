<?php
    /**
     * @file
     * Contains \Drupal\jbi_event\Controller\EventController.
     */
 
namespace Drupal\jbi_event\Controller;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;
use Drupal\taxonomy\Entity\Term;
use Drupal\Component\Render\FormattableMarkup;   
use Drupal\Core\Controller\ControllerBase;
 
class EventController extends ControllerBase {

  /*==========Function content() to display all the Events and its location to display at frontend==========*/
  public function content() {
    $siteUrl = base_path();
    $query = \Drupal::entityQuery('node')
              ->condition('status', 1) //published or not
              ->condition('type', 'jbi_event') //content type
              ->groupBy('field_event_categories')
              ->sort('created' , 'DESC') //Display Desc Order
              ->pager(10); //specify results to return
    $nids = $query->execute();
    $rows=array();
    $i = 1;
    foreach ($nids as $nid) {
      $node = \Drupal\node\Entity\Node::load($nid);
      $tid =  $node->field_event_categories->target_id;
      $term = Term::load($tid);
      $termName = $term->getName();
      // echo "<pre>";
      // print_r($node); die();

      $rows[] = array(
        'S. NO.'  => $i,
        'eventName' => $node->title->value,
        'eventLocation' => $node->field_event_location->value,
        'category'      => $termName,
          array('data' => new FormattableMarkup('<a href=":link">@name</a>', 
            [':link' => $siteUrl.'jbi_event/subscribe/'.urlencode(base64_encode($nid)), 
            '@name' => 'Subscribe'])
          ),
      );
      $i++;
    }  
    $header = ['S.No.','Event Name', 'Location', 'Category', 'Action'];
    return array(
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => t('No Events found'),
    );   
  }

  /*==========Function userSubscriber() to get all the subscribers and display at admin ==========*/
  public function userSubscriber() { 
    //select records from table
    $query = \Drupal::database()->select('jbi_event_subscriber', 'm');
    $query->fields('m', ['id','name','email','node_id','submission_date']);
    $results = $query->execute()->fetchAll();
    $rows=array();
    $j =1;
    foreach($results as $data)
    {
      $node = \Drupal\node\Entity\Node::load($data->node_id);
      //print the data from table
      $rows[] = array(
      'id' =>$j,
      'name' => $data->name,
      'email' => $data->email,
      'Event Name' => $node->title->value,
      'Date' => $data->submission_date,
      );
      $j++;
    }
    $header = ['S.No.','Subscriber Name', 'Subscriber Email', 'Event Name', 'Submission Date'];

    $form['table'] = [
      '#type' => 'table',    
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => t('No Subscriber found'),
    ];
    return $form;
  }
}