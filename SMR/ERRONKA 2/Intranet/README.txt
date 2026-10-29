INTRANET - ESPACIO PRIVADO DE EQUIPO
====================================

DESCRIPCION
-----------
Aplicacion interna de demostracion para consultar avisos, horarios y productos,
y para tramitar solicitudes de vacaciones o permisos. El acceso comienza en
../Web 1.0/index.html. Tras iniciar sesion correctamente, login.php abre
portal.html.

REQUISITOS Y ARRANQUE
---------------------
Se necesita PHP con las extensiones mysqli y MySQL/MariaDB. Desde la carpeta
ERRONKA 2 puedes iniciar el servidor integrado de PHP:

  php -S localhost:8000

Abre http://localhost:8000/Web%201.0/index.html para iniciar sesion. No abras
los HTML directamente con file://: el login y las funciones de datos dependen
de PHP. La tabla usuarios debe existir previamente y tener id, usuario,
password, nombre y rol. password debe contener un hash compatible con
password_verify. El proyecto no crea usuarios ni proporciona credenciales de
demostracion.

La conexion esta configurada por defecto para localhost, usuario root,
contrasena vacia y base empresa. Ajusta los parametros en
../Web 1.0/login.php, app.php y api.php segun tu instalacion.

BASE DE DATOS
-------------
En MySQL/MariaDB, crea o selecciona la base empresa y ejecuta:
- gestion.sql: crea las tablas productos y horarios.
- solicitudes.sql: crea solicitudes_ausencia y mensajes_solicitud.

Si ya tenias una tabla solicitudes_ausencia de una version anterior, revisa
actualizar_solicitudes.sql y ejecutalo una sola vez para incorporar los cambios
necesarios. No ejecutes una migracion sin revisar antes el estado de la base.

PAGINAS Y FUNCIONES
-------------------
- portal.html: marco con navegacion lateral, encabezado y contenido incrustado.
- index.html: tablón de anuncios y accesos rapidos; contiene textos de ejemplo.
- solicitudes.html: formulario para enviar solicitudes de ausencia.
- mis-solicitudes.html: historial, estado y conversacion de las solicitudes
  propias.
- respuestas.php y respuestas.html: panel de solicitudes para el rol Director,
  con resumen, graficos, lista y gestion del estado y los mensajes.
- horarios.html: consulta y gestion de horarios por equipo.
- productos.html: catalogo con busqueda y gestion de productos.
- styles.css: estilos compartidos de las paginas de la intranet.
- auth.php, app.php y portal.php: sirven JavaScript de la aplicacion y conectan
  las acciones de la interfaz con PHP.
- api.php: operaciones de datos y comprobaciones de permisos.
- logout.php: cierra la sesion y vuelve al portal publico.

SOLICITUDES Y PERMISOS
----------------------
Las solicitudes quedan asociadas al usuario autenticado y guardan tipo, fechas,
comentarios, estado y fecha de envio. Comienzan como «Enviada»; Director puede
cambiarlas a «En revision» y despues a «Cerrada». El empleado puede consultar
el estado y la conversacion de sus propias solicitudes. Solo se permiten
mensajes mientras la solicitud esta en revision.

El panel respuestas.php esta reservado al rol exacto «Director». Director y
Gerente pueden crear, editar y borrar productos y horarios; los demas roles
pueden consultarlos. Las operaciones del servidor comprueban los permisos,
ademas de ocultar o mostrar controles en la interfaz. Los roles utilizados son
Director, Director Ejecutivo, Gerente, Empleado y Cliente.

PERSONALIZACION Y LIMITACIONES
------------------------------
Sustituye los textos entre corchetes de las paginas por informacion real de la
empresa. En productos.html puedes completar o cambiar las imagenes, condiciones
y datos del catalogo. Los avisos y horarios deben mantenerse actualizados.

Las paginas principales son HTML estaticos: la comprobacion de sesion en estas
paginas se apoya en JavaScript. No las consideres una barrera suficiente para
proteger contenido confidencial; las operaciones PHP si deben validar la
sesion y los permisos en el servidor. No uses esta maqueta para proteger
informacion real sin revisar y reforzar la seguridad del servidor.

Algunas fotografias y tipografias pueden depender de servicios externos y
necesitar conexion a Internet. Para mas informacion sobre el conjunto del
proyecto, consulta ../README.txt.
