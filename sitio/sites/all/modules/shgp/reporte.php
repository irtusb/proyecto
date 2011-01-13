<?php
/**
 * @file reporte.php
 *    Implementa clase Reporte.
 */

/**
 * Clase Reporte.
 *
 * Provee de la funcionalidad necesaria para generar reportes con la 
 *    planificaci&oacute;n semanal de cada uno de los usuarios del sistema.
 */
class Reporte{
  /**
   * ID de usuario, para no pedir valores globales.
   */
  private $idUsuario;
  /**
   * Genera un reporte con todos los horarios de los usuarios del sistema.
   *
   * @return
   *    P&aacute;gina html con un reporte de todos los horarios de los usuarios.
   *    del sistema.
   */
  public function generarReporte(){
    require_once('horarios.php');
    if(!isset($_GET['idUsuario']) || strlen(trim($_GET['idUsuario'])) == 0 || !is_numeric($_GET['idUsuario'])){
      drupal_set_title('Reporte de todos los horarios de los usuarios');
      return $this->reporteGeneral();
    }
    elseif ($nombreUsuario = db_result(db_query("select name from {users} where uid = %d", $_GET['idUsuario']))){
      drupal_set_title('Planificación semanal de ' . $nombreUsuario);
      $this->idUsuario = trim($_GET['idUsuario']);
      return $this->reporteEspecifico();
    }
    else{
      return MENU_ACCESS_DENIED;
    }
  }
  /**
   * Recupera los datos necesarios de la base de datos y genera una p&aacute;gina
   *    html con cada uno de los horarios de los usuarios del sistema.
   */
  private function reporteGeneral(){
    $salida = '';
    $consulta = db_query("SELECT * FROM {sghp_usuario}");
    $listaUsuarios = array();
    while($usuario = db_fetch_object($consulta)){
      // nos saltamos al usuario actual
      if($usuario->idUsuario == $this->idUsuario)
        continue;

      $listaUsuarios[] = array(
        'nombreCompleto' => $usuario->nombre . ' ' . $usuario->apellido,
        'idUsuario' => $usuario->idUsuario,
      );
    }
    foreach ($listaUsuarios as $usuario) {
      $horario = new Horario($usuario['idUsuario']);
      $salida .= '<p>Planificación semanal de ' . $usuario['nombreCompleto'] . '</p>';
    	$salida .= $horario->verHorario();
    	$salida .= '<p>&nbsp;</p>';
    }
    return $salida;
  }
/*
  /**
   * Genera un reporte con el horario de un usuario espec&iacute;fico.
   *
   * @return
   *    P&aacute;gina html con la planificaci&oacute;n semanal del usuario.
   * /
  private function reporteEspecifico(){
    $horario = new Horario($this->idUsuario);
    $salida = ' ';
    $usuario = db_fetch_object(db_query("SELECT nombre, apellido FROM {sghp_usuario}"));
    $salida .= $horario->verHorario();
    return $salida;
  }
*/
}

/**
 * Crea una instancia de la clase reporte y llama al m&eacute;todo
 *    generarReporte() el cual se encarga de generar un reporte general con
 *    los horarios de todos los usuario con permiso de 'ver horario'.
 *
 * @return
 *    P&aacute;gina html con el reporte general.
 */
function sghpReporte(){
  $reporte = new Reporte();
  return $reporte->generarReporte();
}
