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
- horarios.html: tabla de horarios por equipo.
- productos.html: galería con búsqueda de productos y precios.

La sesión de acceso y la validación del formulario de solicitudes se gestionan
en PHP. El catálogo y la navegación instantánea siguen necesitando JavaScript
en el navegador; PHP sirve ese código desde archivos .php. Las páginas HTML
son estáticas y no pueden bloquearse por sesión en el servidor sin cambiar su
extensión o configurar reglas en el servidor web. No uses esta maqueta para
proteger información real. El formulario de solicitudes solo muestra una
confirmación y no guarda los datos.
Las fotografías del catálogo y las tipografías se cargan desde servicios
externos, por lo que necesitan conexión a Internet.

El formulario de acceso está en Web 1.0; esta carpeta contiene las páginas
privadas de la intranet.
