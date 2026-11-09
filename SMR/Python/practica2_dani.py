# Practica 2 - Estación oceanográfica Pelagia-3
# Autor: Dani
# Una adaptación de la consola del ingeniero para explorar y mantener
# una estación submarina.
# Se utilizan variables, condicionales, bucles, funciones, cadenas y listas.

# Estado inicial de la estación
oxigeno = 65
salud = 90
zona_actual = "Arrecife Azul"
nombre_estacion = "Pelagia-3"
alerta = False
inventario = ["botella de oxígeno", "kit médico"]


# Imprime una línea para separar las secciones del menú.
def linea():
    print("-" * 50)


# Muestra las opciones para gestionar la estación submarina.
def mostrar_menu():
    linea()
    print("CENTRO DE CONTROL SUBMARINO —", nombre_estacion)
    linea()
    print("1) Mostrar estado de la estación")
    print("2) Recargar el oxígeno")
    print("3) Mostrar suministros")
    print("4) Añadir un suministro")
    print("5) Usar o retirar un suministro")
    print("6) Explorar la zona submarina")
    print("7) Desbloquear la compuerta")
    print("8) Cambiar nombre de la estación")
    print("9) Sondear el fondo marino")
    print("10) Activar o desactivar la alerta")
    print("11) Salir")


# Muestra los recursos, la salud, la zona y el estado de la alerta.
def mostrar_estado(oxigeno_actual, salud_actual, zona, alerta_activa):
    print("Oxígeno:", oxigeno_actual / 100, "| Salud del equipo:", salud_actual / 100)
    print("Zona:", zona)
    if alerta_activa:
        print("Alerta activa: comprueba la estación.")
    else:
        print("Estación en estado normal.")


# Repone oxígeno consumiendo una botella del inventario.
def recargar_oxigeno(oxigeno_actual, items):
    botella_encontrada = False
    for objeto in items:
        if objeto.lower() == "botella de oxígeno":
            items.remove(objeto)
            botella_encontrada = True
            break
    if botella_encontrada:
        oxigeno_actual = oxigeno_actual + 30
        if oxigeno_actual > 100:
            oxigeno_actual = 100
        print("Botella utilizada. Oxígeno actual:", oxigeno_actual / 100)
    else:
        print("No quedan botellas de oxígeno en los suministros.")
    return oxigeno_actual


# Muestra los suministros numerados.
def mostrar_inventario(items):
    if len(items) == 0:
        print("[No quedan suministros]")
        return
    print("Suministros:")
    for indice, objeto in enumerate(items, start=1):
        print(indice, "-", objeto)


# Añade un suministro si el nombre es válido y todavía no existe.
def agregar_suministro(items):
    objeto = input("Nombre del suministro que quieres añadir: ").strip().lower()
    if len(objeto) == 0:
        print("El nombre no puede estar vacío.")
    else:
        repetido = False
        for elemento in items:
            if elemento.lower() == objeto:
                repetido = True
        if repetido:
            print("Ese suministro ya está guardado.")
        else:
            items.append(objeto)
            print("Suministro añadido:", objeto)
    mostrar_inventario(items)


# Usa el suministro seleccionado; las botellas y los kits tienen un efecto.
def usar_suministro(items):
    if len(items) == 0:
        print("[No quedan suministros]")
        return
    mostrar_inventario(items)
    seleccion = input("Indica el número o el nombre del suministro: ").strip()
    objeto_elegido = ""
    if seleccion.isdigit():
        indice = int(seleccion)
        if indice >= 1 and indice <= len(items):
            objeto_elegido = items.pop(indice - 1)
    else:
        for objeto in items:
            if objeto.lower() == seleccion.lower():
                objeto_elegido = objeto
                items.remove(objeto)
                break

    if len(objeto_elegido) == 0:
        print("No se encontró ese suministro.")
    elif objeto_elegido.lower() == "botella de oxígeno":
        print("La botella se usa para respirar fuera de la estación.")
    elif objeto_elegido.lower() == "kit médico":
        print("El kit médico queda reservado para atender al equipo.")
    else:
        print("Suministro utilizado:", objeto_elegido)
    mostrar_inventario(items)


# Simula una inmersión y permite guardar un suministro encontrado.
def explorar_zona(zona, items):
    if zona == "Arrecife Azul":
        print("El arrecife está lleno de vida marina.")
    else:
        print("Comienza la inmersión en", zona)
    respuesta = input("¿Has encontrado un suministro? (s/n): ").strip().lower()
    if respuesta == "s":
        agregar_suministro(items)
    elif respuesta == "n":
        print("La inmersión termina sin nuevos suministros.")
    else:
        print("Respuesta no válida. Usa s o n.")


# Pide el código de seguridad para abrir la compuerta submarina.
def desbloquear_compuerta():
    codigo = input("Introduce el código de la compuerta: ").strip().lower()
    if codigo == "pelagia":
        print("Código correcto. Compuerta desbloqueada.")
    else:
        print("Código incorrecto. La compuerta sigue cerrada.")


# Cambia el nombre de la estación y devuelve el nuevo nombre.
def cambiar_nombre_estacion():
    nombre = input("Nuevo nombre para la estación: ").strip()
    if len(nombre) > 0:
        print("Estación renombrada a", nombre)
        return nombre
    print("Nombre inválido. Se conserva el nombre actual.")
    return nombre_estacion


# Recorre tres puntos de sondeo y comunica el resultado.
def sondear_fondo():
    for punto in range(1, 4):
        print("Sondeando punto", punto, "...")
    print("Resultado: fondo estable y posibles restos de un naufragio.")


# Alterna la alerta de la estación y devuelve su nuevo estado.
def cambiar_alerta(alerta_actual):
    if alerta_actual:
        print("Alerta desactivada. La estación vuelve a la normalidad.")
        return False
    print("Alerta activada. Comprueba oxígeno y presión.")
    return True


# Bucle principal del centro de control.
while True:
    mostrar_menu()
    opcion = input("> ").strip()

    if opcion == "1":
        mostrar_estado(oxigeno, salud, zona_actual, alerta)
    elif opcion == "2":
        oxigeno = recargar_oxigeno(oxigeno, inventario)
    elif opcion == "3":
        mostrar_inventario(inventario)
    elif opcion == "4":
        agregar_suministro(inventario)
    elif opcion == "5":
        usar_suministro(inventario)
    elif opcion == "6":
        explorar_zona(zona_actual, inventario)
    elif opcion == "7":
        desbloquear_compuerta()
    elif opcion == "8":
        nombre_estacion = cambiar_nombre_estacion()
    elif opcion == "9":
        sondear_fondo()
    elif opcion == "10":
        alerta = cambiar_alerta(alerta)
    elif opcion == "11":
        print("Cerrando estación. Hasta la próxima inmersión.")
        break
    else:
        print("Opción inválida. Elige del 1 al 11.")
