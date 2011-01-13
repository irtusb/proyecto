<?php
/**
 * @file sghpMain.php
 *    Implementa la clase Interfaz.
 */


/**
 * Clase con un m&eacute;todo &uacute;nico Interfaz::screen() que muestra la
 *    portada del sistema.
 */
class Interfaz{
  /**
   * Muestra un mensaje de bienvenida.
   *
   * @return
   *    El mensaje de bienvenida formateado como html.
   */
  static public function screen(){
    require_once('mensaje.php');
    global $user;
    $casilla = new Casilla();
    $salida = '<h2>Bienvenido al Sistema Gestión de horarios de profesores</h2>';
    $salida .= $casilla->mostrarListaMensajes($user->uid, FALSE);
    return $salida;
  }
}



