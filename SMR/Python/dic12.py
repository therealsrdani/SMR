# Relacionamos cada ciclo formativo con su número de alumnos.
ciclos = {
    "DAW": 25,
    "SMR": 20,
    "DAM": 22,
    "STI": 18,
}

# Sumamos los alumnos de todos los ciclos.
total_alumnos = sum(ciclos.values())
# Contamos cuántos ciclos tienen más de 22 alumnos.
ciclos_con_mas_de_22 = sum(1 for alumnos in ciclos.values() if alumnos > 22)
# Buscamos el nombre del ciclo con la cifra de alumnos más alta.
ciclo_con_mas_alumnos = max(ciclos, key=ciclos.get)

# Mostramos los tres resultados calculados.
print("Total de alumnos:", total_alumnos)
print("Ciclos con más de 22 alumnos:", ciclos_con_mas_de_22)
print("Ciclo con más alumnos:", ciclo_con_mas_alumnos)
