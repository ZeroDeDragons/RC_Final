class_name Personagem
extends CharacterBody2D

const VELOCIDADE = 300.0

@onready var corpo: AnimatedSprite2D = $Anim

var virado_direita = true
var ultima_direcao = "Lado"  # Lado, Atras, Cima

func Movimento(direcao: Vector2):
	# Normaliza se necessário
	if direcao.length() > 1.0:
		direcao = direcao.normalized();
	
	# Aplica velocidade
	velocity = direcao * VELOCIDADE
	
	# Animação
	if direcao == Vector2.ZERO:
		# Parado
		if ultima_direcao == "Lado":
			corpo.flip_h = not virado_direita
		corpo.play("Parado-" + ultima_direcao)
		move_and_slide()
		return

	if direcao.y < 0:
		corpo.play("Cima")
		ultima_direcao = "Cima"
	# Vertical
	elif direcao.y > 0:
		corpo.play("Atras")
		ultima_direcao = "Atras"
	
		# Horizontal
	if direcao.x > 0:
		corpo.flip_h = false
		virado_direita = true
		corpo.play("Lado")
		ultima_direcao = "Lado"
	elif direcao.x < 0:
		corpo.flip_h = true
		virado_direita = false
		corpo.play("Lado")
		ultima_direcao = "Lado"
	# Move o personagem
	move_and_slide();
