notas = {}

while True:
	print("\nNotas:")
	print("1. Añadir nota")
	print("2. Mostrar notas")
	print("3. Salir")

	opcion = input("Selecciona una opción: ")

	if opcion == "1":
		alumno = input("Nombre del alumno: ").strip()
		if not alumno:
			print("El nombre no puede estar vacío.")
			continue

		try:
			nota = int(input("Nota: "))
		except ValueError:
			print("Introduce la nota como un número entero.")
			continue

		if alumno not in notas:
			notas[alumno] = []
		notas[alumno].append(nota)
		print("Nota registrada.")

	elif opcion == "2":
		if not notas:
			print("No hay notas registradas.")
		else:
			for alumno, lista_notas in notas.items():
				print(f"{alumno}: {lista_notas}")

	elif opcion == "3":
		break

	else:
		print("Opción no válida.")
		