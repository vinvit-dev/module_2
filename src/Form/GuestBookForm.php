<?php

namespace Drupal\guest_book\Form;

use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\MessageCommand;
use Drupal\Core\Url;
use Drupal\file\Entity\File;

/**
 * Class for main form in guest book module.
 */
class GuestBookForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'guest_book_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $val = NULL) {
    // Containers. Make two columns layout form.
    $form['first-col'] = ['#type' => 'container'];
    $form['sec-col'] = ['#type' => 'container'];

    $form['first-col']['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your name:'),
      '#required' => TRUE,
      '#maxlength' => 100,
      '#attributes' => [
        'title' => $this->t("Length: from 2 to 100 symbols"),
      ],
      '#default_value' => (isset($val['name'])) ? $val['name'] : "",
    ];

    $form['first-col']['email'] = [
      '#type' => 'email',
      '#title' => $this->t("Your email:"),
      '#required' => TRUE,
      '#attributes' => [
        'title' => $this->t('Example: example@info.org'),
      ],
      '#default_value' => (isset($val['email'])) ? $val['email'] : "",
    ];
    $form['first-col']['phone'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Your phone number:"),
      '#required' => TRUE,
      '#attributes' => [
        'title' => $this->t('Phone number'),
      ],
      '#default_value' => (isset($val['phone'])) ? $val['phone'] : "",
    ];
    $form['sec-col']['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t("Message:"),
      '#required' => TRUE,
      '#default_value' => (isset($val['message'])) ? $val['message'] : "",
    ];
    $form['first-col']['avatar'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Your photo:'),
        // Validate avatar.
      '#upload_validators' => [
        'file_validate_extensions' => ['jpeg jpg png'],
        'file_validate_size' => [2100000],
      ],
      '#upload_location' => 'public://guest_book/avatars',

    ];
    $form['sec-col']['image'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Image:'),
        // Validate image.
      '#upload_validators' => [
        'file_validate_extensions' => ['jpeg jpg png'],
        'file_validate_size' => [5240000],
      ],
      '#upload_location' => 'public://guest_book/images',
      '#weight' => -1,
    ];
    $form['sec-col']['error-message'] = [
      '#markup' => '<div id="error-message-edit" class="error-meessage"></div>',
    ];
    $form['sec-col']['submit'] = [
      '#type' => 'submit',
      '#value' => (isset($val)) ? $this->t('Edit') : $this->t('Send'),
        // Set ajax callback function.
      '#ajax' => [
        'callback' => '::submitAjax',
      ],

    ];
    // Hidden feild which ocntaine feedback id to edit.
    $form['id'] = [
      '#type' => 'hidden',
      '#default_value' => (isset($val['id'])) ? $val['id'] : NULL,
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
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function submitAjax(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    // Validate name field.
    if (strlen($form_state->getValue('name')) < 2 || strlen($form_state->getValue('name') > 100
              || strlen($form_state->getValue('name')) == 0)) {
      $response->addCommand(new MessageCommand(
            $this->t('Invalid name!'),
          '#error-message-edit',
            ['type' => 'error']
        ));
    }
    // Validate email field.
    elseif (!filter_var($form_state->getValue('email'), FILTER_VALIDATE_EMAIL)
          || strlen($form_state->getValue('email')) == 0) {
      $response->addCommand(new MessageCommand(
            $this->t('Invalid email!'),
          '#error-message-edit',
            ['type' => 'error'],
        ));
    }
    elseif (preg_match('/[^-_@.\dZa-z]/', $form_state->getValue('email'))) {
      $response->addCommand(new MessageCommand(
            $this->t('In email allow only dash, underline and latin symbols!'),
          '#error-message-edit',
            ['type' => 'error'],
        ));
    }
    // Validate phone field.
    elseif (!preg_match('/^\d{10,12}/', $form_state->getValue('phone'))
          || strlen($form_state->getValue('email')) == 0) {
      $response->addCommand(new MessageCommand(
            $this->t('Invalid phone'),
          '#error-message-edit',
            ['type' => 'error'],
        ));
    }
    // Validate message filed.
    elseif (strlen($form_state->getValue('message')) === 0) {
      $response->addCommand(new MessageCommand(
            $this->t('Please write message!'),
          '#error-message-edit',
            ['type' => 'error']
        ));
    }
    elseif (strlen($form_state->getValue('message')) > 1023) {
      $response->addCommand(new MessageCommand(
            $this->t('Massage is too long!'),
            '#error-message-edit',
            ['type' => 'error']
        ));
    }

    // If fieild with id not null -> update data into database.
    elseif ($form_state->getValue('id') != NULL) {
      $fields['name'] = $form_state->getValue('name');
      $fields['email'] = $form_state->getValue('email');
      $fields['phone'] = $form_state->getValue('phone');
      $fields['message'] = $form_state->getValue('message');

      $avatar = $form_state->getValue('avatar');
      $image = $form_state->getValue('image');

      // If field avatar not null load new avatar.
      if ($avatar != NULL) {
        $file = File::load($avatar[0]);
        $file->setPermanent();
        $file->save();
        $uri = $file->getFileUri();
        $url = file_create_url($uri);
        $fields['avatar'] = $url;
      }

      // If field image not null load new image.
      if ($image != NULL) {
        $file = File::load($image[0]);
        $file->setPermanent();
        $file->save();
        $uri = $file->getFileUri();
        $url = file_create_url($uri);
        $fields['image'] = $url;
      }

      // Update database.
      $connection = \Drupal::database()->update('guest_book');
      $connection->condition('id', $form_state->getValue('id'));
      $connection->fields($fields);
      $connection->execute();

      // Output message about work.
      $response->addCommand(new MessageCommand(
            $this->t('Update done!'),
            NULL,
            ['type' => 'status']
        ));
      $response->addCommand(new MessageCommand(
            '',
            '#error-message-edit',
            ['type' => 'status']
        ));

      // Redirect on /guest_book page after update data.
      $url = Url::fromRoute("guest_book.main");
      $response->addCommand(new RedirectCommand($url->toString()));
    }

    // Else add new content to database.
    else {
      $fields['name'] = $form_state->getValue('name');
      $fields['email'] = $form_state->getValue('email');
      $fields['phone'] = $form_state->getValue('phone');
      $fields['message'] = $form_state->getValue('message');

      $now = \Drupal::time()->getCurrentTime();
      $fields['date'] = \Drupal::service('date.formatter')->format($now, 'custom', 'Y/m/d H:i:s');

      $avatar = $form_state->getValue('avatar');
      $image = $form_state->getValue('image');

      // If avatar field is null set default avatar image else save avaatar url.
      if ($avatar == NULL) {
        $fields['avatar'] = '/modules/custom/guest_book/images/default-avatar.png';
      }
      else {
        $file = File::load($avatar[0]);
        $file->setPermanent();
        $file->save();
        $uri = $file->getFileUri();
        $url = file_create_url($uri);
        $fields['avatar'] = $url;
      }

      // If image field is NULL set NULL else save image url.
      if ($image == NULL) {
        $fields['image'] = NULL;
      }
      else {
        $file = File::load($image[0]);
        $file->setPermanent();
        $file->save();
        $uri = $file->getFileUri();
        $url = file_create_url($uri);
        $fields['image'] = $url;
      }

      // Add new content to database.
      $connection = \Drupal::database();
      $connection->insert('guest_book')->fields($fields)->execute();

      $response->addCommand(new MessageCommand(
            $this->t('All great!'),
          NULL,
            ['type' => 'status']
        ));
      $response->addCommand(new MessageCommand(
            '',
            '#error-message-edit',
            ['type' => 'status']
        ));
    }

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
