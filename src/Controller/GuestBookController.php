<?php

namespace Drupal\guest_book\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;

use Drupal\Core\Controller\ControllerBase;

/**
 * {@inheritDoc}
 */
class GuestBookController extends ControllerBase
{

    /**
     *
     */
    public function content()
    {
        $add_form = \Drupal::formBuilder()->getForm('Drupal\guest_book\Form\GuestBookForm');

        $conn = \Drupal::database()->select('guest_book', 'g');
        $conn->fields(
            'g',
            ['id', 'name', 'email', 'phone', 'message', 'avatar', 'image', 'date']
        );
        $conn->orderBy('date', 'DESC');
        $results = $conn->execute()->fetchAll();

        return [
            '#theme' => 'guest-book',
            '#markup' => "On this page you can leave you comment",
            '#items' => $results,
            '#add_form' => $add_form,
        ];
    }


    public function delete($id)
    {
        $delete_form = \Drupal::formBuilder()->getForm("Drupal\guest_book\Form\GuestBookDeleteForm", $id);
        $response = new AjaxResponse();
        $response->addCommand(new OpenModalDialogCommand("Delete", $delete_form, ['width' => 450, 'height' => 140]));
        return $response;
    }

    public function edit($id)
    {
        $conn = \Drupal::database()->select('guest_book', 'g');
        $conn->condition('id', $id)->fields(
            'g',
            ['id', 'name', 'email', 'phone', 'message']
        );
        $result = $conn->execute()->fetchAssoc();

        $edit_form = \Drupal::formBuilder()->getForm('Drupal\guest_book\Form\GuestBookForm', $result);
        $response = new AjaxResponse();
        $response->addCommand(new OpenModalDialogCommand("Edit", $edit_form, ['width' => 600]));
        return $response;
    }

}
