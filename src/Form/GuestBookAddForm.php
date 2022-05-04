<?php

namespace Drupal\guest_book\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\MessageCommand;
use Drupal\file\Entity\File;

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
            $fields['name'] = $form_state->getValue('name');
            $fields['email'] = $form_state->getValue('name');
            $fields['phone'] = $form_state->getValue('phone_number');
            $fields['message'] = $form_state->getValue('message');

            $now = \Drupal::time()->getCurrentTime();
            $fields['date'] = \Drupal::service('date.formatter')->format($now, 'custom', 'Y/m/d H:i:s');

            $avatar = $form_state->getValue('avatar');
            $image = $form_state->getValue('image');

            if ($avatar == null) {
                $fields['avatar'] = '/modules/custom/guest_book/images/default-avatar.png';
            } else {
                $file = File::load($avatar[0]);
                $file->setPermanent();
                $file->save();
                $uri = $file->getFileUri();
                $url = file_create_url($uri);
                $fields['avatar'] = $url;
            }

            if ($image == null) {
                $fields['image'] = null;
            } else {
                $file = File::load($image[0]);
                $file->setPermanent();
                $file->save();
                $uri = $file->getFileUri();
                $url = file_create_url($uri);
                $fields['image'] = $url;
            }


            $connection = \Drupal::database();
            $connection->insert('guest_book')->fields($fields)->execute();

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
