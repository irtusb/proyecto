<?php


function sghp_actividades(){
  global $user;
  
  $lineaEnBlanco = array('&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;');
  $lineaDescanso = array(array('data' => '<hr>', 'colspan' => 5));
  $header = array(
    t('Monday'),
    t('Tuesday'),
    t('Wednesday'),
    t('Thursday'),
    t('Friday'),
  );
  for($c = 1; $c < 9; $c++){
    if($c == 4)
      $datos[] = $lineaDescanso;
    else {
      $linea = $lineaEnBlanco;
      $consulta = db_query('SELECT nombre, dia FROM {sghp_bloque} WHERE idUsuario = %d && numero = %d', $user->uid, $c);
      while($bloque = db_fetch_object($consulta)){
        $linea[$bloque->dia] = array('data' => $bloque->nombre, 'style' => 'background-color:orange');
      }
      $datos[] = $linea;
    }
  }
  return theme_table($header, $datos);

}

