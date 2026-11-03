def empleados_en_rango_salarial(salarios):
    """Devuelve los nombres de quienes cobran entre 1.200 y 2.000 euros."""
    empleados = []

    for nombre, salario in salarios.items():
        if 1200 <= salario <= 2000:
            empleados.append(nombre)

    return empleados


salarios_ruinasa = {
    "Ana": 1200,
    "Luis": 1500,
    "Marta": 2000,
    "Iker": 2100,
    "Nerea": 1100,
}

empleados_seleccionados = empleados_en_rango_salarial(salarios_ruinasa)
print(empleados_seleccionados)