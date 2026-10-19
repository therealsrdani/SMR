# Creamos un diccionario con las características del ordenador.
pc = {
    "ram": "8GB",
    "cpu": "i5",
    "disco": "500GB",
}

# Eliminamos del diccionario la información del disco.
del pc["disco"]
# Mostramos el diccionario después de eliminar esa clave.
print(pc)
