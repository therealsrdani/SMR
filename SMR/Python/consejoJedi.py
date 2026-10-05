# Mostramos las preguntas a través de variables y pedimos la respuesta al usuario con input.
atraccion = input("¿Te atrae el Lado Oscuro? (s/n): ").strip().casefold()
preferencia = input("¿Prefieres usar la Fuerza o un bláster? (f/b): ").strip().casefold()
lider = input("¿Te consideras un líder? (s/n): ").strip().casefold()
# Configuramos el mensaje dependiendo de las respuestas del if y las mostramos con print.
if atraccion in ("s"):
    print("¡Eres un Sith!")
elif atraccion == "n" and preferencia == "f":
    print("¡Eres un Jedi!")
elif preferencia in ("b"):
    print("¡Eres un piloto rebelde!")
elif atraccion == "n" and lider in ("s"):
    print("¡Eres parte de la Alianza Rebelde!")
else:
    print("¡Eres un droide muy útil para nuestra causa!")
