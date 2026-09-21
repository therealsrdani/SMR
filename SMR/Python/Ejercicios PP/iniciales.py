# Ejercicio 1, pido variable y me das el valor de la variable
var1 = int(input("Dame un número: "))
print("El número que has escogido es: ", var1)
suma = var1 + 3
print("El resultado de la suma de", var1, "+ 3 es", suma, ".")

# Ejercicio 2, depende del numero x o y
num2 = int(input("Dame un número entre 0 y 10: "))
if 0 <= num2 <= 10:
    print("Es correcto")
else:
    print("Error")

# Ejercicio 3, Cadena de carácteres, pido nombre y edad y calculo el año de nacimiento
from datetime import datetime
nombre = input("Dame tu nombre: ")
edad = int(input("Dame tu edad: "))
fecha_actual = datetime.now()
año_nacimiento = fecha_actual.year - edad
print("Hola", nombre, ", naciste en el año", año_nacimiento, ".")

# Ejercicio 4, Par o impar
num4 = int(input("Dame un número: "))
if num4 % 2 == 0:
    print("El número", num4, "es par.")
else:
    print("El número", num4, "es impar.")

# Ejercicio 5, Números: par/impar y múltiplo de 3
num5 = int(input("Dame un número: "))
if num5 % 2 == 0:
    print("El número", num5, "es par.")
else:
    print("El número", num5, "es impar.")
if num5 % 3 == 0:
    print("El número", num5, "es múltiplo de 3.")
else:
    print("El número", num5, "no es múltiplo de 3.")

# Ejercicio 6, Conversor de temperatura. Crea una función
def celsius_a_fahrenheit(celsius):
    return (celsius * 9/5) + 32
temperatura = float(input("Introduce la temperatura: "))
unidad = input("¿Celsius(C) o Fahrenheit(F)?: ").strip().upper()
if unidad == "C":
    celsius = temperatura
    fahrenheit = celsius_a_fahrenheit(celsius)
elif unidad == "F":
    fahrenheit = temperatura
    celsius = (fahrenheit - 32) * 5/9
else:
    print("Unidad no válida. Usa C o F.")
    exit()
print(f"Temperatura: {celsius:g} °C | {fahrenheit:g} °F")

# Ejercicio 7, Calculadora simple
def calculadora(number1, number2, operacion):
    if operacion == "+":
        return number1 + number2
    elif operacion == "-":
        return number1 - number2
    elif operacion == "*":
        return number1 * number2
    elif operacion == "/":
        if number2 != 0:
            return number1 / number2
        else:
            return "Error: División por cero"
    else:
        return "Operación no válida"


number1 = float(input("Introduce el primer número: "))
number2 = float(input("Introduce el segundo número: "))
operacion = input("Elige una operación (+, -, *, /): ").strip()
resultado = calculadora(number1, number2, operacion)
print(f"El resultado de {number1:g} {operacion} {number2:g} es {resultado}")