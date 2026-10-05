# TIENDA DE POCIONES

print("Tienda de pociones")

# Pedir datos al usuario con input (int si es necesario…)
nombre = input("¿Cuál es tu nombre? ")
monedas = int(input("¿Cuántas monedas tienes? "))
while monedas < 1 or monedas > 10:
    print("El número de monedas debe estar entre 1 y 10.")
    monedas = int(input("¿Cuántas monedas tienes? "))

# Mostrar menú
print("Tenemos las siguientes pociones:")
print("1. Poción de salud - 10 monedas")
print("2. Poción de magia - 5 monedas")
print("3. Poción de fuerza - 2 monedas")

# Elegir poción
eleccion = int(input("¿Cuál quieres comprar? (1-3): "))

# Decidir precio según la elección
if (eleccion == 1):
    precio = 10
elif eleccion == 2:
    precio = 5
elif eleccion == 3:
    precio = 2
else:
    print("No tenemos esa poción...Agur!")
    exit()

# Comprobar si tiene suficiente dinero
if monedas >= precio:
    print("¡Aquí tienes tu poción de", eleccion, "!") 
else:
    print("No te llega, ¡ahorra más monedas!")