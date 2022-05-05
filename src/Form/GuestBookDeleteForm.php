<?php

namespace Drupal\guest_book\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\MessageCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Url;

class GuestBookDeleteForm extends  FormBase
{
    public function getFormId()
    {
        return 'guest_book_delete_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state, $id = null)
    {
        $form['question'] = [
            '#markup' => '<p class="delete-question">' . $this->t('You really want ot delete it?') . '</p>',
        ];
        $form['actions']['delete'] = [
            '#type' => 'submit',
            '#value' => $this->t("Delete"),
            '#ajax' => [
                'callback' => '::submitAjax'
            ]
        ];
        $form['id'] = [
            '#type' => 'hidden',
            '#value' => $id,
        ];
        return $form;
    }

    public function submitAjax(array &$form, FormStateInterface $form_state)
    {
        $response = new AjaxResponse();
        $id = $form_state->getValue('id');
        $conn = \Drupal::database()->delete('guest_book');
        $conn->condition('id', $id);
        $conn->execute();

        $response->addCommand(new MessageCommand($this->t('Deleted')));

        $url = Url::fromRoute("guest_book.main");
        $response->addCommand(new RedirectCommand($url->toString()));

        return $response;
    }

    public function validateForm(array &$form, FormStateInterface $form_state)
    {
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
    }

}
