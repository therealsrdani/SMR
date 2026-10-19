# Este diccionario almacena el nombre de cada producto y su cantidad.
almacen = {}

# Repetimos el menú hasta que el usuario elija salir.
while True:
    # Mostramos las operaciones disponibles para gestionar el almacén.
    print("\n1. Añadir producto")
    print("2. Borrar producto")
    print("3. Buscar producto")
    print("4. Mostrar todo")
    print("5. Salir")

    opcion = input("Elige una opción: ")

    if opcion == "1":
        # Solicitamos el producto y validamos que la cantidad sea un entero.
        nombre = input("Nombre del producto: ")
        try:
            cantidad = int(input("Cantidad: "))
        except ValueError:
            print("La cantidad debe ser un número entero.")
            continue
        # Guardamos la cantidad; si ya existía, se actualiza.
        almacen[nombre] = cantidad
        print("Producto añadido.")
    elif opcion == "2":
        # Eliminamos el producto solo si está registrado.
        nombre = input("Nombre del producto que quieres borrar: ")
        if nombre in almacen:
            del almacen[nombre]
            print("Producto borrado.")
        else:
            print("El producto no está en el almacén.")
    elif opcion == "3":
        # Buscamos el producto y mostramos su cantidad si existe.
        nombre = input("Nombre del producto que quieres buscar: ")
        if nombre in almacen:
            print(f"{nombre}: {almacen[nombre]}")
        else:
            print("El producto no está en el almacén.")
    elif opcion == "4":
        # Mostramos todos los productos o avisamos si no hay ninguno.
        if almacen:
            for nombre, cantidad in almacen.items():
                print(f"{nombre}: {cantidad}")
        else:
            print("El almacén está vacío.")
    elif opcion == "5":
        # Terminamos el bucle y, con ello, el programa.
        print("Fin del programa.")
        break
    else:
        # Avisamos cuando la opción introducida no pertenece al menú.
        print("Opción no válida.")
