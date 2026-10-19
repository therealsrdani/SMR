# Creamos un personaje con sus características y una lista de habilidades.
pj = {
    "nombre": "Arkon",
    "nivel": 4,
    "vida": 100,
    "habilidades": ["fuego", "hielo"],
}

# Añadimos una habilidad nueva y quitamos la habilidad "hielo".
pj["habilidades"].append("rayo")
pj["habilidades"].remove("hielo")

# Mostramos cuántas habilidades conserva el personaje.
print("Número de habilidades:", len(pj["habilidades"]))
# Recorremos e imprimimos las habilidades restantes.
for habilidad in pj["habilidades"]:
    print(habilidad)
