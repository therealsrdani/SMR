# Registramos en un diccionario las cantidades de material disponible.
inventario = {
    "ordenadores": 25,
    "switches": 3,
    "routers": 1,
}

# Actualizamos las existencias y añadimos la cantidad de cables de red.
inventario["ordenadores"] += 1
inventario["switches"] -= 1
inventario["cables_red"] = 150

# Sumamos los aparatos, sin incluir los cables, e imprimimos el resultado.
total_aparatos = (
    inventario["ordenadores"]
    + inventario["switches"]
    + inventario["routers"]
)
print("Total de aparatos sin contar cables:", total_aparatos)
