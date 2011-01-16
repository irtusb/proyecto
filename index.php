<html>
<head>
<title>MVC</title>
<style type="text/css">
body{
  font-family: sans-serif;
}
</style>
</head>
<body>
<p>Intentaré hacer uso del patrón Modelo Vista Controlador, el cual propone la existencia de 3 capas:</p>
<ul>
  <li>Modelo</li>
  <li>Vista</li>
  <li>Controlador</li>
</ul>
<p>El <strong>modelo</strong> incluye la definición de las clases que participan en el sistema.</p>
<p>Las <strong>vistas</strong> consideran la interacción con el usuario, ya sea pedir o mostrar información.</p>
<p>Los <strong>controladores</strong> son las funciones a las que llaman las vistas y utilizan los objetos y clases del <strong>modelo</strong> para cumplir su objetivo.</p>
<p>Para el sistema <strong>proyecto</strong> crearé los archivos <em>modelo.php</em>, <em>vistas.php</em> y <em>controlador.php</em> con el código correspondiente. Debido a la naturaleza de Drupal no se puede hacer que TODO el código quepa en estos tres archivos, por lo menos lo relativo al menú y la creación/destrucción de la base de datos debe ir en archivos <em>.module</em> y <em>.install</em> respectivamente.</p>
</body>
</html>
