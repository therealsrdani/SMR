correos = {}
correo = input("Escribe un correo o 'fin' para terminar: ").strip()

while correo.lower() != "fin":
    if "@" in correo and "." in correo:
        usuario, dominio = correo.split("@", 1)
        correos[usuario] = dominio
        print("Correo guardado.")
    else:
        print("El correo no es válido.")

    correo = input("Escribe otro correo o 'fin' para terminar: ").strip()

contador_egibide = 0
print("\nCorreos guardados:")
for usuario, dominio in correos.items():
    print(usuario, "→", dominio)
    if dominio.lower() == "egibide.org":
        contador_egibide += 1

print("Correos de egibide.org:", contador_egibide)