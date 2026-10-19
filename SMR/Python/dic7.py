# Guardamos la configuración de red en un diccionario.
config = {
    "Dirección IP": "192.168.1.20",
    "Máscara Subred": "255.255.255.0",
    "Puerta de enlace": "192.168.1.1",
}

# Cambiamos la dirección IP y añadimos una dirección DNS.
config["Dirección IP"] = "192.168.1.50"
config["DNS"] = "8.8.8.8"

# Comprobamos si se ha definido una entrada DNS.
print("¿Hay DNS?", "DNS" in config)
# Mostramos cada clave de configuración junto con su valor.
for clave, valor in config.items():
    print(clave, ":", valor)
