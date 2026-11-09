# Consola del Ingeniero – EG1-B1D3 (versión base)
# Autor/a: <Tu nombre>
# Descripción: Menú interactivo para gestionar sistemas básicos de una nave.
# Objetivos: Completa las funciones marcadas como TODO y añade tus propias funciones.

# ---------------------
# Variables de estado
# ---------------------
energia = 70            # Valores posibles: 0–100
salud = 85              # Valores posibles: 0–100
planeta_actual = "Órbita de Nexus-3" # Información para dar contexto a la práctica
alerta = False          # True si hay incidente
inventario = ["kit reparación", "celda de energía"]  # Lista

# ---------------------
# Funciones auxiliares: describe con comentarios lo que hace cada función y para qué se usa
# ---------------------

def linea():
    print("".center(50,"-"))


def mostrar_menu():
    linea()
    print("CONSOLA DEL INGENIERO — NAVE EG1-B1D3")
    linea()
    print("1) Mostrar estado de la nave")
    print("2) Reparar escudos (consume energía)")
    print("3) Mostrar inventario")
    print("4) Agregar objeto al inventario -- (En desarrollo)")
    print("5) Usar/retirar objeto -- (En desarrollo)")
    print("6) Explorar planeta -- (En desarrollo)")
    print("7) Hackear puerta -- (En desarrollo)")
    print("8) Función por desarrollar  -- (Función nueva)")
    print("9) Función por desarrollar -- (Función nueva)")
    print("10) Función por desarrollar -- (Función nueva)")
    print("11) Salir")


def mostrar_estado(energia, salud, planeta, alerta):
    print("Energía: ", (energia/100), " | Salud tripulación: ", (salud/100))
    print("Ubicación:", planeta)
    if (alerta):
        print("Alerta activa: revisar sistemas")
    else:
        print("Sistemas estables")


def reparar_escudos(energia_actual):
    """
    ESTA FUNCIÓN YA ESTÁ EXPLICADA, NO HAY QUE VOLVERLA A EXPLICAR
    Simula una reparación de escudos:
    --> Si hay suficiente energía (energía_actual), aumenta la estabilidad (no utilizamos 'escudos' como variable por simplificar).
    --> Consume 15 de energía; se controla que no baja de 0 ni supera 100.
    Devuelve la nueva energía (RETURN) sea la que sea (controlado por 2 if).
    """
    coste = 15
    if (energia_actual >= coste):
        print("Reparación en curso...")
        energia_actual = energia_actual - coste
        print("Escudos estabilizados. Energía -15.")
    else:
        print("Energía insuficiente. Conecta una celda o reduce consumo.")
    if (energia_actual < 0):
        energia_actual = 0
    if (energia_actual > 100):
        energia_actual = 100
    return energia_actual


def mostrar_inventario(items):
    if (len(items) == 0):
        print("[Inventario vacío]")
        return
    print("Inventario:")
    # Recorremos la lista con 'for' y 'enumerate' para mostrar índices (pero usando enumerate, empezamos por el 1)
    for i, objeto in enumerate(items, start=1):
        print(f"  {i}. {objeto}") # Veremos f-strings en la siguiente evaluación, son más cómodas de utilizar


# ---------------------
# TODO: Funciones a implementar para la práctica
# ---------------------

def agregar_objeto(inventario):
    """Pide al usuario un nombre de objeto y lo añade a la lista inventario.
    Requisitos mínimos:
    - Usar métodos de string: .strip() y .lower()/ .title() para normalizar
    - Validar que no esté vacío ni duplicado (puedes usar 'in' en listas)
    - Mostrar el inventario actualizado al final --> puedes llamar a la función mostrar_inventario(inventario)
    """
    print("En desarrollo")  # TODO: Implementar


def usar_objeto(inventario):
    """Permite retirar un objeto del inventario.
    Requisitos mínimos:
    - Mostrar el inventario con índices --> puedes llamar a la función mostrar_inventario(inventario)
    - Solicitar número o nombre del objeto
    - Eliminarlo si existe (p.ej., .pop(indice-1) o .remove(nombre)) o del(...)
    - Mensajes claros si no se encuentra
    """
    print("En desarrollo")  # TODO: Implementar


def explorar_planeta(planeta, inventario):
    """Simula una exploración sencilla.
    Ideas:
    - Preguntar si se ha encontrado algo (s/n)
    - Si la respuesta es 's', pedir nombre del objeto y añadirlo al inventario (llama a la función agregar_objeto)
    - Usar condicionales según el argumento 'planeta' para mostrar texto distinto
    """
    print("En desarrollo")  # TODO: Implementar


def hackear_puerta():
    """Mini‑juego textual muy simple. Crea lo que se te ocurra, sin complicaciones
    Ideas:
    - Pedir un 'código' al usuario y comprobarlo con condicionales
    - Usar métodos de string para comparar (lower/strip)
    - Mensajes de éxito/fracaso
    """
    print("En desarrollo")  # TODO: Implementar


# ---------------------
# IMPLEMENTACIÓN DE NUEVAS FUNCIONES (Nota: 9) --> Implementa 3 funciones nuevas y agrégalas al menú principal.
# Ejemplos de ideas (elige las tuyas):
#   - cambiar_nombre_nave()
#   - escanear_area()
#   - registrar_evento(registro) usando otra lista
#   - recargar_energia(inventario)
#   - curar_tripulacion()
#   - establecer_alerta(on/off)
# Integra tus funciones en la opción 8 del menú.
# ---------------------

# Ejemplos muy sencillos como ejemplo, para que te sirvan de referencia para implementar los tuyos (no uses estos)

def cambiar_nombre_nave():
    nombre = input("Nuevo nombre para la nave: ").strip()
    if (len(nombre) > 0):
        print("¡Nave renombrada a", nombre, "!")
    else:
        print("Nombre inválido.")


def escanear_area():
    for paso in range(1, 4):
        print("Escaneando sector", paso, "...")
    print("Anomalías: 0 | Recursos: 1 (posible celda de energía)")


# ---------------------
# Bucle principal (menú)
# ---------------------
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
        agregar_objeto(inventario)  # TODO

    elif opcion == "5":
        usar_objeto(inventario)     # TODO

    elif opcion == "6":
        explorar_planeta(planeta_actual, inventario)  # TODO

    elif opcion == "7":
        hackear_puerta()            # TODO

    elif opcion == "8":
        # Crea la función y llámala desde aqui´. Es una de las funciones para llegar al 9
        print("Función por desarrollar")    #TODO

    elif opcion == "9":
        # Crea la función y llámala desde aqui´. Es una de las funciones para llegar al 9
        print("Función por desarrollar")    #TODO

    elif opcion == "10":
        # Crea la función y llámala desde aqui´. Es una de las funciones para llegar al 9
        print("Función por desarrollar")    #TODO

    elif opcion == "11":
        print("Cerrando consola. Buen viaje.")
        break

    else:
        print("Opción inválida. Elige del 1 al 11.")

#FIN