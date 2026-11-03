def resumen_intervenciones(
    intervenciones: dict[str, list[float]],
) -> dict[str, dict[str, float | int]]:
    """Devuelve la media y el número de intervenciones de técnicos con al menos cinco."""
    return {
        tecnico: {
            "tiempo_medio": sum(tiempos) / len(tiempos),
            "numero_intervenciones": len(tiempos),
        }
        for tecnico, tiempos in intervenciones.items()
        if len(tiempos) >= 5
    }