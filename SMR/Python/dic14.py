# Creamos dos diccionarios con distintas partes de la configuración de red.
a = {"Dirección IP": "10.0.0.5", "Máscara de subred": "255.0.0.0"}
b = {"Gateway": "10.0.0.1", "DNS": "1.1.1.1"}

# Incorporamos al primer diccionario las claves y valores del segundo.
a.update(b)
# Mostramos el diccionario combinado.
print(a)
