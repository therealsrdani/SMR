# Este programa determina qué personaje de Marvel eres según tus respuestas.
print("Responde a estas tres preguntas para descubrir qué personaje de Marvel eres.")
# Pedimos al usuario que responda a las preguntas con input y guardamos sus respuestas en variables.
poder = input("¿Qué prefieres: fuerza, inteligencia o tecnología? (f/i/t) ").strip().casefold()
# Ponemos bucle para que si o si la respuesta sea bien f, i o t.
while poder not in ("f", "i", "t"):
    print("Respuesta inválida. Por favor, elige 'f', 'i' o 't'.")
    poder = input("¿Qué prefieres: fuerza, inteligencia o tecnología? (f/i/t) ").strip().casefold()
# Pedimos al usuario que responda a las preguntas con input y guardamos sus respuestas en variables.
cualidad = input("¿Qué prefieres: lider, solitario o aliado? (l/s/a) ").strip().casefold()
# Ponemos bucle para que si o si la respuesta sea bien l, s o a.
while cualidad not in ("l", "s", "a"):
    print("Respuesta inválida. Por favor, elige 'l', 's' o 'a'.")
    cualidad = input("¿Qué prefieres: lider, solitario o aliado? (l/s/a) ").strip().casefold()
# Pedimos al usuario que responda a las preguntas con input y guardamos sus respuestas en variables.
objetivo = input("¿Qué te mueve: deber o venganza? (d/v) ").strip().casefold()
# Ponemos bucle para que si o si la respuesta sea bien d o v.
while objetivo not in ("d", "v"):
    print("Respuesta inválida. Por favor, elige 'd' o 'v'.")
    objetivo = input("¿Qué te mueve: deber o venganza? (d/v) ").strip().casefold()
# Creamos un diccionario con las combinaciones de respuestas y los personajes correspondientes.
resultados = {
    ("f", "l", "d"): "Eres el Capitán América.",
    ("f", "s", "v"): "Eres la Capitana Marvel.",
    ("i", "l", "d"): "Eres Iron Man.",
    ("i", "s", "v"): "Eres Viuda Negra.",
    ("t", "a", "d"): "Eres Spider-Man.",
    ("t", "s", "v"): "Eres Black Panther.",
}
# Mostramos el resultado según las respuestas del usuario. Si no coincide con resultados sacará una frase default.
print(resultados.get(
    (poder, cualidad, objetivo),
    "Eres un gran agente del S.H.I.E.L.D.",
))