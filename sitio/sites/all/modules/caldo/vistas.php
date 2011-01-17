<?php

/**
 * Este es el formulario que permite usar los nuevos campos necesarios para el
 * sistema.
 */ 
function caldo_opciones($form_state){
  $form['personal_data'] = array(
    '#type' => 'fieldset',
    '#title' => t('Personal data'),
    '#collapsible' => FALSE,
  );
  $form['personal_data']['full_name'] = array(
    '#type' => 'textfield',
    '#title' => t('Full name'),
    '#required' => TRUE,
  );
  $form['personal_data']['address'] = array(
    '#type' => 'textfield',
    '#title' => t('Address'),
    '#required' => FALSE,
  );
  $form['personal_data']['phone'] = array(
    '#type' => 'textfield',
    '#title' => t('Phone number'),
    '#required' => FALSE,
  );
  
  $form['options'] = array(
    '#type' => 'fieldset',
    '#title' => t('Options'),
    '#collapsible' => FALSE,
  );
  $form['options']['accept_all_invitations'] = array(
    '#type' => 'checkbox',
    '#title' => t('Accept all invitations to workgroups.'),
    '#description' => t('By enabling this option you will automatically accept all invitations to workgroups.'),
  );
  $form['options']['privacy_level'] = array(
    '#type' => 'radios',
    '#title' => t('Privacy level'),
    '#default_value' => 'semipublic',
    '#options' => array(
        'private' => t('Private: only you can see your schedule.'),
        'semipublic' => t('Public: you will be able to see your full schedule, but other people will only see a mark instead of the name of the activity you have in each block.'),
        'public' => t('Public: you and all people using this system will be able to see your full schedule. (Not recomended)'),
        ),
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save'),
  );
  return $form;
}

/**
 * Esta es la página que muestra el horario individual de cada usuario.
 * 
 * Incluye la posibilidad de modificar el horario.
 * 
 * @todo Implementar  
 */   
function caldo_horario_personal(){
  return t('Not implemented');
}
