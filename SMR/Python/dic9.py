# Guardamos los datos de una cuenta de usuario en un diccionario.
usuario = {
    "nombre": "ekaitz",
    "uid": 1001,
    "shell": "/bin/bash",
    "home": "/home/juan",
}

# Cambiamos la shell predeterminada y guardamos los grupos del usuario.
usuario["shell"] = "/usr/bin/zsh"
usuario["grupos"] = ["sudo", "video"]

# Recorremos e imprimimos los grupos a los que pertenece el usuario.
for grupo in usuario["grupos"]:
    print(grupo)
