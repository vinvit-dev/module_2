<?php

namespace Drupal\guest_book\Controller;

use Drupal\Core\Controller\ControllerBase;

class GuestBookController extends ControllerBase
{
    public function content()
    {
        $add_form = \Drupal::formBuilder()->getForm('Drupal\guest_book\Form\GuestBookAddForm');
        return [
            '#theme' => 'guest-book',
            '#markup' => "On this page you can leave you comment",
            '#add_form' => $add_form,
        ];
    }
}
