WEB 1.0 - PORTAL PUBLICO
========================

DESCRIPCION
-----------
Portal publico de Kalvex. Presenta una pagina de inicio con secciones de
informacion del proyecto y contacto, navegacion entre secciones y un formulario
de acceso para empleados que conduce a la intranet.

ARCHIVOS PRINCIPALES
--------------------
- index.html: estructura general y marcos de cabecera, navegacion, contenido
  y pie.
- header.html: logotipo y nombre de la empresa.
- navigation.html: enlaces que cambian la seccion mostrada en el contenido.
- content.html: inicio, proyecto, contacto y formulario de acceso.
- footer.html: pie del portal.
- login.php: valida las credenciales contra la tabla usuarios y sirve tambien
  el JavaScript utilizado por el contenido.
- img/: logotipos y otros recursos graficos.

REQUISITOS Y ARRANQUE
---------------------
Para usar el portal completo, incluido el acceso de empleados, sirve el
proyecto con un servidor web que ejecute PHP. El formulario consulta MySQL o
MariaDB; no funciona abriendo index.html directamente con file://.

Desde la carpeta ERRONKA 2 puedes arrancar el servidor de desarrollo de PHP:

  php -S localhost:8000

Abre despues:

  http://localhost:8000/Web%201.0/index.html

La autenticacion requiere la base de datos empresa y una tabla usuarios con
las columnas id, usuario, password, nombre y rol. La contrasena debe estar
guardada como hash compatible con password_verify. No se incluyen usuarios
ni credenciales de demostracion.

CONFIGURACION Y PERSONALIZACION
-------------------------------
La conexion a la base de datos esta definida en login.php: por defecto usa
localhost, usuario root, contrasena vacia y base empresa. Cambia esos valores
para ajustarlos a tu entorno.

Modifica los textos de content.html para completar la informacion real de la
empresa. Cambia los recursos en img/ si quieres usar otros logotipos o imagenes.
La navegacion entre secciones se realiza dentro del marco de contenido; el
formulario de acceso se envia a login.php y, si las credenciales son validas,
redirige a ../Intranet/portal.html.

La pagina usa marcos HTML para organizar sus partes. Si cambias los nombres o
la ubicacion de los archivos, actualiza tambien las rutas de index.html,
navigation.html y login.php.

Para una descripcion del proyecto completo y de la intranet, consulta
../README.txt e ../Intranet/README.txt.
