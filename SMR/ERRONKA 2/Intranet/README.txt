INTRANET

Abre ../Web 1.0/index.html y utiliza el formulario «Acceso de empleados».
Al introducir las credenciales de demostración correctamente, el formulario
se valida en Web 1.0/login.php y se abrirá portal.html. Necesitas servir el
proyecto con PHP; no funcionará abriendo index.html directamente como archivo.
Las páginas de la intranet conservan sus archivos HTML. Los antiguos scripts
JavaScript se sirven desde auth.php, app.php y portal.php.
En Web 1.0, el script también se sirve desde login.php; las páginas index.html
y content.html se conservan.

Credenciales de demostración:
- Usuario: empleado
- Contraseña: intranet123

Puedes cambiarlas en ../Web 1.0/login.php.

Los textos entre corchetes son campos para completar con el nombre y la
información real de la empresa. En productos.html puedes sustituir también
las direcciones de las imágenes por las fotografías que quieras utilizar.

Páginas privadas:
- portal.html: marco común con menú, encabezado y el iframe de contenido.
- index.html: tablón de anuncios y accesos rápidos.
- solicitudes.html: formulario de vacaciones y permisos.
- respuestas.php y respuestas.html: acceso protegido y plantilla del panel gráfico de respuestas, solo para el rol Director.
- horarios.html: tabla de horarios por equipo.
- productos.html: galería con búsqueda de productos y precios.
- gestion.sql: tablas persistentes del catálogo y los horarios.

La sesión de acceso y el formulario de solicitudes se gestionan en PHP. Para
guardar las solicitudes, ejecuta solicitudes.sql en MySQL/MariaDB (crea la tabla
solicitudes_ausencia dentro de la base empresa). El formulario guarda el ID y
usuario de la sesión, el tipo, las fechas, los comentarios y la fecha de envío.
La conexión usa localhost, usuario root, contraseña vacía y base empresa;
ajusta esos datos en app.php si tu instalación es diferente.
Las respuestas se consultan en respuestas.php; el HTML del panel está separado
en respuestas.html. La página y su API validan el acceso en el servidor y están
limitadas al rol exacto «Director»; «Director Ejecutivo» y los demás roles no
pueden consultar este registro.

Ejecuta también gestion.sql en MySQL/MariaDB para crear las tablas productos y
horarios en la base empresa. Los datos se consultan desde la base de datos.
Director (también «Director Ejecutivo») y Gerente pueden crear, editar y borrar
productos y horarios; Empleado y Cliente solo pueden consultarlos. Los permisos
se validan tanto en la interfaz como en api.php.
El nombre, las iniciales y el rol del perfil se obtienen del usuario que inició
sesión en la tabla usuarios.

El catálogo y la navegación instantánea siguen necesitando JavaScript en el
navegador; PHP sirve ese código desde archivos .php. Las páginas HTML son
estáticas y no pueden bloquearse por sesión en el servidor sin cambiar su
extensión o configurar reglas en el servidor web. No uses esta maqueta para
proteger información real.
Las fotografías del catálogo y las tipografías se cargan desde servicios
externos, por lo que necesitan conexión a Internet.

El formulario de acceso está en Web 1.0; esta carpeta contiene las páginas
privadas de la intranet.
Para cambiar la conexión a MySQL, actualiza las credenciales en app.php y
api.php. La tabla usuarios debe incluir id, usuario, password, nombre y rol;
los roles admitidos para permisos son Director, Director Ejecutivo, Gerente,
Empleado y Cliente.
