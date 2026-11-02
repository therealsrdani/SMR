def empleados_en_rango_salarial(salarios: dict[str, float]) -> list[str]:
    """Devuelve los empleados con un salario entre 1.200 y 2.000 euros."""
    return [
        nombre
        for nombre, salario in salarios.items()
        if 1200 <= salario <= 2000
    ]


def trabajadores_con_mas_de_40_horas(
    tareas: dict[str, list[float]],
) -> list[str]:
    """Devuelve los trabajadores con más de 40 horas de tareas asignadas."""
    return [
        trabajador
        for trabajador, duraciones in tareas.items()
        if sum(duraciones) > 40
    ]


def trabajadores_con_menos_de_5_horas_de_formacion(
    formacion: dict[str, list[float]],
) -> dict[str, list[float]]:
    """Filtra los trabajadores con una media de formación inferior a 5 horas."""
    return {
        trabajador: horas
        for trabajador, horas in formacion.items()
        if horas and sum(horas) / len(horas) < 5
    }


def resumen_intervenciones(
    intervenciones: dict[str, list[float]],
) -> dict[str, dict[str, float | int]]:
    """Resume las intervenciones de técnicos con al menos cinco registros."""
    return {
        tecnico: {
            "tiempo_medio": sum(tiempos) / len(tiempos),
            "total_intervenciones": len(tiempos),
        }
        for tecnico, tiempos in intervenciones.items()
        if len(tiempos) >= 5
    }


if __name__ == "__main__":
    salarios = {
        "Ana": 1200,
        "Luis": 1750,
        "Marta": 2000,
        "Iker": 2100,
    }
    print("Ejercicio 1:", empleados_en_rango_salarial(salarios))

    tareas = {
        "Ana": [8, 8, 8, 8, 8],
        "Luis": [10, 10, 10, 10, 1],
        "Marta": [6, 7, 8],
    }
    print("Ejercicio 2:", trabajadores_con_mas_de_40_horas(tareas))

    formacion = {
        "Ana": [4, 5, 3, 4],
        "Luis": [5, 5, 5, 5],
        "Marta": [2, 3, 4, 4],
    }
    print(
        "Ejercicio 3:",
        trabajadores_con_menos_de_5_horas_de_formacion(formacion),
    )

    intervenciones = {
        "Ana": [20, 30, 25, 35, 40],
        "Luis": [15, 20, 25, 30],
        "Marta": [10, 15, 20, 25, 30, 35],
    }
    print("Ejercicio 4:", resumen_intervenciones(intervenciones))
