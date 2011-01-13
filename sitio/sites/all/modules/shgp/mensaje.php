<?php

// $Id$
/**
 * @file mensaje.php
 *    Implementa las clases relacionadas al manejo del sistema de
 *    mensajer&iacute;a interna de SGHP.
 */
/**
 * Clase casilla de correo
 */
class Casilla{
	/**
	 * Muestra una lista con los encabezados de los mensajes en pantalla.
	 *
	 * Específicamente muestra remitente, asunto y fecha de los mensajes
	 * recibidos por un usuario en particular. Estos datos los recupera a
	 * trav&eacute;s del m&eacute;todo buscarMensajes().
   *
	 * @param $idUsuario
	 *     Identificador del usuario dentro del sistema
	 *
	 * @return
	 *     Tabla html con la lista de los mensajes recibidos por el usuario o
	 *     la cadena de texto 'No tiene mensajes'.
	 *
	 * @see buscarMensajes($idUsuario)
	 */
	public function mostrarListaMensajes($idUsuario){

    $args = func_get_args();
    $incluyeLeidos = isset($args[1]) ? $args[1] : TRUE;

    // obtener la lista de mensajes de la base de datos
    $listaMensajes = $this->buscarMensajes($idUsuario, $incluyeLeidos);
    if($listaMensajes == FALSE){
      // usuario no ha recibido mensaje alguno, se procede a informar al usuario
      // de esta situación
      if($incluyeLeidos == TRUE)
        $salida = '<p>No tiene mensajes</p>';
      else
        $salida = '<p>No tiene mensajes nuevos</p>';
    }
    else {
      // usuario sí ha recibido mensajes
      if($incluyeLeidos == TRUE)
        $salida = '<p>Lista de todos sus mensajes</p>';
      else
        $salida = '<p>Lista de los mensajes nuevos</p>';

      $salida .= theme('table', $listaMensajes['encabezados'], $listaMensajes['datos']);
    }
    return $salida;
	}
	/**
	 * Recupera la lista de mensajes desde la base de datos
	 *
	 * Los datos a entregar por cada mensaje son:
	 * - De (remitente)
	 * - Asunto
	 * - Leído
	 * - Fecha (como unix timestamp)
	 *
	 * @param $idUsuario
	 *     Identificador del usuario dentro del sistema
	 * @return
	 *     array('encabezados'=>array(), 'datos'=>array()) si encuentra mensajes,
	 *     FALSE en caso contrario.
	 */
	private function buscarMensajes($idUsuario){
    // lo hago de esta forma para no necesitar documentarlo
    $args = func_get_args();
    $incluyeLeidos = $args[1];


    // contar la cantidad de mensajes que tiene el usuario
    if ($incluyeLeidos == TRUE)
      $cantidadMensajes = db_result(db_query('SELECT count(*) FROM {sghp_mensaje} WHERE propietario = %d', $idUsuario));
    else
      $cantidadMensajes = db_result(db_query('SELECT count(*) FROM {sghp_mensaje} WHERE propietario = %d && leido = 0', $idUsuario));

    if($cantidadMensajes > 0){
      // si hay mensajes entonces crear encabezado tabla
      $header = array(
        array('data' => 'De'),
        array('data' => 'Asunto'),
        array('data' => 'Leído'),
        array('data' => 'Fecha'),
        array('data' => 'Eliminar'),
      );
      //luego buscar los datos en la tabla
      if ($incluyeLeidos == TRUE)
        $consulta = db_query('select idMensaje, de, asunto, leido, fecha from {sghp_mensaje} WHERE propietario = %d ORDER BY fecha DESC', $idUsuario);
      else
        $consulta = db_query('select idMensaje, de, asunto, leido, fecha from {sghp_mensaje} WHERE propietario = %d && leido = 0 ORDER BY fecha DESC', $idUsuario);
      
      //por cada mensaje que haya se crea una entrada en la lista
      while($result = db_fetch_object($consulta)){
        //nombre del remitente
        $nombreCompleto = db_fetch_object(db_query('select nombre, apellido from {sghp_usuario} where idUsuario = %d', $result->de));
        
        $lineaMsg[] = array(
          $nombreCompleto->nombre . ' ' . $nombreCompleto->apellido,
          l($result->asunto, 'sghp/mensaje/leer/' . $result->idMensaje),
          $result->leido==FALSE ? 'NO' : 'SÍ',
          format_date($result->fecha, 'medium'),
          l('Eliminar', 'sghp/mensaje/borrar/' . $result->idMensaje),
        );
      }
      return array('encabezados' => $header, 'datos' => $lineaMsg);
    }
    else {
      return FALSE;
    }
	}
	/**
	 * Genera el arreglo con los campos del formulario Componer mensaje
	 *
	 * @return
	 *   array(array(), ...)
	 */
	public function componer(){
    drupal_set_title('Nuevo Mensaje');
    $form['para'] = array(
      '#type' => 'textfield',
      '#title' => 'Destinatario',
      '#autocomplete_path' => 'user/autocomplete',
      '#required' => TRUE,
      '#description' => 'Nick del usuario a quien le quiere enviar el mensaje.'
    );
    $form['asunto'] = array(
      '#type' => 'textfield',
      '#title' => t('Subject'),
      '#maxlength' => 255,
      '#required' => TRUE,
      '#description' => '¿Sobre qué habla este mensaje?',
    );
    $form['mensaje'] = array(
      '#type' => 'textarea',
      '#title' => t('Message'),
      '#required' => TRUE,
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Send'),
    );
	  return $form;
	}
	/**
	 * Guarda el mensaje en la base de datos
	 *
	 * @param $mensaje
	 *   Arreglo asociativo con los datos del mensaje
	 */
	public function enviarMensaje($mensaje){
    $mensaje['para'] = db_result(db_query("SELECT uid FROM {users} WHERE name = '%s'", $mensaje['para']));
    db_query(
      'insert into {sghp_mensaje}' .
      '       (de, para, propietario, asunto, contenido, fecha) ' .
      'values (%d, %d,   %d,          \'%s\', \'%s\',     %d);',
      $mensaje['de'],
      $mensaje['para'],
      $mensaje['para'],
      $mensaje['asunto'],
      $mensaje['mensaje'],
      $mensaje['fecha']);

    drupal_set_message('El mensaje ha sido enviado correctamente con fecha ' .
      format_date($mensaje['fecha'], 'small'));
	}
	/**
	 * Borra un mensaje del sistema.
	 *
	 * Verifica si el usuario es el due&ntilde;o del mensaje y luego lo borra, de
	 *   lo contrario muestra un mensaje de error.
	 *
	 * @param $idMensaje
	 *   Identificador del mensaje a borrar
	 *
	 * @return
	 *   Texto HTML indicando si la operaci&oacute;n tuvo &eacute;xito o no.
   */
	public function borrarMensaje($idMensaje){
    global $user;
    //primero verificar si el mensaje es suyo
    $propietario = db_result(db_query('SELECT propietario FROM {sghp_mensaje}' .
      ' WHERE idMensaje = %d', $idMensaje));
    if($propietario == $user->uid){
      //el mensaje es del ususario actual, entonces borrar
      db_query('DELETE FROM {sghp_mensaje} WHERE idMensaje = %d', $idMensaje);
      drupal_set_message('El mensaje ha sido eliminado');
      return '<p>' . l('Volver', 'sghp/mensaje') . '</p>';
    }
    else {
      drupal_set_message('No tienes permiso para eliminar este mensaje', 'error');
      return '<p>No tienes permiso para eliminar este mensaje</p>';
    }
	}
};

/**
 * Clase Mensaje
 */
class Mensaje{
	/**
	 * Muestra el contenido del mensaje seleccionado.
	 *
	 * @param $idMensaje
	 *     Identificador del mensaje dentro del sistema.
	 * @param $idUsuario
	 *     Identificador del usuario (Usado para comprobar que no se quiere
	 *     acceder a mensajes ajenos).
	 * @return
	 *     Texto html listo para ser mostrado en pantalla.
	 * @see obtenerMensaje($idMensaje, $idUsuario)
	 */
	public function mostrarMensaje($idMensaje, $idUsuario){
    $mensaje = $this->obtenerMensaje($idMensaje, $idUsuario);
    $tags = array('a', 'em', 'strong', 'ul', 'ol', 'li', 'p', 'h2', 'h3', 'img');
    if($mensaje == FALSE){
      drupal_set_message('Error: Mensaje no existe o no eres el propietario del mismo','error');
      $salida = '';
    }
    else {
      $salida =
        '<p>De: ' . $mensaje->de . '</p>' .
        '<p>Para: ' . $mensaje->para . '</p>' .
        '<p>Fecha: ' . format_date($mensaje->fecha, 'large') . '</p>' .
        '<fieldset>' .
          '<legend>' . $mensaje->asunto . '</legend>' .
          '<div>' . filter_xss($mensaje->contenido, $tags) . '</div>' .
        '</fieldset>' .
        l('volver', 'sghp/mensaje');
    }
    return $salida;
	}
	/**
	 * Recupera el mensaje desde la base de datos.
	 *
	 * @param $idMensaje
	 *     Identificador del mensaje.
	 * @param $idUsuario
	 *     Identificador del usuario.
	 * @return
	 *     El mensaje solicitado o FALSE en caso de que el usuario no sea el
	 *     propietario del mensaje.
	 * @see mostrarMensaje($idMensaje, $idUsuario)
   */
	private function obtenerMensaje($idMensaje, $idUsuario){
    $mensaje = db_fetch_object(db_query('select * from {sghp_mensaje} where idMensaje = %d', $idMensaje));
    if($mensaje->propietario != $idUsuario){
      $salida = FALSE;
    }
    else {
      $nombreRemitente = db_fetch_object(db_query('select nombre, apellido from {sghp_usuario} where idUsuario = %d', $mensaje->de));
      $nombreDestinatario = db_fetch_object(db_query('select nombre, apellido from {sghp_usuario} where idUsuario = %d', $mensaje->para));
      $mensaje->de = $nombreRemitente->nombre . ' ' . $nombreRemitente->apellido;
      $mensaje->para = $nombreDestinatario->nombre . ' ' . $nombreDestinatario->apellido;
      $salida = $mensaje;
    }
    //marcamos el mensaje como leído
    db_query('UPDATE {sghp_mensaje} set leido = 1 where idMensaje = %d', $idMensaje);
    return $salida;
	}
}



/**
 * Esta función crea los objetos de las clases necesarias e invoca a sus métodos.
 *
 * @param $accion
 *    Cadena de texto indicando la acción a realizar. Puede ser 'lista', 'leer',
 *    'componer' o 'eliminar'
 * @param $idMensaje
 *    Identificador del mensaje sobre el cual se va a actuar
 */
function sghpMensaje($accion, $idMensaje = 0){
  global $user;
  $salida = '';
  switch($accion){
  case 'lista':
    $casilla = new Casilla();
    $salida = $casilla->mostrarListaMensajes($user->uid);
    break;
  case 'leer':
    $mensaje = new Mensaje();
    $salida = $mensaje->mostrarMensaje($idMensaje, $user->uid);
    break;
  case 'eliminar':
    $mensaje = new Casilla();
    $salida = $mensaje->borrarMensaje($idMensaje);
    break;
  default:
    drupal_set_message('No se entiende lo que quieres hacer o no está implementado: '. $accion, 'error');
    break;
  }
  return $salida;
}
/**
 * Esta funci&oacute;n delega su trabajo al m&eacute;todo componer() de la clase
 *    Casilla.
 *
 * @param $form_state
 *    Valores actuales del formulario (manejado por Drupal).
 *
 * @return
 *    Formulario HTML para escribir un mensaje.
 */
function sghpMensajeNuevo($form_state) {
  $casilla = new Casilla();
  $form = $casilla->componer();
  return $form;
}
/**
 * Implementa hook_validate().
 *
 * Toma un formulario y los valores enviados por el usuario y verifica la
 *    correctitud de los valores en relaci&oacute;n a la configuraci&oacute;n
 *    del formulario.
 *
 * @param $form
 *    Identificaci&oacute;n del formulario.
 * @param &$form_state
 *    Valores del formulario en forma de arreglo asociativo.
 */
function sghpMensajeNuevo_validate($form, &$form_state){
  $nickDestino = $form_state['values']['para'];
  /*
  if(!is_numeric($idDestino) || $idDestino < 1){
    form_set_error('para', 'El campo Destinatario no tiene un valor válido');
  }
  else{
    $destino = db_result(db_query('select count(*) from {sghp_usuario} where idUsuario = %d', $idDestino));
    if($destino != 1){
      form_set_error('para', 'Usuario destino no tiene un perfil activo dentro de SGHP');
    }
  }*/
  $destino = db_result(db_query('select count(*) from {users} where name = %d', $nickDestino));
  $asunto = $form_state['values']['asunto'];
  if(drupal_strlen($asunto)<5){
    form_set_error('asunto', 'Asunto es demasiado corto (mínimo 5 caracteres)');
  }
}
/**
 * Implementa hook_submit().
 *
 * Recibe los datos del formulario una vez que haya pasado sin errores la
 *    validaci&oacute;n y llama a Casilla::enviarMensaje() con los valores del
 *    formulario.
 *
 * @param $form
 *    Arreglo compuesto con la estructura del formulario.
 * @param $form_state
 *    Arreglo compuesto con los datos del formulario.
 */
function sghpMensajeNuevo_submit($form, &$form_state){
  global $user;
  $casilla = new Casilla();
  //rellenar algunos valores
  $form_state['values']['fecha'] = date('U');
  $form_state['values']['de'] = $user->uid;
  $casilla->enviarMensaje($form_state['values']);
}

?>
