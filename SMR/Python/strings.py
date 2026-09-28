# 1. Contar cuantas veces aparece una letra en una frase.
frase = input("1. Escribe una frase: ")
letra = input("Escribe una letra: ")
print("La letra aparece", frase.count(letra), "veces.")

# 2. Mostrar la frase en distintos formatos.
frase = input("\n2. Escribe una frase: ")
print("En mayusculas:", frase.upper())
print("En minusculas:", frase.lower())
print("Con la primera letra de cada palabra en mayuscula:", frase.title())

# 3. Comprobar la primera y la ultima letra de una palabra.
palabra = input("\n3. Escribe una palabra: ")
print('Empieza por "a":', palabra.startswith("a"))
print('Termina por "z":', palabra.endswith("z"))

# 4. Sustituir "malo" por "bueno".
frase = input("\n4. Escribe una frase que contenga  'malo' o 'bueno': ")
print(frase.replace("malo", "bueno"))

# 5. Separar las palabras por comas y quitar los espacios alrededor.
palabras = input("\n5. Escribe palabras separadas por comas: ")
lista_palabras = [palabra.strip() for palabra in palabras.split(",")]
print("Lista resultante:", lista_palabras)

# 6. Comprobar si el texto contiene solo letras o letras y numeros.
texto = input("\n6. Escribe un texto con o sin numeros: ")
print("Solo tiene letras:", texto.isalpha())
print("Solo tiene letras y numeros:", texto.isalnum())

# 7. Pedir un email y devolver su nombre y dominio por separado.
def pedir_nombre_y_dominio():
	email = input("\n7. Escribe tu email: ")
	nombre, dominio = email.split("@")
	return nombre, dominio


nombre_email, dominio_email = pedir_nombre_y_dominio()
print("Nombre:", nombre_email)
print("Dominio:", dominio_email)