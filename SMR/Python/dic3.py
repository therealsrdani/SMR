# Creamos un diccionario con las características del ordenador.
pc = {
    "ram": "8GB",
    "cpu": "i5",
    "disco": "500GB",
}

# Comprobamos si existe la clave "ram" antes de mostrar el mensaje.
if "ram" in pc:
    print("Este PC tiene RAM")
