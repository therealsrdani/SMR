# Creamos una agenda que relaciona cada contacto con su teléfono.
agenda = {
    "Carlos": "612345678",
    "Lucia": "678543210",
    "Pepe": "666666666",
}

# Añadimos un contacto, actualizamos otro y eliminamos a Carlos.
agenda["Ana"] = "699999999"
agenda["Pepe"] = "600000000"
del agenda["Carlos"]

# Recorremos e imprimimos los nombres de los contactos.
print("Contactos:")
for contacto in agenda.keys():
    print(contacto)

# Recorremos e imprimimos los números guardados en la agenda.
print("Números:")
for numero in agenda.values():
    print(numero)
