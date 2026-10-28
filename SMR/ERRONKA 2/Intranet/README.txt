INTRANET

Abre ../Web 1.0/index.html y utiliza el formulario «Acceso de empleados».
Al introducir las credenciales de demostración correctamente, el formulario
se valida en Web 1.0/login.php y se abrirá portal.html. Necesitas servir el
proyecto con PHP; no funcionará abriendo index.html directamente como archivo.
El menú y el encabezado permanecen fijos mientras las secciones se cargan
dentro del iframe central.

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

La intranet es una maqueta de HTML, CSS, JavaScript y PHP. El acceso al portal
privado sigue siendo una comprobación del navegador porque las páginas de la
intranet son estáticas; no uses esta maqueta para proteger información real.
El formulario de solicitudes solo muestra una confirmación en pantalla. Para
proteger información real y procesar solicitudes, hace falta conectar las
páginas privadas a un servidor.
Las fotografías del catálogo y las tipografías se cargan desde servicios
externos, por lo que necesitan conexión a Internet.

El formulario de acceso está en Web 1.0; esta carpeta contiene las páginas
privadas de la intranet.
