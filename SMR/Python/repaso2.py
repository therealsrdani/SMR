def trabajadores_con_mas_de_40_horas(
    tareas: dict[str, list[float]],
) -> list[str]:
    """Devuelve los trabajadores con mas de 40 horas de tareas asignadas."""
    horas_por_trabajador = {
        trabajador: sum(duraciones)
        for trabajador, duraciones in tareas.items()
    }

    return [
        trabajador
        for trabajador, horas in horas_por_trabajador.items()
        if horas > 40
    ]