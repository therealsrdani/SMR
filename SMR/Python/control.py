usuarios = {
	"dani": "admin",
	"rai": "profesor",
	"mikel": "profesor",
	"eva": "alumna",
	"marco": "alumno",
	"haritz": "alumno",
}

usuario = input("Introduce un nombre de usuario o 'salir' para terminar: ").strip()

while usuario.lower() != "salir":
	if usuario not in usuarios:
		print("Usuario no encontrado")
	elif usuarios[usuario] == "admin":
		print("Tienes acceso total")
	elif usuarios[usuario] == "profesor":
		print("Tienes acceso medio")
	elif usuarios[usuario] == "alumno":
		print("Tienes acceso limitado")
	elif usuarios[usuario] == "alumna":
		print("Tienes acceso femenino limitado")

	usuario = input("Introduce otro nombre de usuario o 'salir' para terminar: ").strip()
