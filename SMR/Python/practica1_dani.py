# Practica 1 - Consola del ingeniero
# Autor: Dani
# Gestiona el estado de una nave, su inventario y algunas operaciones.
# Se utilizan variables, condicionales, bucles, funciones, cadenas y listas.

# Estado inicial de la nave
energia = 70
salud = 85
planeta_actual = "Órbita de Nexus-3"
nombre_nave = "EG1-B1D3"
alerta = False
inventario = ["kit reparación", "celda de energía"]


# Imprime una línea para separar las secciones del menú.
def linea():
    print("-" * 50)


# Muestra las opciones disponibles en la consola.
def mostrar_menu():
    linea()
    print("CONSOLA DEL INGENIERO — NAVE", nombre_nave)
    linea()
    print("1) Mostrar estado de la nave")
    print("2) Reparar escudos (consume energía)")
    print("3) Mostrar inventario")
    print("4) Agregar objeto al inventario")
    print("5) Usar o retirar un objeto")
    print("6) Explorar planeta")
    print("7) Probar el código de la puerta")
    print("8) Cambiar nombre de la nave")
    print("9) Escanear el área")
    print("10) Activar o desactivar la alerta")
    print("11) Salir")


# Muestra los datos actuales de la nave y de sus sistemas.
def mostrar_estado(energia_actual, salud_actual, planeta, alerta_activa):
    print("Energía:", energia_actual / 100, "| Salud de la tripulación:", salud_actual / 100)
    print("Ubicación:", planeta)
    if alerta_activa:
        print("Alerta activa: revisar sistemas")
    else:
        print("Sistemas estables")


# Repara los escudos si hay energía suficiente y devuelve la energía restante.
def reparar_escudos(energia_actual):
    coste = 15
    if energia_actual >= coste:
        print("Reparación en curso...")
        energia_actual = energia_actual - coste
        print("Escudos estabilizados. Energía -15.")
    else:
        print("Energía insuficiente. Conecta una celda o reduce consumo.")
    if energia_actual < 0:
        energia_actual = 0
    if energia_actual > 100:
        energia_actual = 100
    return energia_actual


# Muestra los objetos del inventario con números para poder seleccionarlos.
def mostrar_inventario(items):
    if len(items) == 0:
        print("[Inventario vacío]")
        return
    print("Inventario:")
    for indice, objeto in enumerate(items, start=1):
        print(indice, "-", objeto)


# Pide un nombre, lo normaliza y evita objetos vacíos o repetidos.
def agregar_objeto(items):
    objeto = input("Nombre del objeto que quieres añadir: ").strip().lower()
    if len(objeto) == 0:
        print("El nombre no puede estar vacío.")
    else:
        repetido = False
        for elemento in items:
            if elemento.lower() == objeto:
                repetido = True
        if repetido:
            print("Ese objeto ya está en el inventario.")
        else:
            items.append(objeto)
            print("Objeto añadido:", objeto)
    mostrar_inventario(items)


# Retira del inventario un objeto indicado por su número o por su nombre.
def usar_objeto(items):
    if len(items) == 0:
        print("[Inventario vacío]")
        return
    mostrar_inventario(items)
    seleccion = input("Indica el número o el nombre del objeto: ").strip()
    if seleccion.isdigit():
        indice = int(seleccion)
        if indice >= 1 and indice <= len(items):
            objeto = items.pop(indice - 1)
            print("Has usado o retirado:", objeto)
        else:
            print("No hay ningún objeto con ese número.")
    else:
        encontrado = False
        for objeto in items:
            if objeto.lower() == seleccion.lower():
                items.remove(objeto)
                print("Has usado o retirado:", objeto)
                encontrado = True
                break
        if not encontrado:
            print("Ese objeto no está en el inventario.")
    mostrar_inventario(items)


# Simula una exploración y permite guardar un objeto encontrado.
def explorar_planeta(planeta, items):
    if planeta == "Órbita de Nexus-3":
        print("La superficie de Nexus-3 está cubierta de cráteres.")
    else:
        print("Comienza la exploración de", planeta)
    respuesta = input("¿Has encontrado algún objeto? (s/n): ").strip().lower()
    if respuesta == "s":
        agregar_objeto(items)
    elif respuesta == "n":
        print("La exploración termina sin nuevos objetos.")
    else:
        print("Respuesta no válida. Usa s o n.")


# Comprueba el código de acceso de la puerta de la nave.
def hackear_puerta():
    codigo = input("Introduce el código de acceso: ").strip().lower()
    if codigo == "nexus3":
        print("Código correcto. Puerta desbloqueada.")
    else:
        print("Código incorrecto. La puerta sigue cerrada.")


# Solicita un nuevo nombre y lo devuelve para actualizar el estado de la nave.
def cambiar_nombre_nave():
    nombre = input("Nuevo nombre para la nave: ").strip()
    if len(nombre) > 0:
        print("Nave renombrada a", nombre)
        return nombre
    print("Nombre inválido. Se conserva el nombre actual.")
    return nombre_nave


# Recorre tres sectores e informa del resultado del escaneo.
def escanear_area():
    for sector in range(1, 4):
        print("Escaneando sector", sector, "...")
    print("Anomalías: 0 | Recursos: 1 (posible celda de energía)")


# Cambia el estado de la alerta y devuelve su nuevo valor.
def cambiar_alerta(alerta_actual):
    if alerta_actual:
        print("Alerta desactivada. Sistemas estables.")
        return False
    print("Alerta activada. Revisa los sistemas de la nave.")
    return True


# Bucle principal: lee la opción y ejecuta la acción correspondiente.
while True:
    mostrar_menu()
    opcion = input("> ").strip()

    if opcion == "1":
        mostrar_estado(energia, salud, planeta_actual, alerta)
    elif opcion == "2":
        energia = reparar_escudos(energia)
    elif opcion == "3":
        mostrar_inventario(inventario)
    elif opcion == "4":
        agregar_objeto(inventario)
    elif opcion == "5":
        usar_objeto(inventario)
    elif opcion == "6":
        explorar_planeta(planeta_actual, inventario)
    elif opcion == "7":
        hackear_puerta()
    elif opcion == "8":
        nombre_nave = cambiar_nombre_nave()
    elif opcion == "9":
        escanear_area()
    elif opcion == "10":
        alerta = cambiar_alerta(alerta)
    elif opcion == "11":
        print("Cerrando consola. Buen viaje.")
        break
    else:
        print("Opción inválida. Elige del 1 al 11.")
