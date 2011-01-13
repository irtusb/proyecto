<?php
// $Id: horarios.php 49 2010-07-20 22:39:22Z irtusb $
/**
 * @file horarios.php
 *    Implementa la clase Horario y la interfaz Bloque.
 */

/**
 * Interface Bloque. Implementada por la clase Horario.
 */
interface Bloque{
  /**
   * Guarda un bloque de tipo = $tipo en la base de datos.
   */
  function crearBloque($tipo);
  /**
   * Verifica si ya hay otro bloque asignado en \a $dia dia y \a $numero hora.
   */
  function chequearBloque($dia, $numero);
  /* *
   * Borra un bloque del sistema
   */
  //function borrarBloque($idBloque);
  /**
   * Devuelve una lista con los bloques correspondientes a un usuario.
   */
  function listarBloques();
}

/**
 * Clase Horario.
 */
class Horario implements Bloque {
  /**
   * Usado para evitar el uso de variables globales.
   */
  private $idUsuario;
  
  /**
   * Guarda los datos del bloque en la base de datos.
   *
   * @param $tipo
   *    cadena de texto indicando el tipo del bloque puede ser 'actividad' o
   *    'asignatura'.
   */
  public function crearBloque($tipo){
    $values = func_get_arg(1);

    if($tipo == 'actividad'){
      $values['idUsuario'] = $this->idUsuario;

      $consulta = sprintf('INSERT INTO {sghp_bloque}' .
        '(idUsuario, nombre, dia, numero, tipo) VALUES (%d, "%s", %d, %d, "%s")',
        $values['idUsuario'], $values['nombre'], $values['dia'], $values['numero'], 'actividad'
        );
      return db_query($consulta);
    }
    elseif($tipo == 'asignatura'){
      $consulta = sprintf('INSERT INTO {sghp_bloque}' .
        '(idUsuario, nombre, dia, numero, tipo) VALUES (%d, "%s", %d, %d, "%s")',
        $values['idUsuario'], $values['nombre'], $values['dia'], $values['numero'], 'asignatura'
        );
      return db_query($consulta);
    }
    else {
      drupal_set_message('el tipo de bloque es inválido', 'error');
    }
  }
  /**
   * Verifica la existencia de un bloque asignado al usuario
   *
   * @param $dia
   *    Día de la semana del bloque a verificar
   * @param $numero
   *    Número de la hora del bloque a verificar
   * @return
   *    El nombre del bloque si ya existe, FALSE en caso contrario.
   */
  public function chequearBloque($dia, $numero){
    $consulta = sprintf('SELECT nombre FROM {sghp_bloque} WHERE idUsuario = %d && dia = %d && numero = %d',
      $this->idUsuario, $dia, $numero);
    $bloque = db_fetch_object(db_query($consulta));
    if(isset($bloque->nombre)){
      return $bloque->nombre;
    }
    return FALSE;
  }
  
  
  public function listarBloques(){}



  /**
   * Constructor de la clase
   *
   * @param $idUsuario
   *    Identificador del usuario dentro del sitio web
   */
  public function Horario($idUsuario){
    $this->idUsuario = $idUsuario;
  }
  /**
   * Despliega una tabla con la planificaci&oacute;n semanal del usuario.
   * @return
   *    tabla html con el horario semanal correspondiente al usuario.
   */
  public function verHorario(){
    $lineaEnBlanco = array('&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;');
    $lineaDescanso = array('data' => '<hr>', 'colspan' => 4);
    $header = array(
      'Hora',
      t('Monday'),
      t('Tuesday'),
      t('Wednesday'),
      t('Thursday'),
      t('Friday'),
    );

    // Hora va de 1 a 8
    // Día va de 1 a 5
    for($hora = 1; $hora < 10; $hora++){
      if($hora == 4)
        $datos[] = array('&nbsp;', $lineaDescanso, '&nbsp;');
      else {
        $miHora = $hora < 4 ? $hora : $hora - 1;
        $linea = $lineaEnBlanco;
        $linea[0] = $miHora;
        $consulta = db_query('SELECT nombre, dia, tipo FROM {sghp_bloque} WHERE idUsuario = %d && numero = %d', $this->idUsuario, $miHora);
        while($bloque = db_fetch_object($consulta)){
          if(is_array($linea[$bloque->dia])){
            drupal_set_message('Choque de horario detectado día '.$bloque->dia.', número '.$hora.': '.$linea[$bloque->dia]['data'], 'warning');
          }
          $linea[$bloque->dia] = array('data' => $bloque->nombre);
          if ($bloque->tipo == 'actividad'){
            $linea[$bloque->dia]['title'] = 'Actividad';
            $linea[$bloque->dia]['style'] = 'background-color:yellow';
          }
          elseif ($bloque->tipo == 'asignatura') {
            $linea[$bloque->dia]['title'] = 'Asignatura';
            $linea[$bloque->dia]['style'] = 'background-color:orange';
          }
        }
        $datos[] = $linea;
      }
    }
    return theme_table($header, $datos);
  }
}

function sghpHorarioVer(){
  global $user;
  $horario = new Horario($user->uid);
  return $horario->verHorario();
}

/**
 * implementa un formulario de drupal para crear una nueva actividad.
 *
 * @param $form_state
 *    arreglo compuesto con valores del formulario. Drupal lo usa para tener
 *    cierta persistencia en los datos cuando &eacute;stos no pasan la
 *    validaci&oacute;n.
 *
 * @return
 *    Arreglo compuesto con la estructura del formulario y, de ser el caso,
 *    valores por defecto.
 */
function sghpNuevaActividad($form_state){
//  global $user;
  $form['dia'] = array(
    '#title' => 'Día',
    '#type' => 'select',
    '#options' => array('1'=>t('Monday'), t('Tuesday'), t('Wednesday'), t('Thursday'), t('Friday')),
    '#default_value' => 0,
    '#weight' => 10,
  );
  $form['numero'] = array(
    '#title' => 'Número',
    '#type' => 'select',
    '#options' => array(
      'Mañana' => array(
        '1' => ' 8:00 -  9:30',
        '2' => ' 9:40 - 11:10',
        '3' => '11:20 - 12:50',
      ),
      'Tarde' => array(
        '4' => '14:45 - 16:15',
        '5' => '16:20 - 17:50',
        '6' => '17:55 - 19:25',
        '7' => '19:30 - 21:00',
      ),
    ),
    '#weight' => 20,
  );
  $form['nombre'] = array(
    '#title' => 'Nombre de la actividad',
    '#type' => 'textfield',
    '#maxlength' => 128,
    '#required' => TRUE,
    '#weight' => 30,
  );
  /*
  $form['idUsuario'] = array(
    '#title' => 'ID del usuario',
    '#type' => 'textfield',
    '#default_value' => $user->uid,
    '#required' => TRUE,
    '#weight' => 40
  );
  */
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save'),
    '#weight' => 200,
  );
  return $form;
}
/**
 * implementa hook_validate para el formulario de nueva actividad.
 *
 * Verifica si el bloque horario ya está ocupado.
 *
 * @param $form
 *    Arreglo compuesto con la estructura del formulario.
 * @param $form_state
 *    Arreglo compuesto con los datos del formulario.
 */
function sghpNuevaActividad_validate($form, &$form_state){
  global $user;
  $values = $form_state['values'];
  $horario = new Horario($user->uid);
  if($act = $horario->chequearBloque($values['dia'], $values['numero'])){
    form_error($form, 'Ya existe una actividad asignada para el usuario en esta misma hora: <strong>' . $act . '</strong>');
  }
}
/**
 * implementa hook_submit para el formulario de nueva actividad.
 *
 * recibe los datos después de haber pasado la validaci&oacute;n de hook_validate()
 *    por lo que est&aacute;n listo para ser guardados en la base de datos.
 *
 * @param $form
 *    Arreglo compuesto con la estructura del formulario.
 * @param $form_state
 *    Arreglo compuesto con los valores del formulario.
 */
function sghpNuevaActividad_submit($form, &$form_state){
  global $user;

  $act = new Horario($user->uid);
  if($act->crearBloque('actividad', $form_state['values']))
    drupal_set_message('Actividad guardada correctamente');
  else
    drupal_set_message('Ha ocurrido un error al guardar la actividad', 'error');
}
/**
 * implementa el formulario para crear una nueva asignatura.
 *
 * Realmente es una modificaci&oacute;n del formulario para crear una nueva
 *    actividad.
 *
 * @param $form_state
 *    Valores del formulario. V&eacute;ase sghpNuevaActividad().
 *
 * @return
 *    Arreglo compuesto con la estructura del formulario.
 */
function sghpNuevaAsignatura($form_state){
  //tomo un formulario base
  $form = sghpNuevaActividad(NULL);
  
  $form['nombre']['#title'] = 'Nombre de la asignatura';
  $form['nick'] = array(
    '#type' => 'textfield',
    '#title' => 'Nick del académico',
    '#required' => TRUE,
    '#autocomplete_path' => 'user/autocomplete',
    '#weight' => 50,
  );
  return $form;
}
/**
 * implementa hook_validate para el formulario de creaci&oacute;n de
 *    asignatura.
 *
 * @param $form
 *    Arreglo compuesto con la estructura del formulario.
 * @param $formState
 *    Arreglo compuesto con los datos del formulario.
 */
function sghpNuevaAsignatura_validate($form, &$formState){
  $values = $formState['values'];
  
  //verificar la existencia del usuario
  $consulta = sprintf("select uid from {users} where name = '%s'", $values['nick']);
  $idUsuario = db_result(db_query($consulta));
  if(!$idUsuario){
    form_set_error('nick', 'Usuario ' . $values['nick'] . ' no existe.');
    return;
  }
  
  // verificar la existencia de otro bloque en el mismo lugar
  $horario = new Horario($idUsuario);
  if($act = $horario->chequearBloque($values['dia'], $values['numero'])){
    form_error($form, 'Ya existe una actividad asignada para el usuario en esta misma hora: <strong>' . $act . '</strong>');
  }
}

/**
 * implementa hook_sumbit() para el formulario de nueva asignatura.
 *
 * @param $form
 *    Arreglo compuesto con la estructura del formulario.
 * @param $formState
 *    Arreglo compuesto con los datos del formulario.
 */
function sghpNuevaAsignatura_submit($form, &$formState){
  $values = $formState['values'];
  
  $values['idUsuario'] = db_result(db_query("SELECT uid FROM {users} WHERE name = '%s'", $values['nick']));

  $asig = new Horario($values['idUsuario']);
  
  if($asig->crearBloque('asignatura', $values))
    drupal_set_message('Asignatura guardada correctamente');
  else
    drupal_set_message('Ha ocurrido un error al guardar la asignatura', 'error');
}
