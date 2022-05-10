<?php

namespace Drupal\guest_book\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\MessageCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Url;

/**
 * Class for delete form in guest book module.
 */
class GuestBookDeleteForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'guest_book_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {
    $form['question'] = [
      '#markup' => '<p class="delete-question">' . $this->t('You really want ot delete it?') . '</p>',
    ];
    $form['actions']['delete'] = [
      '#type' => 'submit',
      '#value' => $this->t("Delete"),
      '#ajax' => [
        'callback' => '::submitAjax',
      ],
    ];
    // Hidden field which contain feedback id to delete.
    $form['id'] = [
      '#type' => 'hidden',
      '#value' => $id,
    ];
    return $form;
  }

  /**
   * Ajax callback function for submit.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   JSON response object for AJAX requests.
   */
  public function submitAjax(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    // Delete content from database.
    $id = $form_state->getValue('id');
    $conn = \Drupal::database()->delete('guest_book');
    $conn->condition('id', $id);
    $conn->execute();

    $response->addCommand(new MessageCommand($this->t('Deleted')));
    // Redirect on /guest_book page.
    $url = Url::fromRoute("guest_book.main");
    $response->addCommand(new RedirectCommand($url->toString()));

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
