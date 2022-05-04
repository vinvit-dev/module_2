<?php

namespace Drupal\guest_book\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\MessageCommand;

class GuestBookAddForm extends FormBase
{
    public function getFormId()
    {
        return 'guest_book_add_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form['first-col'] = ['#type' => 'container'];
        $form['sec-col'] = ['#type' => 'container'];

        $form['first-col']['name'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Your name:'),
            '#required' => true,
            '#maxlength' => 100,
            '#attributes' => [
                'title' => $this->t("Length: from 2 to 100 symbols"),
            ],
        ];

        $form['first-col']['email'] = [
            '#type' => 'email',
            '#title' => $this->t("Your email:"),
            '#required' => true,
            '#attributes' => [
                'title' => $this->t('Example: example@info.org')
            ],
        ];
        $form['first-col']['phone_number'] = [
            '#type' => 'tel',
            '#title' => $this->t("Your phone number:"),
            '#required' => true,
            '#attributes' => [
                'title' => $this->t('gg')
            ],
        ];
        $form['sec-col']['message'] = [
            '#type' => 'textarea',
            '#title' => $this->t("Message:"),
            '#required' => true,
        ];
        $form['first-col']['avatar'] = [
            '#type' => 'managed_file',
            '#title' => $this->t('Your photo:'),
            '#upload_validators' => [
                'file_validate_extensions' => ['jpeg jpg png'],
                'file_validate_size' => [2100000]
            ],
            '#upload_location' => 'public://guest_book/avatars'
        ];
        $form['sec-col']['image'] = [
            '#type' => 'file',
            '#title' => $this->t('Image:'),
            '#upload_validators' => [
                'file_validate_extensions' => ['jpeg jpg png'],
                'file_validate_size' => [5240000]
            ],
            '#upload_location' => 'public://guest_book/images',
            '#weight' => -1
        ];
        $form['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Send'),
            '#ajax' => [
                'callback' => '::submitAjax'
            ]
        ];
        return $form;
    }

    public function submitAjax(array &$form, FormStateInterface $form_state)
    {
        $response = new AjaxResponse();
        if (strlen($form_state->getValue('name')) < 2 || strlen($form_state->getValue('name') > 100
                || strlen($form_state->getValue('name')) == 0)) {
            $response->addCommand(new MessageCommand(
                $this->t('Invalid name!'),
                null,
                ['type' => 'error']
            ));
        } elseif (!filter_var($form_state->getValue('email'), FILTER_VALIDATE_EMAIL)
            || strlen($form_state->getValue('email')) == 0) {
            $response->addCommand(new MessageCommand(
                $this->t('Invalid email!'),
                null,
                ['type' => 'error'],
            ));
        } elseif (preg_match('/[^-_@.0-9A-Za-z]/', $form_state->getValue('email'))) {
            $response->addCommand(new MessageCommand(
                $this->t('In email allow only dash, underline and latin symbols!'),
                null,
                ['type' => 'error'],
            ));
        } elseif (!preg_match('/^[0-9]{10,}/', $form_state->getValue('phone_number'))
            || strlen($form_state->getValue('email')) == 0) {
            $response->addCommand(new MessageCommand(
                $this->t('Invalid phone'),
                null,
                ['type' => 'error'],
            ));
        } elseif (strlen($form_state->getValue('message')) === 0) {
            $response->addCommand(new MessageCommand(
                $this->t('Please write message!'),
                null,
                ['type' => 'error']
            ));
        } else {
            $response->addCommand(new MessageCommand(
                $this->t('All good!'),
                null,
                ['type' => 'status']
            ));
        }

        return $response;
    }

    public function validateForm(array &$form, FormStateInterface $form_state)
    {
    }


    public function submitForm(array &$form, FormStateInterface $form_state)
    {
    }
}
