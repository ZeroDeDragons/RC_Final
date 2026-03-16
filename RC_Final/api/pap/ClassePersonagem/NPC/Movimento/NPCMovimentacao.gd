class_name NPCMovimentacao

static func get_pontos(tipo_caminho, distancia_entre_pontos, vertices) -> Array[Vector2]:
	var pontos: Array[Vector2] = []
	var N = vertices
	var s = distancia_entre_pontos
	
	if N <= 0:
		return pontos
	
	match tipo_caminho:
		"Linha":
			if N == 1:
				pontos.append(Vector2.ZERO)
			else:
				# Centraliza a linha na origem
				var offset = (N - 1) * s / 2.0
				for i in range(N):
					var x = -offset + i * s
					pontos.append(Vector2(x, 0))
		
		"Circulo":
			if N == 1:
				pontos.append(Vector2.ZERO)
			else:
				# Raio calculado para que a corda entre pontos consecutivos seja s
				var raio = s / (2.0 * sin(PI / N))
				for i in range(N):
					var ang = i * 2.0 * PI / N
					pontos.append(Vector2(raio * cos(ang), raio * sin(ang)))
		
		"Quadrado":
			if N == 0:
				return pontos
			var perimetro = N * s
			var lado = perimetro / 4.0
			# Vértices do quadrado centrado, ordem anti‑horária: inferior esquerdo, inferior direito, superior direito, superior esquerdo
			var v0 = Vector2(-lado/2, -lado/2)
			var v1 = Vector2( lado/2, -lado/2)
			var v2 = Vector2( lado/2,  lado/2)
			var v3 = Vector2(-lado/2,  lado/2)
			var vertices_quad = [v0, v1, v2, v3]
			var comprimentos = [lado, lado, lado, lado]
			pontos = _distribuir_no_perimetro(vertices_quad, comprimentos, N, s)
		
		"Triangulo_Pe", "Triangulo_Ba":
			if N == 0:
				return pontos
			var perimetro = N * s
			var lado = perimetro / 3.0
			var altura = lado * sqrt(3.0) / 2.0
			var v0; var v1; var v2
			if tipo_caminho == "Triangulo_Pe":
				# Triângulo com base em baixo (pé) – vértice para cima
				v0 = Vector2(-lado/2, -altura/3)   # base esquerda
				v1 = Vector2( lado/2, -altura/3)   # base direita
				v2 = Vector2(0, 2 * altura / 3)    # vértice superior
			else: # Triangulo_Ba
				# Triângulo com base em cima – vértice para baixo
				v0 = Vector2(-lado/2,  altura/3)   # base esquerda
				v1 = Vector2( lado/2,  altura/3)   # base direita
				v2 = Vector2(0, -2 * altura / 3)   # vértice inferior
			var vertices_tri = [v0, v1, v2]
			var comprimentos = [lado, lado, lado]
			pontos = _distribuir_no_perimetro(vertices_tri, comprimentos, N, s)
	
	return pontos

static func _distribuir_no_perimetro(vertices: Array, comprimentos: Array, num_pontos: int, dist: float) -> Array[Vector2]:
	var pontos: Array[Vector2] = []
	if num_pontos == 0:
		return pontos
	
	var perimetro_total = 0.0
	for comp in comprimentos:
		perimetro_total += comp
	
	for i in range(num_pontos):
		var t = i * dist  # distância percorrida a partir do primeiro vértice
		var acumulado = 0.0
		for j in range(len(vertices)):
			var comp = comprimentos[j]
			if t < acumulado + comp or (j == len(vertices) - 1 and abs(t - perimetro_total) < 1e-6):
				# Estamos no lado j
				var frac = (t - acumulado) / comp
				var inicio = vertices[j]
				var fim = vertices[(j + 1) % len(vertices)]
				pontos.append(inicio.lerp(fim, frac))
				break
			acumulado += comp
	return pontos
