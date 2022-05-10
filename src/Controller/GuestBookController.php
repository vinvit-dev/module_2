<?php

namespace Drupal\guest_book\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;

use Drupal\Core\Controller\ControllerBase;

/**
 * Guest book controller class.
 */
class GuestBookController extends ControllerBase {

  /**
   * Return page.
   *
   * @return array
   *   Return randerable array
   */
  public function content() {
    // Get form to add content.
    $add_form = \Drupal::formBuilder()->getForm('Drupal\guest_book\Form\GuestBookForm');

    // Get content from database.
    $conn = \Drupal::database()->select('guest_book', 'g');
    $conn->fields(
          'g',
          ['id', 'name', 'email', 'phone', 'message', 'avatar', 'image', 'date']
      );
    // Sort by date.
    $conn->orderBy('date', 'DESC');
    $results = $conn->execute()->fetchAll();

    // Return array to render.
    return [
      '#theme' => 'guest-book',
      '#markup' => "On this page you can leave you comment",
      '#items' => $results,
      '#add_form' => $add_form,
    ];
  }

  /**
   * Return modal dialog with delete form.
   *
   * @param int $id
   *   Feedback id to delete.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   return ajax response
   */
  public function delete(int $id) {
    // Get delete form.
    $delete_form = \Drupal::formBuilder()->getForm("Drupal\guest_book\Form\GuestBookDeleteForm", $id);
    $response = new AjaxResponse();
    // Add modal dialog with delete form.
    $response->addCommand(new OpenModalDialogCommand(
          "Delete",
          $delete_form,
          ['width' => 450, 'height' => 80]
      ));
    return $response;
  }

  /**
   * Return modal dialog with edit form.
   *
   * @param int $id
   *   Feedback id to edit.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   return ajax response
   */
  public function edit($id) {
    // Get content from database by feedback id.
    $conn = \Drupal::database()->select('guest_book', 'g');
    $conn->condition('id', $id)->fields(
          'g',
          ['id', 'name', 'email', 'phone', 'message']
      );
    $result = $conn->execute()->fetchAssoc();

    // Get edit form.
    $edit_form = \Drupal::formBuilder()->getForm('Drupal\guest_book\Form\GuestBookForm', $result);
    $response = new AjaxResponse();
    // Add modal dialog with edit form.
    $response->addCommand(new OpenModalDialogCommand("Edit", $edit_form, ['width' => 900]));
    return $response;
  }

}
