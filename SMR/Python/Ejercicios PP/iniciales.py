# Ejercicio 1, pido variable y me das el valor de la variable
var1 = int(input("Dame un número: ")) # Pedimos un número
print("El número que has escogido es: ", var1) # Mostramos el numero que ha elegido
suma = var1 + 3 # Asignamos la suma del numero que nos da +3 y lo convertimos en la variable suma
print("El resultado de la suma de", var1, "+ 3 es", suma, ".") # Mostramos el resultado de la suma

# Ejercicio 2, depende del numero x o y
num2 = int(input("Dame un número entre 0 y 10: ")) # Pedimos un número
if 0 <= num2 <= 10: # Comprobamos si el número está entre 0 y 10
    print("Es correcto") # Mostramos que es correcto
else: # Si el número no está entre 0 y 10
    print("Error") # Mostramos el error

# Ejercicio 3, Cadena de carácteres, pido nombre y edad y calculo el año de nacimiento
from datetime import datetime # Metemos el datetime para poder calcular el año de nacimiento
nombre = input("Dame tu nombre: ") # Pedimos el nombre
edad = int(input("Dame tu edad: ")) # Pedimos la edad
fecha_actual = datetime.now() # Obtenemos la fecha actual
año_nacimiento = fecha_actual.year - edad # Hacemos la resta del año actual menos la edad
print("Hola", nombre, ", naciste en el año", año_nacimiento, ".") # Mostramos el nombre y el resultado del año de nacimiento

# Ejercicio 4, Par o impar
num4 = int(input("Dame un número: ")) # Pedimos un número
if num4 % 2 == 0: # Comprobamos si el número es par
    print("El número", num4, "es par.") # Mostramos que el número es par
else: # Si el número no es par, es impar
    print("El número", num4, "es impar.") # Mostramos que el número es impar

# Ejercicio 5, Números: par/impar y múltiplo de 3
num5 = int(input("Dame un número: ")) # Pedimos un número
if num5 % 2 == 0: # Comprobamos si el número es par
    print("El número", num5, "es par.") # Mostramos que el número es par
else: # Si el número no es par, es impar
    print("El número", num5, "es impar.") # Mostramos que el número es impar
if num5 % 3 == 0: # Comprobamos si el número es múltiplo de 3
    print("El número", num5, "es múltiplo de 3.") # Mostramos que el número es múltiplo de 3
else: # Si el número no es múltiplo de 3
    print("El número", num5, "no es múltiplo de 3.") # Mostramos que el número no es múltiplo de 3

# Ejercicio 6, Conversor de temperatura. Crea una función
def celsius_a_fahrenheit(celsius): # Creamos la función que convierte de Celsius a Fahrenheit
    return (celsius * 9/5) + 32 # Hacemos la conversión de Celsius a Fahrenheit
temperatura = float(input("Introduce la temperatura: ")) #  Pedimos la temperatura
unidad = input("¿Celsius(C) o Fahrenheit(F)?: ").strip().upper() # Pedimos la unidad de temperatura y la convertimos a mayúsculas
if unidad == "C": # Comprobamos si la unidad es Celsius
    celsius = temperatura # Asignamos la temperatura a la variable celsius
    fahrenheit = celsius_a_fahrenheit(celsius) # Hacemos la conversión de Celsius a Fahrenheit
elif unidad == "F": # Comprobamos si la unidad es Fahrenheit
    fahrenheit = temperatura # Asignamos la temperatura a la variable fahrenheit
    celsius = (fahrenheit - 32) * 5/9 # Hacemos la conversión de Fahrenheit a Celsius
else: # Si la unidad no es válida
    print("Unidad no válida. Usa C o F.") # Mostramos el error
    exit() # Salimos del programa
print(f"Temperatura: {celsius:g} °C | {fahrenheit:g} °F") # Mostramos la temperatura en ambas unidades

# Ejercicio 7, Calculadora simple
def calculadora(number1, number2, operacion): # Creamos la función que hace la calculadora
    if operacion == "+": # Comprobamos si la operación es suma
        return number1 + number2 # Hacemos la suma de los dos números
    elif operacion == "-": # Comprobamos si la operación es resta
        return number1 - number2 # Hacemos la resta de los dos números
    elif operacion == "*": # Comprobamos si la operación es multiplicación
        return number1 * number2 # Hacemos la multiplicación de los dos números
    elif operacion == "/": # Comprobamos si la operación es división
        if number2 != 0: # Comprobamos si el segundo número no es cero para evitar división por cero
            return number1 / number2 # Hacemos la división de los dos números
        else: # Si el segundo número es cero, mostramos un error
            return "Error: División por cero" # Mostramos el error
    else: # Si la operación no es válida, mostramos un error
        return "Operación no válida" # Mostramos el error
number1 = float(input("Introduce el primer número: ")) # Pedimos el primer número
number2 = float(input("Introduce el segundo número: ")) # Pedimos el segundo número
operacion = input("Elige una operación (+, -, *, /): ").strip() # Pedimos la operación y eliminamos espacios en blanco
resultado = calculadora(number1, number2, operacion) # Llamamos a la función calculadora con los números y la operación
print(f"El resultado de {number1:g} {operacion} {number2:g} es {resultado}") # Mostramos el resultado de la operación