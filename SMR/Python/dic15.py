# Guardamos para cada día una lista de las direcciones IP conectadas.
historial = {
    "lunes": ["192.168.1.10", "192.168.1.14"],
    "martes": ["192.168.1.20"],
    "miercoles": [],
}

# Añadimos al miércoles una nueva dirección IP conectada.
historial["miercoles"].append("192.168.1.30")

# Sumamos la cantidad de direcciones registradas en todos los días.
total_conexiones = sum(len(ips) for ips in historial.values())
print("Total de conexiones:", total_conexiones)

# Mostramos cuántas direcciones IP se registraron cada día.
for dia, ips in historial.items():
    print(f"{dia}: {len(ips)} IPs")
