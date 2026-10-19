# Guardamos los datos de varios ordenadores en un diccionario anidado.
equipo = {
    "pc1": {"ip": "192.168.0.10", "usuario": "ana"},
    "pc2": {"ip": "192.168.0.11", "usuario": "carlos"},
}

# Cambiamos el usuario de pc1 y agregamos un tercer ordenador.
equipo["pc1"]["usuario"] = "laura"
equipo["pc3"] = {"ip": "192.168.0.12", "usuario": "pedro"}

# Recorremos los ordenadores e imprimimos su dirección IP y usuario.
for nombre, datos in equipo.items():
    print(f"{nombre} → ip: {datos['ip']} / usuario: {datos['usuario']}")
