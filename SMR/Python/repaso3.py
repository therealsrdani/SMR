def trabajadores_con_media_inferior_a_5(
    horas_formacion: dict[str, list[float]],
) -> dict[str, list[float]]:
    """Devuelve los trabajadores cuya media mensual es inferior a 5 horas."""
    return {
        trabajador: horas
        for trabajador, horas in horas_formacion.items()
        if sum(horas) / len(horas) < 5
    }
