KALVEX - PORTAL DE EMPRESA E INTRANET
=====================================

DESCRIPCION
-----------
Proyecto web de demostracion para una empresa. Reune un portal publico
(Web 1.0) y una intranet para empleados. El portal ofrece informacion general
y un acceso que lleva al espacio privado. La intranet incluye avisos, horarios,
catalogo de productos y un flujo de solicitudes de ausencia.

ESTRUCTURA
----------
- Web 1.0/: portal publico, formulario de acceso y recursos visuales.
- Intranet/: paginas privadas, gestion de solicitudes, catalogo y horarios.
- apariencia/: recursos graficos del proyecto.

COMO PONERLO EN MARCHA
----------------------
Se necesita un servidor web con PHP y MySQL o MariaDB. No abras los archivos
directamente con file://: el login y las funciones conectadas a la base de
datos requieren que las peticiones pasen por PHP.

Por ejemplo, desde esta carpeta puedes iniciar el servidor integrado de PHP:

  php -S localhost:8000

Despues abre en el navegador:

  http://localhost:8000/Web%201.0/index.html

Tambien puedes configurar Apache/XAMPP para que esta carpeta sea accesible
desde el servidor web.

BASE DE DATOS Y ACCESO
----------------------
La aplicacion utiliza una base de datos MySQL/MariaDB llamada empresa.
Intranet/gestion.sql crea las tablas de productos y horarios.
Intranet/solicitudes.sql crea las tablas de solicitudes y mensajes. Si ya
existe una tabla solicitudes_ausencia de una version anterior, revisa
Intranet/actualizar_solicitudes.sql y ejecutalo una sola vez cuando proceda.

La tabla usuarios debe existir previamente y contener, como minimo, las
columnas id, usuario, password, nombre y rol. El valor de password debe ser
un hash compatible con password_verify de PHP; no se guarda la contrasena
en texto plano. No se incluyen usuarios ni credenciales de demostracion en
el proyecto.

La conexion esta configurada para localhost, usuario root, contrasena vacia
y base empresa. Ajusta la configuracion en Web 1.0/login.php e Intranet/app.php
y api.php si tu entorno usa otros datos.

DOCUMENTACION
-------------
- Web 1.0/README.txt: descripcion y configuracion del portal publico.
- Intranet/README.txt: paginas, permisos, datos y funcionamiento de la intranet.

AVISO
-----
Es una maqueta educativa y contiene contenido de ejemplo y campos pendientes
de personalizar. No debe utilizarse para almacenar o proteger informacion
real sin una revision de seguridad y una configuracion de servidor adecuadas.
