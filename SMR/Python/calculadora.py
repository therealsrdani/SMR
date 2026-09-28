# Calculadora de descuentos
precio = float(input("Introduce el precio del artículo: "))
edad = int(input("Introduce tu edad: "))
bday = input("¿Es tu cumpleaños?: ").strip().lower()

descuento = 0
if edad < 18 or edad > 65:
	descuento = 15

if bday in ("s", "si", "sí", "y", "yes", "ye", "bai", "B", "b", "YES", "Y", "S", "SI", "SÍ"):
	descuento += 5

precio_final = precio * (1 - descuento / 100)

print(f"Precio original: {precio:.2f} €")
print(f"Descuento aplicado: {descuento}%")
print(f"Precio final a pagar: {precio_final:.2f} €")